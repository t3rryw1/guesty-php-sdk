<?php

namespace Cozy\Lib\Guesty\Webhook;

class GuestyWebhookHandler implements IWebhookHandler
{
    private $reservationUpdateCallback;
    private $reservationCreateCallback;
    private $listingUpdateCallback;
    private $listingCreatedCallback;

    public function handle(array $requestData): bool
    {
        switch ($requestData['event']) {
            case "reservation.new":
                $parser = new GuestyReservationParser();
                $parser->loadData($requestData['reservation']);
                call_user_func($this->reservationCreateCallback, $parser);
                return true;
            case "reservation.updated":
                $parser = new GuestyReservationParser();
                $parser->loadData($requestData['reservation']);
                call_user_func($this->reservationUpdateCallback, $parser);
                return true;
            case "listing.update":
                $parser = new GuestyListingParser();
                $parser->loadData($requestData['listing']);
                call_user_func($this->listingUpdateCallback, $parser);
                return true;
            case "listing.created":
                $parser = new GuestyListingParser();
                $parser->loadData($requestData['reservation']);
                call_user_func($this->listingCreatedCallback, $parser);
                return true;
            default:
                return false;
        }
    }
    public function setOnReservationUpdate(callable $callback): IWebhookHandler
    {
        $this->reservationUpdateCallback = $callback;
        return $this;
    }
    public function setOnReservationCreate(callable $callback): IWebhookHandler
    {
        $this->reservationCreateCallback = $callback;
        return $this;
    }
    public function setOnListingUpdate(callable $callback): IWebhookHandler
    {
        $this->listingUpdateCallback = $callback;
        return $this;
    }
    public function setOnListingCreated(callable $callback): IWebhookHandler
    {
        $this->listingCreatedCallback = $callback;
        return $this;
    }
}
