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

    public function testExpect() {
      $file = $this->initFileWithCode("<?php
        echo 1+3-5;
        echo 2+4-6;
        echo 1+4;
      ");

      $collection = $file->getCollection();

      $eq = $collection->extendedQuery();
      $eq->strict()->valueIs('echo');
      $eq->expect()->valueIs('-');

      $this->assertCount(2, $eq->getBlock());
    }

    public function testSearch() {
      $file = $this->initFileWithCode('<?php
         echo 1+5*9; echo 2+4
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->search()->valueIs(';');

      $this->assertCount(1, $q->getBlock());
      $code = (string)$q->getBlock()->getFirst();
      $this->assertEquals('echo 1+5*9;', $code);

    }

    public function testExpectResult() {
      $file = $this->initFileWithCode('<?php
         echo 1+5*9; echo 2+4
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->expect()->valueIs(';');

      $this->assertCount(1, $q->getBlock());
      $code = (string)$q->getBlock()->getFirst();
      $this->assertEquals('echo 1+5*9', $code);
    }

    public function testExpectFail() {
      $file = $this->initFileWithCode('<?php
         echo 1+5*9; echo 2+4
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->expect()->typeIs(T_WHITESPACE);

      $this->assertCount(0, $q->getBlock());
    }

    public function testSectionFail() {
      $file = $this->initFileWithCode('<?php
         function(){}
         function (){}
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('function');
      $q->section('(', ')');

      $this->assertCount(2, $q->getBlock());
    }

    public function testMoreQueriesThanTokens() {
      $file = $this->initFileWithCode('<?php
         echo
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueLike('!.+!');
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->typeIs(T_ECHO);
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->valueLike('!.+!');

      $this->assertCount(0, $q->getBlock());

      $file = $this->initFileWithCode('<?php
         echo 1;
         echo');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->typeIs(T_ECHO);
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->valueLike('!.+!');

      $this->assertCount(1, $q->getBlock());

    }

  }
