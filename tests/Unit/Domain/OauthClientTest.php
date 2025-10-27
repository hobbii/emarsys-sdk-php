<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Tests\Unit\Domain;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Domain\OauthClient;
use PHPUnit\Framework\TestCase;

class OauthClientTest extends TestCase
{
    public function test_refresh_oauth_data_success(): void
    {
        $mock = new MockHandler([
            new PsrResponse(200, [], (string) json_encode([
                'access_token' => 'test_token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new GuzzleClient(['handler' => $handlerStack]);

        $oauthClient = new OauthClient('id', 'secret', $client);
        $options = $oauthClient->addAuthHeadersToRequestOptions([]);
        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('Bearer test_token', $options['headers']['Authorization']);
    }

    public function test_refresh_oauth_data_failure(): void
    {
        $mock = new MockHandler([
            new PsrResponse(401, [], (string) json_encode([
                'error' => 'invalid_client',
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new GuzzleClient(['handler' => $handlerStack]);

        $oauthClient = new OauthClient('id', 'secret', $client);

        $this->expectException(AuthenticationException::class);
        $oauthClient->addAuthHeadersToRequestOptions([]);
    }

    public function test_reset_oauth_data(): void
    {
        $mock = new MockHandler([
            new PsrResponse(200, [], (string) json_encode([
                'access_token' => 'token1',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ])),
            new PsrResponse(200, [], (string) json_encode([
                'access_token' => 'token2',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ])),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $client = new GuzzleClient(['handler' => $handlerStack]);

        $oauthClient = new OauthClient('id', 'secret', $client);
        $this->assertEquals('Bearer token1', $oauthClient->addAuthHeadersToRequestOptions([])['headers']['Authorization']);

        $oauthClient->resetOauthData();
        $this->assertEquals('Bearer token2', $oauthClient->addAuthHeadersToRequestOptions([])['headers']['Authorization']);
    }
}
