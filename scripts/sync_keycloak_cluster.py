import json
import sys
from datetime import datetime

import requests
import urllib3
import json
import os
import keycloak
import kubernetes
from keycloak import KeycloakOpenID
from ldap3 import Server, Connection, ALL
from kubernetes import client, config


# --- Konfiguration aus .env laden ---
load_dotenv() # Sucht automatisch nach einer .env Datei im gleichen Ordner

NAMESPACE = os.getenv("NAMESPACE", "opendesk")

# Keycloak Config
KEYCLOAK_CLIENT_ID = os.getenv("KEYCLOAK_CLIENT_ID")
KEYCLOAK_CLIENT_SECRET = os.getenv("KEYCLOAK_CLIENT_SECRET")
KEYCLOAK_LABEL = "app.kubernetes.io/name=keycloak"

# LDAP Config
LDAP_LABEL = "app.kubernetes.io/name=ldap-server"
LDAP_SEARCH_BASE = os.getenv("LDAP_SEARCH_BASE")
LDAP_USER = os.getenv("LDAP_USER")
LDAP_PASSWORD = os.getenv("LDAP_PASSWORD")

# --- TLS Warnungen Unterdruecken ---
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)


def get_pod_ip(namespace, label_selector):
    """Holt die IP-Adresse eines laufenden Pods anhand seines Labels."""
    try:
        config.load_kube_config(config_file="/etc/rancher/k3s/k3s.yaml")
        v1 = client.CoreV1Api()
        pods = v1.list_namespaced_pod(namespace=namespace, label_selector=label_selector)

        if not pods.items:
            raise Exception(f"Kein Pod mit Label '{label_selector}' im Namespace '{namespace}' gefunden.")

        for pod in pods.items:
            if pod.status.phase == "Running":
                return pod.status.pod_ip

        raise Exception(f"Pod gefunden, läuft aber aktuell nicht (Label: {label_selector}).")

    except Exception as e:
        print(f"Fehler beim Abrufen der Pod-IP: {e}")
        return None


def get_admin_token(keycloak_ip):
    """Holt das Admin-Token direkt ueber die Keycloak REST-API."""
    url = f"http://{keycloak_ip}:8080/realms/{NAMESPACE}/protocol/openid-connect/token"
    data = {
        "grant_type": "client_credentials",
        "client_id": KEYCLOAK_CLIENT_ID,
        "client_secret": KEYCLOAK_CLIENT_SECRET
    }

    response = requests.post(url, data=data, verify=False)
    response.raise_for_status()
    return response.json()["access_token"]


def get_users(ldap_ip):
    """Holt alle Benutzer aus dem Nubus-LDAP."""
    server = Server(ldap_ip, get_info=ALL)

    try:
        conn = Connection(server, user=LDAP_USER, password=LDAP_PASSWORD, auto_bind=True)
        base = LDAP_SEARCH_BASE
    except Exception as e:
        print(f"Standard LDAP-Bind fehlgeschlagen, versuche Fallback... ({e})")
        # Automatischer Fallback
        base = "dc=swp,dc=de"
        alt_user = f"cn=admin,{base}"
        conn = Connection(server, user=alt_user, password=LDAP_PASSWORD, auto_bind=True)

    search_filter = "(objectClass=univentionMail)"
    conn.search(
        search_base=base,
        search_filter=search_filter,
        attributes=['uid', 'title', 'displayname', 'mailPrimaryAddress', 'employeeType', 'userPassword']
    )

    users = []
    for entry in conn.entries:
        users.append({
            "username": str(entry.uid),
            "anrede": str(entry.title) if hasattr(entry, 'title') else "Divers",
            "nachname": str(entry.displayname),
            "email": str(entry.mailPrimaryAddress),
            "typ": str(entry.employeeType) if hasattr(entry, 'employeeType') else "N/A",
            "pw": str(entry.userPassword)
        })

    conn.unbind()
    # Nur User zurueckgeben, die auch einen Nachnamen haben
    return [u for u in users if u.get("nachname") and u["nachname"] != "[]"]


def get_sessions(keycloak_ip, token, target_client_id):
    """Ermittelt alle aktiven Sitzungen fuer einen bestimmten Client in Keycloak."""
    url = f"http://{keycloak_ip}:8080/admin/realms/{NAMESPACE}/clients"
    headers = {"Authorization": f"Bearer {token}"}

    all_clients = requests.get(url, headers=headers, verify=False).json()
    client_internal_id = None

    for c in all_clients:
        if target_client_id.lower() in c.get("clientId", "").lower():
            client_internal_id = c.get("id")
            break

    if not client_internal_id:
        print(f"Warnung: Client '{target_client_id}' nicht in Keycloak gefunden.")
        return []

    sessions_url = f"http://{keycloak_ip}:8080/admin/realms/{NAMESPACE}/clients/{client_internal_id}/user-sessions"
    return requests.get(sessions_url, headers=headers, verify=False).json()

def main():
    try:
        # IPs abrufen (ohne print, da es sonst das JSON zerstört)
        keycloak_ip = get_pod_ip(NAMESPACE, KEYCLOAK_LABEL)
        ldap_ip = get_pod_ip(NAMESPACE, LDAP_LABEL)

        if not keycloak_ip or not ldap_ip:
            # Fehler werden in den Error-Stream geschrieben, damit PHP sie ignorieren kann
            sys.stderr.write("Abbruch: Konnte nicht alle IPs aus dem Cluster ermitteln.\n")
            sys.exit(1)

        # Daten einsammeln
        token = get_admin_token(keycloak_ip)
        active_sessions = get_sessions(keycloak_ip, token, target_client_id="opendesk-nextcloud")
        active_usernames = {session.get("username") for session in active_sessions if session.get("username")}

        ldap_users = get_users(ldap_ip)

        # Daten für Laravel vorbereiten
        export_data = []

        for user in ldap_users:
            is_online = user["username"] in active_usernames

            # Wir bereiten exakt die Felder vor, die deine Laravel-Migration erwartet
            export_data.append({
                "username": user["username"],
                "email": user["email"],
                "password_hash": user["pw"],
                "role": user["typ"],  # Wird in Laravel zur Rolle (z.B. 'admin' oder 'user')
                "anrede": user["anrede"],  # Optional, falls du es im Profil anzeigen willst
                "nachname": user["nachname"],  # Optional, z.B. für "Max Mustermann"
                "is_online": is_online,
                "last_seen_at": datetime.now().isoformat() if is_online else None
            })

        # WICHTIG: Das ist die einzige reguläre Ausgabe! PHP fängt genau dieses JSON auf.
        print(json.dumps(export_data, indent=2))

    except Exception as e:
        sys.stderr.write(f"Schwerer Fehler im Skript: {e}\n")
        sys.exit(1)


if __name__ == "__main__":
    main()
