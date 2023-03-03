Description du sous-composant SimpleHtml
==

Introduction
--
La librairie SimpleHtml a pour objectif de simplifier la manipulation d'éléments DOM provenant d'HTML/XML.

Démarrage rapide
--

#### Instanciation sous SimpleHtml

Soit une variable *$html* contenant le texte d'un élément HTML. Nous allons déclarer l'instance *$dom* qui va nous permettre de manipuler le contenu de cette variable.

```
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;
...
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

/** @var SimpleHtmlDom $dom */
$dom = SimpleHtml::str_get_html($html)->getContainer();
```

#### Cas d'usage de SimpleHtml

##### Recherche de noeuds
###### 1. Recherche par nom de balises
```
/**
 * Récupération des noeuds de balise H3 contenu par une balise H1
 * @var SimpleHtmlNodeList $nodes
 */
$nodes = $dom->findAll('h1/h3');
$tab   = [];
/**
 * Parcours itératif
 * @var SimpleHtmlNode $node
 */
foreach($nodes as $node) {
  /** @var string $tmp */
  $tmp=$node->getText();
  $tab[]=$tmp;
}
/**
 * Extraction par concaténation des contenus
 * @var string $full
 */
$full = $nodes->getText();

dump(implode(',', $tab)); # renvoit 'ahlalallalalalal'
dump($full);              # renvoit 'ahlalallalalalal'
```
###### 2. Recherche par nom de identifiant
```
/**
 * Récupération des noeuds d'id "test"
 * @var SimpleHtmlNodeList $nodes
 */
$nodes = $dom->findById('test');
/**
 * Récupération du premier noeud d'id "test"
 * @var ?SimpleHtmlNode $node
 */
$node = $dom->findOneById('test');

dump($nodes->getText());                # renvoit 'Ceci est un test'
dump($node ? $node->getText() : null);  # renvoit 'Ceci est un test'
```
###### 3. Recherche par nom de classe
@todo
