<?php

namespace Ssntpl\LaravelComments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReaction extends Model
{
    protected $table = 'comment_reactions';

    protected $fillable = [
        'comment_id',
        'user_id',
        'reaction',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(config('comments.models.comment', Comment::class), 'comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('comments.user_model', 'App\\Models\\User'), 'user_id');
    }
}
