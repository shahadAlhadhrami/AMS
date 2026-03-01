<?php

namespace App\Providers;

use App\Models\Criterion;
use App\Models\Project;
use App\Observers\CriterionObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        Criterion::observe(CriterionObserver::class);
        Project::observe(ProjectObserver::class);
    }
}
