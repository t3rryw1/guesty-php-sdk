<?php

namespace Cozy\Lib\Guesty\Webhook;

interface IReservationParser
{
    public function getGuestId();

    public function getGuestCount();

    public function getCheckIn();

    public function getCheckOut();

    public function getStatus();

    public function getPlatformCode();

    public function getPlatformOrderId();

    public function getChannelId();

    public function getExternalPropertyId();

    public function getMeta();
}
