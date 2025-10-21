<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Base HTTP client for Emarsys API communication.
 */
class HttpClient
{
    /**
     * Base Emarsys API v3 endpoint.
     *
     * Important:
     * Guzzle follows RFC 3986 URI resolution rules - if the base_uri does not end with /, it’s considered a file.
     */
    private const BASE_URL = 'https://api.emarsys.net/api/v3/';

    private const OAUTH2_TOKEN_URL = 'https://auth.emarsys.net/oauth2/token';

    private readonly GuzzleClient $client;

    private ?string $accessToken = null;

    private ?int $tokenExpiresAt = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        ?string $baseUrl = null
    ) {
        $this->client = new GuzzleClient([
            'base_uri' => $baseUrl ?? self::BASE_URL,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Make a GET request to the API.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, [
            'query' => $query,
        ]);
    }

    /**
     * Make a POST request to the API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Make a PUT request to the API.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Make a DELETE request to the API.
     *
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make an authenticated request to the API.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        // Ensure we have a valid access token
        $this->ensureValidAccessToken();

        $options = array_merge_recursive($options, [
            'headers' => $this->getAuthHeaders(),
        ]);

        try {
            $response = $this->client->request($method, $endpoint, $options);

            return $this->parseResponse($response);
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (RequestException $e) {
            throw new ApiException(
                message: 'Request failed: '.$e->getMessage(),
                code: $e->getCode(),
                previous: $e
            );
        }
    }

    /**
     * Ensure we have a valid access token, refresh if necessary.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function ensureValidAccessToken(): void
    {
        if ($this->accessToken === null || $this->isTokenExpired()) {
            $this->refreshAccessToken();
        }
    }

    /**
     * Check if the current token is expired.
     */
    private function isTokenExpired(): bool
    {
        return $this->tokenExpiresAt === null || time() >= $this->tokenExpiresAt;
    }

    /**
     * Refresh the access token using OAuth 2.0 client credentials flow.
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function refreshAccessToken(): void
    {
        try {
            $response = $this->client->request('POST', self::OAUTH2_TOKEN_URL, [
                'auth' => [$this->clientId, $this->clientSecret],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ],
            ]);

            $data = $this->parseResponse($response);

            if (! isset($data['access_token'])) {
                throw new AuthenticationException('Invalid OAuth response: missing access_token');
            }

            $this->accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600; // Default to 1 hour
            $this->tokenExpiresAt = time() + $expiresIn - 60; // Refresh 1 minute early

        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $body = $this->parseResponse($response);

            throw new AuthenticationException(
                message: 'OAuth authentication failed',
                httpStatusCode: $statusCode,
                responseBody: $body,
                previous: $e
            );
        } catch (RequestException $e) {
            throw new AuthenticationException(
                message: 'OAuth request failed: '.$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Generate authentication headers for the request.
     *
     * @return array<string, string>
     */
    private function getAuthHeaders(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->accessToken,
        ];
    }

    /**
     * Parse the response body.
     *
     * @return array<string, mixed>
     *
     * @throws ApiException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $body = $response->getBody()->getContents();

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($data)) {
                throw new JsonException('Expected an array. Got: ' . gettype($data));
            }
        } catch (JsonException $e) {
            throw new ApiException(
                'Invalid JSON response',
                httpStatusCode: $response->getStatusCode(),
                responseBody: $body,
                previous: $e
            );
        }

        return $data ?? [];
    }

    /**
     * Handle client exceptions (4xx errors).
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function handleClientException(ClientException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        if ($statusCode === 401) {
            throw new AuthenticationException(
                message: 'Authentication failed',
                httpStatusCode: $statusCode,
                responseBody: $body,
                previous: $e
            );
        }

        if ($statusCode === 403) {
            throw new ApiException(
                message: 'Access forbidden - insufficient permissions for this endpoint',
                code: $body['replyCode'] ?? 0,
                httpStatusCode: $statusCode,
                responseBody: $body,
                previous: $e
            );
        }

        throw new ApiException(
            message: $body['replyText'] ?? 'Client error occurred',
            code: $body['replyCode'] ?? 0,
            httpStatusCode: $statusCode,
            responseBody: $body,
            previous: $e
        );
    }

    /**
     * Handle server exceptions (5xx errors).
     *
     * @throws ApiException
     */
    private function handleServerException(ServerException $e): never
    {
        $response = $e->getResponse();
        $statusCode = $response->getStatusCode();
        $body = $this->parseResponse($response);

        throw new ApiException(
            message: 'Server error occurred',
            httpStatusCode: $statusCode,
            responseBody: $body,
            previous: $e
        );
    }
}
