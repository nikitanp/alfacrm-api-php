<?php

declare(strict_types=1);

namespace Nikitanp\AlfacrmApiPhp\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\Psr7\Response;
use Nikitanp\AlfacrmApiPhp\Client;
use Nikitanp\AlfacrmApiPhp\Exceptions\ApiNotAvailableException;
use Nikitanp\AlfacrmApiPhp\Exceptions\PathNotFoundException;
use Nikitanp\AlfacrmApiPhp\Exceptions\TooManyRequestsException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends TestCase
{
    public function test_authorize_with_credentials(): void
    {
        $resultToken = 'result-token';

        $transactionsContainer = [];
        $client = $this->createClient(new Response(200, [], '{"token":"'.$resultToken.'"}'), $transactionsContainer);

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');
        $client->authorize();

        $reflector = new \ReflectionClass($client);
        $reflectorProperty = $reflector->getProperty('token');
        $reflectorProperty->setAccessible(true);

        $this->assertEquals($resultToken, $reflectorProperty->getValue($client));
        // Authorize request must be sent without token header
        $this->assertArrayNotHasKey('X-ALFACRM-TOKEN', $transactionsContainer[0]['request']->getHeaders());
    }

    public function test_throw_exception_with_bad_credentials_or_bad_request(): void
    {
        $client = $this->createClient(new Response(401));

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');

        $this->expectException(ApiNotAvailableException::class);
        $client->authorize();
    }

    public function test_throw_exception_when_too_many_requests(): void
    {
        $client = $this->createClient(new Response(429));

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');

        $this->expectException(TooManyRequestsException::class);
        $client->sendRequest('test');
    }

    public function test_throw_exception_when_path_not_found(): void
    {
        $client = $this->createClient(new Response(404));

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');

        $this->expectException(PathNotFoundException::class);
        $client->sendRequest('test');
    }

    public function test_throw_exception_when_credentials_not_settled(): void
    {
        $client = $this->createClient(new Response(404));

        $this->expectException(\Exception::class);
        $client->sendRequest('test');
    }

    public function test_added_headers_to_request(): void
    {
        $transactionsContainer = [];
        $client = $this->createClient(new Response(200, [], '{}'), $transactionsContainer);

        $client->sendRequest('/');

        foreach ($transactionsContainer as $transaction) {
            $this->assertArrayHasKey('Accept', $transaction['request']->getHeaders());
            $this->assertArrayHasKey('Content-Type', $transaction['request']->getHeaders());
            $this->assertArrayHasKey('X-ALFACRM-TOKEN', $transaction['request']->getHeaders());
        }
    }

    public function test_adding_data_to_request_body(): void
    {
        $transactionsContainer = [];
        $client = $this->createClient(new Response(200, [], '{}'), $transactionsContainer);

        $data = ['body' => 'test'];

        $client->sendRequest('/', $data);

        foreach ($transactionsContainer as $transaction) {
            $this->assertEquals('{"body":"test"}', $transaction['request']->getBody()->getContents());
        }
    }

    private function createClient(ResponseInterface $response, array &$transactionsContainer = []): Client
    {
        $handlerStack = HandlerStack::create(new MockHandler([$response]));
        $handlerStack->push(Middleware::history($transactionsContainer));

        return new Client(
            new HttpClient(['handler' => $handlerStack]),
            new HttpFactory(),
            new HttpFactory()
        );
    }
}
