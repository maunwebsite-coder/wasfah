<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Workshop;
use Carbon\Carbon;

class WorkshopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workshops = [
            [
                'title' => 'ÙˆØ±Ø´Ø© ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„Ø¹Ø±Ø¨ÙŠØ© Ø§Ù„ØªÙ‚Ù„ÙŠØ¯ÙŠØ©',
                'description' => 'ØªØ¹Ù„Ù… ÙƒÙŠÙÙŠØ© ØµÙ†Ø¹ Ø£Ø´Ù‡Ù‰ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„Ø¹Ø±Ø¨ÙŠØ© Ù…Ø«Ù„ Ø§Ù„Ø¨Ù‚Ù„Ø§ÙˆØ© ÙˆØ§Ù„ÙƒÙ†Ø§ÙØ© ÙˆØ§Ù„Ù‚Ø·Ø§ÙŠÙ',
                'content' => 'ÙÙŠ Ù‡Ø°Ù‡ Ø§Ù„ÙˆØ±Ø´Ø© Ø³ÙˆÙ ØªØªØ¹Ù„Ù… Ø£Ø³Ø±Ø§Ø± ØµÙ†Ø¹ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„Ø¹Ø±Ø¨ÙŠØ© Ø§Ù„ØªÙ‚Ù„ÙŠØ¯ÙŠØ© Ù…Ù† Ø§Ù„ØµÙØ±. Ø³Ù†Ø¨Ø¯Ø£ Ø¨Ø§Ù„ØªØ¹Ø±Ù Ø¹Ù„Ù‰ Ø§Ù„Ù…ÙƒÙˆÙ†Ø§Øª Ø§Ù„Ø£Ø³Ø§Ø³ÙŠØ© ÙˆØ·Ø±Ù‚ Ø§Ù„ØªØ­Ø¶ÙŠØ± Ø§Ù„ØµØ­ÙŠØ­Ø©ØŒ Ø«Ù… Ù†Ù†ØªÙ‚Ù„ Ù„ØµÙ†Ø¹ Ø§Ù„Ø¨Ù‚Ù„Ø§ÙˆØ© ÙˆØ§Ù„ÙƒÙ†Ø§ÙØ© ÙˆØ§Ù„Ù‚Ø·Ø§ÙŠÙ Ø®Ø·ÙˆØ© Ø¨Ø®Ø·ÙˆØ©.',
                'instructor' => 'Ø§Ù„Ø´ÙŠÙ ÙØ§Ø·Ù…Ø© Ø£Ø­Ù…Ø¯',
                'instructor_avatar' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face',
                'instructor_bio' => 'Ø´ÙŠÙ Ù…ØªØ®ØµØµØ© ÙÙŠ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„Ø¹Ø±Ø¨ÙŠØ© Ù…Ø¹ 15 Ø¹Ø§Ù… Ù…Ù† Ø§Ù„Ø®Ø¨Ø±Ø©',
                'category' => 'Ø­Ù„ÙˆÙŠØ§Øª',
                'level' => 'Ù…Ø¨ØªØ¯Ø¦',
                'duration' => 180, // 3 Ø³Ø§Ø¹Ø§Øª
                'max_participants' => 15,
                'price' => 150.00,
                'currency' => 'JOD',
                'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=300&fit=crop',
                'images' => [
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=300&fit=crop',
                    'https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400&h=300&fit=crop',
                    'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400&h=300&fit=crop'
                ],
                'location' => 'Ù…Ø·Ø¨Ø® ÙˆØµÙØ© - Ø§Ù„Ø±ÙŠØ§Ø¶',
                'address' => 'Ø´Ø§Ø±Ø¹ Ø§Ù„Ù…Ù„Ùƒ ÙÙ‡Ø¯ØŒ Ø­ÙŠ Ø§Ù„Ø¹Ù„ÙŠØ§ØŒ Ø§Ù„Ø±ÙŠØ§Ø¶',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'start_date' => Carbon::now()->addDays(7)->setTime(10, 0),
                'end_date' => Carbon::now()->addDays(7)->setTime(13, 0),
                'registration_deadline' => Carbon::now()->addDays(5),
                'is_online' => false,
                'requirements' => 'Ù„Ø§ ØªÙˆØ¬Ø¯ Ù…ØªØ·Ù„Ø¨Ø§Øª Ø®Ø§ØµØ©ØŒ Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…ÙƒÙˆÙ†Ø§Øª Ù…ØªÙˆÙØ±Ø©',
                'what_you_will_learn' => 'ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ø¨Ù‚Ù„Ø§ÙˆØ©ØŒ Ø§Ù„ÙƒÙ†Ø§ÙØ©ØŒ Ø§Ù„Ù‚Ø·Ø§ÙŠÙØŒ ÙˆØ£Ø³Ø±Ø§Ø± Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„Ø¹Ø±Ø¨ÙŠØ©',
                'materials_needed' => 'Ø¬Ù…ÙŠØ¹ Ø§Ù„Ø£Ø¯ÙˆØ§Øª ÙˆØ§Ù„Ù…ÙƒÙˆÙ†Ø§Øª Ù…ØªÙˆÙØ±Ø© ÙÙŠ Ø§Ù„ÙˆØ±Ø´Ø©',
                'is_active' => true,
                'is_featured' => false,
                'rating' => 4.8,
                'reviews_count' => 25
            ],
            [
                'title' => 'ÙˆØ±Ø´Ø© Ø§Ù„Ø·Ø¨Ø® Ø§Ù„Ø¥ÙŠØ·Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙŠÙ„',
                'description' => 'Ø§ÙƒØªØ´Ù Ø£Ø³Ø±Ø§Ø± Ø§Ù„Ù…Ø·Ø¨Ø® Ø§Ù„Ø¥ÙŠØ·Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙŠÙ„ Ù…Ø¹ Ø´ÙŠÙ Ø¥ÙŠØ·Ø§Ù„ÙŠ Ù…Ø­ØªØ±Ù',
                'content' => 'ÙˆØ±Ø´Ø© Ø´Ø§Ù…Ù„Ø© Ù„ØªØ¹Ù„Ù… Ø§Ù„Ø·Ø¨Ø® Ø§Ù„Ø¥ÙŠØ·Ø§Ù„ÙŠ Ø§Ù„Ø£ØµÙŠÙ„. Ø³Ù†ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ù…Ø¹ÙƒØ±ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ø²Ø¬Ø©ØŒ Ø§Ù„Ø¨ÙŠØªØ²Ø§ØŒ ÙˆØ§Ù„Ø±ÙŠØ²ÙˆØªÙˆØŒ Ø¨Ø§Ù„Ø¥Ø¶Ø§ÙØ© Ø¥Ù„Ù‰ Ø§Ù„ØµÙ„ØµØ§Øª Ø§Ù„ØªÙ‚Ù„ÙŠØ¯ÙŠØ© Ø§Ù„Ø¥ÙŠØ·Ø§Ù„ÙŠØ©.',
                'instructor' => 'Ø§Ù„Ø´ÙŠÙ Ù…Ø§Ø±ÙƒÙˆ Ø±ÙˆØ³ÙŠ',
                'instructor_avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face',
                'instructor_bio' => 'Ø´ÙŠÙ Ø¥ÙŠØ·Ø§Ù„ÙŠ Ù…Ø­ØªØ±Ù Ù…Ø¹ 20 Ø¹Ø§Ù… Ù…Ù† Ø§Ù„Ø®Ø¨Ø±Ø© ÙÙŠ Ø§Ù„Ù…Ø·Ø§Ø¹Ù… Ø§Ù„Ø¥ÙŠØ·Ø§Ù„ÙŠØ©',
                'category' => 'Ù…Ø·Ø¨Ø® Ø¹Ø§Ù„Ù…ÙŠ',
                'level' => 'Ù…ØªÙˆØ³Ø·',
                'duration' => 180, // 3 Ø³Ø§Ø¹Ø§Øª (Ø§Ù„Ø­Ø¯ Ø§Ù„Ø£Ù‚ØµÙ‰)
                'max_participants' => 12,
                'price' => 200.00,
                'currency' => 'JOD',
                'image' => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop',
                'images' => [
                    'https://images.unsplash.com/photo-1565299624946-b28f40a0ca4b?w=400&h=300&fit=crop',
                    'https://images.unsplash.com/photo-1572441713132-51c75654db73?w=400&h=300&fit=crop'
                ],
                'location' => 'Ù…Ø·Ø¨Ø® ÙˆØµÙØ© - Ø¬Ø¯Ø©',
                'address' => 'ÙƒÙˆØ±Ù†ÙŠØ´ Ø¬Ø¯Ø©ØŒ Ø­ÙŠ Ø§Ù„Ø²Ù‡Ø±Ø§Ø¡',
                'latitude' => 21.4858,
                'longitude' => 39.1925,
                'start_date' => Carbon::now()->addDays(10)->setTime(14, 0),
                'end_date' => Carbon::now()->addDays(10)->setTime(18, 0),
                'registration_deadline' => Carbon::now()->addDays(8),
                'is_online' => false,
                'requirements' => 'Ù…Ø¹Ø±ÙØ© Ø£Ø³Ø§Ø³ÙŠØ© Ø¨Ø§Ù„Ø·Ø¨Ø®',
                'what_you_will_learn' => 'ØµÙ†Ø¹ Ø§Ù„Ù…Ø¹ÙƒØ±ÙˆÙ†Ø© Ø§Ù„Ø·Ø§Ø²Ø¬Ø©ØŒ Ø§Ù„Ø¨ÙŠØªØ²Ø§ØŒ Ø§Ù„Ø±ÙŠØ²ÙˆØªÙˆØŒ ÙˆØ§Ù„ØµÙ„ØµØ§Øª Ø§Ù„Ø¥ÙŠØ·Ø§Ù„ÙŠØ©',
                'materials_needed' => 'Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…ÙƒÙˆÙ†Ø§Øª Ù…ØªÙˆÙØ±Ø©',
                'is_active' => true,
                'is_featured' => false,
                'rating' => 4.9,
                'reviews_count' => 18
            ],
            [
                'title' => 'ÙˆØ±Ø´Ø© Ø§Ù„Ø·Ø¨Ø® Ø§Ù„ØµØ­ÙŠ - Ø£ÙˆÙ†Ù„Ø§ÙŠÙ†',
                'description' => 'ØªØ¹Ù„Ù… Ø§Ù„Ø·Ø¨Ø® Ø§Ù„ØµØ­ÙŠ ÙˆØ§Ù„Ù…ØªÙˆØ§Ø²Ù† Ù…Ù† Ù…Ù†Ø²Ù„Ùƒ',
                'content' => 'ÙˆØ±Ø´Ø© Ø£ÙˆÙ†Ù„Ø§ÙŠÙ† Ù„ØªØ¹Ù„Ù… Ø§Ù„Ø·Ø¨Ø® Ø§Ù„ØµØ­ÙŠ ÙˆØ§Ù„Ù…ØªÙˆØ§Ø²Ù†. Ø³Ù†ØªØ¹Ù„Ù… ÙƒÙŠÙÙŠØ© ØªØ­Ø¶ÙŠØ± ÙˆØ¬Ø¨Ø§Øª ØµØ­ÙŠØ© ÙˆÙ„Ø°ÙŠØ°Ø© Ø¨Ø§Ø³ØªØ®Ø¯Ø§Ù… Ù…ÙƒÙˆÙ†Ø§Øª Ø·Ø¨ÙŠØ¹ÙŠØ© ÙˆÙ…ØºØ°ÙŠØ©.',
                'instructor' => 'Ø¯. Ø³Ø§Ø±Ø© Ù…Ø­Ù…Ø¯ - Ø£Ø®ØµØ§Ø¦ÙŠØ© Ø§Ù„ØªØºØ°ÙŠØ©',
                'instructor_avatar' => 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?w=150&h=150&fit=crop&crop=face',
                'instructor_bio' => 'Ø£Ø®ØµØ§Ø¦ÙŠØ© ØªØºØ°ÙŠØ© Ù…Ø¹ 10 Ø£Ø¹ÙˆØ§Ù… Ù…Ù† Ø§Ù„Ø®Ø¨Ø±Ø© ÙÙŠ Ø§Ù„Ø·Ø¨Ø® Ø§Ù„ØµØ­ÙŠ',
                'category' => 'Ø·Ø¨Ø® ØµØ­ÙŠ',
                'level' => 'Ù…Ø¨ØªØ¯Ø¦',
                'duration' => 120, // Ø³Ø§Ø¹ØªØ§Ù†
                'max_participants' => 30,
                'price' => 80.00,
                'currency' => 'JOD',
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
                'images' => [
                    'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop',
                    'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=400&h=300&fit=crop'
                ],
                'location' => 'Ø£ÙˆÙ†Ù„Ø§ÙŠÙ†',
                'address' => null,
                'latitude' => null,
                'longitude' => null,
                'start_date' => Carbon::now()->addDays(5)->setTime(19, 0),
                'end_date' => Carbon::now()->addDays(5)->setTime(21, 0),
                'registration_deadline' => Carbon::now()->addDays(3),
                'is_online' => true,
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
                'requirements' => 'Ù…Ø·Ø¨Ø® Ù…Ø¬Ù‡Ø²ØŒ Ø§ØªØµØ§Ù„ Ø¥Ù†ØªØ±Ù†Øª Ø¬ÙŠØ¯',
                'what_you_will_learn' => 'Ù…Ø¨Ø§Ø¯Ø¦ Ø§Ù„Ø·Ø¨Ø® Ø§Ù„ØµØ­ÙŠØŒ ØªØ­Ø¶ÙŠØ± ÙˆØ¬Ø¨Ø§Øª Ù…ØªÙˆØ§Ø²Ù†Ø©ØŒ Ø§Ø®ØªÙŠØ§Ø± Ø§Ù„Ù…ÙƒÙˆÙ†Ø§Øª Ø§Ù„ØµØ­ÙŠØ©',
                'materials_needed' => 'Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…ÙƒÙˆÙ†Ø§Øª Ø³ØªÙØ±Ø³Ù„ Ù‚Ø¨Ù„ Ø§Ù„ÙˆØ±Ø´Ø©',
                'is_active' => true,
                'is_featured' => false,
                'rating' => 4.6,
                'reviews_count' => 32
            ],
            [
                'title' => 'ÙˆØ±Ø´Ø© ØµÙ†Ø¹ Ø§Ù„Ø®Ø¨Ø² ÙˆØ§Ù„Ù…Ø¹Ø¬Ù†Ø§Øª',
                'description' => 'ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ø®Ø¨Ø² ÙˆØ§Ù„Ù…Ø¹Ø¬Ù†Ø§Øª Ø§Ù„Ø·Ø§Ø²Ø¬Ø© Ù…Ù† Ø§Ù„ØµÙØ±',
                'content' => 'ÙˆØ±Ø´Ø© Ø´Ø§Ù…Ù„Ø© Ù„ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ø®Ø¨Ø² ÙˆØ§Ù„Ù…Ø¹Ø¬Ù†Ø§Øª Ø§Ù„Ø·Ø§Ø²Ø¬Ø©. Ø³Ù†ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ø®Ø¨Ø² Ø§Ù„Ø¹Ø±Ø¨ÙŠØŒ Ø§Ù„ÙØ±Ù†Ø³ÙŠØŒ ÙˆØ§Ù„ÙƒØ±ÙˆØ§Ø³Ø§Ù†ØŒ Ø¨Ø§Ù„Ø¥Ø¶Ø§ÙØ© Ø¥Ù„Ù‰ Ø§Ù„Ù…Ø¹Ø¬Ù†Ø§Øª Ø§Ù„Ø­Ù„ÙˆØ© ÙˆØ§Ù„Ù…Ø§Ù„Ø­Ø©.',
                'instructor' => 'Ø§Ù„Ø´ÙŠÙ Ø®Ø§Ù„Ø¯ Ø§Ù„Ø³Ø¹ÙŠØ¯',
                'instructor_avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&h=150&fit=crop&crop=face',
                'instructor_bio' => 'Ø´ÙŠÙ Ù…ØªØ®ØµØµ ÙÙŠ Ø§Ù„Ø®Ø¨Ø² ÙˆØ§Ù„Ù…Ø¹Ø¬Ù†Ø§Øª Ù…Ø¹ 12 Ø¹Ø§Ù… Ù…Ù† Ø§Ù„Ø®Ø¨Ø±Ø©',
                'category' => 'Ø®Ø¨Ø² ÙˆÙ…Ø¹Ø¬Ù†Ø§Øª',
                'level' => 'Ù…ØªÙˆØ³Ø·',
                'duration' => 180, // 3 Ø³Ø§Ø¹Ø§Øª (Ø§Ù„Ø­Ø¯ Ø§Ù„Ø£Ù‚ØµÙ‰)
                'max_participants' => 10,
                'price' => 180.00,
                'currency' => 'JOD',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
                'images' => [
                    'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
                    'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400&h=300&fit=crop'
                ],
                'location' => 'Ù…Ø·Ø¨Ø® ÙˆØµÙØ© - Ø§Ù„Ø¯Ù…Ø§Ù…',
                'address' => 'Ø´Ø§Ø±Ø¹ Ø§Ù„Ù…Ù„Ùƒ Ø¹Ø¨Ø¯Ø§Ù„Ø¹Ø²ÙŠØ²ØŒ Ø­ÙŠ Ø§Ù„ÙÙŠØµÙ„ÙŠØ©ØŒ Ø§Ù„Ø¯Ù…Ø§Ù…',
                'latitude' => 26.4207,
                'longitude' => 50.0888,
                'start_date' => Carbon::now()->addDays(14)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(14)->setTime(14, 0),
                'registration_deadline' => Carbon::now()->addDays(12),
                'is_online' => false,
                'requirements' => 'Ù„Ø§ ØªÙˆØ¬Ø¯ Ù…ØªØ·Ù„Ø¨Ø§Øª Ø®Ø§ØµØ©',
                'what_you_will_learn' => 'ØµÙ†Ø¹ Ø§Ù„Ø®Ø¨Ø² Ø§Ù„Ø¹Ø±Ø¨ÙŠ ÙˆØ§Ù„ÙØ±Ù†Ø³ÙŠØŒ Ø§Ù„ÙƒØ±ÙˆØ§Ø³Ø§Ù†ØŒ ÙˆØ§Ù„Ù…Ø¹Ø¬Ù†Ø§Øª Ø§Ù„Ù…Ø®ØªÙ„ÙØ©',
                'materials_needed' => 'Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…ÙƒÙˆÙ†Ø§Øª ÙˆØ§Ù„Ø£Ø¯ÙˆØ§Øª Ù…ØªÙˆÙØ±Ø©',
                'is_active' => true,
                'is_featured' => false,
                'rating' => 4.7,
                'reviews_count' => 20
            ],
            [
                'title' => 'ÙˆØ±Ø´Ø© Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„ÙØ±Ù†Ø³ÙŠØ© Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©',
                'description' => 'ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø£Ø´Ù‡Ø± Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„ÙØ±Ù†Ø³ÙŠØ© Ù…Ø¹ ØªÙ‚Ù†ÙŠØ§Øª Ù…ØªÙ‚Ø¯Ù…Ø©',
                'content' => 'ÙˆØ±Ø´Ø© Ù…ØªÙ‚Ø¯Ù…Ø© Ù„ØªØ¹Ù„Ù… ØµÙ†Ø¹ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„ÙØ±Ù†Ø³ÙŠØ© Ø§Ù„ÙƒÙ„Ø§Ø³ÙŠÙƒÙŠØ© Ù…Ø«Ù„ Ø§Ù„Ù…Ø§ÙƒØ§Ø±ÙˆÙ†ØŒ Ø§Ù„Ø¥ÙƒÙ„ÙŠØ±ØŒ ÙˆØ§Ù„ØªØ§Ø±Øª. Ø³Ù†ØªØ¹Ù„Ù… Ø§Ù„ØªÙ‚Ù†ÙŠØ§Øª Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø© ÙˆØ§Ù„Ø¯Ù‚ÙŠÙ‚Ø© Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø© Ù„Ù‡Ø°Ù‡ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª.',
                'instructor' => 'Ø§Ù„Ø´ÙŠÙ Ù…Ø§Ø±ÙŠ ÙƒÙ„ÙŠØ±',
                'instructor_avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face',
                'instructor_bio' => 'Ø´ÙŠÙ ÙØ±Ù†Ø³ÙŠØ© Ù…ØªØ®ØµØµØ© ÙÙŠ Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ù…Ø¹ 18 Ø¹Ø§Ù… Ù…Ù† Ø§Ù„Ø®Ø¨Ø±Ø©',
                'category' => 'Ø­Ù„ÙˆÙŠØ§Øª',
                'level' => 'Ù…ØªÙ‚Ø¯Ù…',
                'duration' => 180, // 3 Ø³Ø§Ø¹Ø§Øª (Ø§Ù„Ø­Ø¯ Ø§Ù„Ø£Ù‚ØµÙ‰)
                'max_participants' => 8,
                'price' => 300.00,
                'currency' => 'JOD',
                'image' => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400&h=300&fit=crop',
                'images' => [
                    'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400&h=300&fit=crop',
                    'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=300&fit=crop'
                ],
                'location' => 'Ù…Ø·Ø¨Ø® ÙˆØµÙØ© - Ø§Ù„Ø±ÙŠØ§Ø¶',
                'address' => 'Ø´Ø§Ø±Ø¹ Ø§Ù„ØªØ­Ù„ÙŠØ©ØŒ Ø­ÙŠ Ø§Ù„Ø¹Ù„ÙŠØ§ØŒ Ø§Ù„Ø±ÙŠØ§Ø¶',
                'latitude' => 24.7136,
                'longitude' => 46.6753,
                'start_date' => Carbon::now()->addDays(21)->setTime(10, 0),
                'end_date' => Carbon::now()->addDays(21)->setTime(16, 0),
                'registration_deadline' => Carbon::now()->addDays(19),
                'is_online' => false,
                'requirements' => 'Ø®Ø¨Ø±Ø© ÙÙŠ Ø§Ù„Ø­Ù„ÙˆÙŠØ§ØªØŒ Ø¯Ù‚Ø© ÙÙŠ Ø§Ù„Ø¹Ù…Ù„',
                'what_you_will_learn' => 'ØµÙ†Ø¹ Ø§Ù„Ù…Ø§ÙƒØ§Ø±ÙˆÙ†ØŒ Ø§Ù„Ø¥ÙƒÙ„ÙŠØ±ØŒ Ø§Ù„ØªØ§Ø±ØªØŒ ÙˆØªÙ‚Ù†ÙŠØ§Øª Ø§Ù„Ø­Ù„ÙˆÙŠØ§Øª Ø§Ù„ÙØ±Ù†Ø³ÙŠØ© Ø§Ù„Ù…ØªÙ‚Ø¯Ù…Ø©',
                'materials_needed' => 'Ù‚Ø§Ø¦Ù…Ø© Ù…ÙØµÙ„Ø© Ø³ØªÙØ±Ø³Ù„ Ù‚Ø¨Ù„ Ø§Ù„ÙˆØ±Ø´Ø©',
                'is_active' => true,
                'is_featured' => true,
                'rating' => 4.9,
                'reviews_count' => 15
            ]
        ];

        foreach ($workshops as $workshop) {
            Workshop::create($workshop);
        }
    }
}

