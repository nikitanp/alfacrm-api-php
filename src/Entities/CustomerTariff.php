<?php

namespace Nikitanp\AlfacrmApiPhp\Entities;

class CustomerTariff extends AbstractEntity
{
    protected $modelName = 'customer-tariff';

    /**
     * @inheritDoc
     */
    public function get(int $page = 0, array $filterData = []): array
    {
        if (!isset($filterData['customer_id'])) {
            throw new \ArgumentCountError('customer_id is required!');
        }

        $getParams = ['customer_id' => $filterData['customer_id']];

        unset($filterData['customer_id']);

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
