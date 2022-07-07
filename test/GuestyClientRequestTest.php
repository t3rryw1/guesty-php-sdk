<?php

namespace Laura\Lib\External;

use Cozy\Lib\Guesty\ClientWrapper;
use Cozy\Lib\Guesty\GuestyClient;

use PHPUnit\Framework\TestCase;

class GuestyClientRequestTest extends TestCase
{
    private $test_client_id;
    private $token;
    private $test_client_secret;
    private $guestyClient;

    // In the setUp
    public function setUp(): void
    {
        $this->test_client_id = readline('Enter a client id: ');
        $this->test_client_secret = readline('Enter a client secret: ');
        $this->token = readline('Enter a token: ');
        $client = new ClientWrapper(GuestyClient::BASE_URL);
        $this->guestyClient = new GuestyClient(
            $this->test_client_id,
            $this->test_client_secret,
            $client,
            $this->token
        );
    }

    public function testGuestyRequests()
    {
        //guest count
        $res = $this->guestyClient->getGuestCount();
        $this->assertEquals(gettype($res), 'integer');

        //guest list
        $res = $this->guestyClient->getGuests(5,5);
        $this->assertEquals(sizeof($res), 5);

        //get guest
        $res = $this->guestyClient->getGuest('5c4465ea7b6c1f001e1a46e8');
        $this->assertEquals('5c4465ea7b6c1f001e1a46e8', $res['_id']);

        //listings count
        $res = $this->guestyClient->getListingCount();
        $this->assertEquals(gettype($res), 'integer');

        //get reservation
        $res = $this->guestyClient->retrieveReservation('5c436b5ab1d05b00335bd517');
        $this->assertEquals('5c436b5ab1d05b00335bd517', $res['_id']);

        //get listing
        $res = $this->guestyClient->getListing('5c030ea850449600408b17aa');
        $this->assertEquals('5c030ea850449600408b17aa', $res['_id']);

        //get listings
        $res = $this->guestyClient->getListings(0,5);
        $this->assertEquals(sizeof($res), 5);

    }
}