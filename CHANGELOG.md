# Changelog

All notable changes to this package will be documented in this file.

## v0.1.0 (01-Jul-2026)
- Renamed the `text` column to `body`.
- Added backward-compatible `text` and `comment` model aliases that read from / write to `body`, so existing code using `$comment->text`, `$comment->comment`, or `createComment(['text' => ...])` keeps working.
- **Threading**: comments can reply to other comments via `reply_to_comment_id`; adds `replyToComment()` / `replies()` relations and `HasComments::rootComments()` (top-level only).
- **Reactions**: `comment_reactions` table + `CommentReaction` model, one reaction per user per comment, via `$comment->react($user, $reaction)` / `unreact($user)` and `$comment->reactions()`.
- **Edit history (opt-in)**: when `config('comments.changelog')` is true, each body edit snapshots into `comment_changelogs` (`CommentChangelog` model, `$comment->changelogs()`).
- **Lifecycle events**: `CommentCreated`, `CommentUpdated` (carries `previousBody`), `CommentDeleted`, `ReactionAdded`, `ReactionRemoved` — apps hook these for notifications / broadcasts / activity; the package stays framework-agnostic.
- **Mentions**: `$comment->mentionedHandles()` parses `@handles` from the body (config-driven pattern). Resolving handles and delivering notifications is left to the app.
- **Config** (`config/comments.php`): `user_model`, overridable `models`, `changelog`, `mentions.pattern`, and `auto_load_migrations` (set false in apps that own the `comments` schema, e.g. otper).
- All additions are additive/backward-compatible; no existing column, relation, or API was removed or renamed.

## v0.0.2 (21-Jan-2025)
- some database changes

## v0.0.1 (15-Jan-2025)
- Initial release
- Includes all the basic required functionality.
- Added delete function to delete comments along with its files
