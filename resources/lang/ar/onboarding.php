<?php

return [
    'page_title' => 'تفعيل حساب الخبير',
    'header' => [
        'title' => '3 خطوات للتفعيل',
        'subtitle' => 'تواصل • حضور رقمي (اختياري) • نبذة (اختيارية).',
        'chips' => [
            'instant' => 'فوري',
            'secure' => 'محمي',
            'spotlight' => 'ظهور',
        ],
        'badge_tip' => 'تفعل الآن؛ أضف النبذة وقتما تشاء',
        'signed_in_as' => 'تم تسجيل الدخول بواسطة :email',
    ],
    'steps' => [
        [
            'title' => 'تواصل',
            'body' => 'دولة + رقم.',
        ],
        [
            'title' => 'حضور رقمي (اختياري)',
            'body' => 'إنستغرام أو يوتيوب (اختياري).',
        ],
        [
            'title' => 'نبذة (اختياري)',
            'body' => 'أضف أو تجاوز.',
        ],
    ],
    'checklist' => [
        'title' => 'تذكير سريع',
        'items' => [
            'رقم يعمل للرسائل أو واتساب.',
            'إضافة رابط عام (اختياري).',
        ],
    ],
    'sections' => [
        'contact' => [
            'title' => 'بيانات التواصل الأساسية',
            'hint' => 'سيصلُك كل ما يتعلق بالحجوزات والتحديثات على هذا الرقم وفق الدولة المختارة.',
            'country_label' => 'الدولة *',
            'country_placeholder' => 'اختر دولتك',
            'phone_label' => 'رقم الجوال *',
            'phone_placeholder' => 'مثال: 5XXXXXXXX',
            'phone_note' => 'سنستخدم هذا الرقم لتأكيد الحجوزات وإرسال التنبيهات.',
            'google_email_label' => 'بريد Google المستَخدم في Meet *',
            'google_email_hint' => 'اكتب بريد Google الذي ستدخل به إلى اجتماعات Google Meet للجلسات لضمان دخولك فوراً كمضيف.',
        ],
        'social' => [
            'title' => 'الحضور على منصات التواصل',
            'hint' => 'اختياري: أضف إنستغرام أو يوتيوب لتسهيل التحقق منك. يمكنك التخطي إن لم يتوفر.',
            'instagram_label' => 'رابط حساب إنستغرام (اختياري)',
            'youtube_label' => 'رابط قناة يوتيوب (اختياري)',
            'required_error' => 'روابط إنستغرام أو يوتيوب اختيارية.',
            'public_notice' => 'إن أضفت رابطاً، تأكد أنه متاح للعرض العام لتسريع المراجعة.',
        ],
        'bio' => [
            'title' => 'نبذة تعريفية (اختياري)',
            'hint' => 'ساعدنا على عرضك بشكل أفضل. يمكنك تجاوز هذه الخطوة وإكمالها لاحقاً.',
            'specialty_label' => 'التخصص الرئيسي (اختياري)',
            'specialty_placeholder' => 'اختر تخصصك (يمكن تجاوزه)',
            'specialty_options' => [
                'food' => 'Food & culinary arts',
                'saudi_cuisine' => 'Saudi cuisine & Khaleeji flavors',
                'middle_eastern' => 'Middle Eastern classics',
                'italian' => 'Italian pasta & sauces',
                'french_pastry' => 'French pastry & viennoiserie',
                'bread_bakery' => 'Artisan bread & bakery',
                'desserts' => 'Desserts & plated sweets',
                'coffee_beverages' => 'Coffee, tea & specialty drinks',
                'grilling' => 'Grilling, BBQ & smoking',
                'seafood' => 'Seafood & fish cookery',
                'vegan' => 'Vegan & plant-based',
                'healthy_meal_prep' => 'Healthy meal prep',
                'kids_family' => 'Family & kids-friendly cooking',
                'asian_fusion' => 'Asian fusion & street food',
                'mexican_latin' => 'Mexican & Latin flavors',
            ],
            'specialty_food' => 'مجال الطعام والطبخ',
            'description_label' => 'نبذة عنك (اختيارية)',
            'description_placeholder' => 'اكتب بإيجاز ما تقدمه أو اتركها فارغة الآن.',
        ],
    ],
    'alerts' => [
        'post_submit' => 'يتفعل حسابك فوراً؛ النبذة يمكن تعديلها لاحقاً.',
    ],
    'submit' => [
        'cta' => 'تفعيل حساب الخبير الآن',
        'time_notice' => 'ثلاث خطوات خفيفة—تنتهي بثوانٍ.',
    ],
    'navigation' => [
        'back' => 'السابق',
        'next' => 'التالي',
        'skip_bio' => 'تخطي النبذة الآن',
        'step_status' => 'خطوة :current من :total',
    ],
];
