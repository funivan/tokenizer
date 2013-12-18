<?php

  namespace Tests\Tokenizer\Query;

  use Fiv\Tokenizer\Collection;
  use Fiv\Tokenizer\Token;

  class ExtendedTest extends \Tests\Main {

    /**
     * @return \Fiv\Tokenizer\File
     */
    protected function getTestFile() {
      $code = <<<CODE
<?php "a"."b";
"c" . "d";
"1". "2";
"3" ."4";

CODE;

      $file = $this->initFileWithCode($code);
      return $file;
    }

    public function testInsertWhitespace() {

      $file = $this->getTestFile();

      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->typeIs(T_CONSTANT_ENCAPSED_STRING);
      $eq->strict()->valueIs('.');
      $eq->strict()->typeIs(T_CONSTANT_ENCAPSED_STRING);

      $this->assertCount(3, $eq->getQueries());

      $eq->insertWhitespaceQueries();

      $this->assertCount(5, $eq->getQueries());

      $block = $eq->getBlock();

      $this->assertCount(4, $block);

    }

    public function testParse() {

      $file = $this->getTestFile();

      $error = null;
      try {
        $eq = $file->getCollection()->extendedQuery();
        $eq->getBlock();
      } catch (\Exception $error) {

      }

      $this->assertInstanceOf('Exception', $error);

//      die();
//
//      echo "\n***" . __LINE__ . "***\n<pre>" . print_r($eq->getBlock(), true) . "</pre>\n";
//      die();
//
//      $this->assertCount(0, $eq->getBlock());

    }

    public function testIndexes() {

      $file = $this->getTestFile();
      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->valueIs('"4"');
      $eq->strict()->valueIs(';');

      $this->assertCount(1, $eq->getEndIndexes());
      $this->assertCount(1, $eq->getStartIndexes());

      # check cache
      $this->assertCount(1, $eq->getEndIndexes());
      $this->assertCount(1, $eq->getStartIndexes());
      $this->assertCount(1, $eq->getBlock());

      $eq = $file->getCollection()->extendedQuery();
      $eq->strict()->valueIs('"5"');
      $eq->strict()->valueIs(';');

      $this->assertCount(0, $eq->getEndIndexes());
      $this->assertCount(0, $eq->getEndIndexes());

      # check cache
      $this->assertCount(0, $eq->getStartIndexes());
      $this->assertCount(0, $eq->getStartIndexes());
      $this->assertCount(0, $eq->getBlock());

    }
  }
