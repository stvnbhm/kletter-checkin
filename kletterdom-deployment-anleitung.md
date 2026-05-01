# Kletterdom Check-in – Deployment Anleitung
**Docker · Raspberry Pi 4 · HTTPS · Lokales Netzwerk**

---

## Voraussetzungen

### Hardware & Betriebssystem
- Raspberry Pi 4 (min. 4 GB RAM empfohlen)
- MicroSD-Karte ≥ 32 GB (Class 10 / A2)
- Netzteil 5V / 3A USB-C
- LAN-Kabel (stabiler als WLAN)
- Raspberry Pi OS Lite 64-bit **oder** Ubuntu Server 24.04 LTS (arm64)
- SSH aktiviert (via Raspberry Pi Imager → Erweiterte Optionen)

### Netzwerk
- Feste lokale IP am Router reservieren (DHCP-Reservierung per MAC-Adresse)
- MAC-Adresse abrufen: `ip link show eth0`
- Ziel-IP Beispiel: `192.168.178.54`

### Erforderliche Software am Pi
Nur **Docker** und **Git** – PHP, Node.js und MySQL laufen alle im Container.

---

## Schritt 1: SSH & Grundeinrichtung

```bash
# Per SSH verbinden
ssh pi@192.168.x.x

# Docker installieren
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Neu einloggen damit Docker-Gruppe aktiv wird
exit
```

Wieder per SSH einloggen, dann:

```bash
sudo apt update && sudo apt install -y git

# Hostname setzen (für kletterdom.local im Netzwerk)
sudo hostnamectl set-hostname kletterdom
sudo apt install -y avahi-daemon
sudo systemctl enable avahi-daemon
```

---

## Schritt 2: Repository klonen

```bash
git clone <REPO-URL> kletter-checkin
cd kletter-checkin
```

---

## Schritt 3: Konfigurationsdateien

### 3.1 docker-compose.yml

```yaml
services:

  app:
    build: .
    image: kletter-checkin
    container_name: kletter-app
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy    # wartet bis MySQL wirklich bereit ist
    networks:
      - kletternet
    environment:
      - TZ=Europe/Vienna
    # KEIN volumes-Mount auf den App-Root! vendor/ würde sonst überschrieben.
    volumes:
      - ./backups:/var/www/html/storage/backups   # nur Backup-Unterordner

  nginx:
    image: nginx:alpine
    container_name: kletter-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./docker/ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - kletternet

  db:
    image: mysql:8.0
    container_name: kletter-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: klettercheckin
      MYSQL_USER: checkinuser
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - kletternet
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 5

  node:
    image: node:20-alpine
    working_dir: /var/www
    volumes:
      - .:/var/www
    profiles: ["build"]

volumes:
  dbdata:

networks:
  kletternet:
    driver: bridge
```

### 3.2 Dockerfile

```dockerfile
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    default-mysql-client \
    libpng-dev libonig-dev libxml2-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer VOR composer install (wichtig!)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --optimize

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

> `default-mysql-client` stellt `mysqldump` im Container bereit – wird für automatische Backups benötigt.

### 3.3 Nginx-Konfiguration (`docker/nginx.conf`)

```nginx
server {
    listen 80;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name _;

    ssl_certificate     /etc/nginx/ssl/nginx.crt;
    ssl_certificate_key /etc/nginx/ssl/nginx.key;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 3.4 Backup-Command (`app/Console/Commands/DatabaseBackup.php`)

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature   = 'backup:database';
    protected $description = 'MySQL Datenbank-Backup erstellen';

    public function handle(): void
    {
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $date     = now()->format('Y-m-d_H-i-s');
        $filename = "kletterdom_{$date}.sql.gz";
        $path     = "{$backupDir}/{$filename}";

        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');

        $command = "mysqldump -h {$host} -u {$user} -p{$password} {$db} | gzip > {$path}";
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup fehlgeschlagen!');
            \Log::error('DB Backup fehlgeschlagen', ['exit' => $exitCode]);
            return;
        }

        // Backups älter als 30 Tage löschen
        collect(glob("{$backupDir}/*.sql.gz"))
            ->filter(fn($f) => filemtime($f) < now()->subDays(30)->timestamp)
            ->each(fn($f) => unlink($f));

        $this->info("✅ Backup gespeichert: {$filename}");
        \Log::info('DB Backup erfolgreich', ['file' => $filename]);
    }
}
```

### 3.5 Schedule registrieren (`routes/console.php`)

```php
use App\Console\Commands\DatabaseBackup;

Schedule::command(DatabaseBackup::class)->dailyAt('03:00');
```

---

## Schritt 4: SSL-Zertifikat erstellen

```bash
mkdir -p docker/ssl

openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
  -keyout docker/ssl/nginx.key \
  -out docker/ssl/nginx.crt \
  -subj "/CN=192.168.x.x" \
  -addext "subjectAltName=IP:192.168.x.x,DNS:kletterdom.local"
```

Die IP `192.168.x.x` durch die tatsächliche Pi-IP ersetzen. Das Zertifikat gilt 10 Jahre.

---

## Schritt 5: .env anlegen

```bash
cp .env.example .env
nano .env
```

```env
APP_NAME="Kletterdom Check-in"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://192.168.x.x

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=klettercheckin
DB_USERNAME=checkinuser
DB_PASSWORD=SicheresPasswort!
DB_ROOT_PASSWORD=SicheresRootPw!

APP_KEY=                            # wird in Schritt 7 generiert

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Schritt 6: Frontend-Assets bauen

```bash
docker compose --profile build run --rm node sh -c "npm install && npm run build"
```

Beim ersten Mal dauert das 3–8 Minuten auf dem Pi.

---

## Schritt 7: Container starten & Laravel einrichten

```bash
# Container starten
docker compose up -d --build

# .env in Container kopieren + Berechtigungen setzen
docker compose cp .env app:/var/www/html/.env
docker compose exec app chown www-data:www-data /var/www/html/.env

# App-Key generieren
docker compose exec app php artisan key:generate

# .env mit generiertem Key zurückholen
docker compose cp app:/var/www/html/.env .env

# Datenbank migrieren
docker compose exec app php artisan migrate --force

# Storage-Link
docker compose exec app php artisan storage:link

# Cache aktivieren
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## Schritt 8: Admin-User anlegen

```bash
docker compose exec app php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@kletterdom.at',
    'password' => bcrypt('SicheresAdminPasswort!'),
]);
$user->is_admin = 1;
$user->save();
exit
```

`is_admin` muss separat gesetzt werden – es ist bewusst nicht in `$fillable` (Sicherheitsmaßnahme gegen Mass Assignment).

---

## Schritt 9: Autostart aktivieren

```bash
sudo systemctl enable docker
```

Durch `restart: unless-stopped` starten alle Container nach jedem Reboot automatisch.

---

## Schritt 10: Automatische Backups einrichten

Backups laufen über den **Laravel Scheduler** und werden täglich um 03:00 Uhr erstellt.  
Die `.sql.gz`-Dateien landen direkt im Projektordner unter `kletter-checkin/backups/` auf dem Pi.

### Cron-Job einrichten (einmalig)

```bash
crontab -e
```

Zeile einfügen:

* * * * * cd /home/pi/kletter-checkin && docker compose exec -T app php artisan schedule:run >> /var/log/kletterdom-scheduler.log 2>&1


> Dieser eine Cron-Job reicht – der Laravel Scheduler übernimmt ab dann alle zeitgesteuerten Aufgaben (Backups, Auto-Checkout etc.).

### Backup manuell testen

```bash
# Backup sofort auslösen
docker compose exec app php artisan backup:database

# Ergebnis prüfen
ls -lh backups/
```

### Backup wiederherstellen (Notfall)

```bash
gunzip < backups/kletterdom_2026-05-01_03-00-00.sql.gz | \
  docker compose exec -T db mysql -u checkinuser -pSicheresPasswort! klettercheckin
```

---

## App aufrufen

| URL | Beschreibung |
|---|---|
| `https://192.168.x.x` | Direkt per IP |
| `https://kletterdom.local` | Per Hostname (alle Geräte im Netzwerk) |

Beim ersten Aufruf zeigt der Browser eine Zertifikatswarnung (Self-Signed) – einmalig pro Gerät bestätigen.

---

## Update deployen

```bash
cd kletter-checkin

git pull
docker compose --profile build run --rm node sh -c "npm run build"
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

echo "Update fertig!"
```

---

## Häufige Befehle

| Aktion | Befehl |
|---|---|
| Status prüfen | `docker compose ps` |
| App neu starten | `docker compose restart app` |
| Alles stoppen | `docker compose down` |
| Alles starten | `docker compose up -d` |
| Laravel-Logs | `docker compose exec app tail -f storage/logs/laravel.log` |
| Alle Logs live | `docker compose logs -f` |
| In DB einloggen | `docker compose exec db mysql -u checkinuser -p klettercheckin` |
| Artisan ausführen | `docker compose exec app php artisan <befehl>` |
| Backup manuell | `docker compose exec app php artisan backup:database` |
| Backup-Dateien | `ls -lh backups/` |
| Mitglieder aus DB entfernen | `docker compose exec app php artisan tinker --execute="DB::table('members')->truncate(); echo 'Done‘;“` |

---

## Troubleshooting

| Problem | Lösung |
|---|---|
| 500 Error | `docker compose exec app tail -50 storage/logs/laravel.log` |
| `vendor/autoload.php` fehlt | `volumes: .:/var/www/html` beim app-Service entfernen, neu bauen |
| `composer: not found` im Build | `COPY --from=composer:latest` muss **vor** `RUN composer install` stehen |
| `.env` fehlt im Container | `docker compose cp .env app:/var/www/html/.env` |
| `APP_KEY` fehlt | `echo "APP_KEY=" >> .env` dann `key:generate` |
| Permission denied auf `.env` | `docker compose exec app chown www-data:www-data /var/www/html/.env` |
| DB nicht erreichbar | `docker compose ps` – ist `kletter-db` healthy? |
| Assets fehlen / CSS kaputt | Node-Build-Schritt wiederholen |
| `is_admin` wird nicht gesetzt | `$user->is_admin = 1; $user->save();` statt über `create()` |
| Pi sehr langsam beim ersten Build | Normal – Docker-Layer werden danach gecacht |
| Backup fehlgeschlagen | `storage/logs/laravel.log` prüfen; `mysqldump` vorhanden? `docker compose exec app which mysqldump` |

---

*Kletterdom Check-in System · Deployment Guide · Stand Mai 2026*