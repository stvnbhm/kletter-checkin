<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('manual_exception_reason')->nullable()->after('access_status');
            $table->dateTime('manual_exception_until')->nullable()->after('manual_exception_reason');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['manual_exception_reason', 'manual_exception_until']);
        });
    }
};