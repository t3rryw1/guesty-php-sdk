<?php

namespace Laura\Lib\External;

use Cozy\Lib\Guesty\ClientWrapper;
use Cozy\Lib\Guesty\GuestyClient;
use Exceptions\Http\Client\BadRequestException;
use Exceptions\Http\Client\NotFoundException;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class GuestyClientTest extends TestCase
{
    public function testFetchNewToken()
    {
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(['POST', '/oauth2/token']),
                $this->equalTo(['Accept: application/json']),
                $this->equalTo([
                    'grant_type' => 'client_credentials',
                    'scope' => 'open-api',
                    'client_secret' => 'test_client_secret',
                    'client_id' => 'test_client_id'
                ]),
            )
            ->willReturn([
                "token_type" => "Bearer",
                "expires_in" => 86400,
                "access_token" => "new_created_token",
                "scope" => "open-api"
            ]);
        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        [$token, $expires] = $guestyClient->fetchNewToken();
        $this->assertEquals($token, 'new_created_token');
        $this->assertEquals($expires, '86400');
    }

    public function testRequestResourceNormalWorkflow()
    {
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(['GET', '/v1/guests']),
                $this->equalTo([
                    'Authorization: Bearer token_in_effective',
                    'Accept: application/json'
                ]),
                $this->equalTo(['fields' => '_id'])
            )
            ->willReturn([
                "count" => 1,
                "results" => [
                    ["_id" => "58243555fb61770400aede31",]
                ]
                //sample guests data
            ]);
        $client->method('getLastResponseCode')
            ->willReturn(200);
        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client,
            'token_in_effective'
        );
        //return expired result at first
        //then return token at second request
        // then third request with updated token and return correct result
        $guestCount = $guestyClient->getGuestCount();
        // verify result
        $this->assertEquals($guestCount, 1);

    }

    public function testRequestResourceWithExpiredToken()
    {
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->exactly(3))
            ->method('request')
            ->withConsecutive(
                [
                    $this->equalTo(['GET', '/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer token_expired',
                        'Accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ],
                [
                    $this->equalTo(['POST', '/oauth2/token']),
                    $this->equalTo(['Accept: application/json']),
                    $this->equalTo([
                        'grant_type' => 'client_credentials',
                        'scope' => 'open-api',
                        'client_secret' => 'test_client_secret',
                        'client_id' => 'test_client_id'
                    ])
                ],
                [
                    $this->equalTo(['GET', '/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer new_created_token',
                        'Accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ])
            ->willReturnOnConsecutiveCalls(
                [],
                [
                    "token_type" => "Bearer",
                    "expires_in" => 86400,
                    "access_token" => "new_created_token",
                    "scope" => "open-api"
                ],
                [
                    "count" => 1,
                    "results" => [
                        ["_id" => "58243555fb61770400aede31",]
                    ]
                    //sample guests data
                ]
            );
        $client->method('getLastResponseCode')
            ->willReturnOnConsecutiveCalls(401, 200, 200);

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client,
            'token_expired'
        );
        $guestyClient->setTokenUpdateCallback(function ($token) {
            $this->assertEquals($token, 'new_created_token');
        });
        $guestCount = $guestyClient->getGuestCount();
        //verify result correct
        $this->assertEquals($guestCount, 1);
    }

    public function testRequestResourceWithNoToken()
    {
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    $this->equalTo(['POST', '/oauth2/token']),
                    $this->equalTo(['Accept: application/json']),
                    $this->equalTo([
                        'grant_type' => 'client_credentials',
                        'scope' => 'open-api',
                        'client_secret' => 'test_client_secret',
                        'client_id' => 'test_client_id'
                    ])
                ],
                [
                    $this->equalTo(['GET', '/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer new_created_token',
                        'Accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ])
            ->willReturnOnConsecutiveCalls(
                [
                    "token_type" => "Bearer",
                    "expires_in" => 86400,
                    "access_token" => "new_created_token",
                    "scope" => "open-api"
                ],
                [
                    "count" => 1,
                    "results" => [
                        ["_id" => "58243555fb61770400aede31",]
                    ]
                    //sample guests data
                ]
            );
        //return token at first request
        // then 2nd request with use updated token and return correct result

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $guestCount = $guestyClient->getGuestCount();
        // verify result
        $this->assertEquals($guestCount, 1);
    }

    public function testRequestNotExistResource()
    {
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [
                    $this->equalTo(['POST', '/oauth2/token']),
                    $this->equalTo(['Accept: application/json']),
                    $this->equalTo([
                        'grant_type' => 'client_credentials',
                        'scope' => 'open-api',
                        'client_secret' => 'test_client_secret',
                        'client_id' => 'test_client_id'
                    ])
                ],
                [
                    $this->equalTo(['GET', '/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer new_created_token',
                        'Accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ])
            ->willReturnOnConsecutiveCalls(
                [
                    "token_type" => "Bearer",
                    "expires_in" => 86400,
                    "access_token" => "new_created_token",
                    "scope" => "open-api"
                ],
                [
                ]
            );
        $client->method('getLastResponseCode')
            ->willReturnOnConsecutiveCalls(404);
        //return 404 saying resource not found

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );

        $this->expectException(NotFoundException::class);
        $guestyClient->getGuestCount();
        // expect exception
    }

    public function testRequestOtherException()
    {
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        /** @var ClientWrapper|MockObject */
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->exactly(1))
            ->method('request')
            ->withConsecutive(
                [
                    $this->equalTo(['POST', '/oauth2/token']),
                    $this->equalTo(['Accept: application/json']),
                    $this->equalTo([
                        'grant_type' => 'client_credentials',
                        'scope' => 'open-api',
                        'client_secret' => 'test_client_secret',
                        'client_id' => 'test_client_id'
                    ])
                ],
                [
                    $this->equalTo(['GET', '/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer new_created_token',
                        'Accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ])
            ->willReturnOnConsecutiveCalls(
                [
                    "token_type" => "Bearer",
                    "expires_in" => 86400,
                    "access_token" => "new_created_token",
                    "scope" => "open-api"
                ],
                [
                ]
            );
        $client->method('getLastResponseCode')
            ->willReturnOnConsecutiveCalls(400);
        //return 400 saying resource not found            //return 400 saying bad request

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $this->expectException(BadRequestException::class);
        $guestyClient->getGuestCount();
        // expect exception
    }
}
