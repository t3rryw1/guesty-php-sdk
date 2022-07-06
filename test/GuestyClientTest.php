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
        $client->method('request')
            ->willReturn(["aaa"]);
        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $token = $guestyClient->fetchNewToken();
    }

    public function testRequestResourceNormalWorkflow()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->method('request')
            ->willReturn(["aaa"]);
        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $guestCount = $guestyClient->getGuestCount();
        //verify result correct
    }

    public function testRequestResourceWithExpiredToken()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->method('request')
            ->willReturn(["aaa"]);
            //return expired result at first
            //then return token at second request
            // then third request with updated token and return correct result
        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $guestCount = $guestyClient->getGuestCount();
        // verify result
        print_r( $guestCount);
    }

    public function testRequestResourceWithNoToken()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->method('request')
            ->willReturn(["aaa"]);
            //return 401 saying no token at first
            //then return token at second request
            // then third request with updated token and return correct result

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $guestCount = $guestyClient->getGuestCount();
        // verify result
        print_r( $guestCount);
    }

    public function testRequestNotExistResource()
    {
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->method('request')
            ->willReturn(["aaa"]);
            //return 404 saying resource not found

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        
        $guestCount = $guestyClient->getGuestCount();
        // expect exception
    }

    public function testRequestOtherException()
    {
        //GUESTY OPEN API KEY
        /** @var ClientWrapper&MockObject $client*/
        $client = $this->createMock(ClientWrapper::class);
        $client->method('request')
            ->willReturn(["aaa"]);
            //return 400 saying bad request

        $guestyClient = new GuestyClient(
            "test_client_id",
            "test_client_secret",
            $client
        );
        $token = $guestyClient->fetchNewToken();
        // expect exception
    }
}
