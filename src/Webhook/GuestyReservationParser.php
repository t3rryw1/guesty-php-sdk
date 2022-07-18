<?php

namespace Cozy\Lib\Guesty\Webhook;

use Cozy\Lib\Guesty\GuestyConstants;
use DateTimeZone;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GuestyReservationParser extends AbstractParser implements IReservationParser
{
    //guesty fields

    public const FEE_ITEM_CONTENT = [
        'ACCOMMODATION' => "Accommodation fare",
        'CLEANING' => "Cleaning fee",
        'PLATFORM' => "Host channel fee",
        'EXTRA_GUEST' => "Extra person fee",
        'LENGTH_DISCOUNT' => "Length of stay discount",
        'TAX' => "Other taxes",
    ];

    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    public function getGuestId()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[guestId]');
    }

    public function getCheckIn()
    {
        return date_create($this->propertyAccessor->getValue($this->getData(), '[checkIn]'), new DateTimeZone("UTC"));
    }

    public function getCheckOut()
    {
        return date_create($this->propertyAccessor->getValue($this->getData(), '[checkOut]'), new DateTimeZone("UTC"));
    }
    public function getConfirmedAt()
    {
        return date_create($this->propertyAccessor->getValue($this->getData(), '[confirmedAt]'), new DateTimeZone("UTC"));
    }

    public function getGuestCount()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[guestsCount]');
    }

    public function getCustomerName()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[guest][fullName]');
    }

    public function getCombineName()
    {
        $firstName = $this->propertyAccessor->getValue($this->getData(), '[guest][firstName]');
        $lastName = $this->propertyAccessor->getValue($this->getData(), '[guest][lastName]');
        if ($firstName || $lastName) {
            return $firstName . " " . $lastName;
        }
        return null;
    }

    public function getStatus()
    {
        return strtoupper($this->propertyAccessor->getValue($this->getData(), '[status]'));
    }

    public function getPlatformCode()
    {
        return str_replace(str_split('\\/:*?"<>|. '), '_', strtoupper($this->propertyAccessor->getValue($this->getData(), '[source]')));
    }

    public function getChannelId()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[_id]');
    }

    public function getExternalPropertyId()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listingId]');
    }

    public function getContactInfo()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[guest][phone]');
    }

    public function getMeta()
    {
        return $this->getData();
    }

    public function getListingId()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[listingId]');
    }

    public function getFees($feeName)
    {
        $fees = $this->propertyAccessor->getValue($this->getData(), '[money][invoiceItems]');
        $key = array_search($feeName, array_column($fees, 'title'));
        $amount = $this->d2c(abs($fees[$key]['amount']));

        return $amount;
    }

    public function getTotal()
    {
        return $this->d2c($this->propertyAccessor->getValue($this->getData(), '[money][netIncome]'));
    }

    public function getPlatformOrderId()
    {
        if (strtoupper($this->propertyAccessor->getValue($this->getData(), '[source]')) === GuestyConstants::PLATFORM_BOOKINGCOM) {
            return $this->propertyAccessor->getValue($this->getData(), '[integration][bookingCom][reservationId]');
        } else {
            return $this->propertyAccessor->getValue($this->getData(), '[confirmationCode]');
        }
    }

    public function getCurrency()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[money][currency]');
    }

    public function getIntegration()
    {
        return strtoupper($this->propertyAccessor->getValue($this->getData(), '[integration][platform]'));
    }

    public function getItemType($itemName)
    {
        if (in_array($itemName, array_flip(self::FEE_ITEM_CONTENT))) {
            return array_flip(self::FEE_ITEM_CONTENT[$itemName]);
        } else {
            return str_replace(' ', '_', strtoupper($itemName));
        }
    }

    public function getIsPaid()
    {
        return $this->propertyAccessor->getValue($this->getData(), '[money][isFullyPaid]');
    }

    private function d2c($number)
    {
        //if the variable is other type e.g. [] will cause error in some place so add this to convert to int 0;
        $number = empty($number) ? 0 : $number;
        return intval(round($number * 100));
    }
}
