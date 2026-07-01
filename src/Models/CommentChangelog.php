<?php

namespace Ssntpl\LaravelComments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentChangelog extends Model
{
    protected $table = 'comment_changelogs';

    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'log',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(config('comments.models.comment', Comment::class), 'comment_id');
    }
}
