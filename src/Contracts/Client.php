<?php

namespace Nikitanp\AlfacrmApiPhp\Contracts;

interface Client
{
    /**
     * send post request to the api
     * @param string $path
     * @param array $data
     * @param bool $useToken
     * @return array
     */
    public function sendRequest(string $path, array $data = [], bool $useToken = true): array;
}
