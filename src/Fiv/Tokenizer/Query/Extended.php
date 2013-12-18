<?php
  /**
   * @author Ivan Shcherbak <dev@funivan.com>
   */

  namespace Fiv\Tokenizer\Query;

  use Fiv\Tokenizer\Block;
  use Fiv\Tokenizer\Query;
  use Fiv\Tokenizer\Token;

  /**
   * Extended query used for parsing complicated blocks
   * @author  Ivan Shcherbak <dev@funivan.com>
   * @package Fiv\Tokenizer\Query
   */
  class Extended extends Base {

    const STRICT = 0;

    const POSSIBLE = 1;

    const EXPECT = 3;

    const SEARCH = 4;

    const SECTION = 5;


    /**
     * @var Query[]
     */
    protected $queries = [];

    /**
     * @var array
     */
    protected $resultStartIndexes = [];

    /**
     * @var array
     */
    protected $resultEndIndexes = [];

    /**
     * @return Query
     */
    public function strict() {
      $query = new Query();
      $this->addQuery($query, self::STRICT);
      return $query;
    }

    /**
     * @return Query
     */
    public function possible() {
      $query = new Query();
      $this->addQuery($query, self::POSSIBLE);
      return $query;
    }

    /**
     * @return Query
     */
    public function expect() {
      $query = new Query();
      $this->addQuery($query, self::EXPECT);
      return $query;
    }

    /**
     * @return Query
     */
    public function search() {
      $query = new Query();
      $this->addQuery($query, self::SEARCH);
      return $query;
    }


    /**
     *
     * @return Block
     */
    public function getBlock() {

      if (!empty($this->cache)) {
        return $this->cache;
      }

      $this->parse();

      return $this->cache;
    }

    /**
     * @return array
     */
    public function getStartIndexes() {

      if (!empty($this->resultStartIndexes)) {
        return $this->resultStartIndexes;
      }

      $this->parse();

      return $this->resultStartIndexes;
    }

    /**
     * @return array
     */
    public function getEndIndexes() {
      if (!empty($this->resultEndIndexes)) {
        return $this->resultEndIndexes;
      }

      $this->parse();

      return $this->resultEndIndexes;
    }

    /**
     *
     * 1. Беремо індекс 0.
     * 2. Беремо умову 0.
     * 3. Перевіряємо чи перша умова підходить для індекса 0
     *  3.1 Якщо умова підходить повертаємо 0
     *  3.2 Якщо умова не підходить повертаємо false
     *
     * 4. Якщо наша умова STRICT
     *  4.1. Умова підійшла. Встановлюємо останню позицію хорошого блоку на даний індекс 0.
     *       Беремо токен 1 і беремо умову 1.
     *       Ідемо до кроку 1 перевіряємо дальші умови
     *  4.2. Умова не підійшла Беремо індекс 1 і умову 0.
     *       Ідемо до кроку 1 і перевіряємо все дальше
     *
     * 5. Якщо умова POSSIBLE
     *  5.1. Умова підійшла.
     *       Встановлюємо останню позицію хорошого блоку на даний індекс 0.
     *       Беремо токен 1 і беремо умову 1.
     *       Ідемо до кроку 1 перевіряємо дальші умови
     *  5.2. Умова не підійшла
     *       Беремо індекс 0 і беремо умову 1.
     *       Ідемо до кроку 1 і перевіряємо все дальше
     *
     * 6. Якщо умова EXPECT
     *  6.1. Умова підійшла.
     *
     *
     *
     *
     *
     * @throws \Fiv\Tokenizer\Exception
     * @return Block
     */
    protected function parse() {
      # validate conditions

      if (count($this->queries) < 1) {
        throw new \Fiv\Tokenizer\Exception('Invalid number of conditions. You need more than 1 condition or use delimiters');
      }

      # drop cache
      $this->cleanCache();

      $this->cache = new Block();

      $listOfQueries = $this->queries;

      # Колекція яка містить токени.
      # Кожен токен задовільняє першій умові
      foreach ($this->collection->getItems() as $index => $tokenRaw) {
        //echo "Start Iterate group from token :" . $index . "\n";
        # Умови спрацювали якщо ми оприділили де закінчується наш блок

        $firstTokenIndex = $index;
        $lastTokenIndex = null;

        foreach ($listOfQueries as $queryIndex => $rawQueryInfo) {

          $this->log('start scan from:' . ($queryIndex + $index));
          /** @var $token Token */
          $token = $this->collection[$queryIndex + $index];
          $this->log('|' . ($token) . '|');

          //echo "\n";
          //echo "Start check Token :" . ($queryIndex + $index) . " (" . $token . "). Query index: " . $queryIndex . "\n";

          # У нас є 2 або більше умов
          # І на останню умову немає токена.
          # Все цикл завершується.
          // @todo Перевірити вихід із цикла, якщо остання умова possible
          if (!is_object($token)) {
            $lastTokenIndex = false;
            break;
          }

          /** @var $query Query */
          $query = $rawQueryInfo[0];
          $type = $rawQueryInfo[1];

          # Перший токен ми знаємо де знаходиться. Перевіряємо його і всі наступні
          $isValid = $query->checkToken($token);

          $this->log("Validation:" . (string)$isValid);

          if ($type == static::STRICT) {
            if ($isValid) {
              $lastTokenIndex = $queryIndex + $index;
            } else {
              $lastTokenIndex = false;
              break;
            }
          } elseif ($type == static::POSSIBLE) {
            # Умова не строга

            if ($isValid) {
              # Токен підійшов ідемо перевіряти дальше
              $lastTokenIndex = $queryIndex + $index;
            } else {
              # Токен не підійшов перевіримо його на наступну умову
              $index--;
            }

          } elseif ($type == static::EXPECT or $type == static::SEARCH) {

            if (!$isValid) {

              $lastTokenIndex = $queryIndex + $index;

              $validTokenFind = false;
              # Go to next token and check it.
              # If condition break return last token.
              # And set Index to this token +1 For next condition
              foreach ($this->collection->getItems() as $indexForExpectCheck => $tokenForExpectCheck) {
                if ($indexForExpectCheck < $lastTokenIndex) {
                  continue;
                }
                $tokenForExpect = $this->collection[$queryIndex + $index];

                //echo "Check expect strict: " . $tokenForExpect . "\n";
                # Check token for expect condition
                $validTokenFind = $query->checkToken($tokenForExpect);
                //echo "Validation: " . (int)$validTokenFind . "\n";
                if (!$validTokenFind) {
                  $index++;
                } else {
                  $lastTokenIndex = $queryIndex + $index;
                  # go check next condition. Expect condition fail.
                  break;
                }
              }

              # remove last token if not valid
              //echo '$validTokenForExpect:' . (int)$validTokenFind . " \$index:$index\n";

              if (!$validTokenFind and $index == $this->collection->count()) {
                $lastTokenIndex = false;
                break;
              }

              //echo "Expect finished. Lat token index: " . $lastTokenIndex . " \n";

            } else {
              $lastTokenIndex = false;
              # Expect fail
              break;
            }

          } elseif ($type === static::SECTION) {

            $this->log('$isValid:' . $isValid);

            $startIndex = $queryIndex + $index + 1;

            if ($isValid) {
              $blockEndFlag = 1;
            } else {
              $blockEndFlag = null;
            }
            $this->log('$blockEndFlag:' . $blockEndFlag);

            $this->log('Start from index:' . $startIndex);
            /** @var $token Token */
            foreach ($this->collection->getItems() as $tokenIndex => $token) {
              if ($tokenIndex < $startIndex) {
                continue;
              }
              $this->log('$blockEndFlag:' . $blockEndFlag);
              $this->log('check token:' . $token);

              if ($query->checkToken($token)) {
                $blockEndFlag++;
              } elseif ($token->value() === $rawQueryInfo[2]) {
                $blockEndFlag--;
              }

              if ($blockEndFlag === 0) {
                $lastTokenIndex = $tokenIndex;
                $index = $lastTokenIndex - $queryIndex;
                $this->log('block end:' . $lastTokenIndex);
                break;
              }
            }

            $this->log('$lastTokenIndex:' . __LINE__ . ':' . $lastTokenIndex);
          }

          if ($type == static::EXPECT) {
            # run step back on expect
            $index--;
            $lastTokenIndex--;
          }

        }

        if (is_int($firstTokenIndex) and is_int($lastTokenIndex)) {
          $this->log('$firstTokenIndex:' . $firstTokenIndex);
          $this->log('$lastTokenIndex:' . $lastTokenIndex);

          $blockCollection = $this->collection->extractItems($firstTokenIndex, $lastTokenIndex - $firstTokenIndex + 1);
          //echo "start:$firstTokenIndex" . "\nlast:$lastTokenIndex///" . $blockCollection . "\n";
          $this->resultStartIndexes[] = $firstTokenIndex;
          $this->resultEndIndexes[] = $lastTokenIndex;
          $this->cache->append($blockCollection);
        }
      }

    }

    /**
     *
     *
     * @param Query $query
     * @param int $type
     * @param array|string|int $options
     * @throws \Fiv\Tokenizer\Exception
     * @return Extended
     */
    protected function addQuery(Query $query, $type, $options = []) {

      if (!in_array($type, [static::STRICT, static::POSSIBLE, static::EXPECT, static::SEARCH, static::SECTION])) {
        throw new \Fiv\Tokenizer\Exception('Invalid condition type: ' . $type);
      }

      $this->cleanCache();

      $this->queries[] = [$query, $type, $options];

      return $this;
    }

    /**
     * @return Query[]
     */
    public function getQueries() {
      return $this->queries;
    }

    /**
     * Clean current cache
     *
     * @return $this
     */
    protected function cleanCache() {
      $this->cache = [];
      $this->resultStartIndexes = [];
      $this->resultStartIndexes = [];
      return parent::cleanCache();
    }

    /**
     *
     * <code>
     *  // find if with conditions and body
     *  $q->strict()->valueIs('if');
     *  $q->section('{', '}');
     * </code>
     *
     * @param string $startDelimiter
     * @param string $endDelimiter
     */
    public function section($startDelimiter, $endDelimiter) {
      $queryStart = new Query();
      $queryStart->valueIs($startDelimiter);
      $this->addQuery($queryStart, self::SECTION, $endDelimiter);
    }

    public function insertWhitespaceQueries() {
      $oldQueries = $this->queries;
      $this->queries = [];

      $i = 0;
      foreach ($oldQueries as $data) {
        list($query, $type, $options) = $data;
        $this->addQuery($query, $type, $options);
        $this->possible()->typeIs(T_WHITESPACE);
        $i += 2;
      }

      // unset last whitespace query
      unset($this->queries[($i - 1)]);

      return $this;
    }

    protected function log($msg) {
//      echo $msg . "\n";
    }

  }
