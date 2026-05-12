<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Concerns\HidesDuringMasterDataSetup;
use Filament\Pages\Page;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HidesDuringMasterDataSetup;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?int $navigationSort = -2;
}
