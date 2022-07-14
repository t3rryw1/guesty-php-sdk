<?php

namespace Cozy\Lib\Guesty\Webhook;

use Symfony\Component\PropertyAccess\PropertyAccess;

class GuestyListingParser extends AbstractParser implements IListingParser
{
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getAccommodates()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][accommodates]');
    }

    public function getNumberOfBeds()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][beds]');
    }

    public function getNumberOfBathrooms()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][bathrooms]');
    }

    public function getNumberOfRooms()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][bedrooms]');
    }

    public function getHouseRules()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][accommodates]');
    }

    public function getRoomType()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][roomType]');
    }

    public function getTimezone()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][timezone]');
    }

    public function getNickname()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][title]');
    }

    public function getPrice()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][prices][basePrice]');
    }

    public function getDepositFee()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][prices][securityDepositFee]');
    }

    public function getCleaningFee()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][prices][cleaningFee]');
    }

    public function getMaximumStay()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][terms][minNights]');
    }

    public function getMinimalStay()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][terms][maxNights]');
    }

    public function getIsActive()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][active]');
    }

    public function getExternalId()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][_id]');
    }

    public function getAmenities()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][amenities]');
    }

    public function getAddress()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listing][address][full]');
    }
}
