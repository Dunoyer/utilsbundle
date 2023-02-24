<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;

class SimpleHtmlTest extends TestCase
{
    const SECTION_HEADER = '[SimpleHtmlTest]';

    public function testBuilding(): void
    {
        $this->section(self::SECTION_HEADER.' Validation sur la transformation des informations du DOM');
        /** @var string $html */
        $html = "<html><body><h1>nothing</h1></body></html>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();
        /** @var SimpleHtmlNodeList $h1s */
        $h1s = $dom->find('h1');
        /** @var SimpleHtmlNode $h1 */
        $h1 = $h1s[0];

        $this->iteration("Insertion d'une balise 'span' devant la balise 'h1'");

        $h1->before('<span>test</span>');


        $this->compareTo($dom->find('body')->html(), "<body><span>test</span><h1>nothing</h1></body>", 'OK', 'KO');

        $this->iteration("Insertion d'une balise 'div' après la balise 'h1'");

        $h1->after('<div id="ab">test</div>');

        $this->compareTo($dom->find('body')->html(), '<body><span>test</span><h1>nothing</h1><div id="ab">test</div></body>', 'OK', 'KO');
        unset($h1);

        $this->iteration("Insertion d'un texte après après la balise 'div'");

        $div = $dom->find('div')[0];
        $div->after('ainsi >');

        $this->compareTo($dom->find('body')->html(), '<body><span>test</span><h1>nothing</h1><div id="ab">test</div>ainsi &gt;</body>', 'OK', 'KO');

        $this->iteration('Gestion du "&" lors des manipulations dans le texte');

        $div->after("xxx&xxx");

        $this->compareTo($dom->find('body')->html(), '<body><span>test</span><h1>nothing</h1><div id="ab">test</div>xxx&amp;xxxainsi &gt;</body>', 'OK', 'KO');
    }

    public function testRetrieving(): void
    {
        $this->section(self::SECTION_HEADER.' Validation sur la récupération des informations du DOM');
        /** @var string $html */
        $html = "<html><body><title class='pong'><h1><h1>test</h1><h3>ahlalallalalalal</h3></h1></title><div id='none' class='article master'><h1 id='test'>Ceci est un test</h1></div></body></html>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();
        /** @var SimpleHtmlNodeList $nodeList */
        $nodeList = $dom->find('h1');

        $this->iteration('Récupération du premier element H1 de <h1><h1>test</h1><h3>ahlalallalalalal</h3></h1>');

        $this->compareTo($nodeList[0]->text(),'testahlalallalalalal','OK','KO');

        $this->iteration('Récupération du second element H1 de <h1><h1>test</h1><h3>ahlalallalalalal</h3></h1>');

        $this->compareTo($nodeList[1]->text(),'test','OK','KO');

        $this->iteration('Récupération de l\'élément de classe "pong"');

        $nodeList = $dom->find('[@class=pong]');

        $this->compareTo((count($nodeList) === 1) && ($nodeList[0]->text() ==='testahlalallalalalal'),true,'OK','KO');

    }
}
