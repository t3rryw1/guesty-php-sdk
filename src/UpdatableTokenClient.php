<?php

namespace Cozy\Lib\Guesty;

abstract class UpdatableTokenClient implements IUpdatableTokenClient{
    /** @var callable */
    protected $tokenUpdateCallback;
    protected $client;
    protected $token;
    protected $expiredAt;

    function __construct($baseUrl,string $token=null,$expiredAt=null)
    {
        $this->client =new ClientWrapper($baseUrl);
        $this->token=$token;
        //TODO: handle expires logic 
        $this->expiredAt=$expiredAt;
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

    function optimisticRequestWithToken($urlArray, $params):mixed{
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
