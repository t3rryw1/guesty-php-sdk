<?php

namespace Cozy\Lib\Guesty;


interface IUpdatableTokenClient{

    function fetchNewToken():array;

    function setTokenUpdateCallback(callable $callback);

    function optimisticRequestWithToken($urlArray, $params): mixed;

    function isRequestTokenExpired($response):bool;

}