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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('name', 'first_name');
            $table->string('last_name')->after('first_name')->comment('Family name of the user');
            $table->string('phone')->nullable()->after('email')->comment('Primary phone number of the user');
            $table->string('secondary_phone')->nullable()->after('phone')->comment('Secondary phone number of the user');
            $table->text('address')->nullable()->after('secondary_phone')->comment('Mailing or residential address of the user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('first_name', 'name');
            $table->dropColumn(['last_name', 'phone', 'secondary_phone', 'address']);
        });
    }
};

