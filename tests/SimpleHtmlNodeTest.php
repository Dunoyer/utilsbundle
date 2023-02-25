<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;

class SimpleHtmlNodeTest extends TestCase
{
    const SECTION_HEADER = '[SimpleHtml:SimpleHtmlNode]';
    const SUBSECTION_TEST_ON_ATTRIBUTES = '[Attributes]';
    public function testBuilding(): void
    {
        $this->section(self::SECTION_HEADER.' Validation sur la transformation des informations du DOM');
        /** @var string $html */
        $html = "<html><body><h1>nothing</h1></body></html>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();
        /** @var SimpleHtmlNode $h1 */
        $h1 = $dom->findOne('h1');

        $this->iteration("Insertion d'une balise 'span' devant la balise 'h1'");

        $h1->insertBefore('<span>test</span>');


        $this->compareTo($dom->getHtml('body'), "<body><span>test</span><h1>nothing</h1></body>", 'OK', 'KO');

        $this->iteration("Insertion d'une balise 'div' après la balise 'h1'");

        $h1->insertAfter('<div id="ab">test</div>');

        $this->compareTo($dom->getHtml('body'), '<body><span>test</span><h1>nothing</h1><div id="ab">test</div></body>', 'OK', 'KO');
        unset($h1);

        $this->iteration("Insertion d'un texte après après la balise 'div'");

        $div = $dom->findOne('div');
        $div->insertAfter('ainsi >');

        $this->compareTo($dom->getHtml('body'), '<body><span>test</span><h1>nothing</h1><div id="ab">test</div>ainsi &gt;</body>', 'OK', 'KO');

        $this->iteration('Gestion du "&" lors des manipulations dans le texte');

        $div->insertAfter("xxx&xxx");

        $this->compareTo($dom->getHtml('body'), '<body><span>test</span><h1>nothing</h1><div id="ab">test</div>xxx&amp;xxxainsi &gt;</body>', 'OK', 'KO');

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

        $this->compareTo($firstDiv,'<h1 id="test">Ceci est un test</h1>','OK','KO');

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
