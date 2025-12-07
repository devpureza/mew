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
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('event_date');
            $table->string('location')->nullable();
            $table->string('location_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('wedding_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('couple');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['wedding_id', 'user_id']);
        });

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_guest_id')->nullable()->constrained('guests')->nullOnDelete();
            $table->string('name');
            $table->string('cpf')->nullable()->index();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_head_of_family')->default(false);
            $table->unsignedInteger('party_size')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
        Schema::dropIfExists('wedding_user');
        Schema::dropIfExists('weddings');
    }
};
