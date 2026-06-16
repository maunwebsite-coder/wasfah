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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('title'); // Ø¹Ù†ÙˆØ§Ù† Ø§Ù„ÙˆØ±Ø´Ø©
            $table->text('description'); // ÙˆØµÙ Ø§Ù„ÙˆØ±Ø´Ø©
            $table->text('content')->nullable(); // Ù…Ø­ØªÙˆÙ‰ Ø§Ù„ÙˆØ±Ø´Ø© Ø§Ù„ØªÙØµÙŠÙ„ÙŠ

            // Instructor Info
            $table->string('instructor'); // Ø§Ø³Ù… Ø§Ù„Ù…Ø¯Ø±Ø¨
            $table->string('instructor_avatar')->nullable(); // ØµÙˆØ±Ø© Ø§Ù„Ù…Ø¯Ø±Ø¨
            $table->text('instructor_bio')->nullable(); // Ù†Ø¨Ø°Ø© Ø¹Ù† Ø§Ù„Ù…Ø¯Ø±Ø¨

            // Classification
            $table->string('category'); // ÙØ¦Ø© Ø§Ù„ÙˆØ±Ø´Ø©
            $table->string('level'); // Ù…Ø³ØªÙˆÙ‰ Ø§Ù„ÙˆØ±Ø´Ø©

            // Duration & Capacity
            $table->integer('duration'); // Ù…Ø¯Ø© Ø§Ù„ÙˆØ±Ø´Ø© Ø¨Ø§Ù„Ø¯Ù‚Ø§Ø¦Ù‚
            $table->integer('max_participants')->default(20); // Ø§Ù„Ø­Ø¯ Ø§Ù„Ø£Ù‚ØµÙ‰ Ù„Ù„Ù…Ø´Ø§Ø±ÙƒÙŠÙ†

            // Pricing
            $table->decimal('price', 8, 2); // Ø³Ø¹Ø± Ø§Ù„ÙˆØ±Ø´Ø©
            $table->string('currency', 3)->default('JOD'); // Ø§Ù„Ø¹Ù…Ù„Ø©

            // Media
            $table->string('image')->nullable(); // ØµÙˆØ±Ø© Ø§Ù„ÙˆØ±Ø´Ø©
            $table->json('images')->nullable(); // ØµÙˆØ± Ø¥Ø¶Ø§ÙÙŠØ©

            // Location
            $table->string('location'); // Ù…ÙˆÙ‚Ø¹ Ø§Ù„ÙˆØ±Ø´Ø©
            $table->string('address')->nullable(); // Ø§Ù„Ø¹Ù†ÙˆØ§Ù†
            $table->decimal('latitude', 10, 8)->nullable(); // Ø®Ø· Ø§Ù„Ø¹Ø±Ø¶
            $table->decimal('longitude', 11, 8)->nullable(); // Ø®Ø· Ø§Ù„Ø·ÙˆÙ„

            // Dates
            $table->dateTime('start_date'); // ØªØ§Ø±ÙŠØ® Ø§Ù„Ø¨Ø¯Ø§ÙŠØ©
            $table->dateTime('end_date'); // ØªØ§Ø±ÙŠØ® Ø§Ù„Ù†Ù‡Ø§ÙŠØ©
            $table->dateTime('registration_deadline')->nullable(); // Ø¢Ø®Ø± Ù…ÙˆØ¹Ø¯ ØªØ³Ø¬ÙŠÙ„

            // Online Settings
            $table->boolean('is_online')->default(false); // Ø£ÙˆÙ†Ù„Ø§ÙŠÙ†ØŸ
            $table->string('meeting_link')->nullable(); // Ø±Ø§Ø¨Ø· Ø§Ù„Ø§Ø¬ØªÙ…Ø§Ø¹
            $table->string('recording_url')->nullable(); // Ø±Ø§Ø¨Ø· ØªØ³Ø¬ÙŠÙ„ Ø§Ù„ÙˆØ±Ø´Ø© (Ù…Ù‡Ù… Ù„Ù„Ù…Ø§ÙŠØ¬Ø±ÙŠØ´Ù† Ø§Ù„ØªØ§Ù„ÙŠØ©)

            // Content Details
            $table->text('requirements')->nullable(); // Ù…ØªØ·Ù„Ø¨Ø§Øª
            $table->text('what_you_will_learn')->nullable(); // Ù…Ø§Ø°Ø§ Ø³ÙŠØªØ¹Ù„Ù…
            $table->text('materials_needed')->nullable(); // Ø§Ù„Ù…ÙˆØ§Ø¯ Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø©

            // Status & Stats
            $table->boolean('is_active')->default(true); // Ù†Ø´Ø·Ø©ØŸ
            $table->boolean('is_featured')->default(false); // Ù…Ù…ÙŠØ²Ø©ØŸ
            $table->integer('views_count')->default(0); // Ø¹Ø¯Ø¯ Ø§Ù„Ù…Ø´Ø§Ù‡Ø¯Ø§Øª
            $table->integer('bookings_count')->default(0); // Ø¹Ø¯Ø¯ Ø§Ù„Ø­Ø¬ÙˆØ²Ø§Øª
            $table->decimal('rating', 3, 2)->default(0); // Ø§Ù„ØªÙ‚ÙŠÙŠÙ…
            $table->integer('reviews_count')->default(0); // Ø¹Ø¯Ø¯ Ø§Ù„ØªÙ‚ÙŠÙŠÙ…Ø§Øª

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};

