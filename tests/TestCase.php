<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Unwahas\ErrorRedirect\ErrorRedirectServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [ErrorRedirectServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');
        $app['config']->set('database.connections.sqlite.foreign_key_constraints', true);
        $app['config']->set('error-redirect.api.key', 'test-api-key');
        $app['config']->set('error-redirect.report_exceptions', true);
    }
}
