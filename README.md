Tokenizer
=====

[![Build Status](https://travis-ci.org/funivan/tokenizer.png?branch=master)](https://travis-ci.org/funivan/tokenizer)

Flexible library for parsing and modify php code;


##Install:
`composer require fiv/tokenizer:dev-master`

##Use
For example lets find all if and elseif conditions:
```
  $fileTokens = new Fiv\Tokenizer\File(__FILE__);
  $query = $fileTokens->getCollection()->extendedQuery();
  $query->strict()->valueIs(array('if', 'elseif'));
  $query->section('(', ')');

  $conditions = $query->getBlock();
  foreach ($conditions as $condition) {
    echo $condition . "\n";
  }

```
