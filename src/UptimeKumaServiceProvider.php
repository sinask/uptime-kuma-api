<?php

namespace UptimeKuma\LaravelApi;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use UptimeKuma\LaravelApi\Http\UptimeKumaClient;

class UptimeKumaServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/uptime-kuma.php', 'uptime-kuma');

        $this->app->singleton(UptimeKumaClient::class, function ($app) {
            $config = $app['config']->get('uptime-kuma');

            return new UptimeKumaClient(
                $config['base_url'],
                $config['username'],
                $config['password'],
                $config['two_factor_token']
            );
        });

        $this->app->alias(UptimeKumaClient::class, 'uptime-kuma');
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/uptime-kuma.php' => config_path('uptime-kuma.php'),
        ], 'uptime-kuma-config');
    }

    public function provides(): array
    {
        return [UptimeKumaClient::class, 'uptime-kuma'];
    }
}
