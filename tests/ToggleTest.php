<?php

namespace DrushUsersCommands\Tests;

use DrushUsersCommands\Tests\TestBase;

class ToggleTestCase extends TestBase
{
    /**
     * Set up each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->drush('user:create', ['foo'], $this->siteOptions);
        $this->drush('user:create', ['bar'], $this->siteOptions);
        $this->drush('user:block', ['bar'], $this->siteOptions);
    }

    /**
     * Test users are correctly blocked.
     */
    public function testUsersBlocked()
    {
        $this->drush('users:toggle', [], $this->siteOptions);
        $this->drush('user:information', ['foo, bar'], $this->siteOptions + $this->jsonOption);

        $output = $this->getOutputFromJSON();

        foreach ($output as $user) {
            $this->assertEquals(0, $user->user_status);
        }
    }

    /**
     * Test users are correctly unblocked.
     */
    public function testUsersUnblocked()
    {
        // First block, then unblock.
        $this->drush('users:toggle', [], $this->siteOptions);
        $this->drush('users:toggle', [], $this->siteOptions);
        $this->drush('user:information', ['foo, bar'], $this->siteOptions + $this->jsonOption);

        $output = $this->getOutputFromJSON();

        foreach ($output as $user) {
            if ($user->name == 'bar') {
                $this->assertEquals(0, $user->user_status);
            } elseif ($user->name == 'foo') {
                $this->assertEquals(1, $user->user_status);
            }
        }
    }
}
