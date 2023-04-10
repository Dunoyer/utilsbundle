<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Tests\classes\FakeClass;
use FOPG\Component\UtilsBundle\Collection\Collection;


class CollectionTest extends TestCase
{
    const SECTION_HEADER = '[Collection]';

    public function testCollectionBasis(): void {

      $max = 1000001;
      $tab = [];
      $correctTab = [];
      for($i=1;$i<=$max;$i++) {
        $tab[]=new FakeClass($i,"label $i");
        $correctTab[]=$i;
      }

      $correctTab[1] = 5;
      $correctTab[4] = 2;
      $correctPermutation= "<".implode(",",$correctTab).">";

      $correctTab[2] = 2;
      $correctTab[3] = 3;
      $correctTab[4] = 4;

      $correctinsertLastToLeft= "<".implode(",",$correctTab).">";

      $orderedTab = [];
      for($i=1;$i<=$max;$i++)
        $orderedTab[]=$i;
      $correctOrdered= "<".implode(",",$orderedTab).">";

      $this->section(self::SECTION_HEADER.' Contrôle des manipulations triviales d\'une collection');

      $this
        ->given(
          description: 'Contrôle des fonctions de base d\'une collection sur un tableau d\'instances de classe',
          tab: $tab
        )
        ->when(
          description: "J'intégre le tableau dans le gestionnaire de collection ou la clé utilisée est l'identifiant",
          callback: function(array $tab, ?Collection &$collection=null) {
            $collection = new Collection(
              $tab,
              /** Fonction d'identification de la valeur de tri */
              function(int $index, FakeClass $item): int { return $item->getId(); },
              /** Fonction de comparaison pour le tri */
              function(int $keyA, int $keyB): bool { return ($keyA<$keyB); }
            );
          }
        )
        ->andWhen(
          description: "Je déplace l'index 10 à la 2ème position",
          callback: function(Collection $collection) {
            $collection->insertLastToLeft(2,5);
          }
        )
        ->then(
          description: "Le 10ème index doit être à la 2ème position, les éléments 2 à 9 sont décalés vers la droite",
          callback: function(Collection $collection) {
            return (string)$collection;
          },
          result: $correctinsertLastToLeft
        )
        ->andWhen(
          description: "Je permute l'index 2 et l'index 5",
          callback: function(Collection $collection, ?bool &$result=null) {
            $collection->insertLastToLeft(5,4);
            $collection->insertLastToLeft(4,3);
            $collection->insertLastToLeft(3,2);
            $result = $collection->permute(2,5);
          }
        )
        ->andThen(
          description: "La permutation doit être effective",
          callback: function(Collection $collection, bool $result) {
            if(true === $result)
              return (string)$collection;
            return false;
          },
          result: $correctPermutation
        )
        ->andWhen(
          description: "J'applique un arrangement aléatoire uniforme",
          callback: function(Collection $collection) {
            $collection->permute(2,5);
            $collection->shuffle();
          }
        )
        ->andThen(
          description: "La répartition aléatoire doit être uniforme",
          callback: function(Collection $collection) {
            /** @var array $sort */
            $sort = $collection->getKeys();
            /** @var float $distance */
            $distance = 0;
            for($i=1;$i<count($sort);$i++)
              $distance+=($i-$sort[$i])*($i-$sort[$i]);
            /**
             * @author yroussel
             *
             * Pour ce test, on compare la distance cartésienne entre la valeur réelle et la valeur attendue sans tri avec la distance minimale
             * qui se matérialise par un décalage de chaque valeur de 1.
             *
             * La probabilité que ce test soit faux est de 1/(n-1)!
             */
            $distance/=count($sort);
            return ($distance > 1);
          },
          result: true
        )
        ->andThen(
          description: "La correspondance entre clé et l'objet associé doit être confirmé",
          callback: function(Collection $collection) {
            /** @var array $sort */
            $sort = $collection->getKeys();
            /** @var bool $check */
            $check = true;
            for($i=0;$i< $collection->count();$i++) {
              /** @var ?FakeClass $obj */
              $obj = $collection->get($i);
              $check = $check && ($sort[$i] === $obj->getId());
            }
            return $check;
          },
          result: true
        )
        ->andWhen(
          description: "Je fais un tri par fusion",
          callback: function(Collection $collection) {
            $collection->mergeSort();
          }
        )
        ->andThen(
          description: "Le tableau est bien ordonné",
          callback: function(Collection $collection) {
            return (string)$collection;
          },
          result: $correctOrdered
        )
      ;
    }
}
