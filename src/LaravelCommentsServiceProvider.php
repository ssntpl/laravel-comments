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
        //
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'laravel-comments-migrations');


        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}