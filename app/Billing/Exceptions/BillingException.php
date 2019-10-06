<?php

namespace App\Billing\Exceptions;

class BillingException extends \Exception
{
    /**
     * @var string $message
     */
    public $message;

    /**
     * @var string $code
     */
    public $code;

    public function __construct($message, $code)
    {
        $this->message = $message;
        $this->code = $code;
    }
}
