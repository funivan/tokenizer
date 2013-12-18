<?php

  namespace Demo;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  class ConcatenationOptimize {

    const MAX_ITERATION = 1000;

    /**
     * @var \Fiv\Tokenizer\File|null
     */
    protected $file = null;

    function __construct(\Fiv\Tokenizer\File $file) {
      $this->file = $file;
      $this->optimize();
    }

    /**
     * @return bool
     */
    protected function optimize() {
      $file = $this->file;
      $collection = $file->getCollection();


      $iteration = static::MAX_ITERATION;
      do {

        $file->refresh();
        $iteration--;

        $optimizeStringsNum = $collection->query()->typeIs(T_ENCAPSED_AND_WHITESPACE)->getTokensNum();
        echo $optimizeStringsNum . "\n";
        if ($optimizeStringsNum) {
          # step 1
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);

          $block = $q->getBlock();
          foreach ($block->getItems() as $col) {
            $firstItem = $col->getFirst();
            $lastItem = $col->getLast();

            $lastItem->setValue(
              $firstItem->value()
              . $lastItem->value()
              . $firstItem->value()
              . '.'
              . $firstItem->value()
            );

            $firstItem->remove();
          }

          # step 2
          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_VARIABLE);

          $block = $q->getBlock();
          foreach ($block->getItems() as $col) {
            $first = $col->getFirst();
            $variable = $col->getLast();
            $variable->setValue(
              $variable->value() . "." . $first->value()
            );
            $first->remove();
          }

        }

      } while (!empty($optimizeStringsNum) and $iteration > 0);

      return true;
    }

  }