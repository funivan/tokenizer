<?php


  require_once __DIR__ . '/vendor/autoload.php';

  $fileTokens = new Fiv\Tokenizer\File(__FILE__);
  $query = $fileTokens->getCollection()->extendedQuery();
  $query->strict()->valueIs(array('if', 'elseif'));
  $query->section('(', ')');

  $conditions = $query->getBlock();
  foreach ($conditions as $condition) {
    echo $condition . "\n";
  }

  if (true) {
    if (!empty($test) && isset($test)) {

    } elseif (is_array(array(isset($a)))) {

    }
  }



