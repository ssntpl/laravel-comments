<?php

namespace Ssntpl\LaravelComments\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ssntpl\LaravelComments\Models\Comment;

/**
 * Fired after a comment's body is edited. `$previousBody` carries the body
 * as it was before the edit.
 */
class CommentUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Comment $comment, public ?string $previousBody = null)
    {
    }
}
