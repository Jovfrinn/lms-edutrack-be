<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('departments')->insert([
            ['name' => 'IT Department', 'description' => 'Mengelola infrastruktur dan aplikasi perusahaan.'],
            ['name' => 'Human Resources', 'description' => 'Bertanggung jawab atas rekrutmen dan kesejahteraan karyawan.'],
            ['name' => 'Finance & Accounting', 'description' => 'Mengurus keuangan dan pembukuan perusahaan.'],
            ['name' => 'Marketing', 'description' => 'Memimpin upaya pemasaran dan promosi produk.'],
            ['name' => 'Operations', 'description' => 'Mengawasi kegiatan operasional sehari-hari.'],
        ]);
    }
}