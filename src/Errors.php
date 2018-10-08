<?php

/**
 * PHP version 7.2.x
 *
 * @category Api
 * @package  Crownlessking/utui
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  LICENSE <email>
 * @link     <domain>
 */

namespace Service;

/**
 * Jsonapi errors
 *
 * @category Api
 * @package  Crownlessking/utui
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  LICENSE <email>
 * @link     <domain>
 */
class Errors
{
  private static $_errors = [
    'bad directory' => [
      'title' => 'Bad Directory',
      'detail' => 'Directory does not exist or it is forbidden!',
      'status' => '404'
    ],
    'missing files' => [
      'title' => 'Missing Files',
      'detail' => 'no files selected',
      'status' => '422'
    ]
  ];

  /**
   * Getter function for array or errors
   *
   * @param string $title title
   * @param string $def   custom error keys definition
   *
   * @return array
   */
  public static function get($title, $def = [])
  {
    if (isset(self::$_errors[$title])) {
      return $def + self::$_errors[$title];
    }
    return $def + [
      'title' => 'Bad error',
      'detail' => 'The error that you\'ve referenced does not exist.',
      'status' => '500'
    ];
  }

}
