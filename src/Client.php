<?php

namespace Nikitanp\AlfacrmApiPhp;

use Nikitanp\AlfacrmApiPhp\Contracts\Client as ApiClientInterface;
use Nikitanp\AlfacrmApiPhp\Exceptions\ApiNotAvailableException;
use Nikitanp\AlfacrmApiPhp\Exceptions\BadRequestException;
use Nikitanp\AlfacrmApiPhp\Exceptions\PathNotFoundException;
use Nikitanp\AlfacrmApiPhp\Exceptions\TooManyRequestsException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class Client
 * @package Nikitanp\AlfacrmApiPhp
 */
class Client implements ApiClientInterface
{
    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $token;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $domain;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $email;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $apiKey;

    /**
     * @var ClientInterface
     */
    private $httpClient;
    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;
    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * Client constructor.
     *
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->httpClient = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Authorize by email and apiKey
     * @return void
     * @throws ApiNotAvailableException
     */
    public function authorize(): void
    {
        try {
            $response = $this->sendRequest(
                'v2api/auth/login',
                [
                    'email' => $this->email,
                    'api_key' => $this->apiKey
                ],
                false
            );
        } catch (\Throwable $e) {
            throw new ApiNotAvailableException(
                $e->getMessage(),
                401,
                $e
            );
        }

        $this->token = $response['token'];
    }

    /**
     * send post request to the api
     * @param string $path
     * @param array $data
     * @param bool $useToken
     * @return array
     * @throws \JsonException
     * @throws ClientExceptionInterface
     */
    public function sendRequest(string $path, array $data = [], bool $useToken = true): array
    {
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->makeUrl($path)
        );

        $request = $this->addHeadersToRequest($request, $useToken);

        $body = $this->streamFactory->createStream(
            json_encode(
                $data,
                JSON_THROW_ON_ERROR
            )
        );

        $request = $request->withBody(
            $body
        );

        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() === 429) {
            throw new TooManyRequestsException('Please try in 1 second!', 429);
        }

        if ($response->getStatusCode() === 404) {
            throw new PathNotFoundException($path . ' not found!', 404);
        }

        if ($response->getStatusCode() !== 200) {
            throw new BadRequestException(
                $response->getBody()->getContents(),
                $response->getStatusCode()
            );
        }

        return json_decode(
            $response->getBody()->getContents(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param string $path
     * @return string
     */
    private function makeUrl(string $path): string
    {
        return $this->domain . '/' . trim($path, '/');
    }

    /**
     * @param RequestInterface $request
     * @param bool $useToken
     * @return RequestInterface
     */
    private function addHeadersToRequest(
        RequestInterface $request,
        bool $useToken = true
    ): RequestInterface {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($useToken) {
            $headers['X-ALFACRM-TOKEN'] = $this->token;
        }

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * @param string $domain
     * @return Client
     */
    public function setDomain(string $domain): Client
    {
        $this->domain = trim($domain, '/');
        return $this;
    }

    /**
     * @param string $email
     * @return Client
     */
    public function setEmail(string $email): Client
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $apiKey
     * @return Client
     */
    public function setApiKey(string $apiKey): Client
    {
        $this->apiKey = $apiKey;
        return $this;
    }
}
