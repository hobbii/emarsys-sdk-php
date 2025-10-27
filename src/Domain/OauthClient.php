<?php

declare(strict_types=1);

namespace Hobbii\Emarsys\Domain;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Hobbii\Emarsys\Domain\Exceptions\AuthenticationException;
use Hobbii\Emarsys\Domain\ValueObjects\OauthData;

class OauthClient
{
    /**
     * Oauth2 token endpoint
     *
     * Important: it must not end with `/` at the end
     */
    private const OAUTH2_TOKEN_URL = 'https://auth.emarsys.net/oauth2/token';

    private readonly Client $client;

    private ?OauthData $oauthData = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
        ?Client $client = null,
    ) {
        $this->client = $client ?? new Client([
            'base_uri' => self::OAUTH2_TOKEN_URL,
            'auth' => [$clientId, $clientSecret],
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);
    }

    /**
     * Add Authorization header to request options.
     */
    public function addAuthHeadersToRequestOptions(array $options): array
    {
        $this->ensureValidOauthData();

        return $this->setRequestOptionsHeader($options, 'Authorization', 'Bearer '.$this->oauthData?->accessToken);
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

    public function resetOauthData(): void
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
            $response = $this->client->request('POST');

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
