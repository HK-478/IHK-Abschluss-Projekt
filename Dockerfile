FROM php:8.4-apache

# 1. System-Pakete und Python installieren
RUN apt-get update && apt-get install -y \
    python3 python3-pip python3-venv libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. PHP SQLite aktivieren
RUN docker-php-ext-install pdo pdo_sqlite

# 3. Python-Umgebung einrichten
RUN python3 -m venv /opt/venv
ENV PATH="/opt/venv/bin:$PATH"
RUN pip install requests kubernetes ldap3 python-dotenv

# 4. Apache konfigurieren
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf
RUN a2enmod rewrite

# 5. DEINE DATEIEN (INKLUSIVE VENDOR) KOPIEREN
COPY . /var/www/html

# 6. Rechte setzen
RUN chown -R www-data:www-data /var/www/html
