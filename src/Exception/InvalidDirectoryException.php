<?php

namespace FOPG\Component\UtilsBundle\Exception;

use FOPG\Component\UtilsBundle\Contracts\ExceptionInterface;

class InvalidDirectoryException extends \Exception implements ExceptionInterface
{

	public function __construct(string $directory) {
		parent::__construct(str_replace("{{directory}}",$directory, self::INVALID_DIRECTORY_EXCEPTION_MSG),self::INVALID_DIRECTORY_EXCEPTION_CODE);
	}
}
