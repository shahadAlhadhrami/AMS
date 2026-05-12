<?php

namespace Database\Seeders;

use App\Support\MasterDataSetup;
use Illuminate\Database\Seeder;

class DefaultGradingScaleSeeder extends Seeder
{
    public function run(): void
    {
        MasterDataSetup::ensureDefaultGradingScales();
    }
}
