<?php

/**
 * PHP Version 7.2
 *
 * This app handles request for the U.T.U.I web interface for phpunit.
 * It requires composer to function as it should. Therefore, it naturally
 * operates on projects that are managed by composer.
 * Mainly, vendor/autoload.php is required for tests that imports classes using
 * the 'use' keyword.
 * However, someone with the proper skill can create their own autoload at
 * vendor/autoload.php and the app will work just fine.
 *
 * To load test files and run a unit test, the app will need to path to the
 * targeted project root directory so it can find the vendor/autoload.php where
 * it should be. If the test files are contained in a directory other than 'tests'
 * within the project root, then a sub directory can be specified.
 *
 * $projectRoot - path of targeted project root directory.
 * $dir - specified test files sub directory within the project root.
 *
 * PHP needs enough permission to create files and directories. So, check the
 * app directory permissions if something goes wrong.
 *
 * @category API
 * @package  Crownlessking/phpunitserver
 * @author   Riviere King <riviere@crownlessking.com>
 * @license  NL <email>
 * @link     domain.com
 */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $page = (include 'src/IndexHtml.php');
  echo $page;
  die;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "vendor/autoload.php";

// Import classes HERE
use Service\UnitTest;
use Service\Errors;

// Constants
define('PHPUNIT', __DIR__ . '/phpunit');
define('CMD', 'php '. PHPUNIT);
define('BOOTSTRAP', 'tmp/bootstrap.php');
define('TEST_LIST_OF_FILES', 2);
define('TEST_DIRECTORY', 1);

// Project root directory
$projectRoot = filter_input(INPUT_POST, 'root');

// Directory path within the provided root directory
// This value will be appended to the project root directory
$dir = filter_input(INPUT_POST, 'dir');

// This value indicate whether a directory or a list of files test should
// be conducted.
$cmd = (int) filter_input(INPUT_POST, 'cmd');

// comma-separated list of filenames that should be bootstrapped.
// These files are the ones which were activated when clicking on
// the cogs icon.
$boot = filter_input(INPUT_POST, 'boot');

// initializing $testDir
if ($projectRoot && $dir) {
  $testDir = $projectRoot . $dir;
} else if ($dir) {
  $testDir = $dir;
} else if ($projectRoot) {
  $testDir = $projectRoot . 'tests/';
} else {
  die;
}

// Bootstrap files
$bootFiles = $boot ? explode(',', $boot) : [];

// Declare variables to store response data
$meta = [];
$data = [];
$errors = [];

// Setup response headers
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

/* ****************************************************************************
 * App logic below
 * ****************************************************************************/

$meta['directory'] = $testDir;
$meta['boot_files'] = $bootFiles;
$dirContent = [];
UnitTest::getDirContents($testDir, $dirContent, $errors);
$data['files'] = $dirContent;
$CMD = CMD;
$bootstrap = isset($projectRoot)
  ? '--bootstrap ' . UnitTest::getPhpUnitConf($projectRoot, $bootFiles)
  : '';
$meta['projectRoot'] = $projectRoot;
$meta['dir'] = $dir;
$meta['bootstrap'] = $bootstrap;

if ($cmd === TEST_DIRECTORY) {
  $data['output'] = `$CMD $bootstrap $testDir 2>&1`;
  $meta['command'] = "$CMD $bootstrap $testDir";
} else if ($cmd === TEST_LIST_OF_FILES) {
  $filesStr =  filter_input(INPUT_POST, 'files');
  if ($filesStr) {
    $files = explode(',', $filesStr);
    if (count($files) === 1) {
      $data['output'] = `$CMD $bootstrap {$files[0]} 2>&1`;
      $meta['command'] = "$CMD $bootstrap {$files[0]} 2>&1";
    } else if (count($files) > 1) {
      $filename = UnitTest::getListFilesConfFilename($files);
      $data['output'] = `$CMD $bootstrap --configuration $filename --testsuite list_of_files 2>&1`;
      $meta['command'] = "$CMD $bootstrap --configuration $filename --testsuite list_of_files 2>&1";
    }
  } else {
    $errors[] = Errors::get('missing files');
  }
}

$json = [
  'meta' => $meta
];

if (count($errors) > 0) {
  $json['errors'] = $errors;
} else {
  $json['data'] = $data;
}

echo json_encode($json);
