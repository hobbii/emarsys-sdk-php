<?php

declare(strict_types=1);

namespace Hobbii\Emarsys;

use Hobbii\Emarsys\Domain\BaseClient;
use Hobbii\Emarsys\Domain\ContactLists\ContactListsClient;
use Hobbii\Emarsys\Domain\Contacts\ContactsClient;
use Hobbii\Emarsys\Domain\Contracts\RequestInterface;
use Hobbii\Emarsys\Domain\Contracts\ResponseDataInterface;
use Hobbii\Emarsys\Domain\OauthClient;
use Hobbii\Emarsys\Domain\ValueObjects\Response;
use RuntimeException;

/**
 * Main Emarsys API client.
 *
 * This class provides access to various Emarsys API endpoints through
 * specialized client instances.
 */
class Client
{
    private readonly BaseClient $client;

    private ?ContactsClient $contactsClient = null;

    private ?ContactListsClient $contactListsClient = null;

    public function __construct(
        string $clientId,
        string $clientSecret,
    ) {
        $oauthClient = new OauthClient($clientId, $clientSecret);
        $this->client = new BaseClient($oauthClient);
    }

    public function send(RequestInterface $request): ResponseDataInterface
    {
        $response = match ($request->method()) {
            'GET' => $this->client->get($request->endpoint(), $request->query()),
            'POST' => $this->client->post($request->endpoint(), $request),
            'PUT' => $this->client->put($request->endpoint(), $request),
            'DELETE' => $this->client->delete($request->endpoint(), $request->query()),
            default => throw new \InvalidArgumentException('Unsupported HTTP method: '.$request->method()),
        };

        return $this->makeResponseData($request, $response);
    }

    private function makeResponseData(RequestInterface $request, Response $response): ResponseDataInterface
    {
        if ($request === null) {
            throw new RuntimeException('No request associated with this response');
        }

        if (! is_a($request->responseDataClass(), ResponseDataInterface::class, true)) {
            throw new RuntimeException('Response data class must implement ResponseDataInterface');
        }

        return $request->responseDataClass()::fromResponse($response);
    }

    /**
     * Get the Contacts client.
     */
    public function contacts(): ContactsClient
    {
        return $this->contactsClient ??= new ContactsClient($this->client);
    }

    /**
     * Get the Contact Lists client.
     */
    public function contactLists(): ContactListsClient
    {
        return $this->contactListsClient ??= new ContactListsClient($this->client);
    }
}
