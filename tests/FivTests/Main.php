<?php

  namespace FivTests;

  use Fiv\Tokenizer\File;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class Main extends \PHPUnit_Framework_TestCase {

    protected function getDemoDataDir() {
      return __DIR__ . '/../demo-data';
    }

    /**
     * @param $string
     * @return string
     */
    protected function createFileWithCode($string) {
      $path = tempnam('/tmp', 'testFileOther');
      file_put_contents($path, $string);

      return $path;
    }

    /**
     * @param $string
     * @return File
     */
    protected function initFileWithCode($string) {
      $tempFilePath = $this->createFileWithCode($string);
      return new File($tempFilePath);
    }

  }