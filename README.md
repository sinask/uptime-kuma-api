# Laravel Uptime Kuma API

A comprehensive Laravel wrapper for the [Uptime Kuma](https://github.com/louislam/uptime-kuma) API. This package provides a complete PHP port of the [Python uptime-kuma-api](https://github.com/lucasheld/uptime-kuma-api), offering a simple service class and facade that can be used from any Laravel application to manage monitors, notifications, status pages, maintenance windows, and more.

## Requirements

- PHP 8.1+
- Laravel 10.0 or 11.0
- Uptime Kuma 1.21.3+

## Installation

```bash
composer require sinask/laravel-kuma-api
```

If you plan to customise the configuration, publish the package config file:

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

## Quick Start

```php
use UptimeKuma\LaravelApi\Facades\UptimeKuma;
use UptimeKuma\LaravelApi\Support\MonitorType;

// The client will use the configured credentials and automatically login
$monitors = UptimeKuma::monitors();

// Create a new HTTP monitor
UptimeKuma::createMonitor([
    'name' => 'Google',
    'type' => MonitorType::HTTP->value,
    'url' => 'https://google.com',
    'interval' => 60,
]);

// Pause a monitor
UptimeKuma::pauseMonitor(1);

// Resume when ready
UptimeKuma::resumeMonitor(1);
```

## Available Methods

### Authentication

```php
UptimeKuma::login($username, $password, $twoFactorToken);
UptimeKuma::logout();
UptimeKuma::token();
UptimeKuma::usingToken($token);
```

### Server Info

```php
UptimeKuma::info();              // Get server information
UptimeKuma::version();           // Get Uptime Kuma version
UptimeKuma::serverTimezone();    // Get server timezone
```

### Monitors

```php
// List & Retrieve
UptimeKuma::monitors();          // Get all monitors
UptimeKuma::monitor($id);        // Get specific monitor

// Create & Update
UptimeKuma::createMonitor($attributes);
UptimeKuma::updateMonitor($id, $attributes);
UptimeKuma::deleteMonitor($id);

// Control
UptimeKuma::pauseMonitor($id);
UptimeKuma::resumeMonitor($id);

// Heartbeats & Statistics
UptimeKuma::heartbeats($monitorId, $hours);
UptimeKuma::getAllHeartbeats();
UptimeKuma::getImportantHeartbeats();
UptimeKuma::sendHeartbeat($monitorId, $payload);
UptimeKuma::avgPing();
UptimeKuma::uptime();
UptimeKuma::certInfo();
```

### Monitor Tags

```php
UptimeKuma::addMonitorTag($tagId, $monitorId, $value);
UptimeKuma::deleteMonitorTag($tagId, $monitorId, $value);
```

### Notifications

```php
UptimeKuma::notifications();
UptimeKuma::notification($id);
UptimeKuma::createNotification($attributes);
UptimeKuma::updateNotification($id, $attributes);
UptimeKuma::deleteNotification($id);
UptimeKuma::testNotification($attributes);
UptimeKuma::checkApprise();
```

### Proxies

```php
UptimeKuma::proxies();
UptimeKuma::proxy($id);
UptimeKuma::createProxy($attributes);
UptimeKuma::updateProxy($id, $attributes);
UptimeKuma::deleteProxy($id);
```

### Status Pages

```php
UptimeKuma::statusPages();
UptimeKuma::statusPage($slug);
UptimeKuma::createStatusPage($slug, $title);
UptimeKuma::saveStatusPage($slug, $attributes);
UptimeKuma::deleteStatusPage($slug);
UptimeKuma::postIncident($slug, $title, $content, $style);
UptimeKuma::unpinIncident($slug);
```

### Tags

```php
UptimeKuma::tags();
UptimeKuma::tag($id);
UptimeKuma::createTag($attributes);
UptimeKuma::updateTag($id, $attributes);
UptimeKuma::deleteTag($id);
```

### Maintenance Windows

```php
UptimeKuma::maintenances();
UptimeKuma::maintenance($id);
UptimeKuma::createMaintenance($attributes);
UptimeKuma::updateMaintenance($id, $attributes);
UptimeKuma::deleteMaintenance($id);
UptimeKuma::pauseMaintenance($id);
UptimeKuma::resumeMaintenance($id);
UptimeKuma::getMaintenanceMonitors($id);
UptimeKuma::addMaintenanceMonitors($id, $monitorIds);
UptimeKuma::getMaintenanceStatusPages($id);
UptimeKuma::addMaintenanceStatusPages($id, $statusPageSlugs);
```

### Docker Hosts

```php
UptimeKuma::dockerHosts();
UptimeKuma::dockerHost($id);
UptimeKuma::createDockerHost($attributes);
UptimeKuma::updateDockerHost($id, $attributes);
UptimeKuma::deleteDockerHost($id);
UptimeKuma::testDockerHost($attributes);
```

### API Keys

```php
UptimeKuma::apiKeys();
UptimeKuma::apiKey($id);
UptimeKuma::createApiKey($attributes);
UptimeKuma::deleteApiKey($id);
UptimeKuma::enableApiKey($id);
UptimeKuma::disableApiKey($id);
```

### Settings & Database

```php
UptimeKuma::settings();
UptimeKuma::updateSettings($attributes);
UptimeKuma::changePassword($currentPassword, $newPassword);
UptimeKuma::backup();
UptimeKuma::restore($backupData);
UptimeKuma::clearEvents();
UptimeKuma::clearHeartbeats();
UptimeKuma::shrinkDatabase();
```

### Two-Factor Authentication

```php
UptimeKuma::prepare2FA();
UptimeKuma::verify2FA($token);
UptimeKuma::disable2FA();
UptimeKuma::twoFAStatus();
```

### Remote Browsers

```php
UptimeKuma::remoteBrowsers();
UptimeKuma::addRemoteBrowser($attributes);
UptimeKuma::updateRemoteBrowser($id, $attributes);
UptimeKuma::deleteRemoteBrowser($id);
```

### Game Servers

```php
UptimeKuma::gameList();  // Get supported games for GameDig monitors
```

## Available Enums

### MonitorType

```php
use UptimeKuma\LaravelApi\Support\MonitorType;

MonitorType::HTTP;          // 'http'
MonitorType::PORT;          // 'port'
MonitorType::PING;          // 'ping'
MonitorType::KEYWORD;       // 'keyword'
MonitorType::DNS;           // 'dns'
MonitorType::DOCKER;        // 'docker'
MonitorType::PUSH;          // 'push'
MonitorType::STEAM;         // 'steam'
MonitorType::GAMEDIG;       // 'gamedig'
MonitorType::MQTT;          // 'mqtt'
MonitorType::SQLSERVER;     // 'sqlserver'
MonitorType::POSTGRES;      // 'postgres'
MonitorType::MYSQL;         // 'mysql'
MonitorType::MONGODB;       // 'mongodb'
MonitorType::REDIS;         // 'redis'
// ... and more
```

### MonitorStatus

```php
use UptimeKuma\LaravelApi\Support\MonitorStatus;

MonitorStatus::PAUSED;   // 0
MonitorStatus::UP;       // 1
MonitorStatus::DOWN;     // 2
MonitorStatus::PENDING;  // 3
MonitorStatus::UNKNOWN;  // 4
```

### NotificationType

Supports 60+ notification providers including:

```php
use UptimeKuma\LaravelApi\Support\NotificationType;

NotificationType::DISCORD;
NotificationType::SLACK;
NotificationType::TELEGRAM;
NotificationType::SMTP;
NotificationType::WEBHOOK;
NotificationType::PAGERDUTY;
NotificationType::OPSGENIE;
NotificationType::TEAMS;
// ... and many more
```

### Other Enums

```php
use UptimeKuma\LaravelApi\Support\AuthMethod;
use UptimeKuma\LaravelApi\Support\ProxyProtocol;
use UptimeKuma\LaravelApi\Support\IncidentStyle;
use UptimeKuma\LaravelApi\Support\DockerType;
use UptimeKuma\LaravelApi\Support\MaintenanceStrategy;
use UptimeKuma\LaravelApi\Support\DnsRecordType;
```

## Dependency Injection

You can resolve the underlying client manually if you prefer dependency injection:

```php
use UptimeKuma\LaravelApi\Http\UptimeKumaClient;

class MonitorService
{
    public function __construct(private UptimeKumaClient $client)
    {
    }

    public function getActiveMonitors(): array
    {
        return collect($this->client->monitors()['data'] ?? [])
            ->filter(fn ($m) => $m['active'] ?? false)
            ->all();
    }
}
```

## Error Handling

The package throws specific exceptions for different error scenarios:

```php
use UptimeKuma\LaravelApi\Exceptions\UptimeKumaException;
use UptimeKuma\LaravelApi\Exceptions\AuthenticationException;

try {
    UptimeKuma::monitors();
} catch (AuthenticationException $e) {
    // Handle authentication errors (invalid credentials, expired token, etc.)
} catch (UptimeKumaException $e) {
    // Handle general API errors
}
```

## Testing

Run the package test suite locally with:

```bash
composer test
```

The test suite relies on mocked HTTP responses and does not require a running Uptime Kuma instance.

## Credits

This package is a PHP/Laravel port of the excellent [uptime-kuma-api](https://github.com/lucasheld/uptime-kuma-api) Python library by Lucas Held.

## License

MIT License. See [LICENSE](LICENSE) for details.
