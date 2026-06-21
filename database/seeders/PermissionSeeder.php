<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run()
    {
        $permissions = [
            // medicine
            ['name' => 'medicine.view', 'display_name' => 'عرض الأدوية', 'group' => 'medicines'],
            ['name' => 'medicine.create', 'display_name' => 'إضافة دواء', 'group' => 'medicines'],
            ['name' => 'medicine.edit', 'display_name' => 'تعديل دواء', 'group' => 'medicines'],
            ['name' => 'medicine.delete', 'display_name' => 'حذف دواء', 'group' => 'medicines'],

            // users
            ['name' => 'users.view', 'display_name' => 'عرض المستخدمين', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'إضافة مستخدم', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'تعديل مستخدم', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'حذف مستخدم', 'group' => 'users'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }
    }
}
