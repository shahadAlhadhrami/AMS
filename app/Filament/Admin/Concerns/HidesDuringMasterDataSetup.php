<?php

namespace App\Filament\Admin\Concerns;

use App\Support\MasterDataSetup;

trait HidesDuringMasterDataSetup
{
    public static function shouldRegisterNavigation(): bool
    {
        return ! MasterDataSetup::shouldFocusNavigation()
            && parent::shouldRegisterNavigation();
    }
}
