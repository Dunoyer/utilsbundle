<?php

namespace FOPG\Component\UtilsBundle\Exception;

use FOPG\Component\UtilsBundle\Contracts\ExceptionInterface;

class InvalidArgumentException extends \Exception implements ExceptionInterface
{
	public function __construct($message,$code=self::DEFAULT_CODE)
	{
		parent::__construct($message,$code);
	}
}
