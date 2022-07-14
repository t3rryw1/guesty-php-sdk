<?php

namespace Cozy\Lib\Guesty\Webhook;

interface IWebhookHandler
{
    public function handle(array $request): bool;
    public function setOnReservationUpdate(callable $callback): IWebhookHandler;
    public function setOnReservationCreate(callable $callback): IWebhookHandler;
    public function setOnListingUpdate(callable $callback): IWebhookHandler;
    public function setOnListingCreated(callable $callback): IWebhookHandler;
    //TODO: other event
}
