<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->text('content');
            $table->string('source_url')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamps();
            
            $table->index('source_url');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
