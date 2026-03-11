<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user) {
            return $next($request);
        }

        $panel = Filament::getCurrentPanel();

        if (! $panel) {
            return $next($request);
        }

        if (! $user->canAccessPanel($panel)) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect($panel->getLoginUrl());
        }

        return $next($request);
    }
}
