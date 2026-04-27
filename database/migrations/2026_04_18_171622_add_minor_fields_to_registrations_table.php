<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'needs_supervision')) {
                $table->boolean('needs_supervision')->default(false);
            }

            if (!Schema::hasColumn('registrations', 'needs_parent_consent')) {
                $table->boolean('needs_parent_consent')->default(false);
            }

            if (!Schema::hasColumn('registrations', 'parent_consent_received')) {
                $table->boolean('parent_consent_received')->default(false);
            }

            if (!Schema::hasColumn('registrations', 'parent_consent_received_at')) {
                $table->timestamp('parent_consent_received_at')->nullable();
            }

            if (!Schema::hasColumn('registrations', 'supervision_confirmed')) {
                $table->boolean('supervision_confirmed')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $drop = [];

            foreach ([
                'needs_supervision',
                'needs_parent_consent',
                'parent_consent_received',
                'parent_consent_received_at',
                'supervision_confirmed',
            ] as $column) {
                if (Schema::hasColumn('registrations', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};