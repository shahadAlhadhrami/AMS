<?php

namespace App\Providers;

use App\Models\Criterion;
use App\Models\Project;
use App\Observers\CriterionObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Criterion::observe(CriterionObserver::class);
        Project::observe(ProjectObserver::class);

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn(): string => '<style>
                form:has(.fi-one-time-code-input-ctn) .fi-fo-field-label-col {
                    display: flex;
                    justify-content: center;
                }
                form:has(.fi-one-time-code-input-ctn) .fi-one-time-code-input-ctn {
                    justify-content: center;
                }
                form:has(.fi-one-time-code-input-ctn) .fi-fo-field-content-col {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    text-align: center;
                }
            </style>'
        );
    }
}
