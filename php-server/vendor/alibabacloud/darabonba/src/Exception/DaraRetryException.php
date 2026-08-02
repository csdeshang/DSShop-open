<?php

namespace AlibabaCloud\Dara\Exception;

/**
 * Class DaraRetryException
 */
class DaraRetryException extends DaraException
{
    /**
     * DaraRetryException constructor.
     *
     * @param string          $message
     * @param int             $code
     * @param null|\Throwable $previous
     */
    public function __construct($message = '', $code = 0, $previous = null)
    {
        parent::__construct([], $message, $code, $previous);
    }
}
