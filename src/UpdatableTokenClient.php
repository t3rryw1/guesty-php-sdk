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
        return $this;
    }

    private function buildHeader(){
        return array(
            "Authorization: Basic {$this->token}",
            "Content-Type: application/json"
        );
    }

    private function refetchTokenAndRequest($urlArray, $params){
        [$token,$expired]= $this->fetchNewToken();
        if($this->tokenUpdateCallback){
            call_user_func($this->tokenUpdateCallback,$token,$expired);
        }
        $this->token = $token;
        return $this->client->request(
            $urlArray,
            $this->buildHeader(),
            $params);

    }

    function optimisticRequestWithToken($urlArray, $params){
        if($this->token){
            $response = $this->client->request(
                $urlArray,
                $this->buildHeader(),
                $params);
            if($this->isRequestTokenExpired($response)){
                return $this->refetchTokenAndRequest($urlArray, $params);
            }else{
                return $response;
            }
        }
        return $this->refetchTokenAndRequest($urlArray, $params);
    }


}
