<?php

namespace FOPG\Component\UtilsBundle\Test;

class TestGiven {

  const PREFIX_WHEN = "[SI]";
  const PREFIX_AND_WHEN = "[ET SI]";
  const PREFIX_THEN = "[ALORS]";
  const PREFIX_AND_THEN = "[ET ALORS]";

  private array $_args = [];
  private ?TestCase $_testCase = null;

  public function __construct(TestCase $testCase, array $args) {
      $this->_args = $args;
      $this->_testCase = $testCase;
  }

  public function andWhen(string $description, Callable $callback): self
  {
    return $this->when($description, $callback, self::PREFIX_AND_WHEN);
  }
  /**
   * @param string $description
   * @param Callable $callback
   */
  public function when(string $description, Callable $callback, string $prefix = self::PREFIX_WHEN): self
  {
    $this->_testCase->subiteration($prefix.' '.$description);
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

  public function andThen(string $description, Callable $callback, mixed $result): self
  {
    return $this->then($description, $callback, $result, self::PREFIX_AND_THEN);
  }

  public function then(string $description, Callable $callback, mixed $result, string $prefix=self::PREFIX_THEN): self
  {
    $this->_testCase->subiteration($prefix.' '.$description);

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
