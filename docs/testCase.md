Description du sous-composant TestCase
==

Introduction
--
Afin d'assurer des mises à l'échelle applicative robuste, une surcharge du composant natif TestCase du framework Symfony a été créé. L'objectif est double :
* rendre humainement compréhensible les réalisations des tests
* fournir une documentation de premier niveau des composants mis en oeuvre.

Utilisation
--
1. Créer un nouveau test via la commande *make:test* en sélectionnant *TestCase* comme type de test

2. Modifier le fichier de test généré comme suit :

Remplacer

```
use PHPUnit\Framework\TestCase;
```
par

```
use FOPG\Component\UtilsBundle\Test\TestCase;
```

3. Déclarer un nouveau test suivant la DDD

A l'intérieur d'une méthode test...(), déclarer votre test selon la syntaxe suivante :

```
// démontrons que l'opérateur > indique que $a est supérieur à $b et que l'opérateur < indique que $a est inférieur à $c
$a = 5;
$b = 3;
$c = 7;
$this
  ->given(description: 'Soit trois nombres a,b et c',a: $a,b: $b, c: $c)
  ->when(description: "J'applique l'opérateur > entre a et b", callback: function(int $a, int $b, &$opSup) {
    $opSup = ($a>$b);
  })
  ->andWhen(description: "J'applique l'opérateur < entre a et c", callback: function(int $a, int $c, &$opInf) {
    $opInf = ($a<$c);
  })
  ->then(description: "L'opérateur > doit être booléen et égal à true", callback: function($opSup){
    return $opSup;
  }, result: true)
  ->andThen(description: "L'opérateur < doit être booléen et égal à true", callback: function($opInf){
    return $opInf;
  }, result: true)
;
```

4. Contrôler le résultat de l'exécution

Le rendu doit être celui-ci :

```
1. [ETANT DONNE] Soit trois nombres a,b et c

1.1. [SI] J'applique l'opérateur > entre a et b

1.2. [ET SI] J'applique l'opérateur < entre a et c

1.3. [ALORS] L'opérateur > doit être booléen et égal à true

 ✔ OK

1.4. [ET ALORS] L'opérateur < doit être booléen et égal à true

 ✔ OK
```
