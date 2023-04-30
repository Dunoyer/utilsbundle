<?php

namespace FOPG\Component\UtilsBundle\Exception;

use FOPG\Component\UtilsBundle\Contracts\ExceptionInterface;

class InvalidArgumentException extends \Exception implements ExceptionInterface
{
	public function __construct($message,$code=self::INVALID_ARGUMENT_EXCEPTION)
	{
		parent::__construct($message,$code);
	}
}
