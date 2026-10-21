<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SystemSetting::class, fn (): SystemSetting => SystemSetting::firstOrNew(['id' => 1]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'auth.login', 'pos.index', 'sales.show', 'settings.edit', 'settings.setup'], function (ViewInstance $view): void {
            $view->with('branding', SystemSetting::current()->settings());
            if ($view->name() === 'layouts.app') {
                $view->with('sidebarItems', SystemSetting::current()->sidebarItems(auth()->user()));
                $view->with('customPages', SystemSetting::current()->customPages(auth()->user()));
                $view->with('needsSuperadminSetup', ! User::where('role', 'Superadmin')->exists());
            }
        });
    }
}
