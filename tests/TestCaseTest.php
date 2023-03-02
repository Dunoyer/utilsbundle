<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;

class TestCaseTest extends TestCase
{
    const SECTION_HEADER = '[TestCase]';

    public function testValidate(): void
    {
      $this->section(self::SECTION_HEADER.' Contrôle fonctionnel');
      // démontrons que l'opérateur > indique que $a est supérieur à $b et que l'opérateur < indique que $a est inférieur à $c
      $a = 5;
      $b = 3;
      $c = 7;
      $this
        ->given(
          description: 'Soit trois nombres a,b et c',
          a: $a,
          b: $b,
          c: $c
        )
        ->when(
          description: "J'applique l'opérateur > entre a et b",
          callback: function(int $a, int $b, &$opSup) { $opSup = ($a>$b); }
        )
        ->then(
          description: "L'opérateur > doit être booléen et égal à true",
          callback: function($opSup){ return $opSup; },
          result: true
        )
        ->andWhen(
          description: "J'applique l'opérateur < entre a et c",
          callback: function(int $a, int $c, &$opInf) { $opInf = ($a<$c); }
        )
        ->andThen(
          description: "L'opérateur < doit être booléen et égal à true",
          callback: function($opInf){ return $opInf; },
          result: true
        )
      ;

      $this
        ->given(
          description:"Je veux effectuer un test avec un paramètre non déclaré dans le when()"
        )
        ->when(
          description: "J'appelle un paramètre non déclaré",
        callback: function($param) { }
        )

        ->then(
          description: "Le paramètre non déclaré doit être null",
          callback: function($param) { return $param; },
          result: null
        )
      ;
    }
}
