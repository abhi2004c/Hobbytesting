<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('text'); // text|image|video|link|poll
            $table->text('content');
            $table->json('attachments')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_announcement')->default(false);
            $table->string('visibility', 20)->default('group'); // group|public
            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->foreignId('shared_post_id')->nullable()
                ->constrained('posts')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('is_pinned');
            $table->index('is_announcement');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};