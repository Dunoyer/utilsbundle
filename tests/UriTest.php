<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Uri\Uri;

class UriTest extends TestCase
{
    const SECTION_HEADER = "[Uri]";

    public function testHtmlUri(): void
    {
      $this->section(self::SECTION_HEADER." Manipulation d'url HTML");
      /** @var Uri $uri */
      $uri = new Uri();
      /** @var string $userInfo */
      $userInfo = "admin:azerty11!";
      /** @var array $urls */
      $urls = [
        "http://www.google.fr/" => "http://www.google.fr/",
        "http://www.google.fr" => "http://www.google.fr/",
        "www.google.fr" => "http://www.google.fr/",
        "google.fr" => "http://www.google.fr/",
        "http://www.google.fr/politique" => "http://www.google.fr/politique",
        "http://www.google.fr/?a=b&test=true" => "http://www.google.fr/?a=b&test=true",
        "google.fr/index.html.twig" => "http://www.google.fr/index.html.twig",
      ];

      $this
        ->given(
          description: "Contrôle de la génération d'URL",
          uri: $uri,
          urls: $urls,
          userInfo: $userInfo
        )
        ->when(
          description: "J'applique la méthode sanitize() sur des URLs partielles",
          callback: function(Uri $uri, array $urls, ?array &$output=[]) {
            foreach($urls as $partialUrl => $url) {
              $uri->sanitize($partialUrl);
              $output[$partialUrl] = (string)$uri;
            }
          }
        )
        ->then(
          description: "Les URLs partielles sont complétées pour être conformes",
          callback: function(array $urls, array $output) {
            $check = true;
            foreach($urls as $key => $url)
            {
              /** @var ?string $tmp */
              $tmp = $output[$key] ?? null;
              $check = $check && ($tmp === $url);
            }
            return $check;
          },
          result: true
        )
        ->andWhen(
          description: "Je souhaite déclarer un userInfo dans mon URL",
          callback: function(Uri $uri, string $userInfo) {
            $uri
              ->sanitize("http://recette.my-app.fr/dev_tools/")
              ->setUserInfo($userInfo)
            ;
          }
        )
        ->andThen(
          description: "L'URL doit être formaté en ayant pris en compte de userInfo",
          callback: function(Uri $uri) {
            return (string)$uri;
          },
          result: "http://admin:azerty11!@recette.my-app.fr/dev_tools/"
        )
        ->andWhen(
          description: "Je souhaite injecter des paramètres dans une URL dynamiquement",
          callback: function(Uri $uri) {
            $uri
              ->sanitize('https://www.boursier.com/actions/{{location}}/{{page}}?a=b&d=u',[
                'location' => 'Paris', 'c' => 'd', 'u' => 'v', 'page' => 1
              ])
            ;
          }
        )
        ->andThen(
          description: "L'URL doit être formaté avec les paramètres injectés",
          callback: function(Uri $uri) {
            return (string)$uri;
          },
          result: "https://www.boursier.com/actions/Paris/1?a=b&c=d&d=u&u=v"
        )
        ->andWhen(
          description: "Je souhaite récupérer finement les composants de l'URL",
          callback: function(Uri $uri) {
            $uri
              ->sanitize("https://subdomain.domain.fr/section1.3/subsection2.34/file.html?a=a&b=b")
            ;
          }
        )
        ->andThen(
          description: "Les sous-éléments sont bien récupérés",
          callback: function(Uri $uri) {
            return  ($uri->getScheme() === 'https') &&
                    ($uri->getSubdomain() === 'subdomain') &&
                    ($uri->getDomain() === 'domain') &&
                    ($uri->getTopLevelDomain() === 'fr') &&
                    ($uri->getHost() === 'subdomain.domain.fr') &&
                    ($uri->getAuthority() === 'subdomain.domain.fr') &&
                    ($uri->getQueryParameterKeys() === array('a','b')) &&
                    ($uri->getQueryParameter('a') === 'a') &&
                    ($uri->getUrlWithoutQueryNorFragment() === 'https://subdomain.domain.fr/section1.3/subsection2.34/file.html') &&
                    ($uri->getFolder() === 'section1.3/subsection2.34') &&
                    ($uri->getFilename() === 'file') &&
                    ($uri->getPath() === 'section1.3/subsection2.34/file.html')
            ;
          },
          result: true
        )
        ->andWhen(
          description: 'Je souhaite construire une URL ex-nihilo',
          callback: function(Uri $uri) {
            $uri->reset();
            $uri
              ->setHost('google.fr')
              ->setPath('/test/myFile.txt')
            ;
          }
        )
        ->andThen(
          description: "Mon URL est bien construite",
          callback: function(Uri $uri) {
            return (string)$uri;
          },
          result: "http://www.google.fr/test/myFile.txt"
        )
      ;


    }
}
