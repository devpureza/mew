<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_user', function (Blueprint $table) {
            if (! Schema::hasColumn('wedding_user', 'user_id')) {
                return;
            }
            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_user', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};
