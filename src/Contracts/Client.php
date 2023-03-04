<?php

namespace Nikitanp\AlfacrmApiPhp\Contracts;

interface Client
{
    /**
     * send post request to the api
     */
    public function sendRequest(string $path, array $data = []): array;
}
