<?php

declare(strict_types=1);

namespace Tamfuldev\Payment;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tamfuldev\Payment\Http\Controllers\WebhookController;

final class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/payment.php', 'payment');

        $this->app->singleton(PaymentManager::class, static function (Application $app): PaymentManager {
            return new PaymentManager($app);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/payment.php' => $this->app->configPath('payment.php'),
            ], 'payment-config');
        }

        $this->registerWebhookRoute();
    }

    private function registerWebhookRoute(): void
    {
        /** @var ConfigRepository $config */
        $config = $this->app->make('config');

        /** @var array{prefix?: string, middleware?: array<int, string>} $webhook */
        $webhook = $config->get('payment.webhook', []);

        Route::prefix($webhook['prefix'] ?? 'payment/webhook')
            ->middleware($webhook['middleware'] ?? ['api'])
            ->group(function (): void {
                Route::post('/{gateway}', WebhookController::class)
                    ->name('payment.webhook');
            });
    }
}
