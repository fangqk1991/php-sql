<?php

namespace FC\SQL;

class SQLException extends \Exception
{
    public function __construct($message)
    {
        parent::__construct($message, -1);
    }
}