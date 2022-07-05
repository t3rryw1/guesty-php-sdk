<?php

namespace Cozy\Lib\Guesty;


abstract class UpdatableTokenClient implements IUpdatableTokenClient{
    /** @var callable */
    protected $tokenUpdateCallback;
    private $client;
    protected $token;

    function __construct($baseUrl,string $token=null)
    {
        $this->client =new ClientWrapper($baseUrl,$token);
        $this->token=$token;
    }

    function setTokenUpdateCallback(callable $callback){
        $this->tokenUpdateCallback = $callback;
    }

    private function refetchTokenAndRequest($method, $url, $params){
        [$token,$expired]= $this->fetchNewToken();
        if($this->tokenUpdateCallback){
            call_user_func($this->tokenUpdateCallback,$token,$expired);
        }
        $this->token = $token;
        return$this->client->request([$method, $url],$params);

    }

    function optimisticRequestWithToken($method, $url, $params){
        if($this->token){
            $response = $this->client->request([$method, $url],$params);
            if($this->isRequestTokenExpired($response)){
                return $this->refetchTokenAndRequest($method, $url, $params);
            }else{
                return $response;
            }
        }
        return $this->refetchTokenAndRequest($method, $url, $params);
    }


}
