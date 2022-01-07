<?php


namespace com\jv\testProject\exception;


use Throwable;

class Exception extends \Exception
{
    protected $message;

    protected $code;

    protected $previous;

    /**
     * Exception constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->message = $message;
        $this->code = $code;
        $this->previous = $previous;
//        parent::__construct($message, $code, $previous);
    }


}