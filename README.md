Description du composant UtilsBundle
==

Introduction
--
L'objectif du composant est de fournir un ensemble d'outils trop limité pour disposer de leur propre bundle.

Les outils compris dans le module sont :
* [SimpleHtml](/docs/simpleHtml.md) - librairie permettant l'exploration simplifiée d'un document HTML
* [TestCase](/docs/testCase.md) - surcharge du composant natif Symfony TestCase pour un pilotage des tests unitaire dans le respect du Domain Driven Design
* [Uri](/docs/uri.md) - librairie de manipulation d'URI

Installation
--

1. Déclaration du package au sein du composer.json

Rajouter le repository externe pour le bundle utils de la manière suivante :

```yaml
"repositories": [
  ...
  {
    "type": "vcs",
    "url": "https://gitlab.adullact.net/friend-of-pastry-garden/component/utilsbundle.git"
  }
  ...
]
```

2. Appel du bundle pour votre application

``
composer require friend-of-pastry-garden/utils-bundle
``

Configuration des tests
--

Un ensemble de variables d'environnement servent à configurer l'exécution des tests
| Variable | Description | Exemple |
| -- | -- | -- |
| TEST__IGNORE_WIP | Autorisation d'ignorer les développements en cours | true |
