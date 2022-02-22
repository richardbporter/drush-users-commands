<?php

namespace UsersCommands\Tests;

use Drush\TestTraits\DrushTestTrait;
use PHPUnit\Framework\TestCase;

class ListTest extends TestCase
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
          'db-url' => getenv('TEST_DB_URL'),
        ]);

        $this->drush('role:create', ['editor']);
        $this->drush('user:create', ['foo']);
        $this->drush('user:create', ['bar']);
        $this->drush('user:block', ['bar']);
        $this->drush('user:role:add', ['editor', 'foo']);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->drush('sql:drop');
    }

  /**
     * Test all users are returned.
     */
    public function testAllUsers()
    {
        $this->drush('users:list', []);

        $output = $this->getOutput();
        $this->assertContains('foo', $output);
        $this->assertContains('bar', $output);
        $this->assertContains('admin', $output);
        $this->assertNotContains('anonymous', $output);
    }

    /**
     * Test role option.
     */
    public function testUsersReturnedByMultipleRoles()
    {
        $this->drush('role:create', ['publisher']);
        $this->drush('user:create', ['baz']);
        $this->drush('user:role:add', ['publisher', 'baz']);

        $this->drush(
            'users:list',
            [],
            ['roles' => 'editor,publisher']
        );

        $output = $this->getOutput();
        $this->assertContains('foo', $output);
        $this->assertContains('baz', $output);
        $this->assertNotContains('bar', $output);
        $this->assertNotContains('admin', $output);
    }

    /**
     * Test no-role option.
     */
    public function testUsersReturnedByMultipleNoRoles()
    {
        $this->drush('role:create', ['publisher']);
        $this->drush('user:create', ['baz']);
        $this->drush('user:role:add', ['publisher', 'baz']);

        $this->drush('role:create', ['owner']);
        $this->drush('user:create', ['qux']);
        $this->drush('user:role:add', ['owner', 'qux']);

        $this->drush('users:list', [], ['no-roles' => 'editor,publisher']);

        $output = $this->getOutput();
        $this->assertContains('qux', $output);
        $this->assertNotContains('foo', $output);
        $this->assertNotContains('baz', $output);
    }

    /**
     * Test status option.
     */
    public function testUsersReturnedByStatus()
    {
        $this->drush(
            'users:list',
            [],
            ['status' => 'blocked']
        );

        $output = $this->getOutput();
        $this->assertNotContains('foo', $output);
        $this->assertContains('bar', $output);
        $this->assertNotContains('admin', $output);
    }

    /**
     * Test last-login option.
     */
    public function testUsersReturnedByLogin()
    {
        // Update the login time for user 1. Drush user:login does not do this.
        $now = time();

        $this->drush(
            'sql:query',
            ["UPDATE users_field_data SET login={$now} WHERE uid=1;"],
        );

        $this->drush(
            'users:list',
            [],
            ['last-login' => 'today']
        );

        $output = $this->getOutput();
        $this->assertContains('admin', $output);
        $this->assertNotContains('foo', $output);
        $this->assertNotContains('bar', $output);
    }

    /**
     * Test status and role options in combination.
     */
    public function testUsersReturnedByStatusRole()
    {
        $this->drush('user:create', ['baz']);
        $this->drush('user:block', ['baz']);
        $this->drush('user:role:add', ['editor', 'baz']);

        $this->drush(
            'users:list',
            [],
            ['roles' => 'editor', 'status' => 'blocked']
        );

        $output = $this->getOutput();
        $this->assertNotContains('foo', $output);
        $this->assertNotContains('bar', $output);
        $this->assertNotContains('admin', $output);
        $this->assertContains('baz', $output);
    }

    /**
     * Test status, role and last-login options in combination.
     */
    public function testUsersReturnedByStatusRoleLogin()
    {
        // Update the login time for user 1. Drush user:login does not do this.
        $now = time();

        $this->drush(
            'sql:query',
            ["UPDATE users_field_data SET login={$now} WHERE uid=1;"],
        );

        // Create another administrator.
        $this->drush('user:create', ['baz']);
        $this->drush('role:create', ['administrator']);

        $this->drush(
            'user:role:add',
            ['administrator', 'baz'],
        );

        // Give the admin user the administrator role.
        $this->drush(
            'user:role:add',
            ['administrator', 'admin'],
        );

        $this->drush(
            'users:list',
            [],
            [
                'roles' => 'administrator',
                'status' => 'active',
                'last-login' => 'today',
            ]
        );

        $output = $this->getOutput();
        $this->assertNotContains('baz', $output);
        $this->assertNotContains('foo', $output);

        // If baz is not in the output then 'admin' has to match user name.
        $this->assertContains('admin', $output);
    }

    /**
     * Test validation.
     */
    public function testValidation()
    {
        // Role 'garbage' does not exist.
        $this->drush(
            'users:list',
            [],
            ['roles' => 'garbage'],
            null,
            null,
            1
        );

        $this->assertContains('Role garbage does not exist.', $this->getErrorOutput());

        // Status 'garbage' does not exist;
        $this->drush(
            'users:list',
            [],
            ['status' => 'garbage'],
            null,
            null,
            1
        );

        $this->assertContains('Unknown status garbage.', $this->getErrorOutput());
    }
}
