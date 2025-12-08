<?php

namespace UptimeKuma\LaravelApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array login(?string $username = null, ?string $password = null, ?string $twoFactorToken = null)
 * @method static void logout()
 * @method static string|null token()
 * @method static static usingToken(?string $token)
 *
 * Server Info Methods
 * @method static array info()
 * @method static string version()
 * @method static array serverTimezone()
 *
 * Monitor Methods
 * @method static array monitors()
 * @method static array getMonitors()
 * @method static array monitor(int $monitorId)
 * @method static array getMonitor(int $monitorId)
 * @method static array createMonitor(array $attributes)
 * @method static array addMonitor(array $attributes)
 * @method static array updateMonitor(int $monitorId, array $attributes)
 * @method static array editMonitor(int $monitorId, array $attributes)
 * @method static void deleteMonitor(int $monitorId)
 * @method static array pauseMonitor(int $monitorId)
 * @method static array resumeMonitor(int $monitorId)
 * @method static array heartbeats(int $monitorId, int $hours = 24)
 * @method static array getMonitorBeats(int $monitorId, int $hours = 24)
 * @method static array getAllHeartbeats()
 * @method static array getImportantHeartbeats()
 * @method static array sendHeartbeat(int $monitorId, ?array $payload = null)
 * @method static array avgPing()
 * @method static array uptime()
 * @method static array certInfo()
 *
 * Monitor Tag Methods
 * @method static array addMonitorTag(int $tagId, int $monitorId, string $value = '')
 * @method static array deleteMonitorTag(int $tagId, int $monitorId, string $value = '')
 *
 * Notification Methods
 * @method static array notifications()
 * @method static array getNotifications()
 * @method static array notification(int $notificationId)
 * @method static array getNotification(int $notificationId)
 * @method static array createNotification(array $attributes)
 * @method static array addNotification(array $attributes)
 * @method static array updateNotification(int $notificationId, array $attributes)
 * @method static array editNotification(int $notificationId, array $attributes)
 * @method static void deleteNotification(int $notificationId)
 * @method static array testNotification(array $attributes)
 * @method static bool checkApprise()
 *
 * Proxy Methods
 * @method static array proxies()
 * @method static array getProxies()
 * @method static array proxy(int $proxyId)
 * @method static array getProxy(int $proxyId)
 * @method static array createProxy(array $attributes)
 * @method static array addProxy(array $attributes)
 * @method static array updateProxy(int $proxyId, array $attributes)
 * @method static array editProxy(int $proxyId, array $attributes)
 * @method static void deleteProxy(int $proxyId)
 *
 * Status Page Methods
 * @method static array statusPages()
 * @method static array getStatusPages()
 * @method static array statusPage(string $slug)
 * @method static array getStatusPage(string $slug)
 * @method static array createStatusPage(string $slug, string $title)
 * @method static array addStatusPage(string $slug, string $title)
 * @method static array saveStatusPage(string $slug, array $attributes)
 * @method static void deleteStatusPage(string $slug)
 * @method static array postIncident(string $slug, string $title, string $content, string $style = 'danger')
 * @method static array unpinIncident(string $slug)
 *
 * Docker Host Methods
 * @method static array dockerHosts()
 * @method static array getDockerHosts()
 * @method static array dockerHost(int $dockerHostId)
 * @method static array getDockerHost(int $dockerHostId)
 * @method static array createDockerHost(array $attributes)
 * @method static array addDockerHost(array $attributes)
 * @method static array updateDockerHost(int $dockerHostId, array $attributes)
 * @method static array editDockerHost(int $dockerHostId, array $attributes)
 * @method static void deleteDockerHost(int $dockerHostId)
 * @method static array testDockerHost(array $attributes)
 *
 * Tag Methods
 * @method static array tags()
 * @method static array getTags()
 * @method static array tag(int $tagId)
 * @method static array getTag(int $tagId)
 * @method static array createTag(array $attributes)
 * @method static array addTag(array $attributes)
 * @method static array updateTag(int $tagId, array $attributes)
 * @method static array editTag(int $tagId, array $attributes)
 * @method static void deleteTag(int $tagId)
 *
 * Maintenance Methods
 * @method static array maintenances()
 * @method static array getMaintenances()
 * @method static array maintenance(int $maintenanceId)
 * @method static array getMaintenance(int $maintenanceId)
 * @method static array createMaintenance(array $attributes)
 * @method static array addMaintenance(array $attributes)
 * @method static array updateMaintenance(int $maintenanceId, array $attributes)
 * @method static array editMaintenance(int $maintenanceId, array $attributes)
 * @method static void deleteMaintenance(int $maintenanceId)
 * @method static array pauseMaintenance(int $maintenanceId)
 * @method static array resumeMaintenance(int $maintenanceId)
 * @method static array getMaintenanceMonitors(int $maintenanceId)
 * @method static array addMaintenanceMonitors(int $maintenanceId, array $monitorIds)
 * @method static array getMaintenanceStatusPages(int $maintenanceId)
 * @method static array addMaintenanceStatusPages(int $maintenanceId, array $statusPages)
 *
 * API Key Methods
 * @method static array apiKeys()
 * @method static array getApiKeys()
 * @method static array apiKey(int $apiKeyId)
 * @method static array getApiKey(int $apiKeyId)
 * @method static array createApiKey(array $attributes)
 * @method static array addApiKey(array $attributes)
 * @method static void deleteApiKey(int $apiKeyId)
 * @method static array enableApiKey(int $apiKeyId)
 * @method static array disableApiKey(int $apiKeyId)
 *
 * Game Server Methods
 * @method static array gameList()
 * @method static array getGameList()
 *
 * Settings & Database Methods
 * @method static array settings()
 * @method static array updateSettings(array $attributes)
 * @method static array changePassword(string $currentPassword, string $newPassword)
 * @method static array backup()
 * @method static array restore(array $backupData)
 * @method static array clearEvents()
 * @method static array clearHeartbeats()
 * @method static array shrinkDatabase()
 *
 * 2FA Methods
 * @method static array prepare2FA()
 * @method static array verify2FA(string $token)
 * @method static array disable2FA()
 * @method static array twoFAStatus()
 *
 * Remote Browser Methods
 * @method static array remoteBrowsers()
 * @method static array getRemoteBrowsers()
 * @method static array addRemoteBrowser(array $attributes)
 * @method static array updateRemoteBrowser(int $remoteBrowserId, array $attributes)
 * @method static void deleteRemoteBrowser(int $remoteBrowserId)
 *
 * Utility Methods
 * @method static array testChrome(string $executable)
 *
 * @see \UptimeKuma\LaravelApi\Http\UptimeKumaClient
 */
class UptimeKuma extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'uptime-kuma';
    }
}
