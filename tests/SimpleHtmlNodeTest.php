<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;

class SimpleHtmlNodeTest extends TestCase
{
    const SECTION_HEADER = '[SimpleHtml:SimpleHtmlNode]';

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
    }
}
