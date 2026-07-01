<?php

namespace Ssntpl\LaravelComments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ssntpl\LaravelComments\Events\CommentCreated;
use Ssntpl\LaravelComments\Events\CommentDeleted;
use Ssntpl\LaravelComments\Events\CommentUpdated;
use Ssntpl\LaravelComments\Events\ReactionAdded;
use Ssntpl\LaravelComments\Events\ReactionRemoved;
use Ssntpl\LaravelComments\Support\MentionParser;
use Ssntpl\LaravelFiles\Traits\HasFiles;

class Comment extends Model
{
    use HasFiles;

    protected $fillable = [
        'type',
        'body',
        // Backward-compatible aliases for the renamed `body` column.
        'text',
        'comment',
        'user_id',
        'reply_to_comment_id',
        'created_at',
    ];

    protected $table = 'comments';

    /**
     * Holds the pre-edit body between the `updating` and `updated` events so
     * the changelog snapshot and CommentUpdated event can report it.
     */
    protected ?string $bodyBeforeUpdate = null;

    protected static function booted(): void
    {
        static::created(function (Comment $comment) {
            CommentCreated::dispatch($comment);
        });

        static::updating(function (Comment $comment) {
            if ($comment->isDirty('body')) {
                $comment->bodyBeforeUpdate = $comment->getOriginal('body');
            }
        });

        static::updated(function (Comment $comment) {
            if ($comment->wasChanged('body')) {
                // Snapshot the PREVIOUS body: the changelog then holds every
                // prior version and the model holds the current one, so the
                // full history is reconstructable.
                if (config('comments.changelog', false)) {
                    $comment->changelogs()->create(['log' => $comment->bodyBeforeUpdate]);
                }
                CommentUpdated::dispatch($comment, $comment->bodyBeforeUpdate);
                $comment->bodyBeforeUpdate = null;
            }
        });

        static::deleted(function (Comment $comment) {
            CommentDeleted::dispatch($comment);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Backward-compatible aliases: `text` / `comment` resolve to `body`.
    |--------------------------------------------------------------------------
    */
    public function getTextAttribute()
    {
        return $this->body;
    }

    public function setTextAttribute($value)
    {
        $this->attributes['body'] = $value;
    }

    public function getCommentAttribute()
    {
        return $this->body;
    }

    public function setCommentAttribute($value)
    {
        $this->attributes['body'] = $value;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function owner()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('comments.user_model', 'App\\Models\\User'), 'user_id');
    }

    public function replyToComment(): BelongsTo
    {
        return $this->belongsTo(config('comments.models.comment', self::class), 'reply_to_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(config('comments.models.comment', self::class), 'reply_to_comment_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(config('comments.models.reaction', CommentReaction::class), 'comment_id');
    }

    public function changelogs(): HasMany
    {
        return $this->hasMany(config('comments.models.changelog', CommentChangelog::class), 'comment_id')
            ->orderBy('created_at', 'desc');
    }

    /*
    |--------------------------------------------------------------------------
    | Reactions
    |--------------------------------------------------------------------------
    */

    /**
     * Add or change a user's reaction to this comment (one per user). Fires
     * ReactionAdded.
     */
    public function react($user, string $reaction): CommentReaction
    {
        // Trim so stray whitespace can't create a duplicate bucket (" 👍" vs "👍").
        // We deliberately do NOT Unicode-normalise: NFC is a no-op for emoji and
        // won't merge presentation variants (U+FE0F is preserved by design), while
        // skin-tone/ZWJ variants are genuinely distinct and should stay distinct.
        $reaction = trim($reaction);

        if ($reaction === '') {
            throw new \InvalidArgumentException('A reaction must be a non-empty string; use unreact() to remove one.');
        }

        $userId = is_object($user) ? $user->getKey() : $user;

        $model = $this->reactions()->updateOrCreate(
            ['user_id' => $userId],
            ['reaction' => $reaction],
        );

        // Don't fire on a no-op re-submit of the same reaction.
        if ($model->wasRecentlyCreated || $model->wasChanged('reaction')) {
            ReactionAdded::dispatch($model);
        }

        return $model;
    }

    /**
     * Remove a user's reaction from this comment. Fires ReactionRemoved.
     */
    public function unreact($user): void
    {
        $userId = is_object($user) ? $user->getKey() : $user;

        $model = $this->reactions()->where('user_id', $userId)->first();

        if ($model) {
            $model->delete();
            ReactionRemoved::dispatch($model);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Mentions
    |--------------------------------------------------------------------------
    */

    /**
     * The @handles mentioned in this comment's body. Resolving handles to
     * users and delivering notifications is the app's responsibility.
     *
     * @return string[]
     */
    public function mentionedHandles(): array
    {
        return MentionParser::handles($this->body);
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    */
    public function delete()
    {
        foreach ($this->files()->get() as $commentFile) {
            $commentFile->delete();
        }

        return parent::delete();
    }
}
