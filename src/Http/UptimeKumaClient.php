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

    public function logout(): void
    {
        $this->ensureAuthenticated();

        $this->request('POST', '/api/logout');
        $this->token = null;
    }

    public function monitors(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/monitors');
    }

    public function monitor(int $monitorId): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/monitors/{$monitorId}");
    }

    public function createMonitor(array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', '/api/monitors', ['json' => $attributes]);
    }

    public function updateMonitor(int $monitorId, array $attributes): array
    {
        $this->ensureAuthenticated();

        return $this->request('PUT', "/api/monitors/{$monitorId}", ['json' => $attributes]);
    }

    public function deleteMonitor(int $monitorId): void
    {
        $this->ensureAuthenticated();

        $this->request('DELETE', "/api/monitors/{$monitorId}");
    }

    public function pauseMonitor(int $monitorId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/pause");
    }

    public function resumeMonitor(int $monitorId): array
    {
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/resume");
    }

    public function heartbeats(int $monitorId, int $limit = 50): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', "/api/monitors/{$monitorId}/heartbeats", [
            'query' => ['limit' => $limit],
        ]);
    }

    public function statusPages(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/status-pages');
    }

    public function proxies(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/proxies');
    }

    public function notifications(): array
    {
        $this->ensureAuthenticated();

        return $this->request('GET', '/api/notifications');
    }

    public function sendHeartbeat(int $monitorId, ?array $payload = null): array
    {
        $payload = $payload ?? ['status' => 'up'];
        $this->ensureAuthenticated();

        return $this->request('POST', "/api/monitors/{$monitorId}/heartbeat", ['json' => $payload]);
    }

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
