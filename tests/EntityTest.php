<?php

namespace Nikitanp\AlfacrmApiPhp\Tests;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Nikitanp\AlfacrmApiPhp\Contracts\Client;
use Nikitanp\AlfacrmApiPhp\Entities\Branch;
use Nikitanp\AlfacrmApiPhp\Exceptions\NoSuchResultsException;

class EntityTest extends MockeryTestCase
{
    /** @test */
    public function get_method_works_without_params()
    {
        $clientMock = \Mockery::mock(Client::class);
        $clientMock->shouldReceive('sendRequest')->andReturn([
            'total' => 4,
            'count' => 4,
            'page' => 0,
            'items' => [
                [
                    'id' => 1,
                    'name' => 'test1',
                    'subject_ids' =>
                        [
                            0 => 1,
                            1 => 2,
                            2 => 5,
                        ],
                    'is_active' => 1,
                    'weight' => 1,
                ],
                [
                    'id' => 2,
                    'name' => 'test2',
                    'subject_ids' =>
                        [
                            0 => 1,
                            1 => 2,
                        ],
                    'is_active' => 0,
                    'weight' => 2,
                ],
                [
                    'id' => 4,
                    'name' => 'test3',
                    'subject_ids' =>
                        [
                            0 => 1,
                            1 => 2,
                            2 => 5,
                        ],
                    'is_active' => 0,
                    'weight' => 3,
                ],
                [
                    'id' => 5,
                    'name' => 'test4',
                    'subject_ids' =>
                        [
                            0 => 1,
                            1 => 2,
                            2 => 5,
                        ],
                    'is_active' => 0,
                    'weight' => 4,
                ],
            ],
        ]);

        $branch = new Branch($clientMock);
        $branch->get();
    }

    /** @test */
    public function get_method_add_page_number_to_request()
    {
        $clientMock = \Mockery::mock(Client::class);
        $clientMock->shouldReceive('sendRequest')
            ->withArgs([
                '/v2api/1/branch/index',
                ['page' => 2]
            ]);

        $branch = new Branch($clientMock);
        $branch->get(2);
    }

    /** @test */
    public function get_method_add_filter_data_to_request()
    {
        $clientMock = \Mockery::mock(Client::class);
        $clientMock->shouldReceive('sendRequest')
            ->withArgs([
                '/v2api/1/branch/index',
                [
                    'page' => 0,
                    'filter' => 'test'
                ]
            ]);

        $branch = new Branch($clientMock);
        $branch->get(0, ['filter' => 'test']);
    }

    /** @test */
    public function get_method_returns_empty_array_when_no_items_for_result()
    {
        $data = [
            'total' => 0,
            'count' => 0,
            'page' => 0,
            'items' => [],
        ];

        $clientMock = \Mockery::mock(Client::class);
        $clientMock->shouldReceive('sendRequest')->andReturn($data);

        $branch = new Branch($clientMock);
        $result = $branch->get();
        $this->assertEquals($data, $result);
    }

    /** @test */
    public function get_first_method_throws_exception_when_no_results()
    {
        $this->expectException(NoSuchResultsException::class);
        $data = [
            'total' => 0,
            'count' => 0,
            'page' => 0,
            'items' => [],
        ];

        $clientMock = \Mockery::mock(Client::class);
        $clientMock->shouldReceive('sendRequest')->andReturn($data);

        $branch = new Branch($clientMock);
        $branch->getFirst();
    }

    /** @test */
    public function fields_method_throws_exception_when_no_results()
    {
        $this->expectException(NoSuchResultsException::class);
        $data = [
            'total' => 0,
            'count' => 0,
            'page' => 0,
            'items' => [],
        ];

        $clientMock = \Mockery::mock(Client::class);
        $clientMock->shouldReceive('sendRequest')->andReturn($data);

        $branch = new Branch($clientMock);
        $branch->fields();
    }
}
