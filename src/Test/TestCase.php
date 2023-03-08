<?php

namespace FOPG\Component\UtilsBundle\Test;

use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase as SfTestCase;

class TestCase extends SfTestCase {

  const PREFIX_GIVEN = "[ETANT DONNE]";
  private $_iteration_number=0;
  private $_sub_iteration_number=0;

  public function setUp(): void
  {
  }

  /**
   * Récupération de la variable d'environnement courante
   *
   * @param string $param
   * @return mixed
   */
  public function getEnv(string $param): mixed
  {
    $block = $_ENV[$param] ?? null;
    if(preg_match("/^[ ]*[{](.*)[}][ ]*$/", $block) && preg_match_all("/[ ]*[']?(?<key>[^'{,]+)[']?[ ]*[:][ ]*(?<value>[']?[^},]+[']?)[ ]*/i", $block, $matches)) {
      $output = [];
      $count = count($matches['key']);
      for($i=0;$i<$count;$i++) {
        $val = trim($matches['value'][$i]);
        if(in_array($val,['true','false']))
          $val = ($val === 'true');
        elseif(preg_match("/^\d$/",$val))
          $val = (int)$val;
        else
          $val = preg_replace("/(^'|'$)/","", $val);
        $output[$matches['key'][$i]]=$val;
      }
      return $output;
    }
    else
      return $block;
  }

  public function compareTo(mixed $value,mixed $valueReferred,string $msgOnSuccess,string $msgOnFailed): bool {
    if(is_array($value))
      $value = serialize($value);
    if(is_array($valueReferred))
      $valueReferred = serialize($valueReferred);
    if(null !== $value) {
      $msgOnSuccess = str_replace("{value}",$value,$msgOnSuccess);
      $msgOnFailed = str_replace("{value}",$value,$msgOnFailed);
    }
    if(null !== $valueReferred) {
      $msgOnSuccess = str_replace("{valueReferred}",$valueReferred,$msgOnSuccess);
      $msgOnFailed = str_replace("{valueReferred}",$valueReferred,$msgOnFailed);
    }
    if($value === $valueReferred) {
      $this->success($msgOnSuccess);
      $this->assertTrue(true);
      return true;
    }
    else {
      $this->error($msgOnFailed);
      $this->assertTrue(false);
      return false;
    }
  }

  public function error(string $text)
  {
    fwrite(STDERR,"\r\n\e[1m\e[31m ✘ $text\e[0m\r\n");
  }

  public function success(string $text)
  {
    fwrite(STDERR,"\r\n\e[1m\e[32m ✔ $text\e[0m\r\n");
  }

  public function section(string $text)
  {
    $len = mb_strlen($text);
    fwrite(STDERR, "\r\n$text\r\n");
    fwrite(STDERR, str_repeat("-",$len)."\r\n");
    $this->_iteration_number=0;
    $this->_sub_iteration_number=0;
  }

  public function given(string $description, ...$args): TestGiven
  {
    $this->iteration(self::PREFIX_GIVEN.' '.$description);
    return new TestGiven($this,$args);
  }

  public function subiteration(string $text)
  {
    $this->_sub_iteration_number++;
    $iteration = $this->_iteration_number;
    $subiteration = $this->_sub_iteration_number;
    fwrite(STDERR, "\r\n$iteration.$subiteration. $text\r\n");
  }

  public function iteration(string $text)
  {
    $this->_iteration_number++;
    $iteration = $this->_iteration_number;
    fwrite(STDERR, "\r\n$iteration. $text\r\n");
    $this->_sub_iteration_number=0;
  }

  public function debug($data)
  {
    fwrite(STDERR, print_r($data, true));
  }
}
