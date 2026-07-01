<?php

namespace Ssntpl\LaravelComments\Traits;

use Ssntpl\LaravelComments\Models\Comment;

/**
 * Adds
 */
trait HasComments
{
    public function comments()
    {
        return $this->morphMany(config('comments.models.comment', Comment::class), 'owner');
    }

    /**
     * Top-level comments only (excludes threaded replies).
     */
    public function rootComments()
    {
        return $this->comments()->whereNull('reply_to_comment_id');
    }

    public function createComment(array $attributes = [])
    {
        // Backward compatibility: accept `text`/`comment` as aliases for `body`.
        foreach (['text', 'comment'] as $alias) {
            if (empty($attributes['body']) && !empty($attributes[$alias])) {
                $attributes['body'] = $attributes[$alias];
            }
        }

        $commentAttributes = [];

        foreach (['created_at', 'user_id', 'body', 'type', 'reply_to_comment_id', 'id'] as $field) {
            if (!empty($attributes[$field])) {
                $commentAttributes[$field] = $attributes[$field];
            }
        }

        if (! $commentAttributes) {
            return null;
        }

        // With an id, update that specific comment; without one, always create
        // a new comment (never collapse two genuinely distinct comments that
        // happen to share body/user/type).
        if (! empty($attributes['id'])) {
            return $this->comments()->updateOrCreate(['id' => $attributes['id']], $commentAttributes);
        }

        return $this->comments()->create($commentAttributes);
    }
}
