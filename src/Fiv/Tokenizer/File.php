<?php

  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  namespace Fiv\Tokenizer;

  class File {

    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var Collection
     */
    protected $collection = null;

    /**
     * <code>
     *
     * </code>
     * @param sting $path
     * @return File
     */
    public static function open($path) {
      $fileTokens = new File($path);
      return $fileTokens;
    }

    /**
     * @param string $path
     */
    public function __construct($path) {
      $this->path = $path;
      $code = file_get_contents($path);
      $tokens = Helper::getTokensFromString($code);
      $this->collection = new Collection($tokens);
    }


    /**
     * @return Collection
     */
    public function getCollection() {
      return $this->collection;
    }


    /**
     * Save tokens to file
     *
     * @return bool
     */
    public function save() {
      $newCode = $this->collection->assemble();
      file_put_contents($this->path, $newCode);
      return true;
    }


    /**
     * Parse current tokens
     *
     * @return $this
     */
    public function refresh() {
      $newCode = $this->collection->assemble();
      $tokens = Helper::getTokensFromString($newCode);
      $this->collection->setItems($tokens);
      return $this;
    }

    /**
     * @return string
     */
    public function getPath() {
      return $this->path;
    }

  }




