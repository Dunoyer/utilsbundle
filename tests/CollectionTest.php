<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Tests\classes\FakeClass;
use FOPG\Component\UtilsBundle\Collection\Collection;


class CollectionTest extends TestCase
{
    const SECTION_HEADER = '[Collection]';

    public function testCollectionBasis(): void {

      $tab = [];
      $correctTab = [];
      for($i=1;$i<=100;$i++) {
        $tab[]=new FakeClass($i,"label $i");
        $correctTab[]=$i;
      }

      $correctTab[1] = 5;
      $correctTab[4] = 2;
      $correctPermutation= "<".implode(",",$correctTab).">";
      $this->section(self::SECTION_HEADER.' Contrôle des manipulations triviales d\'une collection');

      $this
        ->given(
          description: 'Contrôle des fonctions de base d\'une collection sur un tableau d\'instances de classe',
          tab: $tab
        )
        ->when(
          description: "J'intégre le tableau dans le gestionnaire de collection ou la clé utilisée est l'identifiant",
          callback: function(array $tab, ?Collection &$collection=null) {
            $collection = new Collection($tab, function($index, $item) { return $item->getId(); });
          }
        )
        ->andWhen(
          description: "Je permute l'index 2 et l'index 5",
          callback: function(Collection $collection, ?bool &$result=null) {
            $result = $collection->permute(2,5);
          }
        )
        ->then(
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
      ;
    }
}
