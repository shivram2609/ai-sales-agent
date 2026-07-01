<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('knowledge_assets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('asset_type', 50)->default('page');
            $table->string('url', 191)->unique();
            $table->text('summary')->nullable();
            $table->json('tags_json')->nullable();
            $table->json('industries_json')->nullable();
            $table->json('technologies_json')->nullable();
            $table->text('use_when')->nullable();
            $table->text('avoid_when')->nullable();
            $table->longText('content_snippet')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('knowledge_assets'); }
};
