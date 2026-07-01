<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reactions are arbitrary emoji, which need utf8mb4 + a binary collation
        // on MySQL/MariaDB (see below). Those options are engine-specific, so only
        // apply them there and leave other drivers (pgsql, sqlite) on their native
        // UTF-8 handling.
        $mysqlFamily = in_array(
            Schema::getConnection()->getDriverName(),
            ['mysql', 'mariadb'],
            true
        );

        Schema::create('comment_reactions', function (Blueprint $table) use ($mysqlFamily) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            // No DB-level FK on user_id: the user model/table is app-configurable.
            $table->unsignedBigInteger('user_id')->index();

            // A reaction is a single emoji, but one emoji can be a multi-code-point
            // grapheme cluster (skin-tone modifiers, ZWJ sequences like 👨‍👩‍👧‍👦,
            // flags, U+FE0F variation selectors), so 32 chars gives ample headroom.
            $reaction = $table->string('reaction', 32);

            // On MySQL/MariaDB, pin the column to utf8mb4 so 4-byte emoji store
            // correctly (legacy utf8/utf8mb3 truncates them), and a *binary*
            // collation so distinct emoji never collapse into the same bucket when
            // reactions are aggregated with GROUP BY reaction.
            if ($mysqlFamily) {
                $reaction->charset('utf8mb4')->collation('utf8mb4_bin');
            }

            $table->timestamps();

            // One reaction per user per comment.
            $table->unique(['comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
