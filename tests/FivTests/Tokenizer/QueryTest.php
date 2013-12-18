<?php

  namespace FivTests\Tokenizer;

  use FivTests\Main;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class QueryTest extends Main {

    public function testSimpleFind() {
      $file = new \Fiv\Tokenizer\File($this->getDemoDataDir().'/demo.php');
      $q = $file->getCollection()->query();

      $q->valueIs(1);

      $this->assertEquals(1, $q->getTokensNum());
    }

  }
