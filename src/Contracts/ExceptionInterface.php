<?php

namespace FOPG\Component\UtilsBundle\Contracts;

interface ExceptionInterface
{
  const INVALID_DIRECTORY_EXCEPTION_CODE = 1000;
  const INVALID_FILENAME_EXCEPTION_CODE = 1002;
  const INVALID_DIRECTORY_EXCEPTION_MSG = "Le répertoire '{{directory}}' n'est pas accessible";
  const INVALID_FILENAME_EXCEPTION_MSG = "Le fichier '{{filename}}' n'est pas accessible";
  const INVALID_ARGUMENT_EXCEPTION = 1001;
}
