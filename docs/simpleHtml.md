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
```php
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

/**
 * Récupération du premier H1
 * @var ?SimpleHtmlNode $node
 */
$node = $dom->findOneByTagName('h1');

dump($node->getText());   # renvoit 'testahlalallalalalal'
```
###### 2. Recherche par identifiant
```php
/**
 * Récupération des noeuds d'id "test"
 * @var SimpleHtmlNodeList $nodes
 */
$nodes = $dom->findById('test');
/**
 * Alternative à la méthode findById()
 * @var SimpleHtmlNodeList $altNodes
 */
$altNodes = $dom->findAll('[id=test]');
/**
 * Récupération du premier noeud d'id "test"
 * @var ?SimpleHtmlNode $node
 */
$node = $dom->findOneById('test');

dump($nodes->getText());                # renvoit 'Ceci est un test'
dump($altNodes->getText());             # renvoit 'Ceci est un test'
dump($node ? $node->getText() : null);  # renvoit 'Ceci est un test'
```
###### 3. Recherche par nom de classe

```php
/**
 * Récupération des noeuds de classe 'article'
 * @var SimpleHtmlNodeList $nodes
 */
$nodes = $dom->findAll('[class=article]');

dump($nodes->getText());    # renvoit 'Ceci est un test'

/**
 * Récupération du premier noeud de classe 'article'
 * @var ?SimpleHtmlNode $node
 */
$node = $dom->findOne('[class=article]');

dump($node->getText());     # renvoit 'Ceci est un test'
```

###### 4. Parcours des résultats via pointeur

Une alternative a été développé au parcours par index classique de l'objet SimpleHtmlNodeList, l'approche par pointeur à l'intérieur de l'objet SimpleHtmlNode. Deux méthodes ont été développée pour ce parcours :
* findNextOne(): ?SimpleHtmlNode - retrouve le successeur ou null si inexistant
* getIndex() : int - retrouve la position courante dans le parcours

```php
/**
 * Récupération du premier H1
 * @var ?SimpleHtmlNode $node
 */
$node = $dom->findOne('h1');
dump($node->getIndex().' : '.$node->getText());
while($node = $node->findNextOne()) {
  dump($node->getIndex().' : '.$node->getText());
}
```

###### 5. Méthode d'extraction de la donnée

@todo
