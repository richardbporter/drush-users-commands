<?php

namespace UsersCommands\Tests;

use Unish\CommandUnishTestCase;

abstract class TestBase extends CommandUnishTestCase
{
    public $siteOptions = [];
    public $jsonOption = [];

    public function setUp()
    {
        $sites = $this->setUpDrupal(1, true);
        $site = key($sites);
        $root = $this->webroot();

        $this->siteOptions = [
          'root' => $root,
          'uri' => $site,
          'yes' => null,
          'include' => dirname(__DIR__),
        ];

        $this->jsonOption = [
          'format' => 'json',
        ];

        $this->drush('cr', [], $this->siteOptions);
    }
}
