<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;

class SimpleHtmlNodeListTest extends TestCase
{
    const SECTION_HEADER = '[SimpleHtml:SimpleHtmlNodeList]';

    const SUBSECTION_TEST_ON_DEPTH = '[depth]';

    public function testDepth(): void
    {
        $this->section(self::SECTION_HEADER.self::SUBSECTION_TEST_ON_DEPTH.' Parcours dans l\'arbre du DOM');

        /** @var string $html */
        $html = "<a><b id='t'>x</b><c>y</c><b><u>ici</u><v class='der'>la</v></b></a><a>z<c>u</c><b><v class='der'>tac</v></b></a>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();
        /** @var SimpleHtmlNodeList $nodeList */

        $this->iteration('Recherche de parcours"');

        $this->subiteration('Recherche de la composition de tag "a/b/u"');

        $this->compareTo($dom->findAll('a/b/u')->getText(), "ici", 'OK', 'KO');

        $this->subiteration('Recherche de la composition de tag "a/b/v[class=der]"');

        $this->compareTo(($dom->findAll('a/b/v[class=der]')->getText() === "latac") && ($dom->findOne('a/b/v[class=der]')->getText() === "la"), true, 'OK', 'KO');

        $this->subiteration('Recherche de la composition de tag [id=t] par sélecteur');

        $this->compareTo(($dom->findAll('[id=t]')->getText() === "x") && ($dom->findOne('[id=t]')->getText() === "x"), true, 'OK', 'KO');

        $this->subiteration('Recherche de la composition de tag [id=t] par les méthodes findById() et findOneById()');

        $this->compareTo(($dom->findById('t')->getText() === "x") && ($dom->findOneById('t')->getText() === "x"), true, 'OK', 'KO');

        $this->subiteration('Recherche de la composition de tag "c" par les méthodes findByTagName() et findOneByTagName()');

        $this->compareTo(($dom->findByTagName('c')->getText() === "yu") && ($dom->findOneByTagName('c')->getText() === "y"), true, 'OK', 'KO');

        $html = "<ul><li>1</li><li>2</li><li>3</li></ul>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $node = $dom->findOne('li');
        $this->iteration('Parcours alternatif à la méthode findAll()');

        $this->subiteration('Parcours par la méthode findNextOne() et contrôle du getIndex()');
        $check = true;
        for($i=2;($node = $node->findNextOne());$i++)
          $check = $check && ((string)$node === "<li>$i</li>") && ($i === $node->getIndex());
        $this->compareTo($check, true, 'OK', 'KO');
    }

    public function testRetrieving(): void
    {
        $this->section(self::SECTION_HEADER.' Validation sur la récupération des informations du DOM');
        /** @var string $html */
        $html = "<html><body><title class='pong'><h1><h1>test</h1><h3>ahlalallalalalal</h3></h1></title><div id='none' class='article master'><h1 id='test'>Ceci est un test</h1></div></body></html>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();
        /** @var SimpleHtmlNodeList $nodeList */
        $nodeList = $dom->findAll('h1');

        $this->iteration('Récupération du premier element H1 de <h1><h1>test</h1><h3>ahlalallalalalal</h3></h1>');

        $this->compareTo($nodeList[0]->getText(),'testahlalallalalalal','OK','KO');

        $this->iteration('Récupération du second element H1 de <h1><h1>test</h1><h3>ahlalallalalalal</h3></h1>');

        $this->compareTo($nodeList[1]->getText(),'test','OK','KO');

        $this->iteration('Récupération de l\'élément de classe "pong"');

        $nodeList = $dom->findAll('[@class=pong]');

        $this->compareTo((count($nodeList) === 1) && ($nodeList[0]->getText() ==='testahlalallalalalal'),true,'OK','KO');

        $this->iteration("Test de la commande getInnertext() sur les balises 'h1'");

        $this->compareTo($dom->findAll('h1')->getInnertext(),'testahlalallalalalaltestCeci est un test','OK','KO');

        $this->iteration("Test de la commande getInnerhtml() sur les balises 'h1'");

        $this->compareTo($dom->findAll('h1')->getInnerhtml(), '<h1>test</h1><h3>ahlalallalalalal</h3>testCeci est un test', 'OK', 'KO');

        /** @var string $html */
        $html = "<ul><li>a</li><li>b</li><li>c</li></ul>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $this->iteration("Correspondance entre getHtml() et les méthodes de SimpleHtmlBase");

        $this->subiteration("Correspondance entre getHtml() et getFirstChild()");

        $this->compareTo($dom->findAll('li')[0]->getHtml(),$dom->findAll('ul')->getFirstChild()->getHtml(),'OK','KO');

        $this->subiteration("Correspondance entre getHtml() et getLastChild()");

        $this->compareTo($dom->findAll('li')[2]->getHtml(),$dom->findAll('ul')->getLastChild()->getHtml(),'OK','KO');
    }
}
