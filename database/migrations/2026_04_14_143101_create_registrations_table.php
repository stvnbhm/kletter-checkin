<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();

            $table->enum('member_type', ['member', 'guest']);
            $table->string('member_number')->nullable();

            $table->boolean('waiver_accepted')->default(false);
            $table->string('waiver_version')->default('v1');

            $table->enum('payment_status', ['paid', 'overdue'])->default('paid');
            $table->enum('access_status', ['green', 'blue', 'orange', 'red'])->default('red');

            $table->string('qr_token')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};