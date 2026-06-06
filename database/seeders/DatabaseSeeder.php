<?php

namespace Database\Seeders;

use App\Models\Nas;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Voucher;
use App\Services\RadiusService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Super-admin (landlord) — manages all tenants, tenant_id = null
        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@hsradius.local',
            'password' => Hash::make('admin123'),
            'role' => 'super_admin',
        ]);

        // Demo tenant + its admin (Phase 1 scaffold)
        $demoTenant = \App\Models\Tenant::create([
            'name' => 'Demo ISP',
            'slug' => 'demo',
            'status' => 'active',
            'plan' => 'pro',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'Admin Demo ISP',
            'email' => 'admin@demo.local',
            'password' => Hash::make('admin123'),
            'tenant_id' => $demoTenant->id,
            'role' => 'admin',
        ]);

        // From here on, all seeded data belongs to the demo tenant.
        app(\App\Tenancy\CurrentTenant::class)->set($demoTenant);

        // Create sample NAS
        $nas = Nas::create([
            'nasname' => '192.168.1.1',
            'shortname' => 'MikroTik-Main',
            'type' => 'mikrotik',
            'ports' => 1812,
            'secret' => 'radius_secret',
            'description' => 'Router MikroTik Utama',
            'api_host' => '192.168.1.1',
            'api_port' => 8728,
            'api_username' => 'admin',
            'api_password' => '',
            'is_active' => true,
        ]);

        // Create sample packages
        $packages = [
            [
                'name' => 'Hotspot 3 Mbps',
                'type' => 'hotspot',
                'rate_up' => 1024,
                'rate_down' => 3072,
                'price' => 50000,
                'validity_type' => 'limited',
                'validity_value' => 30,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'pool_name' => 'hotspot-pool',
                'description' => 'Paket Hotspot 3 Mbps / 30 Hari',
            ],
            [
                'name' => 'Hotspot 5 Mbps',
                'type' => 'hotspot',
                'rate_up' => 2048,
                'rate_down' => 5120,
                'price' => 75000,
                'validity_type' => 'limited',
                'validity_value' => 30,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'pool_name' => 'hotspot-pool',
                'description' => 'Paket Hotspot 5 Mbps / 30 Hari',
            ],
            [
                'name' => 'Hotspot 10 Mbps',
                'type' => 'hotspot',
                'rate_up' => 3072,
                'rate_down' => 10240,
                'price' => 100000,
                'validity_type' => 'limited',
                'validity_value' => 30,
                'validity_unit' => 'days',
                'shared_users' => 2,
                'pool_name' => 'hotspot-pool',
                'description' => 'Paket Hotspot 10 Mbps / 30 Hari',
            ],
            [
                'name' => 'PPPoE 10 Mbps',
                'type' => 'pppoe',
                'rate_up' => 5120,
                'rate_down' => 10240,
                'price' => 150000,
                'validity_type' => 'limited',
                'validity_value' => 30,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'pool_name' => 'pppoe-pool',
                'description' => 'Paket PPPoE 10 Mbps / 30 Hari',
            ],
            [
                'name' => 'PPPoE 20 Mbps',
                'type' => 'pppoe',
                'rate_up' => 10240,
                'rate_down' => 20480,
                'price' => 250000,
                'validity_type' => 'limited',
                'validity_value' => 30,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'pool_name' => 'pppoe-pool',
                'description' => 'Paket PPPoE 20 Mbps / 30 Hari',
            ],
            [
                'name' => 'PPPoE 50 Mbps',
                'type' => 'pppoe',
                'rate_up' => 25600,
                'rate_down' => 51200,
                'price' => 500000,
                'validity_type' => 'limited',
                'validity_value' => 30,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'pool_name' => 'pppoe-pool',
                'description' => 'Paket PPPoE 50 Mbps / 30 Hari',
            ],
            [
                'name' => 'Voucher 2 Jam',
                'type' => 'hotspot',
                'rate_up' => 1024,
                'rate_down' => 2048,
                'price' => 5000,
                'validity_type' => 'limited',
                'validity_value' => 2,
                'validity_unit' => 'hours',
                'shared_users' => 1,
                'pool_name' => 'hotspot-pool',
                'description' => 'Voucher Hotspot 2 Jam',
            ],
            [
                'name' => 'Voucher 1 Hari',
                'type' => 'hotspot',
                'rate_up' => 1024,
                'rate_down' => 3072,
                'price' => 10000,
                'validity_type' => 'limited',
                'validity_value' => 1,
                'validity_unit' => 'days',
                'shared_users' => 1,
                'pool_name' => 'hotspot-pool',
                'description' => 'Voucher Hotspot 1 Hari',
            ],
        ];

        $radiusService = new RadiusService();

        foreach ($packages as $pkgData) {
            $pkg = Package::create($pkgData);
            $radiusService->syncPackage($pkg);
        }

        // Create sample customers
        $sampleCustomers = [
            ['username' => 'user001', 'password' => 'pass001', 'fullname' => 'Ahmad Rizki', 'phone' => '081234567890', 'service_type' => 'hotspot', 'package_id' => 1],
            ['username' => 'user002', 'password' => 'pass002', 'fullname' => 'Siti Nurhaliza', 'phone' => '081234567891', 'service_type' => 'hotspot', 'package_id' => 2],
            ['username' => 'pppoe001', 'password' => 'ppp001', 'fullname' => 'Budi Santoso', 'phone' => '081234567892', 'service_type' => 'pppoe', 'package_id' => 4],
            ['username' => 'pppoe002', 'password' => 'ppp002', 'fullname' => 'Dewi Lestari', 'phone' => '081234567893', 'service_type' => 'pppoe', 'package_id' => 5],
        ];

        foreach ($sampleCustomers as $custData) {
            $customer = Customer::create(array_merge($custData, [
                'nas_id' => $nas->id,
                'status' => 'active',
                'start_date' => now(),
                'expiry_date' => now()->addDays(30),
            ]));
            $radiusService->createCustomer($customer);
        }

        // Create sample vouchers
        $voucherPackage = Package::find(7);
        if ($voucherPackage) {
            for ($i = 0; $i < 20; $i++) {
                Voucher::create([
                    'code' => Voucher::generateCode(8),
                    'package_id' => $voucherPackage->id,
                    'status' => 'unused',
                    'batch_name' => 'BATCH-SAMPLE',
                    'validity_value' => $voucherPackage->validity_value,
                    'validity_unit' => $voucherPackage->validity_unit,
                ]);
            }
        }
    }
}
