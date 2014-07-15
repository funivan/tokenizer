<?php

  require_once __DIR__ . '/../vendor/autoload.php';
  /*
    Lets detect next conditions:

    1. if (true)
    2. if (!empty($test) && isset($test))
    3. elseif (is_array(array(isset($a))))
  */

  $fileTokens = new Fiv\Tokenizer\File(__FILE__);
  $query = $fileTokens->getCollection()->extendedQuery();
  $query->strict()->valueIs(array('if', 'elseif'));
  $query->section('(', ')');

  $blocks = $query->getBlock();
  foreach ($blocks as $code) {
    echo $code . "\n";
  }

  if (true) {
    if (!empty($test) && isset($test)) {

    } elseif (is_array(array(isset($a)))) {

    }
  }



