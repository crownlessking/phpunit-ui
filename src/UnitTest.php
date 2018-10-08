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

use Service\Errors;

/**
 * Unit test
 *
 * @category Api
 * @package  Crownlessking/utui
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  LICENSE <email>
 * @link     <domain>
 */
class UnitTest
{

  /**
   * Maximum number of files which can be returned with every request.
   *
   * If the max is exceeded, an error should occur.
   */
  const MAX_TOTAL_FILES = 1000;

  const CONFIG_DIRECTORY = 'tmp';

  /**
   * Go f yourself.
   *
   * @param string $dir     directory
   * @param array  $results results
   * @param array  $errors  if an error occurs, it will be inserted in this array.
   *
   * @return array
   */
  public static function getDirContents($dir, array &$results, array &$errors = [])
  {
    if (!is_dir($dir)) {
        $errors[] = Errors::get(
            'bad directory',
            [
              'source' => [
                'parameter' => $dir
              ]
            ]
        );
      return;
    }
    self::_getdc($dir, $results, $errors);
    if (count($results) > UnitTest::MAX_TOTAL_FILES) {
      $errors[] = [
        'title' => 'Too Many Files',
        'detail' => 'Maximum number of files exceeded.',
        'source' => [
          'parameter' => $dir
        ]
      ];
    }
  }

  /**
   * Get directory content
   *
   * @param string $dir     directory
   * @param array  $results list of all files found
   * @param array  $errors  array of errors. It will be populated when an error
   *                        occurs
   *
   * @return void
   */
  private static function _getdc($dir, array &$results, array &$errors = [])
  {
    if (count($results) > UnitTest::MAX_TOTAL_FILES) {
      return;
    }
    $files = scandir($dir);
    foreach ($files as $key => $value) {
      $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
      if (!is_dir($path)) {
        $results[] = $path;
      } else if ($value != "." && $value != "..") {
        self::_getdc($path, $results, $errors);
        // $results[] = $path;
      }
    }

  }

  /**
   * Save the list of files as phpunit XML configuration file.
   * Then returns the configuration filename.
   *
   * @param array $files array of files
   *
   * @return string
   */
  public static function getListFilesConfFilename(array $files)
  {
    $dir = UnitTest::CONFIG_DIRECTORY;
    if (!file_exists($dir)) {
      mkdir($dir, 0755);
    }
    $xml = "<testsuites>\n"
              ."<testsuite name=\"list_of_files\">\n";
    foreach ($files as $file) {
      $xml .= "<file>$file</file>\n";
    }
    $xml .= "</testsuite>\n"
          ."</testsuites>\n";
    $filename = $dir . '/filesList.xml';
    file_put_contents($filename, $xml);

    return $filename;
  }

  /**
   * Get phpunit configuration
   *
   * @param string $path      directory of test files
   * @param array  $bootFiles array of filenames
   *
   * @return string
   */
  public static function getPhpUnitConf($path, $bootFiles = [])
  {
    $dir = UnitTest::CONFIG_DIRECTORY;
    if (!file_exists($dir)) {
      mkdir($dir, 0755);
    }
    if (!empty($bootFiles)) {
      $require = self::_formatBootFiles($bootFiles);
    } else {
      $require = "\$dir = '$path';\n\n"
                  ."require_once \$dir . 'vendor/autoload.php';";
    }
    $fileStr = <<<END
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

$require
END;
    $filename = $dir . '/bootstrap.php';
    file_put_contents($filename, $fileStr);

    return $filename;
  }

  /**
   * Format boot files to be included in and run in the phpunit
   * bootstrap file.
   *
   * @param array $bootFiles boot files
   *
   * @return string
   */
  private static function _formatBootFiles($bootFiles)
  {
    $includes = '';
    foreach ($bootFiles as $file) {
      $includes .= "require_once '" . $file . "';\n";
    }
    return $includes;
  }

}