<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->dropUnique('fcm_tokens_token_unique');
            $table->unique(['user_id', 'token']);
        });
    }

    public function down(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $table->dropUnique('fcm_tokens_user_id_token_unique');
            $table->unique('token');
        });
    }
};

