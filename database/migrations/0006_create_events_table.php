<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');

            $table->string('type', 20)->default('in_person'); // online|in_person|hybrid
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('online_url', 500)->nullable();

            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->unsignedInteger('capacity')->nullable();

            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 500)->nullable(); // RRULE string

            $table->string('status', 20)->default('draft'); // draft|published|cancelled
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->unsignedInteger('rsvp_count_cache')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('group_id');
            $table->index('creator_id');
            $table->index('starts_at');
            $table->index('status');
            $table->index(['group_id', 'starts_at']);
            $table->index(['status', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};