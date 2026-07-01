<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    | The model a comment's `user_id` belongs to. Defaults to the app's
    | configured auth user model.
    */
    'user_model' => env('COMMENTS_USER_MODEL') ?: (config('auth.providers.users.model') ?? 'App\\Models\\User'),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    | Point these at your own subclasses if you need extra behaviour. The
    | package resolves related models through these keys.
    */
    'models' => [
        'comment'   => \Ssntpl\LaravelComments\Models\Comment::class,
        'reaction'  => \Ssntpl\LaravelComments\Models\CommentReaction::class,
        'changelog' => \Ssntpl\LaravelComments\Models\CommentChangelog::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Edit history (changelog)
    |--------------------------------------------------------------------------
    | When true, every edit to a comment's body snapshots the new body into the
    | `comment_changelogs` table. Off by default — enable per app that wants it.
    */
    'changelog' => false,

    /*
    |--------------------------------------------------------------------------
    | Mentions
    |--------------------------------------------------------------------------
    | The package only PARSES mentions out of the body (returns the raw handles);
    | resolving handles to users and delivering notifications is the app's job,
    | typically via the CommentCreated / CommentUpdated events.
    */
    'mentions' => [
        'pattern' => '/@([A-Za-z0-9_.\-]+)/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-load migrations
    |--------------------------------------------------------------------------
    | When true, the package registers its own migrations. Set false in apps
    | that already own a `comments` table and manage the schema themselves
    | (e.g. otper) so the package migrations don't collide.
    */
    'auto_load_migrations' => true,
];
