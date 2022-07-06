<?php

namespace Cozy\Lib\Guesty;

use Exceptions\Http\Client\BadRequestException;
use Exceptions\Http\Client\NotFoundException;

abstract class UpdatableTokenClient implements IUpdatableTokenClient{
    /** @var callable */
    protected $tokenUpdateCallback;
    protected $client;
    protected $token;
    protected $expiredAt;

    function __construct(ClientWrapper $client, string $token=null, string $expiredAt=null)
    {
        $this->client =$client;
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
            "Authorization: Bearer {$this->token}",
            "accept: application/json"
        );
    }

    private function refetchTokenAndRequest($urlArray, $params){
        [$token,$expired]= $this->fetchNewToken();
        if($this->tokenUpdateCallback){
            call_user_func($this->tokenUpdateCallback,$token,$expired);
        }
        $this->token = $token;
        $res= $this->client->request(
            $urlArray,
            $this->buildHeader(),
            $params);
        $responseCode = $this->client->getLastResponseCode();
        $this->throwException($responseCode);
        return $res;
    }

    private function throwException($code){
        if($code >=400){
            switch($code){
                case 404:
                    throw new NotFoundException($code);
                default:
                    throw new BadRequestException($code);
            }    
        }
    }

    function optimisticRequestWithToken($urlArray, $params):array{
        if(!$this->token){
            return $this->refetchTokenAndRequest($urlArray, $params);
        }
        $response = $this->client->request(
            $urlArray,
            $this->buildHeader(),
            $params);

        $responseCode = $this->client->getLastResponseCode();
        if($responseCode >=400){
            if($responseCode===401){
                return $this->refetchTokenAndRequest($urlArray, $params);
            }else{
                $this->throwException($responseCode);
            }
        }else{
            return $response;
        }
    }
}
