<?php

  namespace FivTests\Tokenizer;

  use Fiv\Tokenizer\Collection;
  use Fiv\Tokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class CollectionTest extends \FivTests\Main {

    public function testCreateFromString() {
      $collection = Collection::createFromString('<?php echo 123;');

      $this->assertInstanceOf(Collection::N, $collection);
    }

    public function testGetNext() {
      $collection = Collection::createFromString('<?php echo 123;');

      $nextToken = $collection->getNext();
      $this->assertInstanceOf(Token::N, $nextToken);

      $nextToken = $collection->getNext(2);
      $this->assertInstanceOf(Token::N, $nextToken);

      $nextToken = $collection->getNext(100);
      $this->assertInstanceOf(Token::N, $nextToken);

      $this->assertEquals(null, $nextToken->value());
    }

    public function testGetPrevious() {
      $collection = Collection::createFromString('<?php echo 123;');

      $previousToken = $collection->getPrevious();
      $this->assertInstanceOf(Token::N, $previousToken);

      $previousToken = $collection->getPrevious(2);
      $this->assertInstanceOf(Token::N, $previousToken);

      $previousToken = $collection->getNext(100);
      $this->assertInstanceOf(Token::N, $previousToken);

      $this->assertEquals(null, $previousToken->value());
    }

    public function testAssemble() {
      $code = '<?php echo 123;';
      $collection = Collection::createFromString($code);

      $this->assertEquals($code, (string)$collection);
    }

    public function testSetToken() {
      $collection = Collection::createFromString('<?php echo 123;');

      $collection[0] = new Token();


      try {
        $collection[10] = null;
        $this->fail('Invalid token set. Expect exception.');
      } catch (\Exception $e) {
        $this->assertInstanceOf('\Fiv\Tokenizer\Exception', $e);
      }

      $itemsNum = $collection->count();
      $collection[] = new Token();
      $this->assertCount($itemsNum + 1, $collection);

    }

  }
