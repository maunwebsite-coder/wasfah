<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ingredients')->truncate();

        DB::table('ingredients')->insert([
            // 1. كرواسان الزبدة المورّق على البارد
            ['recipe_id' => 1, 'name' => 'دقيق قوي', 'quantity' => '500 جرام', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 1, 'name' => 'زبدة باردة 82%', 'quantity' => '300 جرام', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 1, 'name' => 'خميرة فورية', 'quantity' => '10 جرام', 'created_at' => now(), 'updated_at' => now()],

            // 2. سلطة بوراتا بالشمندر المشوي والجرجير
            ['recipe_id' => 2, 'name' => 'شمندر طازج', 'quantity' => '3 حبات', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 2, 'name' => 'جرجير', 'quantity' => '2 حزمة', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 2, 'name' => 'جبنة بوراتا', 'quantity' => '1 كرة كبيرة', 'created_at' => now(), 'updated_at' => now()],

            // 3. رامن ميزو بالدجاج والخضار الموسمية
            ['recipe_id' => 3, 'name' => 'مرق دجاج', 'quantity' => '1.5 لتر', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 3, 'name' => 'معجون ميزو', 'quantity' => '3 ملاعق كبيرة', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 3, 'name' => 'نودلز رامن طازجة', 'quantity' => '300 جرام', 'created_at' => now(), 'updated_at' => now()],

            // 4. بريسكيت تاكو بصلصة مانجو حارة
            ['recipe_id' => 4, 'name' => 'لحم بريسكيت', 'quantity' => '1 كيلوجرام', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 4, 'name' => 'تورتيلا ذرة', 'quantity' => '12 قطعة', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 4, 'name' => 'مانجو ناضجة', 'quantity' => '2 حبة', 'created_at' => now(), 'updated_at' => now()],

            // 5. سلمون مشوي بقشرة أعشاب الليمون
            ['recipe_id' => 5, 'name' => 'فيليه سلمون بجلده', 'quantity' => '2 قطعة', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 5, 'name' => 'قشر ليمون', 'quantity' => 'ملعقة كبيرة', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 5, 'name' => 'شبت طازج', 'quantity' => '2 ملاعق كبيرة', 'created_at' => now(), 'updated_at' => now()],

            // 6. تارت الليمون المحروق مع مرنغ إيطالي
            ['recipe_id' => 6, 'name' => 'عجينة بريزيه', 'quantity' => '1 قرص جاهز', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 6, 'name' => 'عصير ليمون طازج', 'quantity' => '200 مل', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 6, 'name' => 'بياض بيض', 'quantity' => '4', 'created_at' => now(), 'updated_at' => now()],

            // 7. باستا بستو الفستق والكريمة المخففة
            ['recipe_id' => 7, 'name' => 'باستا بيني', 'quantity' => '400 جرام', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 7, 'name' => 'فستق محمص', 'quantity' => '120 جرام', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 7, 'name' => 'كريمة طبخ خفيفة', 'quantity' => '200 مل', 'created_at' => now(), 'updated_at' => now()],

            // 8. برجر نباتي بالفاصوليا السوداء والشيا
            ['recipe_id' => 8, 'name' => 'فاصوليا سوداء مطبوخة', 'quantity' => '400 جرام', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 8, 'name' => 'شوفان مطحون', 'quantity' => '1 كوب', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 8, 'name' => 'بذور شيا', 'quantity' => '2 ملاعق كبيرة', 'created_at' => now(), 'updated_at' => now()],

            // 9. بودينغ الأرز بحليب جوز الهند والهيل
            ['recipe_id' => 9, 'name' => 'أرز مصري قصير', 'quantity' => '1 كوب', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 9, 'name' => 'حليب جوز الهند', 'quantity' => '400 مل', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 9, 'name' => 'هيل مطحون', 'quantity' => 'نصف ملعقة صغيرة', 'created_at' => now(), 'updated_at' => now()],

            // 10. شيا بودنغ بالتوت الأزرق واللافندر
            ['recipe_id' => 10, 'name' => 'بذور شيا', 'quantity' => '6 ملاعق كبيرة', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 10, 'name' => 'حليب لوز غير محلى', 'quantity' => '500 مل', 'created_at' => now(), 'updated_at' => now()],
            ['recipe_id' => 10, 'name' => 'توت أزرق', 'quantity' => '1 كوب', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
