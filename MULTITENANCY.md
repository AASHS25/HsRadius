# Rencana Arsitektur Multi-Tenant HsRadius

Dokumen ini merancang transformasi HsRadius dari **single-tenant** menjadi
**multi-tenant SaaS** (1 aplikasi melayani banyak operator/ISP yang berbagi
infrastruktur), memakai strategi **single database + `tenant_id` (row-level)**.

---

## 1. Tujuan & Lingkup

- Satu deployment melayani banyak tenant (ISP) yang datanya **terisolasi**.
- Tiap tenant punya pelanggan, paket, NAS, voucher, invoice sendiri.
- Ada **super-admin (landlord)** yang mengelola tenant + langganan.
- Tiap tenant punya **admin & operator** sendiri (role per-tenant).
- Tenant ditagih langganan (billing tenant) dan bisa di-**suspend**.

## 2. Kondisi Saat Ini

100% single-tenant: tidak ada `tenant_id`, tidak ada role, semua query global,
satu skema RADIUS dipakai bersama. (Lihat `README`/diskusi.)

## 3. Tantangan Khusus: FreeRADIUS (paling krusial)

FreeRADIUS mengautentikasi berdasarkan kolom `username` di tabel `radcheck`.
Dua tenant bisa punya pelanggan dengan username sama → **tabrakan**. Selain itu
FreeRADIUS membaca tabel RADIUS **langsung via SQL**, bukan lewat aplikasi
Laravel, jadi scoping aplikasi saja tidak cukup.

**Strategi terpilih (terimplementasi): username & groupname global-unik.**
`customers.username` sudah unik global dan groupname RADIUS = `pkg-{id}`
(id paket unik global), jadi **tidak mungkin tabrakan** dan **FreeRADIUS tidak
perlu diubah** — autentikasi tetap benar. Multi-tenant di lapisan RADIUS murni
soal *scoping di aplikasi*:

- `tenant_id` ditambahkan ke tabel yang ditulis aplikasi (`radcheck`,
  `radreply`, `radgroupcheck`, `radgroupreply`, `radusergroup`) + trait
  `BelongsToTenant` (RadiusService mengisi otomatis).
- `radacct` & `radpostauth` (ditulis FreeRADIUS) di-*scope* saat dibaca lewat
  username ∈ pelanggan tenant (`BelongsToTenantViaUsername`).

Konsekuensi: username pelanggan unik **lintas semua tenant** (trade-off demi
kesederhanaan & nol perubahan FreeRADIUS). Bila kelak butuh username sama
antar-tenant, baru beralih ke scoping berbasis NAS + namespacing (Alternatif).

Alternatif (didokumentasikan, tidak dipakai): **database-per-tenant**
(`stancl/tenancy`) — isolasi paling kuat, tapi FreeRADIUS harus menunjuk banyak
DB (1 instance per tenant / routing kompleks) → beban operasional tinggi.

## 4. Identifikasi Tenant

| Konteks | Cara |
|---|---|
| Panel web | **Akun user yang login** → `users.tenant_id` (satu domain untuk semua tenant, tanpa subdomain) |
| API/MikroTik (RADIUS) | **NAS/Client IP** → `nas.tenant_id` |

## 5. Komponen yang Dibangun

1. **Tabel `tenants`** + model `Tenant` (nama, slug, domain, status, plan).
2. **Role**: `super_admin` (landlord), `admin` (tenant), `operator` (tenant).
3. **`BelongsToTenant` trait + `TenantScope`** (global scope) + auto-isi
   `tenant_id` saat create. Diterapkan ke semua model milik tenant.
4. **`CurrentTenant`** (singleton) + **middleware `ResolveTenant`** (set tenant
   dari **user yang login**; super-admin tanpa tenant = akses lintas-tenant).
5. **Panel Landlord**: CRUD tenant, kelola langganan, suspend/aktifkan.
6. **Billing tenant**: paket langganan SaaS, invoice ke tenant, auto-suspend
   bila nunggak (terpisah dari billing pelanggan milik tenant).
7. **FreeRADIUS tenant-aware**: `tenant_id` di tabel RADIUS + query ber-scope.

## 6. Roadmap Fase

| Fase | Isi | Status |
|---|---|---|
| **1. Fondasi** | Tabel `tenants`, `Tenant` model, `users.tenant_id`+`role`, trait+scope, `CurrentTenant`, seeder super-admin+tenant demo | ✅ **Selesai (PR ini)** |
| **2. Isolasi data app** | `tenant_id` di `customers/packages/vouchers/invoices/nas`, trait `BelongsToTenant`, middleware `ResolveTenant` (dari user login), auto-scope query | ✅ **Selesai (PR ini)** |
| **3. Role & akses** | Gate super-admin/admin/operator, route ber-`can:`, menu sidebar ber-`@can`, seeder operator | ✅ **Selesai (PR ini)** |
| **4. RADIUS tenant-aware** | `tenant_id` + scope di tabel provisioning RADIUS; `radacct`/`radpostauth` ter-scope via username. Username global-unik → FreeRADIUS tak diubah | ✅ **Selesai (PR ini)** |
| **5. Panel Landlord** | UI super-admin: CRUD tenant + onboarding (buat tenant + admin), suspend/aktifkan | ✅ **Selesai (PR ini)** |
| **6. Billing tenant** | Paket SaaS (CRUD), tagihan tenant (buat/lunas), auto-suspend (command harian) + blokir login, limit pelanggan per paket | ✅ **Selesai (PR ini)** |

## 7. Perubahan Skema DB (ringkas)

`tenant_id` (FK → `tenants.id`) ditambahkan ke:
`users, customers, packages, vouchers, invoices, nas, radcheck, radreply,
radusergroup, radgroupcheck, radgroupreply`. Tabel `radacct` & `radpostauth`
di-scope via username pelanggan (tanpa kolom `tenant_id`).

`super_admin` memiliki `tenant_id = NULL` (lintas-tenant).

## 8. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| **Kebocoran data antar-tenant** (query lupa scope) | Global scope default + uji otomatis per model + code review |
| **Tabrakan username RADIUS** | Scoping NAS→tenant + namespacing username |
| **Query lintas-tenant super-admin** | `CurrentTenant` null = tanpa filter, hanya untuk role super_admin |
| **Migrasi data existing** | Backfill: semua data lama → tenant default pertama |

## 9. Catatan Implementasi Fase 1 (PR ini)

- `TenantScope` **hanya memfilter bila** `CurrentTenant` ter-set, sehingga
  CLI/seeder/login tetap berjalan (tanpa konteks tenant = tanpa filter).
- Trait `BelongsToTenant` sudah tersedia tapi **belum dipasang** ke model bisnis
  (itu Fase 2, setelah middleware penyetel tenant diuji) agar app tetap stabil.
- `users` **tidak** memakai global scope (auth login by email bersifat sentral).
