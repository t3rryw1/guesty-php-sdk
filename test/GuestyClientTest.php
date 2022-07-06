<?php

namespace Laura\Lib\External;

use Cozy\Lib\Guesty\ClientWrapper;
use Cozy\Lib\Guesty\GuestyClient;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class GuestyClientTest extends TestCase
{

    public function testFetchNewToken()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(['POST','/oauth2/token']),
                $this->equalTo(['accept: application/json']),
                $this->equalTo([
                    'grant_type' => 'client_credentials',
                    'scope' => 'open-api',
                    'client_secret' => 'test_client_secret',
                    'client_id' => 'test_client_id'
                ]),
            )
            ->willReturn([
                "token_type"=>"Bearer",
                "expires_in"=> 86400,
                "access_token"=>"new_created_token",
                "scope"=>"open-api"
            ]);
        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        [$token,$expires] = $guestyClient->fetchNewToken();
        $this->assertEquals($token,'new_created_token');
        $this->assertEquals($expires,'86400');
    }

    public function testRequestResourceNormalWorkflow()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo(['GET','/v1/guests']),
                $this->equalTo([
                    'Authorization: Bearer token_in_effective',
                    'accept: application/json'
                ]),
                $this->equalTo(['fields' => '_id'])
            )
            ->willReturn([
                "count"=>1,
                "results"=>[
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
        $this->assertEquals($guestCount,1);

    }

    public function testRequestResourceWithExpiredToken()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client
        ->method('request')
            ->withConsecutive(
                [
                    $this->equalTo(['GET','/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer token_expired',
                        'accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ],
                [
                    $this->equalTo(['POST','/oauth2/token']),
                    $this->equalTo(['accept: application/json']),
                    $this->equalTo([
                        'grant_type' => 'client_credentials',
                        'scope' => 'open-api',
                        'client_secret' => 'test_client_secret',
                        'client_id' => 'test_client_id'
                    ])
                ],
                [
                    $this->equalTo(['GET','/v1/guests']),
                    $this->equalTo([
                        'Authorization: Bearer new_created_token',
                        'accept: application/json'
                    ]),
                    $this->equalTo(['fields' => '_id'])
                ])
            ->will($this->onConsecutiveCalls(
                [
                    [],
                    [
                        "token_type"=>"Bearer",
                        "expires_in"=> 86400,
                        "access_token"=>"new_created_token",
                        "scope"=>"open-api"
                    ],
                    [
                        "count"=>1,
                        "results"=>[
                            ["_id" => "58243555fb61770400aede31",]
                        ]
                        //sample guests data
                    ]
                ]));
        $client->method('getLastResponseCode')
            ->will($this->onConsecutiveCalls(401,200,200));

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client,
            'token_expired'
        );
        $guestCount = $guestyClient->getGuestCount();
        //verify result correct
        $this->assertEquals($guestCount,1);
    }


    // public function testRequestResourceWithNoToken()
    // {
    //     /** @var ClientWrapper&MockObject $client*/
    //     $client = $this->createMock(ClientWrapper::class);
    //     $client->method('request')
    //         ->willReturn(["aaa"]);
    //         //return 401 saying no token at first
    //         //then return token at second request
    //         // then third request with updated token and return correct result

    //     $guestyClient = new GuestyClient(
    //         "test_client_id",
    //         "test_client_secret",
    //         $client
    //     );
    //     $guestCount = $guestyClient->getGuestCount();
    //     // verify result
    //     print_r( $guestCount);
    // }

    // public function testRequestNotExistResource()
    // {
    //     /** @var ClientWrapper&MockObject $client*/
    //     $client = $this->createMock(ClientWrapper::class);
    //     $client->method('request')
    //         ->willReturn(["aaa"]);
    //         //return 404 saying resource not found

    //     $guestyClient = new GuestyClient(
    //         "test_client_id",
    //         "test_client_secret",
    //         $client
    //     );
        
    //     $guestCount = $guestyClient->getGuestCount();
    //     // expect exception
    // }

    // public function testRequestOtherException()
    // {
    //     //GUESTY OPEN API KEY
    //     /** @var ClientWrapper&MockObject $client*/
    //     $client = $this->createMock(ClientWrapper::class);
    //     $client->method('request')
    //         ->willReturn(["aaa"]);
    //         //return 400 saying bad request

    //     $guestyClient = new GuestyClient(
    //         "test_client_id",
    //         "test_client_secret",
    //         $client
    //     );
    //     $token = $guestyClient->fetchNewToken();
    //     // expect exception
    // }
}
