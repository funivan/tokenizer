<?php

  namespace Fiv\Tokenizer;

  /**
   * Represent array of collections
   *
   * @method \Fiv\Tokenizer\Collection getLast();
   * @method \Fiv\Tokenizer\Collection getFirst();
   * @method \Fiv\Tokenizer\Collection current();
   * @method \Fiv\Tokenizer\Collection[] getItems();
   * @method \Fiv\Tokenizer\Collection extractItems($offset, $length = null);
   *
   * @package Fiv\Tokenizer
   */
  class Block extends \Fiv\Spl\ObjectCollection {

    /**
     * Used for validation
     *
     * @return string
     */
    public function objectsClassName() {
      return Collection::N;
    }

    /**
     * For each token in collection apply callback
     *
     * <code>
     * //Remove fist token in all collections
     * $block->mapCollection(function($item, $index, $collection){
     *   if ( $index == 1 ) {
     *     $item->remove();
     *   }
     * })
     * </code>
     *
     * @param callback $callback
     * @return $this
     * @throws \Fiv\Tokenizer\Exception
     */
    public function mapCollection($callback) {

      if (!is_callable($callback)) {
        throw new \Fiv\Tokenizer\Exception('Invalid callback function');
      }

      foreach ($this as $collection) {
        $collection->map($callback);
      }

      return $this;
    }
  }

