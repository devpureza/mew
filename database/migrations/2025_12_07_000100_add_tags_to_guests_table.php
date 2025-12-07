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
        Schema::table('guests', function (Blueprint $table) {
            $table->boolean('is_godparent')->default(false)->after('is_head_of_family');
            $table->string('godparent_role')->nullable()->after('is_godparent'); // padrinho, madrinha
            $table->string('relationship')->nullable()->after('godparent_role'); // mae, pai, amigos, familia, outros
            $table->foreignId('belongs_to_user_id')->nullable()->after('relationship')->constrained('users')->nullOnDelete(); // de qual casal é
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropForeign(['belongs_to_user_id']);
            $table->dropColumn(['is_godparent', 'godparent_role', 'relationship', 'belongs_to_user_id']);
        });
    }
};
