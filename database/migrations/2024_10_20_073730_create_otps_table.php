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
        Schema::create('otps', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('token')->unique(); // Unique token for each OTP request
            $table->string('otp'); // OTP value
            $table->timestamp('created_at')->nullable(); // Timestamp when OTP was created
            $table->timestamp('expires_at')->nullable(); // Timestamp for when OTP expires
            $table->timestamps(); // Additional timestamps (created_at, updated_at)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
