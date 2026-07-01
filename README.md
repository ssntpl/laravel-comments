# laravel-comments
This is a simple package to associate comments with your eloquent model in laravel. This package is providing functionality for adding, retrieving, editing, and deleting comments along with its associated files if any on various entities:

## Installation

You can install the package via composer:

```bash
composer require ssntpl/laravel-comments
```

Run the migrations with:

```bash
php artisan migrate
```

Optionally, You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-comments-migrations"
php artisan migrate
```

Publish the config to customise behaviour (user model, edit history, mention pattern, etc.):

```bash
php artisan vendor:publish --tag="laravel-comments-config"
```

> Apps that already own a `comments` table (and manage the schema themselves) can set
> `comments.auto_load_migrations` to `false` so the package does not register its own migrations.

## Usage

Add the `HasComments` trait to your model.

```php
namespace App\Models;
use Ssntpl\LaravelComments\Traits\HasComments;

class Post extends Model
{
    use HasComments;
}
```


Add new comment to the model.

```php
$model = Post::find(1);

$comment = $model->createComment([
    
    // type: Optional. It represents the type of comment.
    'type' => 'commentType', 

    // body: This is the body of the comment.
    // (The `text` and `comment` keys are still accepted as backward-compatible aliases.)
    'body' => 'This is the body of the comment', 

    // user_id: Optional. This is a foreign key belonging to that entity who is making the comment. For e.g:Users(so it will be the id of User who is making the comment).
    'user_id' => 1,

    // created_at: The created_at timestamp is automatically managed by Eloquent. Represents the time at which the comment is made. Otherwise one can manually assign a value to created_at when creating a new comment.
    'created_at' => '2025-01-17 05:14:13',  
]);

```


Accessing the comment model.

```php

$card = Card::find(1);//comments can be added on a card

$card->comments; //return all the comments linked with the card

$card->comments() // returns the Illuminate\Database\Eloquent\Relations\MorphMany relation

$card->comments()->where('user_id',1)->get()  //Accessing all comments of the card made for particular user

$comment = $card->comments()->where('id',3)->first() // One can access the specific comment of card

// to create an attachment on that comment
$comment->createFile([
                    'key' => 'path/filename.jpg',
                    'name' => 'filename.jpg'
                ]);

$comment->file //To access the first or only attachment with that comment on the card

$comment->files //To access all the attachments related to that comment on that card

//One can update particular comment by adding id as one of the params
$card->createComment(['id' => 23, 'user_id' => 2,'body'=> "This is a new comment"])

// The comment body is stored in the `body` column. For backward compatibility,
// `$comment->text` and `$comment->comment` both read from / write to `body`.
$comment->body;    // "This is a new comment"
$comment->text;    // same value (alias)
$comment->comment; // same value (alias)

//Like this one can delete all the comments along with its attachment on the card
$comments = $card->comments()->get();
foreach($comments as $comment) {
    $comment->delete();
}

```


### Threading (replies)

A comment can reply to another via `reply_to_comment_id`:

```php
$reply = $card->createComment([
    'user_id' => 2,
    'body' => 'I agree',
    'reply_to_comment_id' => $comment->id,
]);

$reply->replyToComment; // the parent comment
$comment->replies;      // replies to this comment
$card->rootComments();  // top-level comments only (excludes replies)
```

### Reactions

One reaction per user per comment (re-reacting changes it):

```php
$comment->react($user, 'thumbsup'); // add or change $user's reaction
$comment->unreact($user);           // remove it
$comment->reactions;                // all reactions on the comment
```

### Edit history (opt-in)

Set `comments.changelog` to `true` to snapshot each edit. On every change to `body`,
the *previous* body is stored in `comment_changelogs`, so the changelog table holds all
prior versions and the model holds the current one — the full history is reconstructable.

```php
$comment->changelogs; // prior versions, newest first
```

### Mentions

The package parses `@handles` out of the body; resolving them to users and delivering
notifications is your app's job (typically in a listener on the events below).

```php
$comment->mentionedHandles(); // ['bob', 'carol.dev']
```

The pattern is configurable via `comments.mentions.pattern`.

### Events

The package fires framework-agnostic events so your app can send notifications, broadcast,
or record activity without the package knowing about any of that:

| Event | When |
|---|---|
| `CommentCreated` | a comment is created |
| `CommentUpdated` | a comment's body is edited (carries `previousBody`) |
| `CommentDeleted` | a comment is deleted |
| `ReactionAdded` | a user reacts (or changes their reaction) |
| `ReactionRemoved` | a user removes their reaction |

```php
Event::listen(\Ssntpl\LaravelComments\Events\CommentCreated::class, function ($event) {
    // $event->comment->mentionedHandles(), notify, broadcast, ...
});
```

### Overriding models

Point `comments.models.*` at your own subclasses to add behaviour. Custom classes **must
extend** the package base (`Comment` / `CommentReaction` / `CommentChangelog`).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jyotsana Sharma](https://github.com/JYOTSANASHARMAA)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
