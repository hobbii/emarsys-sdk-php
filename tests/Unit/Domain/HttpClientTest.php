<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Domain\HttpClient;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{
    private array $requestHistory = [];

    private function createHttpClientWithMockHandler(array $responses): HttpClient
    {
        $this->requestHistory = [];
        $history = Middleware::history($this->requestHistory);

        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($history);

        // Use empty base_uri to ensure absolute URLs work
        $guzzleClient = new GuzzleClient([
            'handler' => $handlerStack,
            'base_uri' => '',
        ]);

        return new HttpClient('test-client-id', 'test-client-secret', null, $guzzleClient);
    }

    public function test_oauth_token_refresh_on_successful_auth(): void
    {
        $responses = [
            // OAuth token request succeeds
            new GuzzleResponse(200, [], json_encode([
                'access_token' => 'test-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ])),
            // API request succeeds
            new GuzzleResponse(200, [], json_encode([
                'replyCode' => 0,
                'replyText' => 'OK',
                'data' => ['test' => 'data'],
                'errors' => [],
            ])),
        ];

        $client = $this->createHttpClientWithMockHandler($responses);

        $response = $client->get('test-endpoint');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(0, $response->replyCode);
        $this->assertSame('OK', $response->replyText);
        $this->assertSame(['test' => 'data'], $response->data);

        // Should make 2 requests: OAuth + API
        $this->assertCount(2, $this->requestHistory);
    }

    public function test_oauth_token_refresh_failure_throws_authentication_exception(): void
    {
        $responses = [
            // OAuth token request fails
            new ClientException(
                'Unauthorized',
                new Request('POST', 'https://auth.emarsys.net/oauth2/token/'),
                new GuzzleResponse(401, [], json_encode([
                    'error' => 'invalid_client',
                    'error_description' => 'Client authentication failed',
                ]))
            ),
        ];

        $client = $this->createHttpClientWithMockHandler($responses);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('OAuth authentication failed');

        $client->get('test-endpoint');
    }

    public function test_successful_api_request_with_fresh_token(): void
    {
        $responses = [
            // OAuth token request
            new GuzzleResponse(200, [], json_encode([
                'access_token' => 'fresh-token',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
            ])),
            // API request succeeds
            new GuzzleResponse(200, [], json_encode([
                'replyCode' => 0,
                'replyText' => 'OK',
                'data' => ['success' => true],
                'errors' => [],
            ])),
        ];

        $client = $this->createHttpClientWithMockHandler($responses);

        $response = $client->post('test-endpoint', ['test' => 'data']);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(0, $response->replyCode);
        $this->assertSame(['success' => true], $response->data);

        // Verify authorization header is present in API request
        $this->assertCount(2, $this->requestHistory);
        $apiRequest = $this->requestHistory[1]['request'];
        $this->assertTrue($apiRequest->hasHeader('Authorization'));
        $this->assertStringContainsString('Bearer fresh-token', $apiRequest->getHeader('Authorization')[0]);
    }
}
