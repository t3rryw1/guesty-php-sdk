<?php

namespace Cozy\Lib\Guesty;

use Exception;
use Exceptions\Http\Client\BadRequestException;
use Exceptions\Http\Client\ForbiddenException;
use Exceptions\Http\Client\GoneException;
use Exceptions\Http\Client\MethodNotAllowedException;
use Exceptions\Http\Client\NotAcceptableException;
use Exceptions\Http\Client\NotFoundException;
use Exceptions\Http\Client\TooManyRequestsException;
use Exceptions\Http\Server\InternalServerErrorException;
use Exceptions\Http\Server\ServiceUnavailableException;

abstract class UpdatableTokenClient implements IUpdatableTokenClient
{
    /** @var callable */
    protected $tokenUpdateCallback;
    protected $client;
    protected $token;
    protected $expiredAt;

    public function __construct(ClientWrapper $client, string $token = null, string $expiredAt = null)
    {
        $this->client = $client;
        $this->token = $token;
        //TODO: handle expires logic
        $this->expiredAt = $expiredAt;
    }

    public function setTokenUpdateCallback(callable $callback)
    {
        $this->tokenUpdateCallback = $callback;
        return $this;
    }

    private function buildHeader()
    {
        return array(
            "Authorization: Bearer {$this->token}",
            "Accept: application/json",
            "Content-Type: application/json"
        );
    }

    private function refetchTokenAndRequest($urlArray, $params)
    {
        [$token, $expired] = $this->fetchNewToken();
        if ($this->tokenUpdateCallback) {
            call_user_func($this->tokenUpdateCallback, $token, $expired);
        }
        $this->token = $token;
        $res = $this->client->request(
            $urlArray,
            $this->buildHeader(),
            $params
        );
        $responseCode = $this->client->getLastResponseCode();
        $this->throwException($responseCode);
        return $res;
    }

    protected function throwException($code)
    {
        if ($code >= 400) {
            switch ($code) {
                case 503:
                    throw new ServiceUnavailableException("", $code);
                case 500:
                    throw new InternalServerErrorException("", $code);
                case 429:
                    throw new TooManyRequestsException("", $code);
                case 410:
                    throw new GoneException("", $code);
                case 406:
                    throw new NotAcceptableException("", $code);
                case 405:
                    throw new MethodNotAllowedException("", $code);
                case 404:
                    throw new NotFoundException("", $code);
                case 403:
                    throw new ForbiddenException("", $code);
                case 400:
                    throw new BadRequestException("", $code);
                default:
                    throw new Exception("Unknown exception", $code);
            }
        }
    }

    public function optimisticRequestWithToken($urlArray, $params): array|null
    {
        if (!$this->token) {
            return $this->refetchTokenAndRequest($urlArray, $params);
        }
        $response = $this->client->request(
            $urlArray,
            $this->buildHeader(),
            $params
        );

        if ($this->client->isDryRun() && strtolower($urlArray[0]) !=='get') {
            return null;
        }

        $responseCode = $this->client->getLastResponseCode();
        if ($responseCode >= 400) {
            if ($responseCode === 401 || $responseCode === 403) {
                return $this->refetchTokenAndRequest($urlArray, $params);
            } else {
                $this->throwException($responseCode);
            }
        }
        return $response;
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;
    }
}
