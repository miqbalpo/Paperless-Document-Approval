<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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
        /**
         * Blade directive to output navigation classes for active route.
         * Usage: class="@navClass('route.name')"
         */
        Blade::directive('navClass', function ($expression) {
            return "<?php echo (request()->routeIs({$expression}) ? 'bg-gray-100 text-indigo-600' : 'text-gray-700 hover:bg-gray-100'); ?>";
        });
    }
}
