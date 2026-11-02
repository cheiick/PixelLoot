<?php

namespace App\Providers;

use App\Mail\MailtrapApiTransport;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Support\CurrencyFormatter;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
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
        Mail::extend('mailtrap_api', static function (array $config): MailtrapApiTransport {
            return new MailtrapApiTransport(
                $config['token'],
                $config['endpoint'],
                $config['category'] ?? 'Transactional',
            );
        });

        Blade::directive('money', static function (string $expression): string {
            return '<?php echo e(app('.CurrencyFormatter::class."::class)->format({$expression})); ?>";
        });

        View::composer([
            'layouts.app',
            'layouts.guest',
            'layouts.admin',
            'products.index',
        ], function ($view): void {
            $view->with('siteSettings', SiteSetting::query()->pluck('value', 'key'));
        });

        View::composer('layouts.app', function ($view): void {
            $view->with('navigationCategories', Category::query()->orderBy('name')->get(['id', 'name']));
        });
    }
}
