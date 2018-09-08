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
        ];

        $this->jsonOption = [
          'format' => 'json',
        ];

        $this->drush('cr', [], $this->siteOptions);
    }
}
