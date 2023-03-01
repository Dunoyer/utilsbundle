<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;

class TestCaseTest extends TestCase
{
    const SECTION_HEADER = '[TestCase]';

    public function testValidate(): void
    {
      // démontrons que l'opérateur > indique que $a est supérieur à $b et que l'opérateur < indique que $a est inférieur à $c
      $a = 5;
      $b = 3;
      $c = 7;
      $this
        ->given(description: 'Soit trois nombres a,b et c',a: $a,b: $b, c: $c)
        ->when(description: "J'applique l'opérateur > entre a et b", callback: function(int $a, int $b, &$opSup) {
          $opSup = ($a>$b);
        })
        ->when(description: "J'applique l'opérateur < entre a et c", callback: function(int $a, int $c, &$opInf) {
          $opInf = ($a<$c);
        })
        ->then(description: "L'opérateur > doit être booléen et égal à true", callback: function($opSup){
          return $opSup;
        }, result: true)
        ->then(description: "L'opérateur < doit être booléen et égal à true", callback: function($opInf){
          return $opInf;
        }, result: true)
      ;
    }
}
