<?php

  namespace FivTests\Tokenizer;

  use Fiv\Tokenizer\Collection;
  use Fiv\Tokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class BlockTest extends \FivTests\Main {

    /**
     * @return \Fiv\Tokenizer\File
     */
    protected function getBlockTestFile() {
      $code = <<<CODE
<?php
  function(){
    echo 1;
  }
  function(){
    echo 1;
    echo 2;
  }
  function(){
    echo 5;
    echo 1;
  }

CODE;

      $file = $this->initFileWithCode($code);
      return $file;
    }

    public function testBlockMapCollection() {

      $file = $this->getBlockTestFile();

      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->valueIs(')');
      $eq->section('{', '}');
      $block = $eq->getBlock();

      $this->assertCount(3, $block);

      $block->mapCollection(function (Token $token, $index, Collection $collection) {
        if ($token->value() == 1) {
          $token->setValue(2);
        }
      });

      $q = $file->getCollection()->query();
      $q->valueIs(2);

      $this->assertCount(4, $q->getTokens());

    }

    public function testMapCollectionInvalidFunction() {

      try {

        $file = $this->getBlockTestFile();

        $eq = $file->getCollection()->extendedQuery();
        $eq->strict()->valueIs(')');
        $eq->section('{', '}');
        $block = $eq->getBlock();

        $block->mapCollection('invalid_function');

      } catch (\Exception $e) {
        $this->assertInstanceOf('\Fiv\Tokenizer\Exception', $e);
        return true;
      }

      $this->fail('Invalid function for collection map via block. Expect exception.');

    }
  }
