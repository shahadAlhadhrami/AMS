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

            // Give a specific message if they have the coordinator role but not yet approved
            if ($user->hasRole('Coordinator') && ! $user->is_approved) {
                session()->flash('status', 'Your account is pending approval by a Super Administrator. You will be notified once it is activated.');
            }

            return redirect($panel->getLoginUrl());
        }

        return $next($request);
    }
}
