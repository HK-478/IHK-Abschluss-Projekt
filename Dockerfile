# Wir nutzen das offizielle PHP-Apache Image
FROM php:8.3-apache

# 1. System-Abhängigkeiten & Python installieren
RUN apt-get update && apt-get install -y \
    python3 python3-pip python3-venv \
    libsqlite3-dev zip unzip git \
    && rm -rf /var/lib/apt/lists/*

# 2. PHP-Erweiterungen für Laravel
RUN docker-php-ext-install pdo pdo_sqlite

# 3. Python-Umgebung einrichten (venv ist in neuen Python-Versionen Pflicht)
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install requests kubernetes ldap3 python-dotenv

# 4. Apache konfigurieren (Laravel braucht DocumentRoot im /public Ordner)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN a2enmod rewrite

# 5. Laravel-Code kopieren
COPY . /var/www/html

# 6. Rechte setzen (Wichtig für SQLite und Laravel-Logs)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
