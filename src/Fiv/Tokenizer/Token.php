<?php

  namespace Fiv\Tokenizer;

  /**
   * Class Token
   *
   * Value is 2 type variable. It can be string or null
   * When you set value is automatically cast to string
   *
   * @package Fiv\Tokenizer
   */
  class Token {

    const N = __CLASS__;

    protected $type = null;

    protected $value = null;

    protected $line = null;

    /**
     * @param array $data
     */
    public function __construct(array $data = []) {
      $this->setData($data);
    }

    /**
     * @return mixed
     */
    public function __toString() {
      return $this->value !== null ? (string)$this->value : '';
    }


    /**
     * @param array $data
     * @return $this
     * @throws \Fiv\Tokenizer\Exception
     */
    public function setData(array $data) {
      $this->type = isset($data[0]) ? $data[0] : null;
      if (isset($data[1])) {
        $this->setValue($data[1]);
      }
      $this->line = isset($data[2]) ? $data[2] : null;

      return $this;
    }

    /**
     * @return bool
     */
    public function valid() {
      return $this->value() !== null;
    }

    /**
     * @return array
     */
    public function getData() {
      return [$this->type(), $this->value(), $this->line()];
    }

    /**
     * @return null
     */
    public function type() {
      return $this->type;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setType($type) {
      $this->type = $type;
      return $this;
    }

    /**
     * @return string
     */
    public function getTypeName() {
      return token_name($this->type());
    }

    /**
     * @return mixed
     */
    public function value() {
      return $this->value;
    }


    /**
     * @param string|int $value
     * @throws \Fiv\Tokenizer\Exception
     * @return $this
     */
    public function setValue($value) {

      if (!is_string($value) and !is_numeric($value)) {
        throw new \Fiv\Tokenizer\Exception('You can set only string ');
      }

      $this->value = (string)$value;
      return $this;
    }

    /**
     * @return null|int
     */
    public function line() {
      return $this->line;
    }


    public function remove() {
      foreach ($this as $property => $value) {
        $this->$property = null;
      }
    }

  }