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
        return $this->morphMany(Comment::class, 'owner');
    }

    public function createComment(array $attributes = [])
    {
        $commentAttributes = [];

        foreach (['date', 'user_id', 'text', 'type', 'id'] as $field) {
            if (!empty($attributes[$field])) {
                $commentAttributes[$field] = $attributes[$field];
            }
        }

        $searchCriteria = !empty($attributes['id']) ? ['id' => $attributes['id']] : $commentAttributes;
        if($commentAttributes) {
            return $this->comments()->updateOrCreate($searchCriteria, $commentAttributes);
        }
    }
}
