<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class AddDefaultRole extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultRole = Role::where('name', 'student')->first();

        User::whereNull('role_id')->update(['role_id' => $defaultRole->id]);

    }
}
