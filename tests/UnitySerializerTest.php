<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Env\Env;
use FOPG\Component\UtilsBundle\Serializer\Unity\Class;
use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Test\TestGiven;

class UnitySerializerTest extends TestCase
{
    const SECTION_HEADER = '[Serializer:Unity]';

    public function testRetrieveData(): void {

      $this->section(self::SECTION_HEADER.' Parcours du modèle Unity');

      /** @var ?bool $ignoreWIP */
      $ignoreWIP = Env::get("TEST__IGNORE_WIP") ? (bool)Env::get("TEST__IGNORE_WIP") : false;

      ;
    }
}
