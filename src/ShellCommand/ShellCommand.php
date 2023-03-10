<?php

namespace FOPG\Component\UtilsBundle\ShellCommand;

class ShellCommand
{
  /** @var array */
  private ?array $_buffer = [];

  public function __construct() {
  }

  public function getBuffer(): array {
    return $this->_buffer;
  }

  public function initBuffer(): self {
    $this->_buffer = [];
    return $this;
  }

  public function appendInBuffer(string $command, array $output, int $exitCode): self {
    $this->_buffer[]=[
      'command' => $command,
      'output' => $output,
      'exit_code' => $exitCode,
    ];
    return $this;
  }

  public function getError(): ?string {
    if(true === $this->hasError()) {
      $lastIndex= count($this->_buffer) - 1;
      $lastErr  = $this->_buffer[$lastIndex];
      $errno    = $lastErr['exit_code'];
      $output   = implode("\n", $lastErr['output']);
      $cmd      = $lastErr['command'];
      return("{command:$cmd, exit_code: $errno, output: $output}");
    }
    return null;
  }

  public function getLastOutput(): array {
    $lastOutput = count($this->_buffer) - 1;
    if($lastOutput >= 0)
      return $this->_buffer[$lastOutput]['output'];
    return [];
  }

  public function hasError(): bool {
    foreach($this->_buffer as $buffer)
      if(0 !== $buffer['exit_code'])
        return true;
    return false;
  }

  public function execute(array $commands): bool {
    $this->initBuffer();
    foreach($commands as $command) {
      $action = $command['action'];
      $args   = $command['args'] ?? [];
      /** @var string $protectedCommand */
      $protectedCommand = $action;
      $output = null;
      $exitCode = null;
      foreach($args as $arg => $replacement)
        $protectedCommand = str_replace($arg, escapeshellarg($replacement), $protectedCommand);
      $protectedCommand.=" 2> /dev/null";
      @exec($protectedCommand, $output, $exitCode);
      $this->appendInBuffer($protectedCommand, $output, $exitCode);
      $stdOut = implode("\n", $output);
      if ($exitCode !== 0)
        return false;
    }
    return true;
  }
}
