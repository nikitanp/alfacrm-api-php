<?php

namespace Nikitanp\AlfacrmApiPhp\Entities;

class Cgi extends AbstractEntity
{
    protected string $modelName = 'cgi';

    /**
     * @inheritDoc
     */
    public function get(int $page = 0, array $filterData = []): array
    {
        if (!isset($filterData['group_id'])) {
            throw new \ArgumentCountError(
                'group_id is required! $filterData = ' . json_encode($filterData, JSON_THROW_ON_ERROR)
            );
        }

        $getParams = ['group_id' => $filterData['group_id']];

        unset($filterData['group_id']);

        $filterData['page'] = $page;

        return $this->client->sendRequest(
            $this->preparePath(
                "$this->modelName/index",
                $getParams
            ),
            $filterData
        );
    }
}
