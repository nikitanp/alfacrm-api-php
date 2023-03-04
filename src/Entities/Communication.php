<?php

namespace Nikitanp\AlfacrmApiPhp\Entities;

class Communication extends AbstractEntity
{
    protected string $modelName = 'communication';

    /**
     * @inheritDoc
     */
    public function get(int $page = 0, array $filterData = []): array
    {
        if (!isset($filterData['class'])) {
            throw new \ArgumentCountError('"class" param must be in array of [Customer, Group, Task]');
        }

        $getParams = ['class' => $filterData['class']];

        unset($filterData['class']);

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
