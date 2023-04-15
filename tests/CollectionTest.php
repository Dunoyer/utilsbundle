<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Tests\classes\FakeClass;
use FOPG\Component\UtilsBundle\Collection\Collection;


class CollectionTest extends TestCase
{
    const SECTION_HEADER = '[Collection]';

    public function testCollectionBasis(): void {

      $max = 10000;
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
          tab: $tab,
          correctOrdered: $correctOrdered
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
              if(($i-$sort[$i])*($i-$sort[$i])> 1)
                $distance+=2;
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
          callback: function(Collection $collection, ?float &$timeOnMergeSort=null) {
            $time = microtime(true);
            $collection->mergeSort();
            $timeOnMergeSort = microtime(true)-$time;
          }
        )
        ->andThen(
          description: "Le tableau est bien ordonné",
          callback: function(Collection $collection) {
            return (string)$collection;
          },
          result: $correctOrdered
        )
        ->andWhen(
          description: "Je souhaite effectuer un tri par tas",
          callback: function(Collection $collection, ?float &$timeOnHeapSort=null) {
            $collection->shuffle();
            $time = microtime(true);
            $collection->heapSort();
            $timeOnHeapSort = microtime(true)-$time;
          }
        )
        ->andThen(
          description: "Le tri doit être bon",
          callback: function(Collection $collection) {
            return (string)$collection;
          },
          result: $correctOrdered
        )
        ->andThen(
          description: "Le tri par fusion est plus rapide que le tri par tas d'un multiple de 2",
          callback: function(float $timeOnMergeSort, float $timeOnHeapSort) {
            $check = (0.5*$timeOnMergeSort < $timeOnHeapSort) && (4*$timeOnMergeSort > $timeOnHeapSort);
            return $check;
          },
          result: true
        )
      ;

      $tab=[];
      for($i=0;$i>100;$i++)
        $tab[$i]=$i;
      $correctOrdered = "<".implode(",",$tab).">";
      $this
        ->given(
          description: "Contrôle du tri par insertion",
          tab: $tab
        )
        ->when(
          description: "Je souhaite effectuer un tri par insertion",
          callback: function(array $tab, ?Collection &$collection=null) {
            $collection = new Collection($tab);
            $collection->shuffle();
            $collection->insertionSort();
          }
        )
        ->then(
          description: "Le tableau est bien ordonné",
          callback: function(Collection $collection) {
            return (string)$collection;
          },
          result: $correctOrdered
        )
      ;

      $tab = ["a" => 1, "b" => 2, 3 => "a", 4 => "b"];

      $this
        ->given(
          description: "Contrôle des ajouts/suppression de la collection",
          tab: $tab
        )
        ->when(
          description: "Je souhaite ajouter de nouvelle valeur au tableau",
          callback: function(array $tab, ?Collection &$collection=null) {
            $collection = new Collection(
              $tab,
              function($index, $item) { return $index; },
              function($a, $b) {
                if(is_string($a) && is_string($b)) { return (strcmp($a, $b) > 0); }
                elseif(is_string($a)) { return false; }
                elseif(is_string($b)) { return true; }
                else { return ($a > $b); }
              }
            );
            $collection->add("z", 5);
            $collection->add(5, "z");
            $collection->shuffle();
            $collection->heapSort();
          }
        )
        ->then(
          description: "La collection est bien ordonnée",
          callback: function(Collection $collection) { return (string)$collection; },
          result: "<5,4,3,z,b,a>"
        )
        ->andWhen(
          description: "Je souhaite supprimer une valeur au tableau",
          callback: function(Collection $collection) { $collection->remove("z"); }
        )
        ->andThen(
          description: "La structure du tableau doit être conservée",
          callback: function(Collection $collection) { return (string)$collection; },
          result: "<5,4,3,b,a>"
        )
        ->andThen(
          description: "Il doit y'avoir conservation des relations entre index et valeurs",
          callback: function(Collection $collection) {
            return $collection->getValues();
          },
          result: ["z","b","a",2,1]
        )
        ->andWhen(
          description: "Je souhaite ajouter une valeur au tableau qui conserve le tri",
          callback: function(Collection $collection) {
            $collection->add("toto",15, true);
          }
        )
        ->andThen(
          description: "Le tableau a bien la donnée correctement triée dans le tableau",
          callback: function(Collection $collection) {
            return (string)$collection;
          },
          result: "<15,5,4,3,b,a>"
        )
      ;
    }
}
