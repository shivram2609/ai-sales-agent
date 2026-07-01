<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            if (! Schema::hasColumn('outreach_drafts', 'preheader')) {
                $table->string('preheader', 255)->nullable()->after('subject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            if (Schema::hasColumn('outreach_drafts', 'preheader')) {
                $table->dropColumn('preheader');
            }
        });
    }
};