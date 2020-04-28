<?php

require __DIR__ . '/../vendor/autoload.php';

// Replace the SUT autoloader with ours.
$content = "<?php return include __DIR__ . '/../../../autoload.php';";
file_put_contents(__DIR__ . '/../vendor/drush/drush/sut/autoload.php', $content);
