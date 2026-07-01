<?php

namespace Ssntpl\LaravelComments\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Ssntpl\LaravelComments\Models\CommentReaction;

/**
 * Fired when a user reacts to a comment (or changes their reaction).
 */
class ReactionAdded
{
    use Dispatchable, SerializesModels;

    public function __construct(public CommentReaction $reaction)
    {
    }
}
