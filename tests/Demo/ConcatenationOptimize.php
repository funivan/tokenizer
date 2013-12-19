<?php

  namespace Demo;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  class ConcatenationOptimize {

    const MAX_ITERATION = 10;

    /**
     * @var \Fiv\Tokenizer\File|null
     */
    protected $file = null;

    /**
     * @param \Fiv\Tokenizer\File $file
     */
    public function __construct(\Fiv\Tokenizer\File $file) {
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

      $this->optimizeVariablesInCurlyBrackets($file);
      do {

        $file->refresh();
        $iteration--;

        $optimizeStringsNum = $collection->query()->typeIs(T_ENCAPSED_AND_WHITESPACE)->getTokensNum();

        # " ,
        # T_ENCAPSED_AND_WHITESPACE
        # T_VARIABLE
        # T_OBJECT_OPERATOR
        # T_STRING
        # T_ENCAPSED_AND_WHITESPACE

        if ($optimizeStringsNum) {

          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->valueIs('->');
          $q->strict()->typeIs(T_STRING);
          $q->strict()->valueIs(['"', "'"]);

          $block = $q->getBlock();
          foreach ($block->getItems() as $col) {
            if ($col->getFirst()->value() == $col->getLast()->value()) {
              $col->getFirst()->remove();
              $col->getLast()->remove();
            }
          }

          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->valueIs(['"', "'"]);

          $block = $q->getBlock();
          foreach ($block->getItems() as $col) {
            if ($col->getFirst()->value() == $col->getLast()->value()) {
              $col->getFirst()->remove();
              $col->getLast()->remove();
            }
          }

          # step 1 Object call
          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->valueIs('->');
          $q->strict()->typeIs(T_STRING);

          $block = $q->getBlock();
          foreach ($block->getItems() as $col) {
            $delimiter = $col->getFirst();

            $dot = new \Fiv\Tokenizer\Token();
            $dot->setValue('.');

            $col->addAfter(1, clone $delimiter);
            $col->addAfter(2, clone $dot);

            $col->append(clone $dot);
            $col->append(clone $delimiter);

            $col->getFirst()->setValue($col->assemble());
            foreach ($col->getItems() as $index => $token) {
              if ($index !== 0) {
                $token->remove();
              }
            }
          }

          # step 2 variable
          $file->refresh();
          $q = $collection->extendedQuery();

          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);

          $block = $q->getBlock();
          foreach ($block->getItems() as $col) {

            $delimiter = $col->getFirst();

            $dot = new \Fiv\Tokenizer\Token();
            $dot->setValue('.');

            $col->addAfter(1, clone $delimiter);
            $col->addAfter(2, clone $dot);

            $col->addAfter(4, clone $dot);
            $col->addAfter(5, clone $delimiter);

            $col->getFirst()->setValue($col->assemble());

            foreach ($col->getItems() as $index => $token) {

              if ($index !== 0) {
                $token->remove();
              }
            }
          }

        }

      } while (!empty($optimizeStringsNum) and $iteration > 0);

      return true;
    }


    /**
     * From:  "test '{$_GET["d"]}' new";
     * To  :  "test '".$_GET["d"]."' new";
     *
     * @param \Fiv\Tokenizer\File $file
     */
    protected function optimizeVariablesInCurlyBrackets(\Fiv\Tokenizer\File $file) {

      $file->refresh();

      $collection = $file->getCollection();

      $q = $collection->extendedQuery();

      $q->strict()->valueIs(['"', "'"]);
      $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);
      $q->strict()->valueIs('{');
      $q->search()->valueIs('}');

      $block = $q->getBlock();
      foreach ($block->getItems() as $col) {
        $open = $col->query()->valueIs('{')->getTokensNum();
        $close = $col->query()->valueIs('}')->getTokensNum();
        if ($open == $close) {
          $delimiter = $col->getFirst()->value();
          $col->rewind();
          $curly = $col->getNext(2);
          $curly->setValue($delimiter . '.');
          $col->getLast()->setValue('.' . $delimiter);
        }
      }

    }
  }