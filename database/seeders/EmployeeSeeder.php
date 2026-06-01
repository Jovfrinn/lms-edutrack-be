<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus data lama
        DB::table('employees')->delete();
        
        DB::table('employees')->insert([
            [
                'user_id' => 1,
                'department_id' => 1, // 1: IT Department
                'employee_number' => 'ADM-001',
                'name' => 'Administrator LMS',
                'phone' => '081234567800',
                'position' => 'LMS Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'department_id' => 3, // 3: Finance & Accounting
                'employee_number' => 'FA-022',
                'name' => 'Budi Santoso',
                'phone' => '081234567801',
                'position' => 'Junior Accountant',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'department_id' => 2, // 2: Human Resources
                'employee_number' => 'HR-015',
                'name' => 'Siti Aisyah',
                'phone' => '081234567802',
                'position' => 'HR Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 4,
                'department_id' => 2, // 2: Human Resources
                'employee_number' => 'HR-016',
                'name' => 'Dewi Lestari',
                'phone' => '081234567803',
                'position' => 'HR Recruiter',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 5,
                'department_id' => 1, // 1: IT Department
                'employee_number' => 'IT-001',
                'name' => 'Eko Prasetyo',
                'phone' => '081234567804',
                'position' => 'IT Support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 6,
                'department_id' => 3, // 3: Finance & Accounting
                'employee_number' => 'FA-023',
                'name' => 'Rina Hartati',
                'phone' => '081234567805',
                'position' => 'Finance Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 7,
                'department_id' => 4, // 4: Marketing
                'employee_number' => 'MKT-005',
                'name' => 'Agus Wijaya',
                'phone' => '081234567806',
                'position' => 'Marketing Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 8,
                'department_id' => 4, // 4: Marketing
                'employee_number' => 'MKT-006',
                'name' => 'Putri Amelia',
                'phone' => '081234567807',
                'position' => 'Digital Marketer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 9,
                'department_id' => 1, // 1: IT Department
                'employee_number' => 'IT-002',
                'name' => 'Fajar Nugroho',
                'phone' => '081234567808',
                'position' => 'Backend Developer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 10,
                'department_id' => 2, // 2: Human Resources
                'employee_number' => 'HR-017',
                'name' => 'Lia Indah',
                'phone' => '081234567809',
                'position' => 'HR Staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 11,
                'department_id' => 1, // 1: IT Department
                'employee_number' => 'IT-003',
                'name' => 'Rudi Setiawan',
                'phone' => '081234567810',
                'position' => 'Frontend Developer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 12,
                'department_id' => 3, // 3: Finance & Accounting
                'employee_number' => 'FA-024',
                'name' => 'Sari Puspita',
                'phone' => '081234567811',
                'position' => 'Tax Specialist',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 13,
                'department_id' => 1, // 1: IT Department
                'employee_number' => 'IT-004',
                'name' => 'Hendra Gunawan',
                'phone' => '081234567812',
                'position' => 'UI/UX Designer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 14,
                'department_id' => 4, // 4: Marketing
                'employee_number' => 'MKT-007',
                'name' => 'Fitriani Rahma',
                'phone' => '081234567813',
                'position' => 'Content Creator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 15,
                'department_id' => 1, // 1: IT Department
                'employee_number' => 'IT-005',
                'name' => 'Dian Permana',
                'phone' => '081234567814',
                'position' => 'IT Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}