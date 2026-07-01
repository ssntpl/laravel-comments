<?php

namespace Ssntpl\LaravelComments\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ssntpl\LaravelComments\Models\Comment;

/**
 * Fired after a comment is created. Apps listen to this to deliver
 * notifications (incl. @mentions and reply alerts), broadcast, or record
 * activity — none of which the package concerns itself with.
 */
class CommentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Comment $comment)
    {
    }
}
