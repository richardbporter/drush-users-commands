<?php

/**
 * @file
 * Test bootstrap.
 */

use Symfony\Component\Filesystem\Filesystem;

require __DIR__ . '/../vendor/autoload.php';

$fs = new Filesystem();
$path = __DIR__ . '/../drush';
$fs->mkdir($path);
$fs->touch("$path/drush.yml");
$workspace = getenv('GITHUB_WORKSPACE');

$contents = <<<EOD
# Register the command in the SUT.
drush:
  commands:
    '\Drush\Commands\UsersCommands\UsersCommands': '$workspace/UsersCommands.php'
EOD;

file_put_contents("$path/drush.yml", $contents);
