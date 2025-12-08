<?php

namespace UptimeKuma\LaravelApi\Tests\Feature;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use UptimeKuma\LaravelApi\Exceptions\AuthenticationException;
use UptimeKuma\LaravelApi\Exceptions\UptimeKumaException;
use UptimeKuma\LaravelApi\Http\UptimeKumaClient;
use UptimeKuma\LaravelApi\Support\AuthMethod;
use UptimeKuma\LaravelApi\Support\DnsRecordType;
use UptimeKuma\LaravelApi\Support\DockerType;
use UptimeKuma\LaravelApi\Support\IncidentStyle;
use UptimeKuma\LaravelApi\Support\MaintenanceStrategy;
use UptimeKuma\LaravelApi\Support\MonitorStatus;
use UptimeKuma\LaravelApi\Support\MonitorType;
use UptimeKuma\LaravelApi\Support\NotificationType;
use UptimeKuma\LaravelApi\Support\ProxyProtocol;

class UptimeKumaClientTest extends TestCase
{
    // =========================================================================
    // Authentication Tests
    // =========================================================================

    public function testLoginStoresToken(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['token' => 'abc123'])),
        ]);

        $client = $this->makeClient($mock);
        $client->login('demo', 'secret');

        $this->assertSame('abc123', $client->token());
    }

    public function testLoginRequiresTokenFromServer(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client = $this->makeClient($mock);

        $this->expectException(AuthenticationException::class);

        $client->login('demo', 'secret');
    }

    public function testClientThrowsExceptionForHttpErrors(): void
    {
        $mock = new MockHandler([
            new Response(500, [], json_encode(['msg' => 'Internal error'])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');

        $this->expectException(UptimeKumaException::class);
        $this->expectExceptionMessage('Internal error');

        $client->monitors();
    }

    public function testClientAutoLogsInWhenCredentialsProvided(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['token' => 'abc123'])),
            new Response(200, [], json_encode(['data' => [['id' => 1, 'name' => 'Example']]])),
        ]);

        $client = $this->makeClient($mock, 'demo', 'secret');

        $monitors = $client->monitors();

        $this->assertSame('abc123', $client->token());
        $this->assertSame(['data' => [['id' => 1, 'name' => 'Example']]], $monitors);
    }

    public function testUsingTokenReturnsClonedClient(): void
    {
        $mock = new MockHandler([]);
        $client = $this->makeClient($mock);
        $clonedClient = $client->usingToken('new-token');

        $this->assertNull($client->token());
        $this->assertSame('new-token', $clonedClient->token());
        $this->assertNotSame($client, $clonedClient);
    }

    public function testThrowsAuthenticationExceptionWhenNotAuthenticated(): void
    {
        $mock = new MockHandler([]);
        $client = $this->makeClient($mock);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('The Uptime Kuma client is not authenticated.');

        $client->monitors();
    }

    // =========================================================================
    // Monitor Tests
    // =========================================================================

    public function testGetMonitors(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'name' => 'Google', 'type' => 'http'],
                    ['id' => 2, 'name' => 'GitHub', 'type' => 'http'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->monitors();

        $this->assertCount(2, $result['data']);
    }

    public function testGetMonitor(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'id' => 1,
                'name' => 'Google',
                'type' => 'http',
                'url' => 'https://google.com',
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->monitor(1);

        $this->assertSame('Google', $result['name']);
        $this->assertSame('http', $result['type']);
    }

    public function testCreateMonitor(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'ok' => true,
                'monitorID' => 5,
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createMonitor([
            'name' => 'New Monitor',
            'type' => MonitorType::HTTP->value,
            'url' => 'https://example.com',
        ]);

        $this->assertTrue($result['ok']);
        $this->assertSame(5, $result['monitorID']);
    }

    public function testPauseMonitor(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->pauseMonitor(1);

        $this->assertTrue($result['ok']);
    }

    public function testResumeMonitor(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->resumeMonitor(1);

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // Notification Tests
    // =========================================================================

    public function testGetNotifications(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'name' => 'Slack', 'type' => 'slack'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->notifications();

        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateNotification(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'ok' => true,
                'id' => 1,
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createNotification([
            'name' => 'Discord Notify',
            'type' => NotificationType::DISCORD->value,
            'discordWebhookUrl' => 'https://discord.com/webhook/...',
        ]);

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // Proxy Tests
    // =========================================================================

    public function testGetProxies(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'protocol' => 'http', 'host' => 'proxy.example.com'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->proxies();

        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateProxy(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'ok' => true,
                'id' => 1,
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createProxy([
            'protocol' => ProxyProtocol::HTTP->value,
            'host' => 'proxy.example.com',
            'port' => 8080,
        ]);

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // Status Page Tests
    // =========================================================================

    public function testGetStatusPages(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['slug' => 'main', 'title' => 'Main Status Page'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->statusPages();

        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateStatusPage(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createStatusPage('new-page', 'New Status Page');

        $this->assertTrue($result['ok']);
    }

    public function testPostIncident(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => true])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->postIncident(
            'main',
            'Server Down',
            'We are investigating the issue.',
            IncidentStyle::DANGER->value
        );

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // Tag Tests
    // =========================================================================

    public function testGetTags(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'name' => 'Production', 'color' => '#ff0000'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->tags();

        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateTag(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'ok' => true,
                'id' => 1,
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createTag([
            'name' => 'Critical',
            'color' => '#ff0000',
        ]);

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // Maintenance Tests
    // =========================================================================

    public function testGetMaintenances(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'title' => 'Scheduled Maintenance'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->maintenances();

        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateMaintenance(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'ok' => true,
                'id' => 1,
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createMaintenance([
            'title' => 'Scheduled Maintenance',
            'strategy' => MaintenanceStrategy::ONCE->value,
            'dateRange' => ['2024-01-01 00:00', '2024-01-01 02:00'],
        ]);

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // Docker Host Tests
    // =========================================================================

    public function testGetDockerHosts(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'name' => 'Local Docker', 'dockerType' => 'socket'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->dockerHosts();

        $this->assertArrayHasKey('data', $result);
    }

    public function testCreateDockerHost(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'ok' => true,
                'id' => 1,
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->createDockerHost([
            'name' => 'Docker Host',
            'dockerType' => DockerType::SOCKET->value,
            'dockerDaemon' => '/var/run/docker.sock',
        ]);

        $this->assertTrue($result['ok']);
    }

    // =========================================================================
    // API Key Tests
    // =========================================================================

    public function testGetApiKeys(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'data' => [
                    ['id' => 1, 'name' => 'My API Key'],
                ],
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->apiKeys();

        $this->assertArrayHasKey('data', $result);
    }

    // =========================================================================
    // Server Info Tests
    // =========================================================================

    public function testGetServerInfo(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'version' => '1.23.0',
                'latestVersion' => '1.23.0',
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $result = $client->info();

        $this->assertSame('1.23.0', $result['version']);
    }

    public function testGetVersion(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'version' => '1.23.0',
            ])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');
        $version = $client->version();

        $this->assertSame('1.23.0', $version);
    }

    // =========================================================================
    // Enum Tests
    // =========================================================================

    public function testMonitorTypeEnum(): void
    {
        $this->assertSame('http', MonitorType::HTTP->value);
        $this->assertSame('ping', MonitorType::PING->value);
        $this->assertSame('port', MonitorType::PORT->value);
        $this->assertSame('dns', MonitorType::DNS->value);
        $this->assertSame('docker', MonitorType::DOCKER->value);
        $this->assertSame('push', MonitorType::PUSH->value);
        $this->assertSame('keyword', MonitorType::KEYWORD->value);
    }

    public function testMonitorStatusEnum(): void
    {
        $this->assertSame(0, MonitorStatus::PAUSED->value);
        $this->assertSame(1, MonitorStatus::UP->value);
        $this->assertSame(2, MonitorStatus::DOWN->value);
        $this->assertSame(3, MonitorStatus::PENDING->value);
    }

    public function testNotificationTypeEnum(): void
    {
        $this->assertSame('discord', NotificationType::DISCORD->value);
        $this->assertSame('slack', NotificationType::SLACK->value);
        $this->assertSame('telegram', NotificationType::TELEGRAM->value);
        $this->assertSame('smtp', NotificationType::SMTP->value);
        $this->assertSame('webhook', NotificationType::WEBHOOK->value);
        $this->assertSame('PagerDuty', NotificationType::PAGERDUTY->value);
    }

    public function testProxyProtocolEnum(): void
    {
        $this->assertSame('http', ProxyProtocol::HTTP->value);
        $this->assertSame('https', ProxyProtocol::HTTPS->value);
        $this->assertSame('socks5', ProxyProtocol::SOCKS5->value);
    }

    public function testIncidentStyleEnum(): void
    {
        $this->assertSame('primary', IncidentStyle::PRIMARY->value);
        $this->assertSame('info', IncidentStyle::INFO->value);
        $this->assertSame('warning', IncidentStyle::WARNING->value);
        $this->assertSame('danger', IncidentStyle::DANGER->value);
        $this->assertSame('success', IncidentStyle::SUCCESS->value);
    }

    public function testDockerTypeEnum(): void
    {
        $this->assertSame('socket', DockerType::SOCKET->value);
        $this->assertSame('tcp', DockerType::TCP->value);
    }

    public function testMaintenanceStrategyEnum(): void
    {
        $this->assertSame('manual', MaintenanceStrategy::MANUAL->value);
        $this->assertSame('single', MaintenanceStrategy::ONCE->value);
        $this->assertSame('recurring-interval', MaintenanceStrategy::RECURRING_INTERVAL->value);
        $this->assertSame('recurring-weekday', MaintenanceStrategy::RECURRING_WEEKDAY->value);
        $this->assertSame('cron', MaintenanceStrategy::CRON->value);
    }

    public function testAuthMethodEnum(): void
    {
        $this->assertSame('', AuthMethod::NONE->value);
        $this->assertSame('basic', AuthMethod::HTTP_BASIC->value);
        $this->assertSame('ntlm', AuthMethod::NTLM->value);
        $this->assertSame('mtls', AuthMethod::MTLS->value);
        $this->assertSame('oauth2-cc', AuthMethod::OAUTH2_CC->value);
    }

    public function testDnsRecordTypeEnum(): void
    {
        $this->assertSame('A', DnsRecordType::A->value);
        $this->assertSame('AAAA', DnsRecordType::AAAA->value);
        $this->assertSame('CNAME', DnsRecordType::CNAME->value);
        $this->assertSame('MX', DnsRecordType::MX->value);
        $this->assertSame('TXT', DnsRecordType::TXT->value);
    }

    // =========================================================================
    // Error Handling Tests
    // =========================================================================

    public function testHandles401Unauthorized(): void
    {
        $mock = new MockHandler([
            new Response(401, [], json_encode(['msg' => 'Unauthorized'])),
        ]);

        $client = $this->makeClient($mock)->usingToken('invalid-token');

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Unauthorized');

        $client->monitors();
    }

    public function testHandlesOkFalseResponse(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['ok' => false, 'msg' => 'Operation failed'])),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');

        $this->expectException(UptimeKumaException::class);
        $this->expectExceptionMessage('Operation failed');

        $client->monitors();
    }

    public function testHandlesInvalidJsonResponse(): void
    {
        $mock = new MockHandler([
            new Response(200, [], 'not valid json'),
        ]);

        $client = $this->makeClient($mock)->usingToken('abc123');

        $this->expectException(UptimeKumaException::class);
        $this->expectExceptionMessage('Unable to decode JSON response from Uptime Kuma.');

        $client->monitors();
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    private function makeClient(MockHandler $mock, ?string $username = null, ?string $password = null): UptimeKumaClient
    {
        $http = new Client([
            'handler' => HandlerStack::create($mock),
            'http_errors' => false,
        ]);

        return new UptimeKumaClient('https://example.com', $username, $password, null, $http);
    }
}
