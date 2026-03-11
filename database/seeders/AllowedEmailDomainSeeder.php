<?php

namespace Database\Seeders;

use App\Models\AllowedEmailDomain;
use Illuminate\Database\Seeder;

class AllowedEmailDomainSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            'university.edu',
        ];

        foreach ($domains as $domain) {
            AllowedEmailDomain::firstOrCreate(
                ['domain' => $domain],
                ['is_active' => true]
            );
        }
    }
}
