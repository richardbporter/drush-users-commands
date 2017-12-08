<?php

namespace DrushUsersCommands\Tests;

use DrushUsersCommands\Tests\TestBase;

class ListTestCase extends TestBase
{
    /**
     * Set up each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->drush('role:create', ['editor'], $this->siteOptions);
        $this->drush('user:create', ['foo'], $this->siteOptions);
        $this->drush('user:create', ['bar'], $this->siteOptions);
        $this->drush('user:block', ['bar'], $this->siteOptions);
        $this->drush('user:role:add', ['editor', 'foo'], $this->siteOptions);
    }

    /**
     * Test all users are returned.
     */
    public function testAllUsers()
    {
        $this->drush('users:list', [], $this->siteOptions);

        $output = $this->getOutput();
        $this->assertContains('foo', $output);
        $this->assertContains('bar', $output);
        $this->assertContains('admin', $output);
        $this->assertNotContains('anonymous', $output);
    }

    /**
     * Test role option.
     */
    public function testUsersReturnedByRole()
    {
        $this->drush(
            'users:list',
            [],
            $this->siteOptions + ['roles' => 'editor']
        );

        $output = $this->getOutput();
        $this->assertContains('foo', $output);
        $this->assertNotContains('bar', $output);
        $this->assertNotContains('admin', $output);
    }

    /**
     * Test status option.
     */
    public function testUsersReturnedByStatus()
    {
        $this->drush(
            'users:list',
            [],
            $this->siteOptions + ['status' => 'blocked']
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
            $this->siteOptions
        );

        $this->drush(
            'users:list',
            [],
            $this->siteOptions + ['last-login' => 'today']
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
        $this->drush('user:create', ['baz'], $this->siteOptions);
        $this->drush('user:block', ['baz'], $this->siteOptions);
        $this->drush('user:role:add', ['editor', 'baz'], $this->siteOptions);

        $this->drush(
            'users:list',
            [],
            $this->siteOptions + ['roles' => 'editor', 'status' => 'blocked']
        );

        $output = $this->getOutput();
        $this->assertNotContains('foo', $output);
        $this->assertNotContains('bar', $output);
        $this->assertNotContains('admin', $output);
        $this->assertContains('baz', $output);
    }

    /**
     * Test validation.
     */
    public function testValidation()
    {
        // Role 'garbage' does not exist.
        $result = $this->drush(
            'users:list',
            [],
            $this->siteOptions + ['roles' => 'garbage'],
            null,
            null,
            self::EXIT_ERROR
        );

        $this->assertEquals(1, $result);

        // Status 'garbage' does not exist;
        $result = $this->drush(
            'users:list',
            [],
            $this->siteOptions + ['status' => 'garbage'],
            null,
            null,
            self::EXIT_ERROR
        );

        $this->assertEquals(1, $result);
    }
}
