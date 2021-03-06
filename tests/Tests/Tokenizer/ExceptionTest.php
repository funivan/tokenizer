<?php

  namespace Tests\Tokenizer;

  use Fiv\Tokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class ExceptionTest extends \Tests\Main {

    public function testSetTokenValueException() {
      $token = new Token();

      try {

        $token->setValue((object)array());

      } catch (\Exception $e) {
        $this->assertInstanceOf('\Fiv\Tokenizer\Exception', $e);
        return true;
      }

      $this->fail('Set invalid token value. Expect exception.');
    }

  }
