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

    // text: This is text of the comment.
    'text' => 'The is the text of the comment', 

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
$card->createComment(['id' => 23, 'user_id' => 2,'text'=> "This is a new comment"])

//Like this one can delete all the comments along with its attachment on the card
$comments = $card->comments()->get();
foreach($comments as $comment) {
    $comment->delete();
}

```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jyotsana Sharma](https://github.com/JYOTSANASHARMAA)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
