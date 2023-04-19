<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Tests\classes\FakeClass;
use FOPG\Component\UtilsBundle\Collection\Queue;
use FOPG\Component\UtilsBundle\Collection\Collection;

class QueueTest extends TestCase
{
    const SECTION_HEADER = '[Queue]';

    public function testCollectionBasis(): void {

      $max = 2000;
      $tab = [];
      for($i=1;$i<=$max;$i++)
        $tab[]=new FakeClass($i,"label $i");

      $this->section(self::SECTION_HEADER.' Contrôle des manipulations triviales d\'une file de priorité');

      $this
        ->given(
          description: 'Contrôle des fonctions de base d\'une file de priorité sur un tableau d\'instances de classe',
          tab: $tab,
          max: $max
        )
        ->when(
          description: "Je peuple une file de priorité",
          callback: function(array $tab, ?Queue &$queue=null) {
            $queue = new Queue(
              $tab,
              /** Fonction d'identification de la valeur de tri */
              function(?int $index, FakeClass $item): int { return $item->getId(); },
              /** Fonction de comparaison pour le tri */
              function(int $keyA, int $keyB): bool { return ($keyA>$keyB); }
            );
          }
        )
        ->then(
          description: "Je retrouve mes éléments dans l'ordre de priorité",
          callback: function(Queue $queue, int $max) {
            $check = true;
            $i = $max;
            while(null !== ($obj = $queue->get())) {
              $check = $check && ($obj->getId() === $i);
              $i--;
            }
            return $check;
          },
          result: true
        )
        ->andWhen(
          description: "J'ajoute des éléments de façon aléatoire dans la file de priorité",
          callback: function(Queue $queue, array $tab) {
            $collection = new Collection($tab);
            $collection->shuffle();
            foreach($collection as $index => $item) {
              $queue->add($item, $index);
            }
          }
        )
        ->andThen(
          description: "Je dois récupérer les éléments correctement trié par priorité",
          callback: function(Queue $queue, int $max) {
            $check = true;
            $i = $max;
            while(null !== ($obj = $queue->get())) {
              $check = $check && ($obj->getId() === $i);
              $i--;
            }
            return $check;
          },
          result: true
        )
      ;
    }
}
