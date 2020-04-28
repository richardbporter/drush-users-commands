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
            'include' => dirname(__DIR__, 3),
        ];

        $this->jsonOption = [
            'format' => 'json',
        ];

        $this->drush('cc', ['drush'], $this->siteOptions);
    }
}
