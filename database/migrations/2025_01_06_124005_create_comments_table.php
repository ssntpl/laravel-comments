<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->string('owner_type');
            // No DB-level FK: the user model/table is app-configurable (config('comments.user_model')).
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('type')->nullable();
            $table->text('body');
            // Threading: a comment may be a reply to another comment.
            $table->unsignedBigInteger('reply_to_comment_id')->nullable();
            $table->timestamps();
            $table->index(['owner_id', 'owner_type'], 'comments_owner_id_owner_type_index');
            $table->index('reply_to_comment_id');
            $table->foreign('reply_to_comment_id')->references('id')->on('comments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
