<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Env\Env;

class EnvTest extends TestCase
{
    const SECTION_HEADER = '[Env]';

    public function testEnvData(): void {
      $this->section(self::SECTION_HEADER.' Contrôle de récupération aux variables d\'environnement');

      $commonFakeEnvParam = md5("abcdefghijklmnopqrstuvwxyz");
      
      $this
        ->given(
          description: 'Contrôle de la bonne récupération des variables d\'environnement',
          commonFakeEnvParam: $commonFakeEnvParam
        )
        ->when(
          description: 'Je souhaite récupérer la valeur de APP_ENV',
          callback: function(?string &$currentEnv=null) {
            $currentEnv = Env::get('APP_ENV');
          }
        )
        ->then(
          description: "Je dois retrouver la valeur 'test'",
          callback: function(string $currentEnv) {
            return $currentEnv;
          },
          result: 'test'
        )
        ->andWhen(
          description: 'Je souhaite récupérer la valeur d\'une variable d\'environnement qui n\'existe pas',
          callback: function(?string &$currentEnv=null) {
            $myFakeEnvData = "aobosolqo4252".rand(0,1000);
            $currentEnv = Env::get($myFakeEnvData);
          }
        )
        ->andThen(
          description: "Je dois retrouver la valeur null",
          callback: function(?string $currentEnv) {
            return $currentEnv;
          },
          result: null
        )
        ->andWhen(
          description: 'Je souhaite modifier une variable d\'environnement',
          callback: function(string $commonFakeEnvParam) {
            Env::set($commonFakeEnvParam,"TEST");
          }
        )
        ->andThen(
          description: "La variable d'environnement modifiée doit être modifiée",
          callback: function(string $commonFakeEnvParam) {
            return Env::get($commonFakeEnvParam);
          },
          result: "TEST"
        )
      ;
    }
}
