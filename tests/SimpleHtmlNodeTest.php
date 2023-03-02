<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlDom;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlNode;

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
    <replacement>
      <div id="replace_me">replace me</div>
    </replacement>
    <delete>
      <mu>noeud à supprimer</mu>
      <mu>second noeud à supprimer</mu>
    </delete>
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
          ->then(
            description: "Le contenu de H1 est 'Test'",
            callback: function(?string $title) {
              return $title;
            },
            result: 'Test'
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
          ->andWhen(
            description: 'On insère un SPAN après le H5 par la méthode insertAfter()',
            callback: function(SimpleHtmlDom $dom) {
              $dom
                ->findOne('h5')
                ->insertAfter('<span>test</span>')
              ;
            }
          )
          ->andThen(
              description: 'Le contenu du BODY prend en compte l\'insertion du SPAN via insertAfter()',
              callback: function(SimpleHtmlDom $dom) {
                return str_replace(["\r","\n","\t"," "],"",$dom->findOne('insert_after')->getHtml());
              },
              result: "<insert_after><h5>all</h5><span>test</span></insert_after>"
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
          ->andThen(
              description: 'Le contenu du BODY gère le caractère ">" en tant que texte quand il est isolé via insertAfter()',
              callback: function(SimpleHtmlDom $dom) {
                return str_replace(["\r","\n","\t"," "],"",$dom->findOne('insert_text')->getHtml()
                );
              },
              result: "<insert_text><my>tac</my>&gt;ici</insert_text>"
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
          ->andThen(
              description: 'Le contenu du BODY gère le caractère "&" en tant que texte quand il est isolé via insertAfter()',
              callback: function(SimpleHtmlDom $dom) {
                return str_replace(["\r","\n","\t"," "],"",$dom->findOne('insert_amp')->getHtml());
              },
              result: "<insert_amp><mam>ping</mam>&amp;ici</insert_amp>"
          )
          ->andWhen(
            description: 'Je remplace le noeud d\'id "replace_me" par la méthode replace()',
            callback: function(SimpleHtmlDom $dom) {
              $dom
                ->findOneById('replace_me')
                ->replace('<toc>tac</toc>')
              ;
            }
          )
          ->andThen(
              description: 'Le noeud d\'id "replace_me" a été remplacé par <toc>tac</toc>',
              callback: function(SimpleHtmlDom $dom) {
                $html = $dom->findOne("replacement")->getHtml();
                return str_replace(["\r","\n","\t"," "],"", $html);
              },
              result: "<replacement><toc>tac</toc></replacement>"
          )
          ->andWhen(
            description: "Je supprime l'ensemble des noeuds 'mu' du HTML par la méthode remove()",
            callback: function(SimpleHtmlDom $dom) {
              $dom->findAll('mu')->remove();
            }
          )
          ->andThen(
              description: "L'ensemble des noeuds 'mu' est supprimé par la méthode remove()",
              callback: function(SimpleHtmlDom $dom) {
                $html = $dom->findOne("delete")->getInnerhtml();
                return str_replace(["\r","\n","\t"," "],"", $html);
              },
              result: ""
          )
          ->andWhen(
            description: "Je récupère le noeud courant 'h1'",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$h1=null) {
              $h1 = $dom->findOne('h1');
            }
          )
          ->andThen(
            description: "Le nom de balise du noeud courant 'h1' obtenue par getTagName() doit correspondance à 'h1'",
            callback: function(?SimpleHtmlNode $h1) {
              return $h1->getTagName();
            },
            result: 'h1'
          )
        ;
    }

    public function testOnAttributes()
    {
      $this->section(self::SECTION_HEADER.self::SUBSECTION_TEST_ON_ATTRIBUTES);
      /** @var string $html */
      $html = <<<EOF
<html>
  <body>
    <title class='pong'>
      <h1>
        <h1>test</h1>
        <h3>ahlalallalalalal</h3>
      </h1>
    </title>
    <div id='none' class='article master'>
      <h1 id='test'>Ceci est un test</h1>
    </div>
  </body>
</html>
EOF;

      /** @var SimpleHtmlDom $dom */
      $dom = SimpleHtml::str_get_html($html)->getContainer();

      $this
        ->given(
            description: "On contrôle la gestion entourant les attributs d'un noeud",
            dom: $dom
        )
        ->when(
            description: "Je souhaite récupérer les attributs via la méthode getAttributes() du noeud d'id 'none'",
            callback: function(SimpleHtmlDom $dom, ?SimpleHtmlNode &$myDiv=null, ?array &$attrs=[]) {
              $myDiv = $dom->findOneById('none');
              $attrs = $myDiv->getAttributes();
            }
        )
        ->then(
            description: "Les attributs 'id' et 'class' doivent être retrouvés par la méthode ",
            callback: function(array $attrs) {
              return (
                !empty($attrs['id']) && ($attrs['id'] === 'none') &&
                !empty($attrs['class']) && ($attrs['class'] === 'article master')
              );
            },
            result: true
        )
        ->andThen(
            description: "Je dois vérifier que l'attribut 'class' est bien présent par la méthode hasAttribute()",
            callback: function(?SimpleHtmlNode $myDiv) {
              return $myDiv->hasAttribute('class');
            },
            result: true
        )
        ->andThen(
            description: "Je dois vérifier que l'attribut 'xyz' n'existe pas par la méthode hasAttribute()",
            callback: function(?SimpleHtmlNode $myDiv) {
              return $myDiv->hasAttribute('xyz');
            },
            result: false
        )
        ->andThen(
            description: "La valeur de l'attribut 'id' récupérée par la méthode getAttribute() doit être 'none'",
            callback: function(?SimpleHtmlNode $myDiv) {
              return $myDiv->getAttribute('id');
            },
            result: 'none'
        )
        ->andThen(
            description: "La valeur de l'attribut 'xyz' récupérée par la méthode getAttribute() doit être null",
            callback: function(?SimpleHtmlNode $myDiv) {
              return $myDiv->getAttribute('xyz');
            },
            result: null
        )
        ->andWhen(
            description: "Je souhaite récupérer les classes via la méthode getClasses() du noeud d'id 'none'",
            callback: function(SimpleHtmlDom $dom, ?array &$classes=[]) {
              $myDiv = $dom->findOneById('none');
              $classes = $myDiv->getClasses();
            }
        )
        ->andThen(
            description: "La classe 'article' doit être présente dans le noeud d'id 'none'",
            callback: function(array $classes) { return in_array('article', $classes); },
            result: true
        )
        ->andThen(
            description: "La classe 'xyz' ne doit pas être présente dans le noeud d'id 'none'",
            callback: function(array $classes) { return in_array('xyz', $classes); },
            result: false
        )
        ->andThen(
            description: "La classe 'article' doit être détectée par la méthode hasClass()",
            callback: function(SimpleHtmlNode $myDiv) { return $myDiv->hasClass('master'); },
            result: true
        )
        ->andThen(
            description: "La classe 'toto' ne doit pas être détectée par la méthode hasClass()",
            callback: function(SimpleHtmlNode $myDiv) { return $myDiv->hasClass('toto'); },
            result: false
        )
      ;
    }
}
