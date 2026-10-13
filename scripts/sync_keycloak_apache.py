import json
import sys
import os
from datetime import datetime
import requests
import urllib3
from dotenv import load_dotenv
from ldap3 import Server, Connection, ALL

# --- Konfiguration aus .env laden ---
load_dotenv()

NAMESPACE = os.getenv("NAMESPACE", "opendesk")

# Externe Endpunkte (Statt Kubernetes-Labels)
KEYCLOAK_URL = os.getenv("KEYCLOAK_URL") # z.B. https://id.conix.at
LDAP_HOST = os.getenv("LDAP_HOST")       # z.B. 192.168.75.40 (Die IP des k3s-Servers)
LDAP_PORT = int(os.getenv("LDAP_PORT", "389")) # Standard LDAP-Port

# Keycloak Config
KEYCLOAK_CLIENT_ID = os.getenv("KEYCLOAK_CLIENT_ID")
KEYCLOAK_CLIENT_SECRET = os.getenv("KEYCLOAK_CLIENT_SECRET")

# LDAP Config
LDAP_SEARCH_BASE = os.getenv("LDAP_SEARCH_BASE")
LDAP_USER = os.getenv("LDAP_USER")
LDAP_PASSWORD = os.getenv("LDAP_PASSWORD")

# --- TLS Warnungen Unterdruecken ---
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

def get_admin_token(base_url):
    """Holt das Admin-Token direkt ueber die externe Keycloak REST-API."""
    url = f"{base_url}/realms/{NAMESPACE}/protocol/openid-connect/token"
    data = {
        "grant_type": "client_credentials",
        "client_id": KEYCLOAK_CLIENT_ID,
        "client_secret": KEYCLOAK_CLIENT_SECRET
    }

    response = requests.post(url, data=data, verify=False)
    response.raise_for_status()
    return response.json()["access_token"]


def get_users(ldap_host, ldap_port):
    """Holt alle Benutzer aus dem Nubus-LDAP ueber die externe IP."""
    server = Server(ldap_host, port=ldap_port, get_info=ALL)

    try:
        conn = Connection(server, user=LDAP_USER, password=LDAP_PASSWORD, auto_bind=True)
        base = LDAP_SEARCH_BASE
    except Exception as e:
        sys.stderr.write(f"Standard LDAP-Bind fehlgeschlagen, versuche Fallback... ({e})\n")
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
    return [u for u in users if u.get("nachname") and u["nachname"] != "[]"]


def get_sessions(base_url, token, target_client_id):
    """Ermittelt alle aktiven Sitzungen fuer einen bestimmten Client ueber die externe Keycloak-API."""
    url = f"{base_url}/admin/realms/{NAMESPACE}/clients"
    headers = {"Authorization": f"Bearer {token}"}

    all_clients = requests.get(url, headers=headers, verify=False).json()
    client_internal_id = None

    for c in all_clients:
        if target_client_id.lower() in c.get("clientId", "").lower():
            client_internal_id = c.get("id")
            break

    if not client_internal_id:
        sys.stderr.write(f"Warnung: Client '{target_client_id}' nicht in Keycloak gefunden.\n")
        return []

    sessions_url = f"{base_url}/admin/realms/{NAMESPACE}/clients/{client_internal_id}/user-sessions"
    return requests.get(sessions_url, headers=headers, verify=False).json()


def main():
    try:
        if not KEYCLOAK_URL or not LDAP_HOST:
            sys.stderr.write("Abbruch: KEYCLOAK_URL und LDAP_HOST muessen in der .env gesetzt sein.\n")
            sys.exit(1)

        # Daten einsammeln
        token = get_admin_token(KEYCLOAK_URL)
        active_sessions = get_sessions(KEYCLOAK_URL, token, target_client_id="opendesk-nextcloud")
        active_usernames = {session.get("username") for session in active_sessions if session.get("username")}

        ldap_users = get_users(LDAP_HOST, LDAP_PORT)

        # Daten für Laravel vorbereiten
        export_data = []

        for user in ldap_users:
            is_online = user["username"] in active_usernames
            export_data.append({
                "username": user["username"],
                "email": user["email"],
                "password_hash": user["pw"],
                "role": user["typ"],
                "anrede": user["anrede"],
                "nachname": user["nachname"],
                "is_online": is_online,
                "last_seen_at": datetime.now().isoformat() if is_online else None
            })

        # Ausgabe fuer PHP
        print(json.dumps(export_data, indent=2))

    except Exception as e:
        sys.stderr.write(f"Schwerer Fehler im Skript: {e}\n")
        sys.exit(1)

if __name__ == "__main__":
    main()
