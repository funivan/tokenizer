<?php

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  namespace Fiv\Tokenizer\Query;

  use Fiv\Tokenizer;

  abstract class Base {

    /**
     * @var Tokenizer\Collection
     */
    protected $collection = null;

    /**
     * @var bool
     */
    protected $cache = null;


    public function __construct($collection = null) {
      $this->collection = $collection;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    protected abstract function parse();

    protected function cleanCache() {
      $this->cache = null;
      return $this;
    }

  }