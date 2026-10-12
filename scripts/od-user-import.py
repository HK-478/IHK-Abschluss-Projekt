import requests
import urllib3
import json
import os
import keycloak
import kubernetes
from keycloak import KeycloakOpenID
from ldap3 import Server, Connection, ALL
from kubernetes import client, config


# --- TLS Warnungen Unterdruecken ---
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

# --- Globale User Arrays ---
USERS = []
ACTIVE_USERS = []

# --- Globale Configuration ---
NAMESPACE = "opendesk"

# --- Keycloak Configuration ---
KEYCLOAK_IP = ""
keycloak_openid = KeycloakOpenID (
    server_url=KEYCLOAK_IP,
    client_id="mein-admin-client",
    client_secret_key="mldPJc2kaenOQuhNxfmfVZilHCGhm1WM",
    realm_name=NAMESPACE,
)
KEYCLOAK_LABEL = "app.kubernetes.io/name=keycloak"        # Das Label aus deinen Pod-Metadaten (app.kubernetes.io/name=keycloak)

# --- LDAP Configuration ---
LDAP_IP = "" # Interne Cluster IP
LDAP_LABEL = "app.kubernetes.io/name=ldap-server"
LDAP_SEARCH_BASE = "dc=swp-ldap,dc=internal"
LDAP_USER = f"cn=admin,{LDAP_SEARCH_BASE}"
LDAP_PASSWORD = "f425a7fa62f25556dffc16f77a2a95868957f0a1"


def get_pod_ip(namespace="default", label_selector="app=keycloak"):
    try:
        # Lädt die k3s-Konfiguration (benötigt Leserechte auf /etc/rancher/k3s/k3s.yaml)
        config.load_kube_config(config_file="/etc/rancher/k3s/k3s.yaml")
        print(label_selector)

        v1 = client.CoreV1Api()

        # Liste der Pods im Namespace anhand des Labels abfragen
        pods = v1.list_namespaced_pod(namespace=namespace, label_selector=label_selector)

        if not pods.items:
            return "Kein passender Keycloak-Pod im Cluster gefunden."

        # Nimm den ersten gefundenen Pod, der aktuell läuft
        for pod in pods.items:
            if pod.status.phase == "Running":
                pod_name = pod.metadata.name
                pod_ip = pod.status.pod_ip
                print(f"Gefundener aktiver Pod: {pod_name}")
                return pod_ip

        return f"{pod.metadata.name} wurde gefunden, läuft aber aktuell nicht."

    except Exception as e:
        return f"Fehler beim Abrufen der Pod-IP: {e}"

def get_admin_token():
    url = f"http://{KEYCLOAK_IP}:8080/realms/{keycloak_openid.realm_name}/protocol/openid-connect/token"
    data = {
        "grant_type": "client_credentials",
        "client_id": keycloak_openid.client_id,
        "client_secret": keycloak_openid.client_secret_key
    }
    response = requests.post(url, data=data)
    response.raise_for_status()
    return response.json()["access_token"]

def get_users():
    server = Server(LDAP_IP, get_info=ALL)

    try:
        conn = Connection(server, user=LDAP_USER, password=LDAP_PASSWORD, auto_bind=True)
        base = LDAP_SEARCH_BASE
    except Exception:
        # Automatischer Fallback auf die ältere Entwickler-Domain dc=swp,dc=de
        alt_search_base = "dc=swp,dc=de"
        alt_user = f"cn=admin,{alt_search_base}"
        conn = Connection(server, user=alt_user, password=LDAP_PASSWORD, auto_bind=True)
        base = alt_search_base

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
            "anrede": str(entry.title) if hasattr(entry, 'title') else "Divers", # Muss geprueft werden
            "nachname": str(entry.displayname),
            "email": str(entry.mailPrimaryAddress),
            "typ": str(entry.employeeType) if hasattr(entry, 'employeeType') else "N/A", # Muss geprueft werden
            "pw": str(entry.userPassword)

        })
    conn.unbind()
    return [u for u in users if u["nachname"]]

def get_sessions(token, target_client_id):
    url = f"http://{KEYCLOAK_IP}:8080/admin/realms/opendesk/clients"
    headers = {"Authorization": f"Bearer {token}"}

    all_clients = requests.get(url, headers=headers).json()
    client_internal_id = None

    for c in all_clients:
        if target_client_id in c.get("clientId", "").lower():
            client_internal_id = c.get("id")
            break

    if not client_internal_id:
        return []

    sessions_url = f"http://{KEYCLOAK_IP}:8080/admin/realms/opendesk/clients/{client_internal_id}/user-sessions"
    return requests.get(sessions_url, headers=headers).json()

def main():
    global USERS
    global KEYCLOAK_IP
    global LDAP_IP
    try:
        print("Hole Keycloak Pod")
        KEYCLOAK_IP = get_pod_ip(namespace=NAMESPACE, label_selector=KEYCLOAK_LABEL)
        print("✅ IP erfolgreich abgerufen!\n")
        print(KEYCLOAK_IP, "\n")

        print("Hole LDAP Pod")
        print(LDAP_LABEL)
        LDAP_IP = get_pod_ip(namespace=NAMESPACE, label_selector=LDAP_LABEL)
        print("✅ IP erfolgreich abgerufen!\n")
        print(LDAP_IP, "\n")

        print("Hole Keycloak Admin Token...")
        token = get_admin_token()
        print("✅ Token erfolgreich abgerufen!\n")

        print("Frage aktive Sessions ab...")
        active_sessions = get_sessions(token, target_client_id="opendesk-nextcloud")
        active_usernames = {session["username"] for session in active_sessions}
        print(f"✅ {len(active_usernames)} aktive Sitzung(en) ermittelt.\n")

        print("Frage alle Benutzer aus Nubus-LDAP ab...")
        USERS = get_users()
        print(f"✅ {len(USERS)} Benutzer im Verzeichnis gefunden.\n")

        print("--- Benutzerstatus ---")
        for user in USERS:
            username = user["username"]
            anrede = user["anrede"]
            nachname = user["nachname"]
            email = user["email"]
            position = user["typ"]
            password = user["pw"]

            is_online = username in active_usernames
            status_str = "🟢 ONLINE" if is_online else "❌ OFFLINE"

            print(f"User: {anrede} {nachname} ({email}) -> {status_str} | {position} | {password}")



    except Exception as e:
        print(f"\n❌ Fehler aufgetreten: {e}")

if __name__ == "__main__":
    main()
