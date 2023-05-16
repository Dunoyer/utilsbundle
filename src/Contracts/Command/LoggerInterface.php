<?php

namespace FOPG\Component\UtilsBundle\Contracts\Command;

interface LoggerInterface {
  const LOG_DEBUG       = 1;
  const LOG_SUCCESS     = 2;
  const LOG_NOTE        = 3;
  const LOG_SECTION     = 4;
  const LOG_TITLE       = 5;
  const LOG_TEXT        = 6;
  const LOG_ERROR       = 7;
  const DEBUG_MODE      = 1;
  const PRODUCTION_MODE = 2;
}
