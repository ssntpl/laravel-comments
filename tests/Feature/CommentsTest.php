<?php

namespace Ssntpl\LaravelComments\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Ssntpl\LaravelComments\Events\CommentCreated;
use Ssntpl\LaravelComments\Events\CommentDeleted;
use Ssntpl\LaravelComments\Events\CommentUpdated;
use Ssntpl\LaravelComments\Events\ReactionAdded;
use Ssntpl\LaravelComments\Events\ReactionRemoved;
use Ssntpl\LaravelComments\Tests\Models\TestArticle;
use Ssntpl\LaravelComments\Tests\Models\TestUser;
use Ssntpl\LaravelComments\Tests\TestCase;

class CommentsTest extends TestCase
{
    private TestUser $alice;
    private TestUser $bob;
    private TestArticle $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alice = TestUser::create(['name' => 'alice']);
        $this->bob = TestUser::create(['name' => 'bob']);
        $this->article = TestArticle::create(['title' => 'Post']);
    }

    public function test_creates_comment_and_body_aliases_resolve(): void
    {
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'comment' => 'Hello']);

        $this->assertSame('Hello', $c->body);
        $this->assertSame('Hello', $c->text);     // alias
        $this->assertSame('Hello', $c->comment);   // alias
        $this->assertDatabaseHas('comments', ['id' => $c->id, 'body' => 'Hello']);
    }

    /** Fix 3: two identical no-id comments must not collapse into one row. */
    public function test_duplicate_no_id_comments_create_distinct_rows(): void
    {
        $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'ok']);
        $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'ok']);

        $this->assertSame(2, $this->article->comments()->count());
    }

    public function test_create_comment_with_id_updates_in_place(): void
    {
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'v1']);
        $this->article->createComment(['id' => $c->id, 'body' => 'v2']);

        $this->assertSame(1, $this->article->comments()->count());
        $this->assertSame('v2', $c->fresh()->body);
    }

    public function test_threading_and_root_comments(): void
    {
        $parent = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'parent']);
        $reply = $this->article->createComment([
            'user_id' => $this->bob->id, 'body' => 'reply', 'reply_to_comment_id' => $parent->id,
        ]);

        $this->assertSame($parent->id, $reply->replyToComment->id);
        $this->assertSame(1, $parent->replies()->count());
        $this->assertSame(1, $this->article->rootComments()->count());
        $this->assertSame(2, $this->article->comments()->count());
    }

    public function test_reaction_is_unique_per_user(): void
    {
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'x']);
        $c->react($this->bob, 'up');
        $c->react($this->bob, 'heart');

        $this->assertSame(1, $c->reactions()->count());
        $this->assertSame('heart', $c->reactions()->first()->reaction);
    }

    /** Fix 5: no event on a no-op re-submit; event on an actual change. */
    public function test_reaction_added_event_gating(): void
    {
        Event::fake([ReactionAdded::class]);
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'x']);

        $c->react($this->bob, 'up');
        $c->react($this->bob, 'up');   // no-op
        Event::assertDispatchedTimes(ReactionAdded::class, 1);

        $c->react($this->bob, 'heart'); // change
        Event::assertDispatchedTimes(ReactionAdded::class, 2);
    }

    /** Fix 6: an empty reaction is rejected. */
    public function test_empty_reaction_is_rejected(): void
    {
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'x']);

        $this->expectException(InvalidArgumentException::class);
        $c->react($this->bob, '  ');
    }

    public function test_unreact_removes_and_fires_event(): void
    {
        Event::fake([ReactionRemoved::class]);
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'x']);
        $c->react($this->bob, 'up');

        $c->unreact($this->bob);

        $this->assertSame(0, $c->reactions()->count());
        Event::assertDispatched(ReactionRemoved::class);
    }

    public function test_changelog_is_off_by_default(): void
    {
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'v1']);
        $c->body = 'v2';
        $c->save();

        $this->assertSame(0, $c->changelogs()->count());
    }

    /** Fix 4: changelog snapshots the PRIOR body, so history is reconstructable. */
    public function test_changelog_records_prior_body_when_enabled(): void
    {
        Config::set('comments.changelog', true);
        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'v1']);

        $c->body = 'v2';
        $c->save();
        $c->body = 'v3';
        $c->save();

        $this->assertSame(['v1', 'v2'], $c->changelogs()->orderBy('id')->pluck('log')->all());
        $this->assertSame('v3', $c->fresh()->body);
    }

    public function test_mentions_are_parsed_from_body(): void
    {
        $c = $this->article->createComment([
            'user_id' => $this->alice->id, 'body' => 'ping @bob and @carol.dev again @bob',
        ]);

        // Handles are de-duplicated; dotted/hyphenated handles are supported.
        $this->assertSame(['bob', 'carol.dev'], $c->mentionedHandles());
    }

    public function test_lifecycle_events_fire(): void
    {
        Event::fake([CommentCreated::class, CommentUpdated::class, CommentDeleted::class]);

        $c = $this->article->createComment(['user_id' => $this->alice->id, 'body' => 'v1']);
        Event::assertDispatched(CommentCreated::class);

        $c->body = 'v2';
        $c->save();
        Event::assertDispatched(CommentUpdated::class, fn ($e) => $e->previousBody === 'v1');

        $c->delete();
        Event::assertDispatched(CommentDeleted::class);
    }
}
