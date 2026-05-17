<?php

namespace App\Filament\Admin\Auth\Pages;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null) {
            // Clear any cross-panel "intended URL" so the admin login always
            // lands on /admin instead of a previously visited student/staff URL.
            session()->forget('url.intended');
        }

        return $response;
    }
}
