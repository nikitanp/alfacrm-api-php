<?php

namespace Nikitanp\AlfacrmApiPhp\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Nikitanp\AlfacrmApiPhp\Client;
use Nikitanp\AlfacrmApiPhp\Entities\Branch;
use Nikitanp\AlfacrmApiPhp\Exceptions\ApiNotAvailableException;
use Nikitanp\AlfacrmApiPhp\Exceptions\PathNotFoundException;
use Nikitanp\AlfacrmApiPhp\Exceptions\TooManyRequestsException;
use PHPUnit\Framework\TestCase;

class ApiClientTest extends TestCase
{
    /** @test */
    public function authorize_with_credentials()
    {
        $resultToken = 'result-token';
        $mock = new MockHandler([
            new Response(200, [], json_encode(['token' => $resultToken]))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');
        $client->authorize();

        $reflector = new \ReflectionClass($client);
        $reflector_property = $reflector->getProperty('token');
        $reflector_property->setAccessible(true);

        $this->assertEquals($resultToken, $reflector_property->getValue($client));
    }

    /** @test */
    public function throw_exception_with_bad_credentials_or_bad_request()
    {
        $mock = new MockHandler([
            new Response(401)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $this->expectException(ApiNotAvailableException::class);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');
        $client->authorize();
    }

    /** @test */
    public function throw_exception_when_too_many_requests()
    {
        $mock = new MockHandler([
            new Response(429)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $this->expectException(TooManyRequestsException::class);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');
        $client->sendRequest('test');
    }

    /** @test */
    public function throw_exception_when_path_not_found()
    {
        $mock = new MockHandler([
            new Response(404)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $this->expectException(PathNotFoundException::class);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->setDomain('https://test.alfacrm.online');
        $client->setApiKey('test-api-key');
        $client->setEmail('test@test.test');
        $client->sendRequest('test');
    }

    /** @test */
    public function throw_exception_when_credentials_not_settled()
    {
        $mock = new MockHandler([
            new Response(404)
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $this->expectException(\Exception::class);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->sendRequest('test');
    }

    /** @test */
    public function added_headers_to_request()
    {
        $mock = new MockHandler([
            new Response(200, [], '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->sendRequest('/');

        foreach ($container as $transaction) {
            $this->assertArrayHasKey('Accept', $transaction['request']->getHeaders());
            $this->assertArrayHasKey('Content-Type', $transaction['request']->getHeaders());
            $this->assertArrayHasKey('X-ALFACRM-TOKEN', $transaction['request']->getHeaders());
        }
    }

    /** @test */
    public function sending_without_token_header()
    {
        $mock = new MockHandler([
            new Response(200, [], '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $client->sendRequest('/', [], false);

        foreach ($container as $transaction) {
            $this->assertArrayNotHasKey('X-ALFACRM-TOKEN', $transaction['request']->getHeaders());
        }
    }

    /** @test */
    public function adding_data_to_request_body()
    {
        $mock = new MockHandler([
            new Response(200, [], '{}')
        ]);

        $handlerStack = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handlerStack->push($history);

        $httpClient = new HttpClient(['handler' => $handlerStack]);

        $client = new Client(
            $httpClient,
            new RequestFactory(),
            new StreamFactory()
        );

        $data = ['body' => 'test'];

        $client->sendRequest('/', $data, false);

        foreach ($container as $transaction) {
            $this->assertEquals(json_encode($data), $transaction['request']->getBody()->getContents());
        }
    }
}
