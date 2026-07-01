<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discovery_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->json('service_focus')->nullable();
            $table->json('target_audience')->nullable();
            $table->json('regions_json')->nullable();
            $table->json('cities_json')->nullable();
            $table->json('exclude_terms_json')->nullable();
            $table->json('exact_queries_json')->nullable();
            $table->string('query_mode', 50)->default('smart')->index();
            $table->integer('max_results_per_query')->default(10);
            $table->string('source_engine', 50)->default('serpapi_google');
            $table->string('status', 50)->default('draft')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('discovery_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discovery_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('source_query', 700);
            $table->string('result_title', 700)->nullable();
            $table->text('result_url');
            $table->string('normalized_domain', 191);
            $table->text('snippet')->nullable();
            $table->string('source_engine', 50)->default('serpapi_google');
            $table->integer('rank')->nullable();
            $table->string('company_name_guess', 191)->nullable();
            $table->string('category_guess', 100)->nullable();
            $table->string('region_guess', 100)->nullable();
            $table->integer('relevance_score')->default(0)->index();
            $table->string('filter_status', 50)->default('pending')->index();
            $table->text('reason')->nullable();
            $table->boolean('converted_to_prospect')->default(false)->index();
            $table->foreignId('prospect_id')->nullable()->constrained()->nullOnDelete();
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->unique(['discovery_campaign_id', 'normalized_domain'], 'campaign_domain_unique');
        });

        Schema::create('discovery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discovery_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('step_name', 100);
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('discovery_logs');
        Schema::dropIfExists('discovery_results');
        Schema::dropIfExists('discovery_campaigns');
    }
};
