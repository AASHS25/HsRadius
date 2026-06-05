# HsRadius - RADIUS Management System

Aplikasi manajemen RADIUS untuk ribuan pelanggan Hotspot dan PPPoE dengan NAS MikroTik.

## Fitur Utama

- **Dashboard** - Statistik real-time, grafik traffic & pendapatan
- **Manajemen Pelanggan** - CRUD lengkap untuk pelanggan Hotspot & PPPoE
- **Paket Layanan** - Manajemen bandwidth dengan burst, quota, dan limitasi
- **NAS/Router** - Multi-router MikroTik dengan API integration
- **Voucher System** - Generate, print, dan manajemen voucher
- **Sesi Aktif** - Monitoring real-time dan disconnect user
- **Invoice & Billing** - Pembuatan invoice dan tracking pembayaran
- **Laporan** - Traffic, pendapatan, dan statistik pelanggan

## Tech Stack

- **Backend**: Laravel 12 (PHP 8.3+)
- **Database**: MySQL 8.0
- **RADIUS**: FreeRADIUS 3.x
- **NAS**: MikroTik RouterOS API
- **Frontend**: Bootstrap 5, Chart.js

## Instalasi dengan Docker

```bash
# Clone repository
git clone <repo-url> hsradius
cd hsradius

# Copy environment file
cp .env.example .env

# Jalankan Docker
docker compose up -d

# Generate app key
docker compose exec app php artisan key:generate

# Jalankan migrasi database
docker compose exec app php artisan migrate

# Seed data contoh (opsional)
docker compose exec app php artisan db:seed

# Akses di browser
# http://localhost
```

## Instalasi Manual

### Prasyarat
- PHP 8.3+
- MySQL 8.0+
- FreeRADIUS 3.x
- Composer

```bash
# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Edit .env sesuai konfigurasi database Anda
nano .env

# Jalankan migrasi
php artisan migrate

# Seed data contoh (opsional)
php artisan db:seed

# Jalankan server
php artisan serve
```

## Login Default

- **Email**: admin@hsradius.local
- **Password**: admin123

## Konfigurasi FreeRADIUS

FreeRADIUS harus dikonfigurasi untuk menggunakan SQL module yang terhubung ke database yang sama.
File konfigurasi contoh tersedia di `docker/freeradius/`.

### Koneksi MikroTik ke RADIUS

```
/radius
add address=<RADIUS_SERVER_IP> secret=<RADIUS_SECRET> service=hotspot,ppp
```

### Hotspot dengan RADIUS
```
/ip hotspot profile
set default use-radius=yes radius-accounting=yes
```

### PPPoE dengan RADIUS
```
/ppp aaa
set use-radius=yes accounting=yes
```

## Struktur Database

### Tabel FreeRADIUS (Standar)
- `radcheck` - Atribut autentikasi user
- `radreply` - Atribut reply ke NAS
- `radgroupcheck` - Atribut cek grup
- `radgroupreply` - Atribut reply grup (bandwidth limit)
- `radusergroup` - Mapping user ke grup
- `radacct` - Data accounting (session)
- `radpostauth` - Log post-authentication
- `nas` - Daftar NAS/Router

### Tabel Aplikasi
- `customers` - Data pelanggan
- `packages` - Paket layanan
- `vouchers` - Voucher hotspot
- `invoices` - Invoice/tagihan

## Lisensi

MIT License
