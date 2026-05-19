<?php

namespace App\Filament\Student\Auth\Pages;

use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null) {
            $intended = session()->get('url.intended', '');

            // Discard any intended URL that doesn't belong to the student panel
            // to prevent cross-panel redirects (e.g. landing on /staff after login)
            if (! str_starts_with($intended, url('/student'))) {
                session()->forget('url.intended');
            }
        }

        return $response;
    }
}
