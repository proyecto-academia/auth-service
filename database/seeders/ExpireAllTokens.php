<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class ExpireAllTokens extends Seeder
{
    public function run()
    {
        $tokens = \DB::table('personal_access_tokens')->get();

        foreach ($tokens as $token) {
            \DB::table('personal_access_tokens')
                ->where('id', $token->id)
                ->update(['expires_at' => now()]);
        }

    }
}
