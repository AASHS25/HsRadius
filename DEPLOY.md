# Panduan Deploy HsRadius ke VPS Linux

Panduan produksi untuk HsRadius (Laravel 13 + PHP 8.5). Ada dua jalur:
**A) Docker Compose (disarankan)** dan **B) Manual (LEMP)**.

> ⚠️ **Wajib sebelum deploy:** jalankan `php artisan migrate` akan membuat
> tabel `users`, `sessions`, `cache`, `jobs` (sudah disediakan) + tabel RADIUS.
> Tanpa tabel ini aplikasi tidak bisa login. Pastikan migrasi sukses.

---

## Prasyarat VPS

- Ubuntu 22.04/24.04 (atau distro setara), minimal 2 vCPU / 2 GB RAM.
- Domain mengarah ke IP VPS (mis. `panel.domainmu.com`) untuk HTTPS.
- Port terbuka: `80`, `443` (web), `1812/udp` + `1813/udp` (RADIUS).
- Akses MikroTik dari VPS (API port `8728`/`8729`) jika pakai sinkronisasi router.

---

## A. Deploy dengan Docker Compose (disarankan)

### 1. Install Docker
```bash
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER && newgrp docker
```

### 2. Ambil kode & siapkan `.env`
```bash
git clone <repo-url> hsradius && cd hsradius
cp .env.example .env
```

Edit `.env` untuk **produksi** (lihat checklist hardening di bawah). Minimal:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://panel.domainmu.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=radius
DB_USERNAME=radius
DB_PASSWORD=<password-kuat>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```
> Samakan `DB_*` dengan kredensial di `docker-compose.yml` (atau ubah keduanya).

### 3. Build & jalankan
```bash
docker compose up -d --build
```

### 4. Inisialisasi aplikasi (sekali jalan)
```bash
# vendor: bind-mount menutupi vendor dari image, jadi install di dalam container
docker compose exec app composer install --no-dev --optimize-autoloader

docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force      # opsional: data contoh + admin

# bangun ulang cache setelah APP_KEY terisi
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Akses: `http://IP_VPS` → login `admin@hsradius.local` / `admin123`
**(segera ganti password ini!)**.

> **Gotcha penting:** service `app` di `docker-compose.yml` mem-bind-mount
> `.:/var/www/html`, sehingga folder `vendor` & `.env` yang dipakai adalah
> milik host/container, bukan hasil `COPY` di image. Karena itu langkah
> `composer install` & `key:generate` di atas wajib dijalankan.

---

## B. Deploy Manual (LEMP, tanpa Docker)

```bash
# PHP 8.5 + ekstensi (repo ondrej)
sudo add-apt-repository ppa:ondrej/php -y && sudo apt update
sudo apt install -y php8.5-fpm php8.5-mysql php8.5-mbstring php8.5-xml \
  php8.5-bcmath php8.5-curl php8.5-gd php8.5-zip php8.5-intl \
  nginx mysql-server git unzip

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Aplikasi
git clone <repo-url> /var/www/hsradius && cd /var/www/hsradius
./install.sh                 # script bawaan: composer install + key + migrate + cache
sudo chown -R www-data:www-data storage bootstrap/cache
```
Buat MySQL DB `radius` + user, isi `.env`, lalu arahkan nginx `root` ke
`/var/www/hsradius/public` (pakai `docker/nginx/default.conf` sebagai contoh,
ganti `fastcgi_pass` ke `unix:/run/php/php8.5-fpm.sock`).

---

## 🔐 Checklist Hardening Produksi (WAJIB)

- [ ] `APP_DEBUG=false` dan `APP_ENV=production` di `.env`.
- [ ] `APP_KEY` sudah di-generate (`php artisan key:generate`).
- [ ] Ganti **password admin default** (`admin123`) setelah login pertama.
- [ ] Ganti password MySQL `root` & `radius` di `docker-compose.yml` + `.env`.
- [ ] Ganti **RADIUS secret** (`testing123`) di `.env` dan konfigurasi MikroTik.
- [ ] Jangan ekspos port `3306` MySQL ke publik (hapus mapping `ports: 3306` di compose).
- [ ] Aktifkan **HTTPS** (lihat di bawah).
- [ ] Firewall: `ufw allow 80,443/tcp` + `1812,1813/udp`, sisanya tutup.
- [ ] Backup terjadwal: `mysqldump` DB `radius` (berisi pelanggan + accounting).

---

## 🌐 HTTPS / SSL (Let's Encrypt)

`docker/nginx/default.conf` saat ini hanya `listen 80`. Cara termudah: pasang
**Caddy** atau **nginx + certbot** di host sebagai reverse proxy ke port app.

Contoh cepat dengan Caddy (otomatis SSL):
```bash
sudo apt install -y caddy
# /etc/caddy/Caddyfile
panel.domainmu.com {
    reverse_proxy 127.0.0.1:80
}
sudo systemctl restart caddy
```
(Map port `80:80` nginx container ke `127.0.0.1:8080:80` agar tidak bentrok,
lalu `reverse_proxy 127.0.0.1:8080`.)

Setelah HTTPS aktif, set `APP_URL=https://...` dan tambahkan
`URL::forceScheme('https')` bila perlu di `AppServiceProvider`.

---

## 📡 FreeRADIUS + MikroTik

- Service `freeradius` di compose sudah terhubung ke DB yang sama (tabel
  `radcheck`, `radreply`, `radacct`, dst. dibuat oleh migrasi).
- Pastikan `docker/freeradius/mods-enabled/sql` memakai kredensial DB yang benar.
- Di MikroTik:
  ```
  /radius add address=<IP_VPS> secret=<RADIUS_SECRET> service=hotspot,ppp
  /ip hotspot profile set default use-radius=yes radius-accounting=yes
  /ppp aaa set use-radius=yes accounting=yes
  ```

---

## 🔁 Update Aplikasi

```bash
git pull
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache && \
docker compose exec app php artisan route:cache && \
docker compose exec app php artisan view:cache
```

---

## 🏢 Catatan untuk SaaS / Sewa (multi-tenant)

HsRadius saat ini **single-tenant** (1 instalasi = 1 operator). Untuk
disewakan, pendekatan paling praktis adalah **instance-per-pelanggan**:

- Satu `docker compose` project + database + subdomain per klien
  (`klienA.panel.com`, `klienB.panel.com`).
- Isolasi data & RADIUS bersih (cocok karena tiap ISP punya NAS sendiri).
- Otomasi: jadikan folder ini template, parameterkan `.env` + nama project
  (`docker compose -p klienA ...`) dan terbitkan subdomain via Caddy.

Untuk **multi-tenant sejati** (1 aplikasi, banyak tenant berbagi) perlu
pengembangan tambahan (lihat diskusi di chat / issue): paket tenancy,
scoping `tenant_id`, role per-tenant, dan strategi FreeRADIUS per-tenant.
