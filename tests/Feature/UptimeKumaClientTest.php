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

class UptimeKumaClientTest extends TestCase
{
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
        $this->assertSame([ 'data' => [['id' => 1, 'name' => 'Example']] ], $monitors);
    }

    private function makeClient(MockHandler $mock, ?string $username = null, ?string $password = null): UptimeKumaClient
    {
        $http = new Client([
            'handler' => HandlerStack::create($mock),
            'http_errors' => false,
        ]);

        return new UptimeKumaClient('https://example.com', $username, $password, null, $http);
    }
}
