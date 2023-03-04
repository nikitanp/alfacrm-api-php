<?php

namespace Nikitanp\AlfacrmApiPhp\Entities;

class Group extends AbstractEntity
{
    protected string $modelName = 'group';

    public const REMOVED_OPTION = [
        'archived_and_active' => 1,
        'archived' => 2,
    ];

    /**
     * @inheritDoc
     */
    public function get(int $page = 0, array $filterData = []): array
    {
        $filterData['removed'] = self::REMOVED_OPTION['archived_and_active'];

        return parent::get($page, $filterData);
    }

    /**
     * @inheritDoc
     */
    public function getAll(array $filterData = []): \Generator
    {
        $filterData['removed'] = self::REMOVED_OPTION['archived_and_active'];

        return parent::getAll($filterData);
    }

    /**
     * Returns only archived customers
     * @param array $filterData
     * @return \Generator
     */
    public function getAllArchived(array $filterData = []): \Generator
    {
        $filterData['removed'] = self::REMOVED_OPTION['archived'];

        return parent::getAll($filterData);
    }
}
