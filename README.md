Tokenizer
=====

[![Build Status](https://travis-ci.org/funivan/tokenizer.png?branch=master)](https://travis-ci.org/funivan/tokenizer)
[![Coverage Status](https://coveralls.io/repos/funivan/tokenizer/badge.png)](https://coveralls.io/r/funivan/tokenizer)

Flexible library for parsing and modify php code;


##Install:
`composer require fiv/tokenizer:dev-master`

##Use
For example lets find all if and elseif conditions:
```php
  $fileTokens = new Fiv\Tokenizer\File(__FILE__);
  $query = $fileTokens->getCollection()->extendedQuery();
  $query->strict()->valueIs(array('if', 'elseif'));
  $query->section('(', ')');

  $conditions = $query->getBlock();
  foreach ($conditions as $condition) {
    echo $condition . "\n";
  }

```
