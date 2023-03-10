<?php

namespace FOPG\Component\UtilsBundle\Exception;

use FOPG\Component\UtilsBundle\Contracts\ExceptionInterface;

class InvalidFilenameException extends \Exception implements ExceptionInterface
{

	public function __construct(string $directory) {
		parent::__construct(str_replace("{{filename}}",$directory, self::INVALID_FILENAME_EXCEPTION_MSG),self::INVALID_FILENAME_EXCEPTION_CODE);
	}
}
