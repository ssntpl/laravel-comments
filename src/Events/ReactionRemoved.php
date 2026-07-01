<?php

namespace Ssntpl\LaravelComments\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ssntpl\LaravelComments\Models\CommentReaction;

/**
 * Fired when a user removes their reaction from a comment.
 */
class ReactionRemoved
{
    use Dispatchable, SerializesModels;

    public function __construct(public CommentReaction $reaction)
    {
    }
}
