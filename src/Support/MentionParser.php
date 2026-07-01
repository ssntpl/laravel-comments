<?php

namespace Ssntpl\LaravelComments\Support;

/**
 * Extracts @mention handles from comment text. The package deliberately does
 * NOT resolve handles to users or deliver notifications — that is app-specific
 * and belongs in a listener on the CommentCreated / CommentUpdated events.
 */
class MentionParser
{
    /**
     * Return the unique handles mentioned in the given text.
     *
     * @return string[]
     */
    public static function handles(?string $text): array
    {
        if ($text === null || $text === '') {
            return [];
        }

        $pattern = config('comments.mentions.pattern', '/@([A-Za-z0-9_.\-]+)/');

        if (! preg_match_all($pattern, $text, $matches)) {
            return [];
        }

        return array_values(array_unique($matches[1] ?? []));
    }
}
