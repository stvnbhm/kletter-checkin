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
        Schema::table('registrations', function (Blueprint $table) {
            // Wir fügen access_reason hinzu, falls es fehlt
            if (!Schema::hasColumn('registrations', 'access_reason')) {
                $table->string('access_reason')->nullable()->after('access_status');
            }
            
            // Wir fügen trial_visits_count hinzu (jetzt ohne strictes 'after', 
            // damit es auf jeden Fall klappt)
            if (!Schema::hasColumn('registrations', 'trial_visits_count')) {
                $table->integer('trial_visits_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'access_reason')) {
                $table->dropColumn('access_reason');
            }
            
            if (Schema::hasColumn('registrations', 'trial_visits_count')) {
                $table->dropColumn('trial_visits_count');
            }
        });
    }
};