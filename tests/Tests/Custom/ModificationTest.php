<?php

  namespace Tests\Custom;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 12/18/13
   */
  class ModificationTest extends \Tests\Main {

    public function testModifyStringsConcatenation() {
      return true;
      $filePath = $this->getDemoDataDir() . '/strings.php';

      $file = new \Fiv\Tokenizer\File($filePath);
      new \Demo\ConcatenationOptimize($file);
      $code = (string)$file->getCollection();

      $newStrings = [
        '"`".$f."`";',
        '$a;',
        '"   test".$test." + ".$a."\"";',
        '"object ".$this->table."+other"',
        '$this->table."+new"',
      ];
      foreach ($newStrings as $string) {
        $this->assertContains($string, $code);
      }

    }

    public function testStringsInside() {

      $file = new \Fiv\Tokenizer\File($this->getDemoDataDir() . '/stringsInside.php');

      new \Demo\ConcatenationOptimize($file);

      $code = $file->getCollection()->assemble();
      $newStrings = [
        '"test \'".$_GET["d"]."\' new";',
      ];
      foreach ($newStrings as $string) {
        $this->assertContains($string, $code);
      }

    }
  }
