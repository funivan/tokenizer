# Upgrade guide

## From 0.9.7
* Remove method `iterate`. For example

```php
  # old code
  foreach($collection->iterate() as $token){}

  # new code
  foreach($collection as $token){}

```
