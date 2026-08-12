<?php

namespace Database\Seeders;

use App\Models\Courier;
use Illuminate\Database\Seeder;

class CourierSeeder extends Seeder
{
    public function run(): void
    {
        $couriers = [
            ['code' => 'KRR001', 'name' => 'Budiono Hadi Agung',   'level' => 2, 'phone' => '081111',  'email' => 'budi@example.com',  'vehicle_type' => 'motor', 'vehicle_plate' => 'B 1234 ABC', 'status' => 'active',    'joined_at' => '2024-01-15'],
            ['code' => 'KRR002', 'name' => 'Siti Aminah',          'level' => 3, 'phone' => '082222',  'email' => 'siti@example.com',  'vehicle_type' => 'mobil', 'vehicle_plate' => 'B 5678 DEF', 'status' => 'active',    'joined_at' => '2023-06-10'],
            ['code' => 'KRR003', 'name' => 'Agus Budi Santoso',    'level' => 5, 'phone' => '083333',  'email' => 'agus@example.com',  'vehicle_type' => 'van',   'vehicle_plate' => 'B 9012 GHI', 'status' => 'active',    'joined_at' => '2020-03-22'],
            ['code' => 'KRR004', 'name' => 'Dewi Lestari',         'level' => 2, 'phone' => '084444',  'email' => 'dewi@example.com',  'vehicle_type' => 'motor', 'vehicle_plate' => 'B 3456 JKL', 'status' => 'inactive',  'joined_at' => '2022-11-01'],
            ['code' => 'KRR005', 'name' => 'Rudi Hartono',         'level' => 4, 'phone' => '085555',  'email' => 'rudi@example.com',  'vehicle_type' => 'mobil', 'vehicle_plate' => 'B 7890 MNO', 'status' => 'active',    'joined_at' => '2025-02-14'],
            ['code' => 'KRR006', 'name' => 'Andini Wijaya',        'level' => 1, 'phone' => '086666',  'email' => 'andin@example.com', 'vehicle_type' => 'motor', 'vehicle_plate' => 'B 1122 PQR', 'status' => 'active',    'joined_at' => '2025-08-01'],
            ['code' => 'KRR007', 'name' => 'Budi Cahyono',         'level' => 3, 'phone' => '087777',  'email' => 'bcah@example.com',  'vehicle_type' => 'mobil', 'vehicle_plate' => 'B 3344 STU', 'status' => 'active',    'joined_at' => '2024-09-05'],
            ['code' => 'KRR008', 'name' => 'Fitri Handayani',      'level' => 2, 'phone' => '088888',  'email' => 'fitri@example.com', 'vehicle_type' => 'motor', 'vehicle_plate' => 'B 5566 VWX', 'status' => 'suspended', 'joined_at' => '2021-07-20'],
        ];

        foreach ($couriers as $c) {
            Courier::create($c);
        }
    }
}
