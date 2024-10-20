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
            $table->id(); // Creates an auto-incrementing primary key
            $table->text('token')->unique(); // Use `text` for larger token sizes
            $table->string('otp'); // OTP column
            $table->timestamps(); // Automatically create `created_at` and `updated_at` columns
            $table->timestamp('expires_at'); // Expiration time for the OTP
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
