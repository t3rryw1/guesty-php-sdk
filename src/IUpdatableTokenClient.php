<?php

namespace Cozy\Lib\Guesty;

interface IUpdatableTokenClient
{
    public function fetchNewToken(): array;

    public function setTokenUpdateCallback(callable $callback);

    public function optimisticRequestWithToken($urlArray, $params): array|null;

    public function isRequestTokenExpired($response): bool;
}
