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
        Schema::create('chat_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->text('query');
            $table->integer('response_time_ms'); // Latency in milliseconds
            $table->enum('search_method', ['semantic', 'keyword', 'failed']); // Which method was used
            $table->integer('result_count')->default(0); // How many results found
            $table->text('error')->nullable(); // Error message if any
            $table->boolean('had_fallback')->default(false); // Did it fallback from semantic to keyword?
            $table->timestamps();
            
            // Indexes for reporting
            $table->index(['search_method', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_metrics');
    }
};
