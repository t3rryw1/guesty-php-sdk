<?php

namespace Cozy\Lib\Guesty\Webhook;

abstract class AbstractParser
{
    private $data;

    public function loadData($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
