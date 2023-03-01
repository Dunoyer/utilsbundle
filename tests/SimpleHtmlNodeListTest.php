<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlDom;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNode;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNodeList;

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

        $this
          ->given(
            description: 'On veut vérifier la récupération d\'information dans un HTML via un parcours arborescent',
            dom: $dom,
            html: $html
          )
          ->when(
            description: 'Je récupère l\'ensemble des noeuds répondant au parcours de balise "a/b/u"',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$result=null) {
              $result = $dom->findAll('a/b/u');
            }
          )
          ->then(
            description: 'Le texte concaténé de l\'ensemble des noeuds doit être égal à "ici"',
            callback: function(?SimpleHtmlNodeList $result){
              return $result->getText();
            },
            result: "ici"
          )
          ->when(
            description: 'Je récupère l\'ensemble des noeuds répondant au parcours de balise "a/b/v[class=der]"',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$result=null) {
              $result = $dom->findAll('a/b/v[class=der]');
            }
          )
          ->andWhen(
            description: 'Je récupère le premier noeud répondant au parcours de balise "a/b/v[class=der]"',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$resultOne=null) {
              $resultOne = $dom->findOne('a/b/v[class=der]');
            }
          )
          ->then(
            description: 'Le texte concaténé de l\'ensemble des noeuds doit être égal à "latac"',
            callback: function(?SimpleHtmlNodeList $result){ return $result->getText(); },
            result: "latac"
          )
          ->andThen(
            description: 'Le texte concaténé de du premier noeud doit être égal à "la"',
            callback: function(?SimpleHtmlNode $resultOne){ return $resultOne->getText(); },
            result: "la"
          )
          ->when(
            description: 'Je récupère l\'ensemble des noeuds répondant à l\'id "t" par la méthode findAll()',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$result=null) {
              $result = $dom->findAll('[id=t]');
            }
          )
          ->andWhen(
            description: 'Je récupère le premier noeud répondant à l\'id "t" par la méthode findOne()',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$resultOne=null) {
              $resultOne = $dom->findOne('[id=t]');
            }
          )
          ->then(
            description: 'Le texte concaténé de l\'ensemble des noeuds doit être égal à "x"',
            callback: function(?SimpleHtmlNodeList $result){ return $result->getText(); },
            result: "x"
          )
          ->andThen(
            description: 'Le texte du premier noeud doit être égal à "x"',
            callback: function(?SimpleHtmlNode $resultOne){ return $resultOne->getText(); },
            result: "x"
          )
          ->when(
            description: 'Je récupère l\'ensemble des noeuds répondant à l\'id "t" par la méthode findById()',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$result=null) {
              $result = $dom->findById('t');
            }
          )
          ->andWhen(
            description: 'Je récupère l\'ensemble des noeuds répondant à l\'id "t" par la méthode findOneById()',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$resultOne=null) {
              $resultOne = $dom->findOneById('t');
            }
          )
          ->then(
            description: 'Le texte concaténé de l\'ensemble des noeuds doit être égal à "x"',
            callback: function(?SimpleHtmlNodeList $result){ return $result->getText(); },
            result: "x"
          )
          ->andThen(
            description: 'Le texte du premier noeud doit être égal à "x"',
            callback: function(?SimpleHtmlNode $resultOne){ return $resultOne->getText(); },
            result: "x"
          )
          ->when(
            description: 'Je récupère l\'ensemble des noeuds répondant à la balise "c" par la méthode findByTagName()',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$result=null) {
              $result = $dom->findByTagName('c');
            }
          )
          ->andWhen(
            description: 'Je récupère le premier noeud répondant à la balise "c" par la méthode findOneByTagName()',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$resultOne=null) {
              $resultOne = $dom->findOneByTagName('c');
            }
          )
          ->then(
            description: 'Le texte concaténé de l\'ensemble des noeuds doit être égal à "yu"',
            callback: function(?SimpleHtmlNodeList $result){ return $result->getText(); },
            result: "yu"
          )
          ->andThen(
            description: 'Le texte du premier noeud doit être égal à "y"',
            callback: function(?SimpleHtmlNode $resultOne){ return $resultOne->getText(); },
            result: "y"
          )
        ;

        $html = "<ul><li>1</li><li>2</li><li>3</li></ul>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $this
          ->given(
            description: "On veut contrôler l'exploitation du parcours alternatif des noeuds par la combinaison findNextOne()/getIndex()",
            dom: $dom
          )
          ->when(
            description: 'Je récupère le second élément "li" du DOM',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$secondLi=null) {
                $secondLi = $dom
                  ->findOne('li')
                  ->findNextOne()
                ;
            }
          )
          ->then(
            description: 'La valeur du second LI doit être "2"',
            callback: function(?SimpleHtmlNode $secondLi) { return $secondLi->getText(); },
            result: '2'
          )
          ->andThen(
            description: 'L\'index courant du second LI doit être 2',
            callback: function(?SimpleHtmlNode $secondLi) { return $secondLi->getIndex(); },
            result: 2
          )
          ->when(
            description: 'Je récupère le troisième élément "li" du DOM',
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$thirdLi=null) {
                $thirdLi = $dom
                  ->findOne('li')
                  ->findNextOne()
                  ->findNextOne()
                ;
            }
          )
          ->then(
            description: 'La valeur du troisième LI doit être "3"',
            callback: function(?SimpleHtmlNode $thirdLi) { return $thirdLi->getText(); },
            result: '3'
          )
          ->andThen(
            description: 'L\'index courant du troisième LI doit être 3',
            callback: function(?SimpleHtmlNode $thirdLi) { return $thirdLi->getIndex(); },
            result: 3
          )
          ;
    }

    public function testRetrieving(): void
    {
        $this->section(self::SECTION_HEADER.' Validation sur la récupération des informations du DOM');
        /** @var string $html */
        $html = "<html><body><title class='pong'><h1><h1>test</h1><h3>ahlalallalalalal</h3></h1></title><div id='none' class='article master'><h1 id='test'>Ceci est un test</h1></div></body></html>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $this
          ->given(
            description: "On veut contrôler l'extraction de l'information contenue dans le HTML",
            dom: $dom
          )
          ->when(
            description: "Je souhaite récupérer les éléments H1 par la méthode findAll()",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$list = null) {
              $list = $dom->findAll('h1');
            }
          )
          ->then(
            description: "La valeur du premier élément de H1 est 'testahlalallalalalal'",
            callback: function(SimpleHtmlNodeList $list) {
              return $list[0]->getText();
            },
            result: 'testahlalallalalalal'
          )
          ->andThen(
            description: "La valeur du second élément de H1 est 'test'",
            callback: function(SimpleHtmlNodeList $list) {
              return $list[1]->getText();
            },
            result: 'test'
          )
          ->andThen(
            description: "La valeur de la méthode Innertext() est 'testahlalallalalalaltestCeci est un test'",
            callback: function(SimpleHtmlNodeList $list) {
              return $list->getInnertext();
            },
            result: 'testahlalallalalalaltestCeci est un test'
          )
          ->andThen(
            description: "La valeur de la méthode Innerhtml() est '<h1>test</h1><h3>ahlalallalalalal</h3>testCeci est un test'",
            callback: function(SimpleHtmlNodeList $list) {
              return $list->getInnerhtml();
            },
            result: '<h1>test</h1><h3>ahlalallalalalal</h3>testCeci est un test'
          )
          ->when(
            description: "Je souhaite récupérer les éléments de classe 'pong' par la méthode findAll()",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNodeList &$list = null) {
              $list = $dom->findAll('[@class=pong]');
            }
          )
          ->then(
            description: "Le nombre d'éléments de class 'pong' doit être égal à 1",
            callback: function(SimpleHtmlNodeList $list) {
              return count($list);
            },
            result: 1
          )
          ->andThen(
            description: "Le premier élément de class 'pong' doit être égal à 'testahlalallalalalal'",
            callback: function(SimpleHtmlNodeList $list) {
              return $list[0]->getText();
            },
            result: 'testahlalallalalalal'
          )
        ;

        /** @var string $html */
        $html = "<ul><li>a</li><li>b</li><li>c</li></ul>";
        /** @var SimpleHtmlDom $dom */
        $dom = SimpleHtml::str_get_html($html)->getContainer();

        $this
          ->given(
            description: "On souhaite contrôler les correspondances entre méthodes de SimpleHtmlBase",
            dom: $dom
          )
          ->when(
            description: "Je récupère la première occurence LI via indexation",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$nodeA=null) {
              $nodeA = $dom->findAll('li')[0];
            }
          )
          ->andWhen(
            description: "Je récupère la dernière occurence LI via indexation",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$nodeD=null) {
              $list = $dom->findAll('li');
              $nodeD = $list[count($list)-1];
            }
          )
          ->andWhen(
            description: "Je récupère le premier enfant du UL via getFirstChild()",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$nodeB=null) {
              $nodeB = $dom->findAll('ul')->getFirstChild();
            }
          )
          ->andWhen(
            description: "Je récupère le dernier enfant du UL via getLastChild()",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$nodeC=null) {
              $nodeC = $dom->findAll('ul')->getLastChild();
            }
          )
          ->then(
            description: "Les méthodes exploitant getFirstChild() et l'indexation 0 récupèrent le même objet",
            callback: function(?SimpleHtmlNode $nodeA, ?SimpleHtmlNode $nodeB) {
              return ($nodeA == $nodeB);
            },
            result: true
          )
          ->andThen(
            description: "Les méthodes exploitant getLastChild() et l'indexation max-1 récupèrent le même objet",
            callback: function(?SimpleHtmlNode $nodeD, ?SimpleHtmlNode $nodeC) {
              return ($nodeC == $nodeD);
            },
            result: true
          )
        ;
    }
}
