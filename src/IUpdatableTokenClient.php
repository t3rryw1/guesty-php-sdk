<?php

namespace Cozy\Lib\Guesty;


interface IUpdatableTokenClient{

    function fetchNewToken();

    function setTokenUpdateCallback(callable $callback);

    function optimisticRequestWithToken($method, $url, $params);

    function isRequestTokenExpired($response);

}