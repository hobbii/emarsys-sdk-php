<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Hobbii\Emarsys\Domain\Exceptions\ApiException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Domain\Exceptions\RateLimitException;
use Hobbii\Emarsys\Domain\ValueObjects\OauthData;
use Hobbii\Emarsys\Domain\ValueObjects\Response;

/**
 * Base HTTP client for Emarsys API communication.
 */
class Client
{
    /**
     * Base Emarsys API v3 endpoint.
     *
     * Important:
     * Guzzle follows RFC 3986 URI resolution rules - if the base_uri does not end with /, it’s considered a file.
     */
    private const BASE_URL = 'https://api.emarsys.net/api/v3/';

    /**
     * Oauth2 token endpoint
     *
     * Important: it must not end with `/` at the end
     */
    private const OAUTH2_TOKEN_URL = 'https://auth.emarsys.net/oauth2/token';

    private readonly GuzzleClient $client;

    private ?OauthData $oauthData = null;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        ?string $baseUrl = null,
        ?GuzzleClient $client = null
    ) {
        $this->client = $client ?? new GuzzleClient([
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
     * @param  array<string,mixed>  $query  URL query parameters
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function get(string $endpoint, array $query = []): Response
    {
        return $this->request('GET', $endpoint, [
            'query' => $query,
        ]);
    }

    /**
     * Make a POST request to the API.
     *
     * @param  array<string,mixed>  $data
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function post(string $endpoint, array $data = []): Response
    {
        return $this->request('POST', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Make a PUT request to the API.
     *
     * @param  array<string,mixed>  $data
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function put(string $endpoint, array $data = []): Response
    {
        return $this->request('PUT', $endpoint, [
            'json' => $data,
        ]);
    }

    /**
     * Make a DELETE request to the API.
     *
     * @param  array<string,mixed>  $query  URL query parameters
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function delete(string $endpoint, array $query = []): Response
    {
        return $this->request('DELETE', $endpoint, [
            'query' => $query,
        ]);
    }

    /**
     * Make a request to the API with OAuth token refresh handling.
     *
     * @param  array<string,mixed>  $options  Client options
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function request(string $method, string $endpoint, array $options = []): Response
    {
        return $this->makeRequest($method, $endpoint, $options);
    }

    /**
     * Retry the request after refreshing the OAuth token.
     *
     * @param  array<string,mixed>  $options  Client options
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function retryRequest(string $method, string $endpoint, array $options = []): Response
    {
        return $this->makeRequest($method, $endpoint, $options, true);
    }

    /**
     * Make an authenticated request to the API with retry logic.
     *
     * @param  array<string,mixed>  $options  Client options
     *
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function makeRequest(string $method, string $endpoint, array $options = [], bool $isRetry = false): Response
    {
        $this->ensureValidOauthData();
        $requestOptions = $this->addAuthHeadersToRequestOptions($options);

        try {
            $response = $this->client->request($method, $endpoint, $requestOptions);

            return Response::fromPsrResponse($response);
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 401) {
                /**
                 * Handle 401 Unauthorized - OAuth token has likely expired.
                 *
                 * This implements automatic token refresh with a single retry:
                 * 1. Check if this is already a retry attempt to prevent infinite loops
                 * 2. If not a retry, clear the cached OAuth token data
                 * 3. Call retryRequest(), which will:
                 *    - Trigger ensureValidOauthData() to fetch a fresh token
                 *    - Retry the original API request with the new token
                 * 4. If it's already a retry and still fails, throw an exception
                 */
                if (! $isRetry) {
                    $this->resetOauthData();

                    // For retry, use original options (without auth headers from first attempt)
                    return $this->retryRequest($method, $endpoint, $options);
                }

                throw new AuthenticationException('Authentication failed', previous: $e);
            }

            if ($statusCode === 403) {
                throw new ApiException('Access forbidden - insufficient permissions for this endpoint', previous: $e);
            }

            if ($statusCode === 429) {
                throw RateLimitException::fromPsrResponse($e->getResponse(), $e);
            }

            throw new ApiException('Client error occurred', previous: $e);
        } catch (ServerException $e) {
            throw new ApiException('Server error occurred', previous: $e);
        } catch (RequestException $e) {
            throw new ApiException('Request failed: '.$e->getMessage(), previous: $e);
        }
    }

    /**
     * Ensure we have a valid oauth data, refresh if necessary.
     *
     * @throws AuthenticationException
     */
    private function ensureValidOauthData(): void
    {
        if ($this->oauthData === null || $this->oauthData->isExpired()) {
            $this->oauthData = $this->refreshOauthData();
        }
    }

    private function resetOauthData(): void
    {
        $this->oauthData = null;
    }

    /**
     * Refresh the access token using OAuth 2.0 client credentials flow.
     *
     * @throws AuthenticationException
     */
    private function refreshOauthData(): OauthData
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

            $body = $response->getBody()->getContents();
            $data = (array) json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            return OauthData::fromArray($data);
        } catch (ClientException $e) {
            throw new AuthenticationException('OAuth authentication failed', previous: $e);
        } catch (RequestException $e) {
            throw new AuthenticationException('OAuth request failed', previous: $e);
        }
    }

    /**
     * Add Authorization header to request options.
     */
    private function addAuthHeadersToRequestOptions(array $options): array
    {
        return $this->setRequestOptionsHeader($options, 'Authorization', 'Bearer '.$this->oauthData?->accessToken);
    }

    /**
     * Set a header in the request options.
     */
    private function setRequestOptionsHeader(array $options, string $name, string $value): array
    {
        if (! isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers'][$name] = $value;

        return $options;
    }
}
