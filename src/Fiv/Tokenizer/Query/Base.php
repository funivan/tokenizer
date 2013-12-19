<?php

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  namespace Fiv\Tokenizer\Query;

  use Fiv\Tokenizer;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  abstract class Base {

    /**
     * @var Tokenizer\Collection
     */
    protected $collection = null;

    /**
     * @var bool
     */
    protected $cache = null;

    /**
     * @param Tokenizer\Collection $collection
     */
    public function __construct(Tokenizer\Collection $collection = null) {
      $this->collection = $collection;
    }

    /**
     * @codeCoverageIgnore
     * @return bool
     */
    protected abstract function parse();

    /**
     * Clean cache
     *
     * @return $this
     */
    public function cleanCache() {
      $this->cache = null;
      return $this;
    }

  }