<?php

  namespace Fiv\Tokenizer;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/26/13
   */
  class Helper {

    /**
     * @param $string
     * @return array
     */
    public static function getTokensFromString($string) {
      $rawTokens = @token_get_all($string);
      $tokens = [];

      foreach ($rawTokens as $index => $token) {

        if (!is_array($token)) {
          $previousIndex = $index - 1;
          $rawTokens[$index] = array(
            null,
            $token
          );
          $rawTokens[$index][2] = $rawTokens[$previousIndex][2];
        }

        $tokens[$index] = $rawTokens[$index];
      }
      return $tokens;
    }
  }