<?php

return [
    'meta' => [
        'title' => 'جميع الأدلة - Peahskill',
    ],
    'hero' => [
        'title' => 'كل الأدلة في تجربة عرض واحدة',
        'badge' => [
            'default' => 'مختارات Peahskill',
            'category' => 'تصنيف :category',
            'search' => 'بحث: ":term"',
        ],
        'subtitle' => [
            'default' => 'اكتشف أدلة عملية مختارة من فريق Peahskill.',
            'category' => 'كل ما يتعلق بـ :category في مكان واحد.',
            'search' => 'عرض النتائج المطابقة لعبارة ":term".',
        ],
        'meta_search' => 'بحث: ":term"',
        'latest_unavailable' => 'غير متوفر',
    ],
    'sort' => [
        'created_at' => 'الأحدث',
        'rating' => 'الأعلى تقييماً',
        'saved' => 'الأكثر حفظاً',
    ],
    'difficulty' => [
        'easy' => 'سهل',
        'medium' => 'متوسط',
        'hard' => 'صعب',
    ],
    'stats' => [
        'total' => [
            'label' => 'إجمالي الأدلة',
            'hint' => 'في مكتبة Peahskill',
        ],
        'current' => [
            'label' => 'المعروضة حالياً',
            'hint' => 'من :first إلى :last',
        ],
        'filters' => [
            'label' => 'عوامل التصفية',
            'hint' => [
                'active' => 'إعدادات مفعّلة',
                'default' => 'عرض افتراضي',
            ],
        ],
        'latest' => [
            'label' => 'أحدث إضافة',
            'hint' => 'لأقرب دليل منشور',
        ],
    ],
    'filters' => [
        'search_label' => 'بحث عن دليل',
        'search_placeholder' => 'مثال: قالب موجز منتج',
        'category_label' => 'التصنيفات',
        'sort_label' => 'ترتيب النتائج',
        'all_categories' => 'كل التصنيفات',
        'submit' => 'عرض النتائج',
        'reset' => 'إعادة الضبط',
        'active_label' => 'عوامل فعّالة:',
        'chip_search' => 'بحث: ":term"',
        'chip_category' => 'تصنيف: :category',
        'chip_sort' => 'ترتيب: :label',
    ],
    'cards' => [
        'category_fallback' => 'دليل',
        'fallback_excerpt' => 'تعرّف على خطوات تطبيق هذا الدليل بأسلوب مبسّط وواضح.',
        'prep_time' => ':minutes دقيقة',
        'servings' => '{1} ينتج وحدة واحدة|[2,10] ينتج :count وحدات|[11,*] ينتج :count وحدة',
        'booking_closed' => 'انتهت مهلة الحجز',
        'view_recipe' => 'عرض الدليل',
        'image_fallback_alt' => 'صورة بديلة',
    ],
    'pagination' => [
        'summary' => ':first - :last من :total دليل',
    ],
    'empty' => [
        'title' => 'لم نعثر على نتائج مطابقة',
        'subtitle' => 'جرّب تعديل كلمات البحث أو اختيار تصنيف مختلف للحصول على المزيد من الأدلة. يتم تحديث مكتبتنا باستمرار بأدلة جديدة.',
        'cta' => 'عرض كل الأدلة',
    ],
];
