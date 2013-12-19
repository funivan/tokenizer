<?php

  namespace Fiv\Tokenizer;

  /**
   * Represent access and manipulation array of tokens
   *
   * @method \Fiv\Tokenizer\Token getLast();
   * @method \Fiv\Tokenizer\Token getFirst();
   * @method \Fiv\Tokenizer\Token[] getItems();
   * @method \Fiv\Tokenizer\Token[] iterate();
   * @method \Fiv\Tokenizer\Collection extractItems($offset, $length = null);
   *
   * @package Fiv\Tokenizer
   */
  class Collection extends \Fiv\Spl\ObjectCollection {

    const N = __CLASS__;

    /**
     * Extract each value from token
     *
     * @return string
     */
    public function __toString() {
      return $this->assemble();
    }

    /**
     * Used for validation
     *
     * @return string
     */
    public function objectsClassName() {
      return Token::N;
    }

    /**
     * @return string
     */
    public function assemble() {
      $string = '';
      foreach ($this->iterate() as $token) {
        if (!$token->valid()) {
          continue;
        }
        $string .= $token->value();
      }

      return $string;
    }

    /**
     * Truncate current list of tokens and add new
     * You can pass array of raw tokens data OR
     * array of token objects
     *
     * @param array|Token[] $items
     * @throws Exception
     * @return $this
     */
    public function setItems($items) {
      $setItems = [];
      foreach ($items as $tokenData) {
        if ($tokenData instanceof Token) {
          $token = $tokenData;
        } elseif (is_array($tokenData)) {
          $token = new Token($tokenData);
        } else {
          throw new Exception('Invalid token data. Expect token or array');
        }
        $setItems[] = $token;
      }

      return parent::setItems($setItems);
    }


    /**
     * Add to the end or modify token with given key
     *
     * @param int|null $offset
     * @param mixed $item
     * @return $this|void
     * @throws \Fiv\Tokenizer\Exception
     */
    public function offsetSet($offset, $item) {
      if (!($item instanceof Token)) {
        throw new \Fiv\Tokenizer\Exception('You can set only Token object to this collection');
      }

      if (is_null($offset)) {
        $this->items[] = $item;
      } else {
        $this->items[$offset] = $item;
      }
    }

    /**
     * @param int $index
     * @param Token|Collection $item
     * @return $this
     * @throws Exception
     */
    public function addAfter($index, $item) {

      if ($item instanceof Token) {
        $insertTokens = array($item);
      } elseif ($item instanceof Collection) {
        $insertTokens = $item->getItems();
      } else {
        throw new \Fiv\Tokenizer\Exception('Invalid token. Must be array of Tokens, Token or Collection');
      }

      return parent::addAfter($index, $insertTokens);
    }


    /**
     * Remove all invalid tokens in collection
     * Refresh index.
     *
     * @return $this
     */
    public function refresh() {
      foreach ($this->iterate() as $index => $token) {
        if (!$token->valid()) {
          unset($this->items[$index]);
        }
      }

      $this->rewind();
      return $this;
    }

    /**
     * @param int $step
     * @return Token|
     */
    public function getPrevious($step = 1) {
      $item = parent::getPrevious($step);
      if ($item === null) {
        $item = new Token();
      }
      return $item;
    }

    /**
     * @param int $step
     * @return Token
     */
    public function getNext($step = 1) {
      $item = parent::getNext($step);
      if ($item === null) {
        $item = new Token();
      }
      return $item;
    }

    /**
     * @return Query
     */
    public function query() {
      return new Query($this);
    }

    /**
     * @return Query\Extended
     */
    public function extendedQuery() {
      return new Query\Extended($this);
    }

    /**
     * @param string $source
     * @return Collection
     */
    public static function createFromString($source) {
      $tokens = Helper::getTokensFromString($source);
      return new Collection($tokens);
    }

    /**
     * @return string
     */
    public function getDumpString() {
      $string = "<pre>\n";
      foreach ($this->getItems() as $token) {
        $string .= '[' . $token->getTypeName() . ']' . "\n" . print_r($token->getData(), true) . "\n";
      }
      $string .= " </pre > ";
      return $string;
    }
  }

