<?php

  namespace Fiv\Tokenizer\Query;

  use Fiv\Tokenizer\Block;
  use Fiv\Tokenizer\Exception;
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
     * @var array
     */
    protected $moves = [];

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
     * Expect - exclude token from result. Main difference from search condition
     *
     * <code>
     *  # perform simple search on this code
     *  #'<?php echo 1+5*9; echo 2+4'
     *
     *  $collection->strict()->valueIs('echo');
     *  $collection->search()->valueIs(';');
     *
     *  # result is WITHOUT semicolon
     *  # echo 1+5*9
     *
     * </code>
     *
     * @return Query
     */
    public function expect() {
      $query = new Query();
      $this->addQuery($query, self::EXPECT);
      return $query;
    }

    /**
     * Search condition include token, Expect - exclude.
     *
     * <code>
     *  # perform simple search on this code
     *  #'<?php echo 1+5*9; echo 2+4'
     *
     *  $collection->strict()->valueIs('echo');
     *  $collection->search()->valueIs(';');
     *
     *  # result WITH semicolon
     *  # echo 1+5*9;
     *
     * </code>
     *
     * @return Query
     */
    public function search() {
      $query = new Query();
      $this->addQuery($query, self::SEARCH);
      return $query;
    }

    /**
     * Moves is very important in code match
     *
     * <code>
     * # in:
     * # $user = $this->getUser();
     * $q->strict()->valueIs('$user');
     * $q->search()->valueIs(';');
     * # at this stage we have full string
     * $q->move(-3)
     *
     * # now we cut 3 last tokens
     * # out:
     * # $user = $this->getUser
     * </code>
     * @param int $index
     * @throws \Fiv\Tokenizer\Exception
     */
    public function move($index) {
      $queriesNum = count($this->queries);
      if ($queriesNum === 0) {
        throw new Exception('Add query and then you can perform move operation');
      }
      $this->cleanCache();
      $this->moves[($queriesNum - 1)] = $index;
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
     * Take token 0.
     * Take query 0.
     * Check token  is valid to query:
     *
     * Strict
     *   Token invalid - Take next token
     *   Token valid   - Take next token and Next query
     *
     * Possible
     *   Token invalid - Take next query and same token
     *   Token valid   - Take next token and Next query
     *
     * Expect
     *   Token valid   - Take next query and previous token
     *   Token invalid - Take next token and same query (if last token All queries failed)
     *
     * Search
     *   Token valid   - Take next query and next token
     *   Token invalid - Take next token and same query (if last token All queries failed)
     *
     * Difference between Expect and Search is following:
     * On expect wt
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
      foreach ($this->collection as $index => $tokenRaw) {
        //echo "Start Iterate group from token :" . $index . "\n";
        # Умови спрацювали якщо ми оприділили де закінчується наш блок

        $firstTokenIndex = $index;
        $lastTokenIndex = null;

        foreach ($listOfQueries as $queryIndex => $rawQueryInfo) {

          /** @var $token Token */
          $token = $this->collection[$queryIndex + $index];

          //echo "\n";
          //echo "Start check Token :" . ($queryIndex + $index) . " (" . $token . "). Query index: " . $queryIndex . "\n";

          # У нас є 2 або більше умов
          # І на останню умову немає токена.
          # Все цикл завершується.
          if (!is_object($token)) {
            $lastTokenIndex = false;
            break;
          }

          /** @var $query Query */
          $query = $rawQueryInfo[0];
          $type = $rawQueryInfo[1];

          # Перший токен ми знаємо де знаходиться. Перевіряємо його і всі наступні
          $isValid = $query->checkToken($token);

          if ($type == static::STRICT) {
            if ($isValid) {
              $lastTokenIndex = $queryIndex + $index;
              $lastTokenIndex = $this->performMove($lastTokenIndex, $queryIndex);
            } else {
              $lastTokenIndex = false;
              break;
            }
          } elseif ($type == static::POSSIBLE) {
            # Умова не строга

            if ($isValid) {
              # Токен підійшов ідемо перевіряти дальше
              $lastTokenIndex = $queryIndex + $index;
              $lastTokenIndex = $this->performMove($lastTokenIndex, $queryIndex);
            } else {
              # Токен не підійшов перевіримо його на наступну умову
              $index--;
            }

          } elseif ($type == static::EXPECT or $type == static::SEARCH) {

            if (!$isValid) {

              $lastTokenIndex = $queryIndex + $index;

              # Go to next token and check it.
              # If condition break return last token.
              # And set Index to this token +1 For next condition
              $tokenLastIndexInCollection = $this->collection->count() - 1;
              foreach ($this->collection as $indexForExpectCheck => $tokenForExpectCheck) {
                if ($indexForExpectCheck < $lastTokenIndex) {
                  continue;
                }

                $currentTokenIndex = $queryIndex + $index;
                $tokenForExpect = $this->collection[$currentTokenIndex];

                # Check token for expect condition
                $validTokenFind = $query->checkToken($tokenForExpect);

                if (!$validTokenFind and $currentTokenIndex == $tokenLastIndexInCollection) {
                  # invalid last token
                  $lastTokenIndex = false;
                  break;
                } elseif (!$validTokenFind) {
                  $index++;
                } else {
                  # go check next condition. Expect condition end.
                  $lastTokenIndex = $queryIndex + $index;
                  $lastTokenIndex = $this->performMove($lastTokenIndex, $queryIndex);
                  break;
                }
              }

            } else {
              $lastTokenIndex = false;
              # Expect fail
              break;
            }

          } elseif ($type === static::SECTION) {

            $startIndex = $queryIndex + $index + 1;

            if ($isValid) {
              $blockEndFlag = 1;
            } else {
              $blockEndFlag = null;
            }

            /** @var $token Token */
            foreach ($this->collection as $tokenIndex => $token) {
              if ($tokenIndex < $startIndex) {
                continue;
              }

              if ($query->checkToken($token)) {
                $blockEndFlag++;
              } elseif ($token->value() === $rawQueryInfo[2]) {
                $blockEndFlag--;
              }

              if ($blockEndFlag === 0) {
                $lastTokenIndex = $tokenIndex;
                $index = $lastTokenIndex - $queryIndex;
                $lastTokenIndex = $this->performMove($lastTokenIndex, $queryIndex);
                break;
              }
            }

          }

          if ($type == static::EXPECT) {
            # run step back on expect
            $index--;
            $lastTokenIndex--;
          }

        }

        if (is_int($firstTokenIndex) and is_int($lastTokenIndex)) {
          # All queries works fine. Add new collection to cache
          $blockCollection = $this->collection->extractItems($firstTokenIndex, $lastTokenIndex - $firstTokenIndex + 1);
          $this->resultStartIndexes[] = $firstTokenIndex;
          $this->resultEndIndexes[] = $lastTokenIndex;
          $this->cache->append($blockCollection);
        }
      }

    }

    /**
     *
     * @param Query $query
     * @param int $type
     * @param array $options
     * @throws \Fiv\Tokenizer\Exception
     * @return Extended
     */
    protected function addQuery(Query $query, $type, $options = []) {

      $this->cleanCache();

      $this->queries[] = [$query, $type, $options];

      return $this;
    }

    /**
     * @param int $lastTokenIndex
     * @param int $queryIndex
     * @return int
     */
    public function performMove($lastTokenIndex, $queryIndex) {
      if (isset($this->moves[$queryIndex])) {
        $lastTokenIndex += $this->moves[$queryIndex];
      }
      return $lastTokenIndex;
    }

    /**
     * Return all queries assigned to this class
     *
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
    public function cleanCache() {
      $this->cache = [];
      $this->resultStartIndexes = [];
      $this->resultStartIndexes = [];
      return parent::cleanCache();
    }

    /**
     * With this query you can simply find function body, arguments, arrays etc
     *
     * <code>
     *  # find if with conditions and body
     *  $q->strict()->valueIs('if');
     *  $q->section('{', '}');
     * </code>
     *
     *
     * @param string $startDelimiter
     * @param string $endDelimiter
     */
    public function section($startDelimiter, $endDelimiter) {
      $queryStart = new Query();
      $queryStart->valueIs($startDelimiter);
      $this->addQuery($queryStart, self::SECTION, $endDelimiter);
    }

    /**
     * Insert whitespaces possible queries between strict queries
     * 1 query strict
     * 2 query strict
     * 3 query expect
     *
     * After insert list of queries will be:
     *
     * 1 query strict
     * 2 query possible whitespace
     * 3 query strict
     * 4 query possible whitespace
     * 5 query expect
     *
     *
     * @return $this
     */
    public function insertWhitespaceQueries() {
      $oldQueries = $this->queries;
      $this->queries = [];

      $oldMoves = $this->moves;
      $this->moves = [];

      $index = 0;
      foreach ($oldQueries as $queryIndex => $data) {
        list($query, $type, $options) = $data;
        $this->addQuery($query, $type, $options);
        if (isset($oldMoves[$queryIndex])) {
          $this->move($oldMoves[$queryIndex]);
        }
        $this->possible()->typeIs(T_WHITESPACE);
        $index += 2;
      }

      # unset last whitespace query
      unset($this->queries[($index - 1)]);

      return $this;
    }

  }
