#!/usr/bin/env php
<?php
/**
 * @file
 * Copy global command file to SUT so it is found.
 */

require __DIR__ . '/../vendor/drush/drush/includes/filesystem.inc';
require __DIR__ . '/../vendor/drush/drush/tests/unish.inc';

list($unish_tmp, $unish_sandbox, $unish_drush_dir) = unishGetPaths();

$project_root = dirname(__DIR__);
$sut_dir = $unish_tmp . DIRECTORY_SEPARATOR . 'drush-sut';

if (file_exists("{$sut_dir}/drush/Commands")) {
    $cmd = "rm -rf {$sut_dir}/drush/Commands";
    passthru($cmd);
}

$cmd = "mkdir {$sut_dir}/drush/Commands";
passthru($cmd);

$cmd = "cp -PR {$project_root} {$sut_dir}/drush/Commands/UsersCommands";
passthru($cmd);
