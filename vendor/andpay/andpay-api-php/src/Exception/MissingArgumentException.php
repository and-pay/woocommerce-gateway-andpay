<?php

namespace Andpay\Exception;

use Andpay\Exception\ErrorException;

class MissingArgumentException extends ErrorException
{
    /**
     * @param string|array    $required
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct($required, $code = 0, $previous = null)
    {
        if (is_string($required)) {
            $required = [$required];
        }

        parent::__construct(sprintf('One or more of required ("%s") parameters is missing!', implode('", "', $required)), $code, 1, __FILE__, __LINE__, $previous);
    }
}
