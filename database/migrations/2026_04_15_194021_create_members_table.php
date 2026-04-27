<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->string('member_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();

            $table->string('membership_status')->default('active');
            $table->string('payment_status')->default('paid');

            $table->date('birth_date')->nullable();
            $table->timestamp('last_imported_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};