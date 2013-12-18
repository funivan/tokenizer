<?php

  namespace FivTests\Tokenizer;

  use Demo\ConcatenationOptimize;
  use Fiv\Tokenizer\Token;

  class FileTest extends \FivTests\Main {

    public function testModifyStringsConcatenation() {
      $filePath = $this->getDemoDataDir() . '/strings.php';

      $file = new \Fiv\Tokenizer\File($filePath);
      new ConcatenationOptimize($file);
      $code = (string)$file->getCollection();

      $newStrings = [
        '"`".$f."`";',
        '$a."";',
        '"   test".$test." + ".$a."\"";',
      ];
      foreach ($newStrings as $string) {
        $this->assertContains($string, $code);
      }

    }

    public function testOpen() {
      $file = new \Fiv\Tokenizer\File($this->getDemoDataDir() . '/demo.php');
      $this->assertCount(7, $file->getCollection());
    }

    public function testFilePath() {
      $file = \Fiv\Tokenizer\File::open($this->getDemoDataDir() . '/demo.php');
      $this->assertContains('demo-data/demo.php', $file->getPath());
    }


    public function testSave() {
      $data = '<?php echo 1;';

      # create temp file
      $tempFile = $this->createFileWithCode($data);

      $file = new \Fiv\Tokenizer\File($tempFile);
      $q = $file->getCollection()->query();
      $q->valueIs(1);

      foreach ($q->getTokens()->getItems() as $token) {
        $token->setValue(2);
      }

      $file->save();

      $file = new \Fiv\Tokenizer\File($tempFile);
      $q = $file->getCollection()->query();
      $q->valueIs(2);

      $this->assertCount(1, $q->getTokens());

      unlink($tempFile);
    }


    public function testRefresh() {
      $file = new \Fiv\Tokenizer\File($this->getDemoDataDir() . '/demo.php');
      $q = $file->getCollection()->query();

      $this->assertCount(7, $q->getTokens());

      $q->valueIs('echo');

      $tokens = $q->getTokens();

      $this->assertCount(1, $tokens);

      $tokens->map(function (Token $item) {
        $item->remove();
      });

      $this->assertCount(7, $file->getCollection());

      $file->refresh();

      $this->assertCount(5, $file->getCollection());

    }

    public function testHtml() {
      # create temp file
      $code = '<html><?= 1 ?></html>';

      $tempFile = $this->createFileWithCode($code);
      $file = new \Fiv\Tokenizer\File($tempFile);

      $this->assertCount(7, $file->getCollection());
    }

  }
