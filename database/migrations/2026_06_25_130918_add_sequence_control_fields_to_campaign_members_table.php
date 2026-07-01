<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_members', function (Blueprint $table) {
            if (! Schema::hasColumn('campaign_members', 'sequence_mode')) {
                $table->string('sequence_mode', 50)->default('manual_only')->after('status');
            }

            if (! Schema::hasColumn('campaign_members', 'approved_for_sending_at')) {
                $table->timestamp('approved_for_sending_at')->nullable()->after('sequence_mode');
            }

            if (! Schema::hasColumn('campaign_members', 'sequence_paused_at')) {
                $table->timestamp('sequence_paused_at')->nullable()->after('approved_for_sending_at');
            }

            if (! Schema::hasColumn('campaign_members', 'sequence_stopped_at')) {
                $table->timestamp('sequence_stopped_at')->nullable()->after('sequence_paused_at');
            }

            if (! Schema::hasColumn('campaign_members', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('sequence_stopped_at');
            }

            if (! Schema::hasColumn('campaign_members', 'stop_reason')) {
                $table->text('stop_reason')->nullable()->after('approval_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaign_members', function (Blueprint $table) {
            $columns = [
                'sequence_mode',
                'approved_for_sending_at',
                'sequence_paused_at',
                'sequence_stopped_at',
                'approval_notes',
                'stop_reason',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('campaign_members', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};