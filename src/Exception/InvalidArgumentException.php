<?php

namespace FOPG\Component\UtilsBundle\Exception;

use FOPG\Component\UtilsBundle\Contracts\InvalidArgumentExceptionInterface;

class InvalidArgumentException extends \Exception implements InvalidArgumentExceptionInterface
{
	public function __construct($message,$code=self::DEFAULT_CODE)
	{
		parent::__construct($message,$code);
	}
}
