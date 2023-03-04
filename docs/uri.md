Description du sous-composant Uri
==

Introduction
--
Le composant Uri offre un puissant ensemble de méthodes offrant un système de normalisation et la validation d'URI. Il s'agit d'une consolidation de la méthode native "parse_url".

La normalisation d'URL garanti une syntaxe respectant la norme RFC 3986. A titre d'illustration, l'appel aux système de fichiers via les URI  "C:\\Windows\system32\test.cnf" (cas windows) ou "/home/myaccount/test.cnf" (cas linux), le composant formatera respectivement en "file:///C:/Windows/system32/test.cnf" et "file:///home/myaccount/test.cnf".

Contexte : qu'est-ce qu'une URL ?
--
Une URL est une chaîne de caractère structurée suivant un motif spécifique donné par le standard RFC 3986. Ce motif n'est qu'un enchevetrement de données élémentaires.

### Section globale

```
  ----------://----------/----------?----------#----------
  \________/   \________/ \________/ \________/ \________/
    scheme        host       path       query    fragment
```

### Sous-section *host*

```
-------------------:-------------------@---------(.?)---------.-------------------.-------------------
\_________________/ \_________________/ \____________________/ \_________________/ \_________________/
     username            password              subdomain            domain           topLevelDomain
\____________________________________________________________________________________________________/
```

### Sous-section *path*

```
-------(/*)-------/(.?)-----(.*)-----(.------------------?)
\________________/ \________________/\____________________/
      folder           filename        filenameExtension
\_________________________________________________________/
```

Démarrage rapide
--

```php
use FOPG\Component\UtilsBundle\Uri\Uri;
...
/** @var Uri $uri */
$uri = new Uri(["http"],[80]);
$uri->sanitize("google.fr");
/** @var string $correctUrl */
$correctUrl = (string)$uri;

dump($correctUrl);         # renvoit 'http://www.google.fr/'

/** @var string $scheme */
$data = [
  'scheme'          => $uri-getScheme(),
  'subdomain'       => $uri->getSubdomain(),
  'domain'          => $uri->getDomain(),
  'topLevelDomain'  => $uri->getTopLevelDomain(),
];

dump($data);  
/**
 * renvoit [
 *  'scheme'          => 'http',
 *  'subdomain'       => 'www',
 *  'domain'          => 'google',
 *  'topLevelDomain'  => 'fr',
 * ]
 */
```
