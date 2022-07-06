<?php

namespace Cozy\Lib\Guesty;


interface IUpdatableTokenClient{

    function fetchNewToken():array;

    function setTokenUpdateCallback(callable $callback);

    function optimisticRequestWithToken($urlArray, $params): array;

    function isRequestTokenExpired($response):bool;

}