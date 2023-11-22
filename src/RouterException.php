<?php

namespace HexMakina\Hopper;

class RouterException extends \Exception
{
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct('KADRO_ROUTER_ERR_' . $message, $code, $previous);
    }
}
