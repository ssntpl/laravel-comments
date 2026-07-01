<?php

namespace Ssntpl\LaravelComments\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Ssntpl\LaravelComments\LaravelCommentsServiceProvider;
use Ssntpl\LaravelComments\Tests\Models\TestUser;
use Ssntpl\LaravelFiles\LaravelFilesServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelFilesServiceProvider::class,
            LaravelCommentsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('comments.user_model', TestUser::class);
        // Off by default; individual tests enable it explicitly.
        $app['config']->set('comments.changelog', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
        // laravel-files `files` table, used by per-comment attachments.
        $this->loadMigrationsFrom(__DIR__ . '/../vendor/ssntpl/laravel-files/database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
