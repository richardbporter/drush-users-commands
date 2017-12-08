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
        $this->drush('users:list', [], $this->siteOptions + ['roles' => 'editor']);
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
        $this->drush('users:list', [], $this->siteOptions + ['status' => 'blocked']);
        $output = $this->getOutput();
        $this->assertNotContains('foo', $output);
        $this->assertContains('bar', $output);
        $this->assertNotContains('admin', $output);
    }

    /**
     * Test both options in combination.
     */
    public function testUsersReturnedByBoth()
    {
        $this->drush('user:create', ['baz'], $this->siteOptions);
        $this->drush('user:block', ['baz'], $this->siteOptions);
        $this->drush('user:role:add', ['editor', 'baz'], $this->siteOptions);
        $this->drush('users:list', [], $this->siteOptions + ['roles' => 'editor', 'status' => 'blocked']);
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
        $result = $this->drush('users:list', [], $this->siteOptions + ['roles' => 2], null, null, self::EXIT_ERROR);
        $this->assertEquals(1, $result);

        $result = $this->drush('users:list', [], $this->siteOptions + ['roles' => null], null, null, self::EXIT_ERROR);
        $this->assertEquals(1, $result);

        $result = $this->drush('users:list', [], $this->siteOptions + ['status' => null], null, null, self::EXIT_ERROR);
        $this->assertEquals(1, $result);

        $result = $this->drush('users:list', [], $this->siteOptions + ['status' => 'foo'], null, null, self::EXIT_ERROR);
        $this->assertEquals(1, $result);
    }
}
