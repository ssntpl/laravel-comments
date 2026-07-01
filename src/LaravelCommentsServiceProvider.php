<?php

namespace Ssntpl\LaravelComments;

use Illuminate\Support\ServiceProvider;

class LaravelCommentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/comments.php', 'comments');
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/comments.php' => config_path('comments.php'),
        ], 'laravel-comments-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'laravel-comments-migrations');

        // Apps that own the `comments` schema themselves (e.g. otper) set
        // `comments.auto_load_migrations` to false to avoid table collisions.
        if (config('comments.auto_load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}