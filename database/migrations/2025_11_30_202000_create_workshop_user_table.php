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
        if (Schema::hasTable('workshop_user')) {
            return;
        }

        Schema::create('workshop_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('registered');
            $table->string('attendance_status', 32)->default('pending');
            $table->boolean('has_recording_access')->default(false);
            $table->timestamp('attended_at')->nullable();
            $table->timestamp('recording_unlocked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['workshop_id', 'user_id']);
            $table->index(['workshop_id', 'status']);
            $table->index('has_recording_access');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshop_user');
    }
};
