<?php

namespace Laura\Lib\External;

use Cozy\Lib\Guesty\GuestyClient;
use PHPUnit\Framework\TestCase;

class GuestyClientTest extends TestCase
{
    public function testCreation()
    {
        $guestyClient = new GuestyClient();
        $count = $guestyClient->getListingCount();
        $this->assertNotEquals($count, 0);
    }
}
