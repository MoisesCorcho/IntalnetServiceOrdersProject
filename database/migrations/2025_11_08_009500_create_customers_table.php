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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->comment('First name of the customer');
            $table->string('last_name')->comment('Last name of the customer');
            $table->string('email')->nullable()->comment('Primary email address of the customer');
            $table->string('phone')->nullable()->comment('Primary phone number of the customer');
            $table->string('secondary_phone')->nullable()->comment('Secondary phone number for the customer');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

