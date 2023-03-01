<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlDom;

class SimpleHtmlNodeTest extends TestCase
{
    const SECTION_HEADER = '[SimpleHtml:SimpleHtmlNode]';
    const SUBSECTION_TEST_ON_ATTRIBUTES = '[Attributes]';
    const SUBSECTION_TEST_ON_TAG = '[Tags]';
    public function testBuilding(): void
    {

        $this->section(self::SECTION_HEADER.self::SUBSECTION_TEST_ON_TAG.' Validation sur la transformation des informations du DOM');

        /** @var string $html */
        $html = <<<EOF
<html>
  <head>
    <title>Test</title>
  </head>
  <body>
    <insert_amp>
      <mam>ping</mam>
    </insert_amp>
    <insert_text>
      <my>tac</my>
    </insert_text>
    <insert_here>
      <h1>nothing</h1>
    </insert_here>
    <insert_after>
      <h5>all</h5>
    </insert_after>
  </body>
</html>
EOF;
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $this
          ->given(
            description: 'On souhaite étudier la récupération d\'informations pour un noeud donné',
            dom: $dom
          )
          ->when(
            description: 'On isole le titre du document',
            callback: function(SimpleHtmlDom $dom, ?string &$title=null) {
              $title = $dom->getTitle();
            }
          )
          ->andWhen(
            description: 'On insère un SPAN devant le H1 par la méthode insertBefore()',
            callback: function(SimpleHtmlDom $dom) {
              $dom
                ->findOne('h1')
                ->insertBefore('<span>test</span>')
              ;
            }
          )
          ->andWhen(
            description: 'On insère un SPAN après le H5 par la méthode insertAfter()',
            callback: function(SimpleHtmlDom $dom) {
              $dom
                ->findOne('h5')
                ->insertAfter('<span>test</span>')
              ;
            }
          )
          ->andWhen(
            description: 'On insère du texte contenant le caractère spécial ">" après la balise MY par la méthode insertAfter()',
            callback: function(SimpleHtmlDom $dom) {
              $dom
                ->findOne('my')
                ->insertAfter('>ici')
              ;
            }
          )
          ->andWhen(
            description: 'On insère du texte contenant le caractère spécial "&" après la balise MAM par la méthode insertAfter()',
            callback: function(SimpleHtmlDom $dom) {
              $dom
                ->findOne('mam')
                ->insertAfter('&ici')
              ;
            }
          )
          ->then(
            description: "Le contenu de H1 est 'Test'",
            callback: function(?string $title) {
              return $title;
            },
            result: 'Test'
          )
          ->andThen(
            description: 'Le contenu du BODY prend en compte l\'insertion du SPAN via insertBefore()',
            callback: function(SimpleHtmlDom $dom) {
              return str_replace(
                ["\r","\n","\t"," "],
                "",
                $dom->findOne('insert_here')->getHtml()
              );

            },
            result: "<insert_here><span>test</span><h1>nothing</h1></insert_here>"
          )
          ->andThen(
              description: 'Le contenu du BODY prend en compte l\'insertion du SPAN via insertAfter()',
              callback: function(SimpleHtmlDom $dom) {
                return str_replace(
                  ["\r","\n","\t"," "],
                  "",
                  $dom->findOne('insert_after')->getHtml()
                );
              },
              result: "<insert_after><h5>all</h5><span>test</span></insert_after>"
          )
          ->andThen(
              description: 'Le contenu du BODY gère le caractère ">" en tant que texte quand il est isolé via insertAfter()',
              callback: function(SimpleHtmlDom $dom) {
                return str_replace(
                  ["\r","\n","\t"," "],
                  "",
                  $dom->findOne('insert_text')->getHtml()
                );
              },
              result: "<insert_text><my>tac</my>&gt;ici</insert_text>"
          )
          ->andThen(
              description: 'Le contenu du BODY gère le caractère "&" en tant que texte quand il est isolé via insertAfter()',
              callback: function(SimpleHtmlDom $dom) {
                return str_replace(
                  ["\r","\n","\t"," "],
                  "",
                  $dom->findOne('insert_amp')->getHtml()
                );
              },
              result: "<insert_amp><mam>ping</mam>&amp;ici</insert_amp>"
          )
        ;
        dump('@todo arrêt ici');
        die;
        return;

        $this->iteration("Remplacement de la balise 'div' par la balise 'h5'");

        $div->replace("<h5>TAC</h5>");

        $this->compareTo($dom->getHtml('body'), '<body><span>test</span><h1>nothing</h1><h5>TAC</h5>xxx&amp;xxxainsi &gt;</body>', 'OK', 'KO');

        $this->iteration("Suppression des balises 'span'");

        $dom->findAll('span')->remove();

        $this->compareTo($dom->getHtml('body'), '<body><h1>nothing</h1><h5>TAC</h5>xxx&amp;xxxainsi &gt;</body>', 'OK', 'KO');

        /** @var string $html */
        $html = "<html><body><title class='pong'><h1><h1>test</h1><h3>ahlalallalalalal</h3></h1></title><div id='none' class='article master'><h1 id='test'>Ceci est un test</h1></div></body></html>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $this->iteration("Récupération du contenu de la première balise 'div'");
        /** @var string $firstDiv */
        $firstDiv = $dom->findAll('div')->getFirstChild();
        
        $this->compareTo($firstDiv->getHtml(),'<h1 id="test">Ceci est un test</h1>','OK','KO');

        $this->iteration("Test de la commande getInnertext() du 'div'");

        $this->compareTo($dom->findOne('div')->getInnertext(),'Ceci est un test','OK','KO');

        $this->iteration("Test de la commande getInnerhtml() du 'div'");

        $this->compareTo($dom->findOne('div')->getInnerhtml(), '<h1 id="test">Ceci est un test</h1>', 'OK', 'KO');

        $this->iteration("Récupération du nom de tag courant");
        
        $this->compareTo($dom->findOne('div')->getTagName(), 'div', 'OK', 'KO');
    }

    public function testOnAttributes()
    {
      $this->section(self::SECTION_HEADER.self::SUBSECTION_TEST_ON_ATTRIBUTES);
      /** @var string $html */
      $html = "<html><body><title class='pong'><h1><h1>test</h1><h3>ahlalallalalalal</h3></h1></title><div id='none' class='article master'><h1 id='test'>Ceci est un test</h1></div></body></html>";
      /** @var SimpleHtmlDom $dom */
      $dom = SimpleHtml::str_get_html($html)->getContainer();

      $this->iteration("Récupération des attributs du noeud d'id 'none'");

      $div = $dom->findOne('[@id=none]');

      $this->compareTo($div->getAttributes(),['id' => 'none', 'class' => 'article master'],'OK','KO');

      $this->iteration("Détection de la présence de l'attribut 'class' du noeud d'id 'none'");

      $this->compareTo(($div->hasAttribute('class') === true) && ($div->hasAttribute('xyz') === false), true, 'OK', 'KO');

      $this->iteration("Récupération de l'attribut 'id' du noeud d'id 'none'");

      $this->compareTo(($div->getAttribute('id') === 'none') && ($div->getAttribute('xyz') === null), true, 'OK', 'KO');

      $this->iteration('Traitement spécifique sur les classes');

      $this->subiteration('Raccourci pour récupérer les classes');

      $classes = $div->getClasses();

      $this->compareTo(in_array('article', $classes) && !in_array('albert', $classes), true, 'OK', 'KO');

      $this->subiteration('Raccourci pour vérifier l\'existence d\'une classe');

      $this->compareTo($div->hasClass('master') && !$div->hasClass('toto'), true, 'OK', 'KO');
    }
}
