<?php

namespace UsersCommands\Tests;

use Drush\TestTraits\DrushTestTrait;
use PHPUnit\Framework\TestCase;

class ToggleTest extends TestCase
{
    use DrushTestTrait;

    /**
     * Set up each test.
     */
    public function setUp() :void
    {
        parent::setUp();

        $this->drush('site:install', ['testing'], [
          'root' => 'sut',
        ]);

        $this->drush('user:create', ['foo']);
        $this->drush('user:create', ['bar']);
        $this->drush('user:block', ['bar']);
    }

    protected function tearDown()
    {
      parent::tearDown();
      $this->drush('sql:drop');
    }

    /**
     * Test users are correctly blocked.
     */
    public function testUsersBlocked()
    {
        $this->drush('users:toggle', []);
        $this->drush('user:information', ['foo, bar'], ['format' => 'json']);

        $output = $this->getOutputFromJSON();

        foreach ($output as $user) {
            $this->assertEquals(0, $user['user_status']);
        }
    }

    /**
     * Test users are correctly unblocked.
     */
    public function testUsersUnblocked()
    {
        // First block, then unblock.
        $this->drush('users:toggle', []);
        $this->drush('users:toggle', []);
        $this->drush('user:information', ['foo, bar'], ['format' => 'json']);

        $output = $this->getOutputFromJSON();

        foreach ($output as $user) {
            if ($user['name'] == 'bar') {
                $this->assertEquals(0, $user['user_status']);
            } elseif ($user['name'] == 'foo') {
                $this->assertEquals(1, $user['user_status']);
            }
        }
    }
}
