<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Package;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\RadGroupCheck;
use App\Models\RadGroupReply;
use App\Models\RadUserGroup;
use App\Models\RadAcct;
use Illuminate\Support\Facades\DB;

class RadiusService
{
    public function createCustomer(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $this->deleteCustomer($customer->username);

            RadCheck::create([
                'username' => $customer->username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $customer->password,
            ]);

            if ($customer->expiry_date) {
                RadCheck::create([
                    'username' => $customer->username,
                    'attribute' => 'Expiration',
                    'op' => ':=',
                    'value' => $customer->expiry_date->format('d M Y H:i:s'),
                ]);
            }

            if ($customer->package && $customer->package->shared_users > 0) {
                RadCheck::create([
                    'username' => $customer->username,
                    'attribute' => 'Simultaneous-Use',
                    'op' => ':=',
                    'value' => (string) $customer->package->shared_users,
                ]);
            }

            if ($customer->service_type === 'pppoe' && $customer->pppoe_caller_id) {
                RadCheck::create([
                    'username' => $customer->username,
                    'attribute' => 'Calling-Station-Id',
                    'op' => '==',
                    'value' => $customer->pppoe_caller_id,
                ]);
            }

            if ($customer->static_ip) {
                RadReply::create([
                    'username' => $customer->username,
                    'attribute' => 'Framed-IP-Address',
                    'op' => ':=',
                    'value' => $customer->static_ip,
                ]);
            }

            if ($customer->service_type === 'pppoe') {
                RadReply::create([
                    'username' => $customer->username,
                    'attribute' => 'Framed-Protocol',
                    'op' => ':=',
                    'value' => 'PPP',
                ]);
                RadReply::create([
                    'username' => $customer->username,
                    'attribute' => 'Service-Type',
                    'op' => ':=',
                    'value' => 'Framed-User',
                ]);
            }

            if ($customer->package) {
                RadUserGroup::create([
                    'username' => $customer->username,
                    'groupname' => $customer->package->getRadiusGroupName(),
                    'priority' => 1,
                ]);
            }
        });
    }

    public function deleteCustomer(string $username): void
    {
        RadCheck::where('username', $username)->delete();
        RadReply::where('username', $username)->delete();
        RadUserGroup::where('username', $username)->delete();
    }

    public function suspendCustomer(string $username): void
    {
        RadCheck::where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->delete();

        RadCheck::create([
            'username' => $username,
            'attribute' => 'Auth-Type',
            'op' => ':=',
            'value' => 'Reject',
        ]);
    }

    public function activateCustomer(Customer $customer): void
    {
        RadCheck::where('username', $customer->username)
            ->where('attribute', 'Auth-Type')
            ->delete();

        $exists = RadCheck::where('username', $customer->username)
            ->where('attribute', 'Cleartext-Password')
            ->exists();

        if (!$exists) {
            RadCheck::create([
                'username' => $customer->username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $customer->password,
            ]);
        }
    }

    public function syncPackage(Package $package): void
    {
        $groupname = $package->getRadiusGroupName();

        RadGroupReply::where('groupname', $groupname)->delete();
        RadGroupCheck::where('groupname', $groupname)->delete();

        RadGroupReply::create([
            'groupname' => $groupname,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $package->mikrotik_rate,
        ]);

        if ($package->pool_name) {
            RadGroupReply::create([
                'groupname' => $groupname,
                'attribute' => 'Framed-Pool',
                'op' => ':=',
                'value' => $package->pool_name,
            ]);
        }

        if ($package->limit_type === 'limited' && $package->quota_bytes) {
            $gigawords = floor($package->quota_bytes / 4294967296);
            $octets = $package->quota_bytes % 4294967296;

            if ($gigawords > 0) {
                RadGroupReply::create([
                    'groupname' => $groupname,
                    'attribute' => 'Mikrotik-Total-Limit-Gigawords',
                    'op' => ':=',
                    'value' => (string) $gigawords,
                ]);
            }
            RadGroupReply::create([
                'groupname' => $groupname,
                'attribute' => 'Mikrotik-Total-Limit',
                'op' => ':=',
                'value' => (string) $octets,
            ]);
        }
    }

    public function deletePackage(string $groupname): void
    {
        RadGroupReply::where('groupname', $groupname)->delete();
        RadGroupCheck::where('groupname', $groupname)->delete();
        RadUserGroup::where('groupname', $groupname)->delete();
    }

    public function disconnectUser(string $username): bool
    {
        RadAcct::where('username', $username)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime' => now(),
                'acctterminatecause' => 'Admin-Reset',
            ]);

        return true;
    }
}
