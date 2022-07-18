<?php

namespace Cozy\Lib\Guesty\Webhook;

interface IListingParser
{
    public function getExternalId();

    public function getAccommodates();

    public function getNumberOfBeds();

    public function getNumberOfBathrooms();

    public function getNumberOfRooms();

    public function getHouseRules();

    public function getRoomType();

    public function getTimezone();

    public function getNickname();

    public function getPrice();

    public function getDepositFee();

    public function getCleaningFee();

    public function getMaximumStay();

    public function getMinimalStay();

    public function getIsActive();

    public function getAddress();

    public function getAmenities();
}
