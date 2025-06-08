<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoreClassPolicies extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Inserta las políticas si no existen
        DB::table('policies')->updateOrInsert(
            ['name' => 'seeClass'],
            ['request_url' => 'https://mardev.es/api/core/policies/classes/show/{id}']
        );

        DB::table('policies')->updateOrInsert(
            ['name' => 'postClass'],
            ['request_url' => 'https://mardev.es/api/core/policies/classes/post/{id}']
        );
    }
}
