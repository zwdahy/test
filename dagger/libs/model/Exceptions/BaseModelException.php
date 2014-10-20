<?php

class BaseModelException extends Exception
{
    public function __construct($message=null, $code=0) {
	parent::__construct($message, $code);
    }
}

function daggerExceptionHandler($exception) {
    if (defined("DAGGER_DEBUG")) {
	BaseModelMessage::showError($exception->getMessage(), array(), $exception->getCode());
    } else {
	BaseModelMessage::showError('我们无能，抱歉让您看到这个页面');
    }
}

set_exception_handler('daggerExceptionHandler');

