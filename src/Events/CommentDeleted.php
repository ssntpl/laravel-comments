<?php

namespace Ssntpl\LaravelComments\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ssntpl\LaravelComments\Models\Comment;

/**
 * Fired after a comment is deleted.
 */
class CommentDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }
}
