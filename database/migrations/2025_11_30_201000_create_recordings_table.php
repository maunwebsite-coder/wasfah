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
        if (Schema::hasTable('recordings')) {
            return;
        }

        Schema::create('recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workshop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('drive_file_id')->nullable()->index();
            $table->string('recording_url', 1024)->nullable();
            $table->string('preview_url', 1024)->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->string('source', 32)->default('drive')->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'workshop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recordings');
    }
};
