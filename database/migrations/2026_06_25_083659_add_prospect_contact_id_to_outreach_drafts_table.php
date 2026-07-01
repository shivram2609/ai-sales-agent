<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            if (! Schema::hasColumn('outreach_drafts', 'prospect_contact_id')) {
                $table->foreignId('prospect_contact_id')
                    ->nullable()
                    ->after('prospect_id')
                    ->constrained('prospect_contacts')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            if (Schema::hasColumn('outreach_drafts', 'prospect_contact_id')) {
                $table->dropConstrainedForeignId('prospect_contact_id');
            }
        });
    }
};