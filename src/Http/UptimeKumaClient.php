<?php

namespace UptimeKuma\LaravelApi\Http;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use UptimeKuma\LaravelApi\Exceptions\AuthenticationException;
use UptimeKuma\LaravelApi\Exceptions\UptimeKumaException;

class UptimeKumaClient
{
    protected ClientInterface $http;

    protected string $baseUrl;

    protected ?string $username;

    protected ?string $password;

    protected ?string $twoFactorToken;

    protected ?string $token = null;

    public function __construct(
        string $baseUrl,
        ?string $username = null,
        ?string $password = null,
        ?string $twoFactorToken = null,
        ?ClientInterface $http = null
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->username = $username;
        $this->password = $password;
        $this->twoFactorToken = $twoFactorToken;
        $this->http = $http ?: new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function usingToken(?string $token): static
    {
        $clone = clone $this;
        $clone->token = $token;

        return $clone;
    }

    public function token(): ?string
    {
        return $this->token;
    }

    // =========================================================================
    // Authentication Methods
    // =========================================================================

    /**
     * Authenticate against the server.
     *
     * @throws AuthenticationException
     */
    public function login(?string $username = null, ?string $password = null, ?string $twoFactorToken = null): array
    {
        $payload = [
            'username' => $username ?? $this->username,
            'password' => $password ?? $this->password,
            'token' => $twoFactorToken ?? $this->twoFactorToken ?? '',
        ];

        if (empty($payload['username']) && empty($payload['password'])) {
            // fallback to auto-login when authentication is disabled
            $payload = [];
        }

        $response = $this->request('POST', '/api/login', ['json' => $payload]);

        $token = $response['token'] ?? $response['data']['token'] ?? null;
        if (! $token) {
            throw new AuthenticationException('Uptime Kuma server did not return an authentication token.');
        }

        $this->token = $token;

        return $response;
    }

    /**
     * Log out from the server.
     */
    public function logout(): void
    {
        $this->ensureAuthenticated();

        $this->request('POST', '/api/logout');
        $this->token = null;
    }

    // =========================================================================
    // Server Info Methods
    // =========================================================================

    /**
     * Get server information.
     */
    public function info(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/info');
    }

    /**
     * Get the Uptime Kuma version.
     */
    public function version(): string
    {
        $info = $this->info();

        return $info['version'] ?? '';
    }

    /**
     * Get server timezone.
     */
    public function serverTimezone(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/server-timezone');
    }

    // =========================================================================
    // Monitor Methods
    // =========================================================================

    /**
     * Get all monitors.
     */
    public function monitors(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/monitors');
    }

    /**
     * Alias for monitors().
     */
    public function getMonitors(): array
    {
        return $this->monitors();
    }

    /**
     * Get a specific monitor by ID.
     */
    public function monitor(int $monitorId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/monitors/{$monitorId}");
    }

    /**
     * Alias for monitor().
     */
    public function getMonitor(int $monitorId): array
    {
        return $this->monitor($monitorId);
    }

    /**
     * Create a new monitor.
     *
     * @param array $attributes Monitor configuration attributes
     */
    public function createMonitor(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/monitors', ['json' => $attributes]);
    }

    /**
     * Alias for createMonitor().
     */
    public function addMonitor(array $attributes): array
    {
        return $this->createMonitor($attributes);
    }

    /**
     * Update an existing monitor.
     *
     * @param int   $monitorId  The monitor ID
     * @param array $attributes Attributes to update
     */
    public function updateMonitor(int $monitorId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/monitors/{$monitorId}", ['json' => $attributes]);
    }

    /**
     * Alias for updateMonitor().
     */
    public function editMonitor(int $monitorId, array $attributes): array
    {
        return $this->updateMonitor($monitorId, $attributes);
    }

    /**
     * Delete a monitor.
     */
    public function deleteMonitor(int $monitorId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/monitors/{$monitorId}");
    }

    /**
     * Pause a monitor.
     */
    public function pauseMonitor(int $monitorId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/pause");
    }

    /**
     * Resume a paused monitor.
     */
    public function resumeMonitor(int $monitorId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/resume");
    }

    /**
     * Get heartbeats for a monitor.
     *
     * @param int $monitorId The monitor ID
     * @param int $hours     Number of hours of heartbeat history (default: 24)
     */
    public function heartbeats(int $monitorId, int $hours = 24): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/monitors/{$monitorId}/heartbeats", [
            'query' => ['hours' => $hours],
        ]);
    }

    /**
     * Alias for heartbeats().
     */
    public function getMonitorBeats(int $monitorId, int $hours = 24): array
    {
        return $this->heartbeats($monitorId, $hours);
    }

    /**
     * Get all heartbeats for all monitors.
     */
    public function getAllHeartbeats(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/heartbeats');
    }

    /**
     * Get important heartbeats for all monitors.
     */
    public function getImportantHeartbeats(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/heartbeats/important');
    }

    /**
     * Send a custom heartbeat for a push monitor.
     *
     * @param int        $monitorId The monitor ID
     * @param array|null $payload   Optional payload data
     */
    public function sendHeartbeat(int $monitorId, ?array $payload = null): array
    {
        $payload = $payload ?? ['status' => 'up'];
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/heartbeat", ['json' => $payload]);
    }

    /**
     * Get average ping for all monitors.
     */
    public function avgPing(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/avg-ping');
    }

    /**
     * Get uptime data for all monitors.
     */
    public function uptime(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/uptime');
    }

    /**
     * Get certificate info for all monitors.
     */
    public function certInfo(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/cert-info');
    }

    // =========================================================================
    // Monitor Tag Methods
    // =========================================================================

    /**
     * Add a tag to a monitor.
     *
     * @param int    $tagId     The tag ID
     * @param int    $monitorId The monitor ID
     * @param string $value     Optional tag value
     */
    public function addMonitorTag(int $tagId, int $monitorId, string $value = ''): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/tags", [
            'json' => [
                'tag_id' => $tagId,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Delete a tag from a monitor.
     *
     * @param int    $tagId     The tag ID
     * @param int    $monitorId The monitor ID
     * @param string $value     Optional tag value
     */
    public function deleteMonitorTag(int $tagId, int $monitorId, string $value = ''): array
    {
        $this->ensureAuthenticated();

        return $this->request('DELETE', "/api/monitors/{$monitorId}/tags/{$tagId}", [
            'json' => ['value' => $value],
        ]);
    }

    // =========================================================================
    // Notification Methods
    // =========================================================================

    /**
     * Get all notifications.
     */
    public function notifications(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/notifications');
    }

    /**
     * Alias for notifications().
     */
    public function getNotifications(): array
    {
        return $this->notifications();
    }

    /**
     * Get a specific notification by ID.
     */
    public function notification(int $notificationId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/notifications/{$notificationId}");
    }

    /**
     * Alias for notification().
     */
    public function getNotification(int $notificationId): array
    {
        return $this->notification($notificationId);
    }

    /**
     * Create a new notification.
     *
     * @param array $attributes Notification configuration
     */
    public function createNotification(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/notifications', ['json' => $attributes]);
    }

    /**
     * Alias for createNotification().
     */
    public function addNotification(array $attributes): array
    {
        return $this->createNotification($attributes);
    }

    /**
     * Update an existing notification.
     *
     * @param int   $notificationId The notification ID
     * @param array $attributes     Attributes to update
     */
    public function updateNotification(int $notificationId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/notifications/{$notificationId}", ['json' => $attributes]);
    }

    /**
     * Alias for updateNotification().
     */
    public function editNotification(int $notificationId, array $attributes): array
    {
        return $this->updateNotification($notificationId, $attributes);
    }

    /**
     * Delete a notification.
     */
    public function deleteNotification(int $notificationId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/notifications/{$notificationId}");
    }

    /**
     * Test a notification configuration.
     *
     * @param array $attributes Notification configuration to test
     */
    public function testNotification(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/notifications/test', ['json' => $attributes]);
    }

    /**
     * Check if Apprise is available.
     */
    public function checkApprise(): bool
    {
        $this->ensureAuthenticated();

        $response = $this->request('GET', '/api/apprise');

        return $response['ok'] ?? false;
    }

    // =========================================================================
    // Proxy Methods
    // =========================================================================

    /**
     * Get all proxies.
     */
    public function proxies(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/proxies');
    }

    /**
     * Alias for proxies().
     */
    public function getProxies(): array
    {
        return $this->proxies();
    }

    /**
     * Get a specific proxy by ID.
     */
    public function proxy(int $proxyId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/proxies/{$proxyId}");
    }

    /**
     * Alias for proxy().
     */
    public function getProxy(int $proxyId): array
    {
        return $this->proxy($proxyId);
    }

    /**
     * Create a new proxy.
     *
     * @param array $attributes Proxy configuration
     */
    public function createProxy(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/proxies', ['json' => $attributes]);
    }

    /**
     * Alias for createProxy().
     */
    public function addProxy(array $attributes): array
    {
        return $this->createProxy($attributes);
    }

    /**
     * Update an existing proxy.
     *
     * @param int   $proxyId    The proxy ID
     * @param array $attributes Attributes to update
     */
    public function updateProxy(int $proxyId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/proxies/{$proxyId}", ['json' => $attributes]);
    }

    /**
     * Alias for updateProxy().
     */
    public function editProxy(int $proxyId, array $attributes): array
    {
        return $this->updateProxy($proxyId, $attributes);
    }

    /**
     * Delete a proxy.
     */
    public function deleteProxy(int $proxyId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/proxies/{$proxyId}");
    }

    // =========================================================================
    // Status Page Methods
    // =========================================================================

    /**
     * Get all status pages.
     */
    public function statusPages(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/status-pages');
    }

    /**
     * Alias for statusPages().
     */
    public function getStatusPages(): array
    {
        return $this->statusPages();
    }

    /**
     * Get a specific status page by slug.
     */
    public function statusPage(string $slug): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/status-pages/{$slug}");
    }

    /**
     * Alias for statusPage().
     */
    public function getStatusPage(string $slug): array
    {
        return $this->statusPage($slug);
    }

    /**
     * Create a new status page.
     *
     * @param string $slug  Unique slug for the status page
     * @param string $title Display title
     */
    public function createStatusPage(string $slug, string $title): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/status-pages', [
            'json' => [
                'slug' => $slug,
                'title' => $title,
            ],
        ]);
    }

    /**
     * Alias for createStatusPage().
     */
    public function addStatusPage(string $slug, string $title): array
    {
        return $this->createStatusPage($slug, $title);
    }

    /**
     * Update/save a status page configuration.
     *
     * @param string $slug       The status page slug
     * @param array  $attributes Configuration attributes
     */
    public function saveStatusPage(string $slug, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/status-pages/{$slug}", ['json' => $attributes]);
    }

    /**
     * Delete a status page.
     */
    public function deleteStatusPage(string $slug): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/status-pages/{$slug}");
    }

    /**
     * Post an incident to a status page.
     *
     * @param string $slug    The status page slug
     * @param string $title   Incident title
     * @param string $content Incident description
     * @param string $style   Incident style (primary, info, warning, danger, success)
     */
    public function postIncident(string $slug, string $title, string $content, string $style = 'danger'): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/status-pages/{$slug}/incidents", [
            'json' => [
                'title' => $title,
                'content' => $content,
                'style' => $style,
            ],
        ]);
    }

    /**
     * Unpin/remove an incident from a status page.
     */
    public function unpinIncident(string $slug): array
    {
        $this->ensureAuthenticated();

        return $this->request('DELETE', "/api/status-pages/{$slug}/incidents");
    }

    // =========================================================================
    // Docker Host Methods
    // =========================================================================

    /**
     * Get all Docker hosts.
     */
    public function dockerHosts(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/docker-hosts');
    }

    /**
     * Alias for dockerHosts().
     */
    public function getDockerHosts(): array
    {
        return $this->dockerHosts();
    }

    /**
     * Get a specific Docker host by ID.
     */
    public function dockerHost(int $dockerHostId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/docker-hosts/{$dockerHostId}");
    }

    /**
     * Alias for dockerHost().
     */
    public function getDockerHost(int $dockerHostId): array
    {
        return $this->dockerHost($dockerHostId);
    }

    /**
     * Create a new Docker host.
     *
     * @param array $attributes Docker host configuration
     */
    public function createDockerHost(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/docker-hosts', ['json' => $attributes]);
    }

    /**
     * Alias for createDockerHost().
     */
    public function addDockerHost(array $attributes): array
    {
        return $this->createDockerHost($attributes);
    }

    /**
     * Update an existing Docker host.
     *
     * @param int   $dockerHostId The Docker host ID
     * @param array $attributes   Attributes to update
     */
    public function updateDockerHost(int $dockerHostId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/docker-hosts/{$dockerHostId}", ['json' => $attributes]);
    }

    /**
     * Alias for updateDockerHost().
     */
    public function editDockerHost(int $dockerHostId, array $attributes): array
    {
        return $this->updateDockerHost($dockerHostId, $attributes);
    }

    /**
     * Delete a Docker host.
     */
    public function deleteDockerHost(int $dockerHostId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/docker-hosts/{$dockerHostId}");
    }

    /**
     * Test a Docker host connection.
     *
     * @param array $attributes Docker host configuration to test
     */
    public function testDockerHost(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/docker-hosts/test', ['json' => $attributes]);
    }

    // =========================================================================
    // Tag Methods
    // =========================================================================

    /**
     * Get all tags.
     */
    public function tags(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/tags');
    }

    /**
     * Alias for tags().
     */
    public function getTags(): array
    {
        return $this->tags();
    }

    /**
     * Get a specific tag by ID.
     */
    public function tag(int $tagId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/tags/{$tagId}");
    }

    /**
     * Alias for tag().
     */
    public function getTag(int $tagId): array
    {
        return $this->tag($tagId);
    }

    /**
     * Create a new tag.
     *
     * @param array $attributes Tag configuration (name, color)
     */
    public function createTag(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/tags', ['json' => $attributes]);
    }

    /**
     * Alias for createTag().
     */
    public function addTag(array $attributes): array
    {
        return $this->createTag($attributes);
    }

    /**
     * Update an existing tag.
     *
     * @param int   $tagId      The tag ID
     * @param array $attributes Attributes to update
     */
    public function updateTag(int $tagId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/tags/{$tagId}", ['json' => $attributes]);
    }

    /**
     * Alias for updateTag().
     */
    public function editTag(int $tagId, array $attributes): array
    {
        return $this->updateTag($tagId, $attributes);
    }

    /**
     * Delete a tag.
     */
    public function deleteTag(int $tagId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/tags/{$tagId}");
    }

    // =========================================================================
    // Maintenance Methods
    // =========================================================================

    /**
     * Get all maintenance windows.
     */
    public function maintenances(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/maintenances');
    }

    /**
     * Alias for maintenances().
     */
    public function getMaintenances(): array
    {
        return $this->maintenances();
    }

    /**
     * Get a specific maintenance window by ID.
     */
    public function maintenance(int $maintenanceId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/maintenances/{$maintenanceId}");
    }

    /**
     * Alias for maintenance().
     */
    public function getMaintenance(int $maintenanceId): array
    {
        return $this->maintenance($maintenanceId);
    }

    /**
     * Create a new maintenance window.
     *
     * @param array $attributes Maintenance configuration
     */
    public function createMaintenance(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/maintenances', ['json' => $attributes]);
    }

    /**
     * Alias for createMaintenance().
     */
    public function addMaintenance(array $attributes): array
    {
        return $this->createMaintenance($attributes);
    }

    /**
     * Update an existing maintenance window.
     *
     * @param int   $maintenanceId The maintenance ID
     * @param array $attributes    Attributes to update
     */
    public function updateMaintenance(int $maintenanceId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/maintenances/{$maintenanceId}", ['json' => $attributes]);
    }

    /**
     * Alias for updateMaintenance().
     */
    public function editMaintenance(int $maintenanceId, array $attributes): array
    {
        return $this->updateMaintenance($maintenanceId, $attributes);
    }

    /**
     * Delete a maintenance window.
     */
    public function deleteMaintenance(int $maintenanceId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/maintenances/{$maintenanceId}");
    }

    /**
     * Pause a maintenance window.
     */
    public function pauseMaintenance(int $maintenanceId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/maintenances/{$maintenanceId}/pause");
    }

    /**
     * Resume a paused maintenance window.
     */
    public function resumeMaintenance(int $maintenanceId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/maintenances/{$maintenanceId}/resume");
    }

    /**
     * Get monitors associated with a maintenance window.
     */
    public function getMaintenanceMonitors(int $maintenanceId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/maintenances/{$maintenanceId}/monitors");
    }

    /**
     * Add monitors to a maintenance window.
     *
     * @param int   $maintenanceId The maintenance ID
     * @param array $monitorIds    Array of monitor IDs
     */
    public function addMaintenanceMonitors(int $maintenanceId, array $monitorIds): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/maintenances/{$maintenanceId}/monitors", [
            'json' => ['monitors' => $monitorIds],
        ]);
    }

    /**
     * Get status pages associated with a maintenance window.
     */
    public function getMaintenanceStatusPages(int $maintenanceId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/maintenances/{$maintenanceId}/status-pages");
    }

    /**
     * Add status pages to a maintenance window.
     *
     * @param int   $maintenanceId The maintenance ID
     * @param array $statusPages   Array of status page slugs
     */
    public function addMaintenanceStatusPages(int $maintenanceId, array $statusPages): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/maintenances/{$maintenanceId}/status-pages", [
            'json' => ['statusPages' => $statusPages],
        ]);
    }

    // =========================================================================
    // API Key Methods
    // =========================================================================

    /**
     * Get all API keys.
     */
    public function apiKeys(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/api-keys');
    }

    /**
     * Alias for apiKeys().
     */
    public function getApiKeys(): array
    {
        return $this->apiKeys();
    }

    /**
     * Get a specific API key by ID.
     */
    public function apiKey(int $apiKeyId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/api-keys/{$apiKeyId}");
    }

    /**
     * Alias for apiKey().
     */
    public function getApiKey(int $apiKeyId): array
    {
        return $this->apiKey($apiKeyId);
    }

    /**
     * Create a new API key.
     *
     * @param array $attributes API key configuration (name, expires, active)
     */
    public function createApiKey(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/api-keys', ['json' => $attributes]);
    }

    /**
     * Alias for createApiKey().
     */
    public function addApiKey(array $attributes): array
    {
        return $this->createApiKey($attributes);
    }

    /**
     * Delete an API key.
     */
    public function deleteApiKey(int $apiKeyId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/api-keys/{$apiKeyId}");
    }

    /**
     * Enable an API key.
     */
    public function enableApiKey(int $apiKeyId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/api-keys/{$apiKeyId}/enable");
    }

    /**
     * Disable an API key.
     */
    public function disableApiKey(int $apiKeyId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/api-keys/{$apiKeyId}/disable");
    }

    // =========================================================================
    // Game Server Methods
    // =========================================================================

    /**
     * Get list of supported game types for GameDig monitors.
     */
    public function gameList(): array
    {
        return $this->request('GET', '/api/game-list');
    }

    /**
     * Alias for gameList().
     */
    public function getGameList(): array
    {
        return $this->gameList();
    }

    // =========================================================================
    // Settings & Database Methods
    // =========================================================================

    /**
     * Get server settings.
     */
    public function settings(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/settings');
    }

    /**
     * Update server settings.
     *
     * @param array $attributes Settings to update
     */
    public function updateSettings(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', '/api/settings', ['json' => $attributes]);
    }

    /**
     * Change the server password.
     *
     * @param string $currentPassword Current password
     * @param string $newPassword     New password
     */
    public function changePassword(string $currentPassword, string $newPassword): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/change-password', [
            'json' => [
                'currentPassword' => $currentPassword,
                'newPassword' => $newPassword,
            ],
        ]);
    }

    /**
     * Backup the database.
     */
    public function backup(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/backup');
    }

    /**
     * Restore from a backup.
     *
     * @param array $backupData Backup data to restore
     */
    public function restore(array $backupData): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/restore', ['json' => $backupData]);
    }

    /**
     * Clear all events/statistics.
     */
    public function clearEvents(): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/clear-events');
    }

    /**
     * Clear all heartbeat data.
     */
    public function clearHeartbeats(): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/clear-heartbeats');
    }

    /**
     * Shrink the database.
     */
    public function shrinkDatabase(): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/shrink-database');
    }

    // =========================================================================
    // 2FA Methods
    // =========================================================================

    /**
     * Prepare 2FA setup (get secret and QR code).
     */
    public function prepare2FA(): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/2fa/prepare');
    }

    /**
     * Verify and enable 2FA.
     *
     * @param string $token The 2FA token to verify
     */
    public function verify2FA(string $token): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/2fa/verify', [
            'json' => ['token' => $token],
        ]);
    }

    /**
     * Disable 2FA.
     */
    public function disable2FA(): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/2fa/disable');
    }

    /**
     * Get 2FA status.
     */
    public function twoFAStatus(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/2fa/status');
    }

    // =========================================================================
    // Remote Browser Methods
    // =========================================================================

    /**
     * Get remote browsers list.
     */
    public function remoteBrowsers(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/remote-browsers');
    }

    /**
     * Alias for remoteBrowsers().
     */
    public function getRemoteBrowsers(): array
    {
        return $this->remoteBrowsers();
    }

    /**
     * Add a remote browser.
     *
     * @param array $attributes Remote browser configuration
     */
    public function addRemoteBrowser(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/remote-browsers', ['json' => $attributes]);
    }

    /**
     * Update a remote browser.
     *
     * @param int   $remoteBrowserId The remote browser ID
     * @param array $attributes      Attributes to update
     */
    public function updateRemoteBrowser(int $remoteBrowserId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/remote-browsers/{$remoteBrowserId}", ['json' => $attributes]);
    }

    /**
     * Delete a remote browser.
     */
    public function deleteRemoteBrowser(int $remoteBrowserId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/remote-browsers/{$remoteBrowserId}");
    }

    // =========================================================================
    // Utility Methods
    // =========================================================================

    /**
     * Test Chrome/Chromium browser availability.
     *
     * @param string $executable Path to Chrome/Chromium executable
     */
    public function testChrome(string $executable): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/test-chrome', [
            'json' => ['executable' => $executable],
        ]);
    }

    // =========================================================================
    // Internal Methods
    // =========================================================================

    protected function ensureAuthenticated(): void
    {
        if (! $this->token) {
            if ($this->username || $this->password) {
                $this->login();
            } else {
                throw new AuthenticationException('The Uptime Kuma client is not authenticated.');
            }
        }
    }

    /**
     * @throws UptimeKumaException
     */
    protected function request(string $method, string $uri, array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        if ($this->token) {
            $headers['Authorization'] = 'Bearer '.$this->token;
        }

        $options['headers'] = $headers;

        try {
            $response = $this->http->request($method, ltrim($uri, '/'), $options);
        } catch (GuzzleException $exception) {
            throw new UptimeKumaException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        return $this->normalizeResponse($response);
    }

    /**
     * @throws UptimeKumaException
     */
    protected function normalizeResponse(ResponseInterface $response): array
    {
        $status = $response->getStatusCode();
        $contents = (string) $response->getBody();
        $data = $contents !== '' ? json_decode($contents, true) : [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UptimeKumaException('Unable to decode JSON response from Uptime Kuma.');
        }

        if ($status >= 400) {
            $message = $data['msg'] ?? $data['message'] ?? $response->getReasonPhrase();
            if ($status === 401) {
                throw new AuthenticationException($message, $status);
            }

            throw new UptimeKumaException($message, $status);
        }

        if (isset($data['ok']) && $data['ok'] === false) {
            $message = $data['msg'] ?? 'The Uptime Kuma server rejected the request.';
            throw new UptimeKumaException($message);
        }

        return $data;
    }
}
