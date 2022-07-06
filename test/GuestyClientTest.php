<?php

namespace Laura\Lib\External;

use Cozy\Lib\Guesty\GuestyClient;
use PHPUnit\Framework\TestCase;

class GuestyClientTest extends TestCase
{
    public function testCreation()
    {
        //GUESTY OPEN API KEY
        $guesty_client_id="";
        $guesty_client_secret="";
        $guestyClient = new GuestyClient($guesty_client_id, $guesty_client_secret,);
        $token = $guestyClient->fetchNewToken();
    }
}
