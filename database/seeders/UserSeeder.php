<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus data lama agar ID bisa di-insert ulang
        DB::table('users')->delete();

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'admin_lms',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'username' => 'budi_santoso',
                'email' => 'budi.santoso@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'username' => 'siti_aisyah',
                'email' => 'siti.aisyah@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'inactive',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'username' => 'dewi_lestari',
                'email' => 'dewi.lestari@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'username' => 'eko_prasetyo',
                'email' => 'eko.prasetyo@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'username' => 'rina_hartati',
                'email' => 'rina.hartati@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 7,
                'username' => 'agus_wijaya',
                'email' => 'agus.wijaya@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 8,
                'username' => 'putri_amelia',
                'email' => 'putri.amelia@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 9,
                'username' => 'fajar_nugroho',
                'email' => 'fajar.nugroho@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 10,
                'username' => 'lia_indah',
                'email' => 'lia.indah@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'inactive',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 11,
                'username' => 'rudi_setiawan',
                'email' => 'rudi.setiawan@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 12,
                'username' => 'sari_puspita',
                'email' => 'sari.puspita@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 13,
                'username' => 'hendra_gunawan',
                'email' => 'hendra.gunawan@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 14,
                'username' => 'fitriani_rahma',
                'email' => 'fitriani.rahma@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 15,
                'username' => 'dian_permana',
                'email' => 'dian.permana@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'status' => 'active',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}