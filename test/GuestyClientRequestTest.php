<?php

namespace Laura\Lib\External;

use Cozy\Lib\Guesty\ClientWrapper;
use Cozy\Lib\Guesty\GuestyClient;

use PHPUnit\Framework\TestCase;

class GuestyClientRequestTest extends TestCase
{
    private static $guestyClient;

    // In the setUp
    public static function setUpBeforeClass(): void
    {
        $test_client_id = readline('Enter a client id: ');
        $test_client_secret = readline('Enter a client secret: ');
        $token = readline('Enter a token(Optional): ');
        $logger = new \Monolog\Logger("guesty");
        $logger->pushHandler(new \Monolog\Handler\ErrorLogHandler());
        
        $client = new ClientWrapper(GuestyClient::BASE_URL,true,$logger);
        $guestyClient = new GuestyClient(
            $test_client_id,
            $test_client_secret,
            $client,
            $token
        );
        $guestyClient->setTokenUpdateCallback(function ($token) {
            echo $token;
        });
        self::$guestyClient = $guestyClient;
    }

    public function testGuestyEndPoints()
    {
        //guest count
        $res = self::$guestyClient->getGuestCount();
        $this->assertEquals(gettype($res), 'integer');
        //guest list
        $res = self::$guestyClient->getGuests(5, 5);
        $this->assertEquals(sizeof($res), 5);
        if (sizeof($res) === 5) {
            $guestId = $res[0]['_id'];
        }
        //get guest
        $res = self::$guestyClient->getGuest($guestId ?? null);
        $this->assertEquals($guestId, $res['_id']);

        //listings count
        $res = self::$guestyClient->getListingCount();
        $this->assertEquals(gettype($res), 'integer');
        //get listings
        $res = self::$guestyClient->getListings(0, 5);
        $this->assertEquals(sizeof($res), 5);
        if (sizeof($res) === 5) {
            $listingId = $res[0]['_id'];
        }
        //get listing
        $res = self::$guestyClient->getListing($listingId ?? null);
        $this->assertEquals($listingId, $res['_id']);

        //get reservation list
        $res = self::$guestyClient->retrieveReservationList(0, 5);
        $this->assertEquals(sizeof($res), 5);
        if (sizeof($res) === 5) {
            $reservationId = $res[0]['_id'];
        }
        //get reservation
        $res = self::$guestyClient->retrieveReservation($reservationId);
        $this->assertEquals($reservationId, $res['_id']);
    }
}