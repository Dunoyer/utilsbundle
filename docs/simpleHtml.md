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

@todo
