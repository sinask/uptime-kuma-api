# Laravel Uptime Kuma API

A Laravel-friendly wrapper around the [Uptime Kuma](https://github.com/louislam/uptime-kuma) API. The package offers a
simple service class and facade that can be used from any Laravel application to manage monitors, notifications and
status pages without dealing with the underlying Socket.IO protocol.

## Installation

```bash
composer require uptime-kuma/laravel-api
```

If you plan to customise the configuration publish the package config file:

```bash
php artisan vendor:publish --tag=uptime-kuma-config
```

The configuration file exposes the connection settings that are read from the environment by default:

```php
return [
    'base_url' => env('UPTIME_KUMA_URL', 'http://127.0.0.1:3001'),
    'username' => env('UPTIME_KUMA_USERNAME'),
    'password' => env('UPTIME_KUMA_PASSWORD'),
    'two_factor_token' => env('UPTIME_KUMA_TOKEN'),
];
```

## Usage

```php
use UptimeKuma\LaravelApi\Facades\UptimeKuma;
use UptimeKuma\LaravelApi\Support\MonitorType;

// the client will use the configured credentials and automatically login
$monitors = UptimeKuma::monitors();

// create a new monitor
UptimeKuma::createMonitor([
    'name' => 'Google',
    'type' => MonitorType::HTTP->value,
    'url' => 'https://google.com',
]);

// pause a monitor
UptimeKuma::pauseMonitor(1);

// resume it when you are ready
UptimeKuma::resumeMonitor(1);
```

You can resolve the underlying client manually if you prefer dependency injection:

```php
use UptimeKuma\LaravelApi\Http\UptimeKumaClient;

public function __construct(private UptimeKumaClient $client)
{
}
```

## Testing

Run the package test suite locally with:

```bash
composer test
```

The test suite relies on mocked HTTP responses and does not require a running Uptime Kuma instance.
