<?php

declare(strict_types=1);

namespace Nikitanp\AlfacrmApiPhp\Tests;

use Nikitanp\AlfacrmApiPhp\Contracts\Client;
use Nikitanp\AlfacrmApiPhp\Entities\Branch;
use Nikitanp\AlfacrmApiPhp\Exceptions\NoSuchResultsException;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    public function test_get_method_works_without_params(): void
    {
        $data = [
            'total' => 2,
            'count' => 2,
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
            ],
        ];

        $branch = new Branch($this->createClient($data));

        $result = $branch->get();

        $this->assertSame($result, $data);
    }

    public function test_get_method_add_page_number_to_request(): void
    {
        $branch = new Branch($this->createClient([], ['/v2api/1/branch/index', ['page' => 2]]));

        $branch->get(2);

        $this->assertTrue(true);
    }

    public function test_get_method_adds_filter_data_to_request(): void
    {
        $branch = new Branch($this->createClient([], ['/v2api/1/branch/index', ['page' => 0, 'filter' => 'test']]));

        $branch->get(0, ['filter' => 'test']);

        $this->assertTrue(true);
    }

    public function test_get_method_returns_empty_array_when_no_items_for_result(): void
    {
        $data = [
            'total' => 0,
            'count' => 0,
            'page' => 0,
            'items' => [],
        ];
        $branch = new Branch($this->createClient($data));

        $result = $branch->get();

        $this->assertEquals($data, $result);
    }

    public function test_get_first_method_throws_exception_when_no_results(): void
    {
        $branch = new Branch($this->createClient());

        $this->expectException(NoSuchResultsException::class);
        $branch->getFirst();
    }

    public function test_fields_method_throws_exception_when_no_results(): void
    {
        $branch = new Branch($this->createClient());

        $this->expectException(NoSuchResultsException::class);
        $branch->fields();
    }

    private function createClient(
        array $returnData = [
            'total' => 0,
            'count' => 0,
            'page' => 0,
            'items' => [],
        ],
        array $expectedArgs = []
    ): Client {
        $client = $this->createMock(Client::class);

        $sendRequestMocker = $client->method('sendRequest');
        if ($expectedArgs) {
            $sendRequestMocker->with(...$expectedArgs);
        }
        $sendRequestMocker->willReturn($returnData);

        return $client;
    }
}
