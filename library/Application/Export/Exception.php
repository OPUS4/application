<?php

class Application_Export_Exception extends Application_Exception
{
    public function __construct($message)
    {
        $this->_httpResponseCode = 401;
        $this->message = $message;
    }
}