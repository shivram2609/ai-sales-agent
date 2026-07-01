<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospect_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->string('page_type', 50)->default('homepage');
            $table->string('url', 500);
            $table->string('title', 500)->nullable();
            $table->text('meta_description')->nullable();
            $table->json('headings_json')->nullable();
            $table->json('links_json')->nullable();
            $table->json('emails_json')->nullable();
            $table->longText('main_text')->nullable();
            $table->longText('raw_html_snapshot')->nullable();
            $table->string('crawl_status', 50)->default('completed');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('prospect_pages'); }
};
