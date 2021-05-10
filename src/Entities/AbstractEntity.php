<?php

namespace Nikitanp\AlfacrmApiPhp\Entities;

use Nikitanp\AlfacrmApiPhp\Contracts\Client;
use Nikitanp\AlfacrmApiPhp\Exceptions\NoSuchResultsException;
use Nikitanp\AlfacrmApiPhp\Exceptions\TooManyRequestsException;

abstract class AbstractEntity
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $modelName;
    /**
     * @var int
     */
    protected $branchId;

    public function __construct(
        Client $client,
        int $branchId = 1
    ) {
        $this->client = $client;
        $this->branchId = $branchId;
    }

    /**
     * get specific page of model items
     * @param int $page
     * @param array $filterData
     * @return array
     */
    public function get(int $page = 0, array $filterData = []): array
    {
        $filterData['page'] = $page;

        return $this->client->sendRequest(
            $this->preparePath("$this->modelName/index"),
            $filterData
        );
    }

    /**
     * Returns first entity from result.
     * @param array $filterData
     * @return array
     */
    public function getFirst(array $filterData = []): array
    {
        $entities = $this->get(0, $filterData);

        if (empty($entities['items'][0])) {
            throw new NoSuchResultsException();
        }

        return $entities['items'][0];
    }

    /**
     * get all model items
     * @param array $filterData
     * @return \Generator
     */
    public function getAll(array $filterData = []): \Generator
    {
        $page = 0;
        $count = 0;

        do {
            try {
                $response = $this->get($page, $filterData);
            } catch (TooManyRequestsException $e) {
                sleep(1);
                $response = $this->get($page, $filterData);
            }

            $items = $response['items'] ?? [];

            foreach ($items as $item) {
                yield $item;
            }

            $count += $response['count'];
            $page++;
        } while ($count < $response['total']);
    }

    /**
     * return total items count
     * @param array $filterData
     * @return int
     */
    public function count(array $filterData = []): int
    {
        $items = $this->get(0, $filterData);

        return $items['total'];
    }

    /**
     * return possible model fields
     * @param array $filterData
     * @return array
     */
    public function fields(array $filterData = []): array
    {
        $item = $this->getAll($filterData)->current();

        if (empty($item)) {
            throw new NoSuchResultsException('Try with other parameters. Model not found');
        }

        $fields = [];

        foreach ($item as $key => $value) {
            $fields[$key] = [
                'type' => gettype($value),
                'example' => $value
            ];
        }

        return $fields;
    }

    /**
     * create model item
     * @param array $entityData
     * @return array
     */
    public function create(array $entityData): array
    {
        return $this->client->sendRequest(
            $this->preparePath("$this->modelName/create"),
            $entityData
        );
    }

    /**
     * update model item using id
     * @param int $entityId
     * @param array $updateData
     * @return array
     */
    public function update(int $entityId, array $updateData): array
    {
        return $this->client->sendRequest(
            $this->preparePath(
                "$this->modelName/create",
                ['id' => $entityId]
            ),
            $updateData
        );
    }

    /**
     * delete model item using id
     * @param int $entityId
     * @return array
     */
    public function delete(int $entityId): array
    {
        return $this->client->sendRequest(
            $this->preparePath(
                "$this->modelName/delete",
                ['id' => $entityId]
            )
        );
    }

    /**
     * prepare path for api request
     * @param string $path
     * @param array $params
     * @return string
     */
    protected function preparePath(string $path, array $params = []): string
    {
        $query = '';

        if (!empty($params)) {
            $path = rtrim($path, '?');
            $query = '?' . http_build_query($params);
        }

        return "/v2api/$this->branchId/" . trim($path, '/') . $query;
    }
}
