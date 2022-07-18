<?php

use Cozy\Lib\Guesty\Webhook\GuestyListingParser;
use Cozy\Lib\Guesty\Webhook\GuestyReservationParser;
use Cozy\Lib\Guesty\Webhook\GuestyWebhookHandler;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertEquals;

class GuestyWebhookTest extends TestCase
{
    public function testReservationParser()
    {
        //TODO: use raw guesty data
        //pass data to parser
        //check if parser fields return successfully
        $guestyReservationParser = new GuestyReservationParser();
        $guestyReservationParser->loadData([/** TODO: actual data */]);
        assertEquals($guestyReservationParser->getCheckIn(), 1);
        //TODO: more tests
    }

    public function testListingParser()
    {
        //TODO: use raw guesty data
        //pass data to listing parser
        //check if parser fields return successfully
        $guestyListingParser = new GuestyListingParser();
        $guestyListingParser->loadData([/** TODO: actual data */]);
        assertEquals($guestyListingParser->getAccommodates(), 1);
        //TODO: more tests
    }

    public function testWebhookHandler()
    {
        $guestyWebhookHandler = new GuestyWebhookHandler();
        $guestyWebhookHandler->setOnListingCreated(function () {
        })
            ->setOnListingUpdate(function () {
            })
            ->setOnReservationCreate(function () {
            })
            ->setOnReservationUpdate(function () {
            })
            ->handle([]);
        //TODO: use raw guesty reservation data
        //set some callback to it
        //check the callback has been invoked
        //see https://stackoverflow.com/questions/9296529/phpunit-how-to-test-if-callback-gets-called
    }
}
