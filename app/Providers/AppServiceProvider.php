<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use App\Support\CurrencyFormatter;

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
        Blade::directive('money', static function (string $expression): string {
            return "<?php echo e(app(".CurrencyFormatter::class."::class)->format({$expression})); ?>";
        });

        View::composer([
            'layouts.app',
            'layouts.guest',
            'layouts.admin',
            'products.index',
        ], function ($view): void {
            $view->with('siteSettings', SiteSetting::query()->pluck('value', 'key'));
        });
    }
}
