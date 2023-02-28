<?php

namespace FOPG\Component\UtilsBundle\Test;

class TestGiven {

  const PREFIX_WHEN = "[SI]";
  const PREFIX_THEN = "[ALORS]";

  private array $_args = [];
  private ?TestCase $_testCase = null;

  public function __construct(TestCase $testCase, array $args) {
      $this->_args = $args;
      $this->_testCase = $testCase;
  }

  /**
   * @param string $description
   * @param Callable $callback
   */
  public function when(string $description, Callable $callback): self
  {
    $this->_testCase->subiteration(self::PREFIX_WHEN.' '.$description);
    $func = new \ReflectionFunction($callback);
    $args = [];
    foreach($func->getParameters() as $param)
    {
      $args[$param->getName()]=in_array($param->getName(), array_keys($this->_args)) ? $this->_args[$param->getName()] : null;
    }

    $callback(...$args);

    foreach($args as $argName => $argValue)
      $this->_args[$argName]=$argValue;
    return $this;
  }

  public function then(string $description, Callable $callback, mixed $result): self
  {
    $this->_testCase->subiteration(self::PREFIX_THEN.' '.$description);

    $func = new \ReflectionFunction($callback);
    $args = [];
    foreach($func->getParameters() as $param)
    {
      $args[$param->getName()]=in_array($param->getName(), array_keys($this->_args)) ? $this->_args[$param->getName()] : null;
    }
    /** @var mixed $target */
    $target = $callback(...$args);

    $this->_testCase->compareTo($target, $result, 'OK', 'KO');
    
    return $this;
  }
}
