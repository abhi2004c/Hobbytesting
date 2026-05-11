<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->morphs('reactable'); // reactable_type, reactable_id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // like|love|wow|haha
            $table->timestamps();

            $table->unique(['reactable_type', 'reactable_id', 'user_id', 'type'], 'reactions_unique');
            $table->index(['reactable_type', 'reactable_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};