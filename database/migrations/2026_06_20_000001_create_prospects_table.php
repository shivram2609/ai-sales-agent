<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('website_url', 500);
            $table->string('domain', 191)->unique();
            $table->string('company_name', 191)->nullable();
            $table->string('category_guess', 100)->nullable();
            $table->string('country_guess', 100)->nullable();
            $table->string('source', 50)->default('manual');
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('new')->index();
            $table->integer('fit_score')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
