<?php

return [
    'title' => 'لوحة تحكم الشيف',

    'meta' => [
        'title' => 'ملف :name',
    ],

    'defaults' => [
        'bio' => 'شيف مبدع يشارك وصفاته المميزة مع المجتمع.',
        'specialty_with_area' => 'متخصص في :area',
        'specialty_generic' => 'عضو في المجتمع',
    ],

    'formats' => [
        'date_time' => 'j F Y • h:i a',
        'table_date_time' => 'j M Y · h:i a',
        'date' => 'j F Y',
    ],

    'status' => [
        'labels' => [
            'draft' => 'مسودة',
            'pending' => 'قيد المراجعة',
            'approved' => 'معتمدة',
            'rejected' => 'بحاجة لتعديلات',
        ],
        'descriptions' => [
            'draft' => 'تابع التعديل على الوصفة بشكل خاص قبل الإرسال.',
            'pending' => 'فريق التحرير يراجع تفاصيل الوصفة حالياً.',
            'approved' => 'نُشرت على Peahskill وجاهزة للمشاركة.',
            'rejected' => 'اطّلع على الملاحظات وحدّث الوصفة ثم أرسلها مجدداً.',
        ],
    ],

    'visibility' => [
        'public' => [
            'label' => 'عام',
            'hint' => 'مرئي للجميع على Peahskill.',
        ],
        'private' => [
            'label' => 'رابط خاص',
            'hint' => 'لا يمكن الوصول إليه إلا عبر الرابط الخاص.',
        ],
    ],

    'hero' => [
        'badge' => 'أدوات الشيف',
        'heading' => ':name',
        'description' => 'أدر وصفاتك وورشاتك وأرباحك من مكان واحد.',
        'avatar_alt' => 'صورة :name',
        'stats' => [
            'wasfah_followers' => 'عدد المشتركين',
            'other_platform_followers' => 'مشتركو المنصات الأخرى',
            'recipes' => 'عدد الوصفات',
            'average_rating' => 'متوسط تقييم الورش',
        ],
        'buttons' => [
            'follow' => 'متابعة',
            'following' => 'تمّت المتابعة',
        ],
        'actions' => [
            'public_profile' => 'عرض الملف العام',
            'workshops' => 'إدارة الورشات',
            'earnings' => 'عرض الأرباح',
            'new_recipe' => 'وصفة جديدة',
            'new_workshop' => 'إنشاء ورشة جديدة',
        ],
    ],

    'follow' => [
        'errors' => [
            'self_follow' => 'لا يمكنك متابعة ملفك الشخصي.',
            'generic' => 'تعذر تحديث حالة المتابعة، حاول مرة أخرى.',
        ],
    ],

    'impact' => [
        'badge' => 'نبض المجتمع',
        'title' => 'أثر الشيف',
        'description' => 'وصفات :name تلهم مجتمع Peahskill. إليك نظرة سريعة على تفاعل الجمهور.',
        'highlight_label' => 'الوصفات المنشورة',
        'highlight_hint' => 'أطباق مميزة تمت مشاركتها على Peahskill',
        'cards' => [
            'saves' => [
                'title' => 'مرات الحفظ',
                'hint' => 'عدد المرات التي أضاف فيها المتابعون الوصفات إلى قوائمهم.',
            ],
            'made' => [
                'title' => 'تم التجربة',
                'hint' => 'الأعضاء الذين أبلغوا أنهم حضّروا الوصفة بالفعل.',
            ],
            'reviews' => [
                'title' => 'التقييمات والمراجعات',
                'hint' => 'إجمالي الملاحظات والتقييمات من مجتمع Peahskill.',
            ],
        ],
    ],

    'dashboard' => [
        'workshops' => [
            'meta_title' => 'ورش الشيف الأونلاين',
            'hero' => [
                'eyebrow' => 'منطقة الشيف',
                'title' => 'ورش العمل الخاصة بي',
                'description' => 'أنشئ جلسات أونلاين بسهولة، وشارك رابط Google Meet مع المشاركين بعد الحجز.',
            ],
            'ctas' => [
                'new' => 'إضافة ورشة جديدة',
                'earnings' => 'عوائد الورش',
                'recordings' => 'تسجيلات الورش',
                'recipes' => 'العودة للوصفات',
            ],
            'device_reset' => [
                'badge' => 'تأكيد الجهاز الموثوق',
                'title' => 'إعادة فتح غرفة الورشة: :title',
                'description' => 'لتأمين المشاركين نسمح لجهاز واحد موثوق بفتح غرفة الورشة. أدخل كلمة المرور لإسناد هذا المتصفح.',
                'password_label' => 'كلمة المرور',
                'password_placeholder' => 'أدخل كلمة المرور الحالية',
                'confirm' => 'تأكيد إعادة التعيين',
                'retry' => 'إعادة المحاولة بعد التعيين',
                'footnote' => 'سيتم حفظ جهازك الحالي كمضيف موثوق، وسيتم رفض أي أجهزة أخرى حتى تعيد التعيين.',
                'reasons' => [
                    'missing_cookie' => 'لم يتم العثور على ملف تعريف الجهاز في هذا المتصفح. ربما تم مسح ملفات التخزين أو تم استخدام جهاز جديد.',
                    'cookie_mismatch' => 'رمز الجهاز الحالي لا يطابق الجهاز الموثوق سابقاً.',
                    'fingerprint_mismatch' => 'تم اكتشاف جهاز أو متصفح مختلف عن الجهاز الموثوق.',
                    'manual_reset_validation_failed' => 'تعذر التحقق من كلمة المرور. حاول مرة أخرى للتأكد من هويتك.',
                ],
            ],
            'host_room' => [
                'meta_title' => 'غرفة المضيف - :title',
                'eyebrow' => 'غرفة المضيف',
                'description' => 'هذه الواجهة مخصصة لك كمضيف للتحكم في الورشة الأونلاين. افتح Google Meet عبر الزر التالي وسيتم إعلام المشاركين تلقائياً عند بدء الجلسة.',
                'stats' => [
                    'schedule' => 'موعد الورشة',
                    'attendance' => 'الحضور المؤكد',
                    'status' => 'الحالة',
                ],
                'no_schedule' => 'غير محدد',
                'attendance_value' => '{0} :count مشارك|{1} مشارك واحد|{2} مشاركان|[3,10] :count مشاركين|[11,*] :count مشارك',
                'status_badges' => [
                    'locked' => 'تم القفل',
                    'live' => 'الاجتماع قيد البث',
                    'pending' => 'بانتظار البدء',
                ],
                'meeting' => [
                    'label' => 'رابط Google Meet',
                    'open' => 'فتح Google Meet الآن',
                    'refresh' => 'تحديث الحالة',
                    'secure_label' => 'رابط المضيف الآمن',
                    'secure_help' => 'يفتح Google Meet باستخدام بريد Google المعتمد لديك لتدخل فوراً كمضيف.',
                    'secure_missing' => 'أضف بريد Google الخاص بك من صفحة الملف الشخصي لتفعيل هذا الاختصار.',
                ],
                'participants' => [
                    'eyebrow' => 'آخر المشاركين المؤكدين',
                    'title' => 'المشاركون الجاهزون',
                    'summary' => ':current من أصل :total',
                    'fallback_name' => 'مشارك',
                    'fallback_email' => 'لم يحدد البريد',
                    'last_update' => 'آخر تحديث: :time',
                    'empty' => 'لا يوجد مشاركون مؤكدون بعد. بمجرد تأكيد الحجوزات سيظهرون هنا.',
                ],
                'recording' => [
                    'messages' => [
                        'offline' => 'لا يمكن مزامنة تسجيل لورشة غير أونلاين.',
                        'missing_code' => 'لا يوجد رمز لاجتماع Google Meet للبحث عن التسجيل.',
                        'disabled' => 'خدمة Google Drive غير مفعّلة. لن يتم جلب التسجيلات تلقائياً حتى يتم تفعيل إعدادات Google Drive.',
                        'not_found' => 'لم يتم العثور على تسجيل مرتبط بهذا الاجتماع بعد.',
                        'synced' => 'تم تحديث رابط التسجيل من Google Drive.',
                        'already_synced' => 'تمت مزامنة التسجيل مسبقاً.',
                    ],
                ],
            ],
            'stats' => [
                'total' => 'إجمالي الورش',
                'active' => 'ورش مفعلة',
                'online' => 'ورش أونلاين',
                'drafts' => 'مسودات',
            ],
            'badges' => [
                'delivery' => [
                    'online' => 'أونلاين',
                    'in_person' => 'حضوري',
                ],
                'status' => [
                    'published' => 'منشورة',
                    'draft' => 'مسودة',
                ],
            ],
            'duration' => '• لمدة :minutes دقيقة',
            'capacity' => ':current / :max مشارك',
            'google_card' => [
                'title' => 'جلسة أونلاين عبر Google Meet',
                'description' => 'الرابط مخفي عن الجميع، ويمكن فتح الغرفة من خلال الزر التالي فقط.',
                'launch' => 'فتح Google Meet',
                'launched' => 'تم فتح الغرفة :time',
                'not_ready' => 'لن يتمكن المشتركون من الدخول حتى تفتح غرفة Google Meet.',
                'pending_link' => 'سيتم تفعيل الزر بعد توليد رابط Google Meet.',
            ],
            'actions' => [
                'edit' => 'تعديل',
                'delete' => 'حذف',
                'delete_confirm' => 'هل أنت متأكد من حذف هذه الورشة؟',
                'view_public' => 'عرض صفحة الورشة',
                'pending_public' => 'سيتم عرض الرابط بعد تفعيل الورشة',
            ],
            'empty' => [
                'title' => 'لا توجد ورش بعد',
                'description' => 'ابدأ أول ورشة لك الآن، وسنقوم بتوليد رابط Google Meet فوراً.',
                'cta' => 'إنشاء ورشة',
            ],
        ],


    ],

    'workshops' => [
        'eyebrow' => 'مركز الورشات',
        'title' => 'ورشات :name',
        'description' => 'اكتشف التجارب التعليمية التي يقدمها :name وتعرّف على الورشات القادمة والماضية التي شارك فيها عشاق الطهي.',
        'view_all' => 'استعراض كل الورشات',
        'upcoming' => 'ورشات قادمة',
        'past' => 'ورشات سابقة',
        'latest_workshop' => 'أحدث ورشة: :title',
        'placeholder_text' => 'ورشة',
        'tbd_time' => 'سيتم التحديد لاحقاً',
        'unscheduled_time' => 'موعد غير محدد',
        'location_tbd' => 'سيتم التحديد لاحقاً',
        'online_live' => 'أونلاين مباشر',
        'delivery' => [
            'online_short' => 'أونلاين',
            'in_person_short' => 'حضوري',
        ],
        'levels' => [
            'beginner' => 'مبتدئ',
            'intermediate' => 'متوسط',
            'advanced' => 'متقدم',
        ],
        'level_label' => 'مستوى :level',
        'capacity_with_limit' => ':current / :max مشارك',
        'capacity_open' => ':count مشاركين',
        'date_format' => 'j F Y',
        'datetime_format' => 'j F Y • h:i a',
        'badges' => [
            'open' => 'متاحة للحجز',
            'closed' => 'انتهى التسجيل',
            'completed' => 'انتهت',
        ],
        'image_alt' => 'ورشة :title',
        'with_instructor' => 'مع :name',
        'delivered_by' => 'قدّمها :name',
        'register_until' => 'التسجيل متاح حتى :date',
        'book_now' => 'احجز مقعدك الآن',
        'registration_closed' => 'انتهى التسجيل',
        'view_details' => 'عرض التفاصيل',
        'stats' => [
            'total' => [
                'title' => 'إجمالي الورشات',
                'hint' => 'كل الجلسات التي أنشأتها.',
            ],
            'active' => [
                'title' => 'ورشات نشطة',
                'hint' => 'مباشرة الآن أو مفتوحة للحجز.',
            ],
            'online' => [
                'title' => 'ورشات أونلاين',
                'hint' => 'تُقدّم عبر الإنترنت.',
            ],
            'upcoming' => [
                'title' => 'ورشات قادمة',
                'hint' => 'تم تأكيد موعدها القادم.',
            ],
        ],
        'buttons' => [
            'create' => 'إنشاء ورشة',
            'manage' => 'إدارة الورشات',
        ],
        'next' => [
            'heading' => 'الورشات القادمة',
            'limit' => 'عرض حتى :count ورشة',
            'quick_join' => 'انضمام سريع',
            'fallback_time' => 'سيتم تحديد الوقت لاحقاً',
            'open_room' => 'فتح الغرفة المباشرة',
        ],
        'labels' => [
            'online' => 'أونلاين',
            'onsite' => 'حضوري',
        ],
        'host_status' => [
            'live' => 'البث مباشر الآن',
            'online_upcoming' => 'الغرفة الافتراضية جاهزة',
            'onsite' => 'ورشة حضورية',
            'live_since' => 'مباشر منذ :time',
        ],
        'sections' => [
            'host' => 'حالة المضيف',
            'participants' => 'المشاركون',
        ],
        'participant_status' => [
            'online_live' => 'المشاركون متصلون',
            'online_waiting' => 'المشاركون بانتظار البدء',
            'onsite' => 'المشاركون في الموقع',
        ],
        'participant_hints' => [
            'online_live' => 'الجميع داخل الغرفة الآن، أبقِ الرابط في متناولك.',
            'online_waiting' => 'سيدخل المشاركون فور فتح الغرفة.',
            'onsite' => 'شارك تعليمات الوصول والتسجيل في الموقع.',
        ],
        'items' => [
            'participants_count' => '{0} لا يوجد مشاركون بعد|{1} مشارك واحد|{2} مشاركان|[3,10] :count مشاركين|[11,*] :count مشاركاً',
            'capacity_count' => '{1} مقعد واحد كحد أقصى|{2} مقعدان كحد أقصى|[3,10] :count مقاعد كحد أقصى|[11,*] :count مقعداً كحد أقصى',
            'capacity_unlimited' => 'سعة غير محدودة',
            'participant_link' => 'فتح رابط الاجتماع',
            'participant_link_restricted' => 'الرابط مخفي حفاظاً على الخصوصية',
            'location_value' => 'الموقع: :location',
            'location_missing' => 'سيتم مشاركة الموقع بعد التأكيد.',
            'edit_details' => 'تعديل تفاصيل الورشة',
            'no_upcoming' => 'لا توجد ورشات قادمة حالياً.',
            'plan_next' => 'خطط للورشة التالية',
        ],
    ],
    'recordings' => [
        'eyebrow' => 'مكتبة الورشات',
        'title' => 'تسجيلات الورش السابقة',
        'description' => 'استعرض ورش :name المكتملة وشاهد تسجيل كل ورشة مباشرة من هنا.',
        'view_all' => 'عرض مكتبة التسجيلات',
        'button' => 'ورشات مسجلة',
        'fallback_drive' => 'التسجيل متوفر عبر Google Drive',
        'badges' => [
            'available' => 'التسجيل جاهز للمشاهدة',
            'drive' => 'رابط خارجي',
        ],
        'cta' => [
            'watch' => 'مشاهدة التسجيل',
        ],
        'library_label' => 'مكتبة Google Drive',
        'drive_default_description' => 'تسجيل متاح ضمن مكتبة التسجيلات.',
        'updated_unknown' => 'آخر تحديث غير معروف',
        'untitled' => 'تسجيل بدون عنوان',
        'drive_warning' => [
            'title' => 'خدمة Google Drive غير مفعّلة',
            'description' => 'لن يتم جلب التسجيلات تلقائياً حتى يتم تفعيل إعدادات Google Drive.',
        ],
        'missing_recording' => 'لم يتم العثور على تسجيل حتى الآن. جرّب مزامنة الرابط من Google Drive.',
        'sync_button' => 'تحديث الرابط من Google Drive',
        'empty' => [
            'title' => 'لا توجد تسجيلات حتى الآن',
            'description' => 'بعد انتهاء الورشات ومزامنة التسجيل سيتم عرضه هنا.',
        ],
    ],

    'workshops_earnings' => [
        'title' => 'عوائد ورش العمل',
        'hero' => [
            'eyebrow' => 'منطقة الشيف',
            'heading' => 'عوائد ورش العمل',
            'description' => 'راقب الأداء المالي لورشاتك وتابع صافي الدخل الفعلي الذي تم تخصيصه لك بعد الموافقة.',
            'cta' => [
                'new' => 'إطلاق ورشة جديدة',
                'back' => 'العودة لقائمة الورش',
            ],
        ],
        'notice' => [
            'eyebrow' => 'معلومة مهمة',
            'title' => 'سيتم خصم 15% – 20% لصالح منصّة Peahskill',
            'description' => 'عند إنشاء أي ورشة جديدة يتم اقتطاع نسبة 15% إلى 20% لتغطية عمليات الدفع، الدعم التقني والتسويق. يتم تحويل باقي المبلغ لك خلال أسبوع من انتهاء الورشة بعد تسوية المدفوعات.',
        ],
        'stats' => [
            'gross' => [
                'label' => 'إجمالي المبالغ المحصّلة',
                'hint' => 'من جميع الحجوزات المدفوعة',
            ],
            'net' => [
                'label' => 'الصافي المصروف لك',
                'hint' => 'يعتمد على التقسيم المالي المُعتمد بعد الموافقة',
            ],
            'paid_seats' => [
                'label' => 'عدد المقاعد المدفوعة',
                'hint' => 'يشمل جميع الورش المكتملة',
            ],
            'average' => [
                'label' => 'متوسط الصافي لكل مشارك',
                'hint' => 'يعتمد على المدفوعات المؤكدة فقط',
            ],
        ],
        'monthly' => [
            'current' => [
                'label' => 'عوائد هذا الشهر',
                'delta' => ':amount مقارنةً بالشهر الماضي',
                'hint' => 'يتم التحديث بمجرد تأكيد دفع المشاركين',
                'net_label' => 'الصافي المصروف',
            ],
            'previous' => [
                'label' => 'عوائد الشهر الماضي',
                'hint' => 'للمقارنة التاريخية فقط',
                'net_label' => 'الصافي المصروف',
            ],
        ],
        'leaderboard' => [
            'eyebrow' => 'أفضل الورش أداءً',
            'title' => 'تفصيل حسب كل ورشة',
            'description' => 'صافي فعلي بعد التقسيم الآلي للمدفوعات.',
            'button' => 'إدارة الورش',
            'table' => [
                'workshop' => 'الورشة',
                'start_date' => 'تاريخ البداية',
                'paid' => 'المشاركون المدفوعون',
                'gross' => 'إجمالي المدفوع',
                'net' => 'الصافي المصروف',
            ],
            'capacity' => 'سعة :capacity',
            'capacity_unknown' => 'غير محددة',
            'date_pending' => 'لم يحدد بعد',
            'empty' => 'لا توجد بيانات مالية بعد. ابدأ بإنشاء ورشة وتأكيد الحجوزات المدفوعة.',
        ],
    ],

    'workshops_create' => [
        'page_title' => 'إطلاق ورشة جديدة',
        'hero_badge' => 'منطقة الشيف',
        'hero_heading' => 'إطلاق ورشة أونلاين جديدة',
        'hero_description' => 'بمجرد الحفظ سنجهز لك رابط Google Meet قابل للمشاركة مع الحضور.',
        'back_to_list' => 'العودة لقائمة الورش',
        'validation_heading' => 'يرجى مراجعة الحقول التالية:',
        'draft_notice' => 'سيتم حفظ الورشة في حسابك ويمكنك تعديلها أو نشرها متى شئت.',
        'submit_label' => 'حفظ الورشة',
    ],

    'workshop_form' => [
        'currencies' => [
            'usd' => 'دولار أمريكي',
            'jod' => 'دينار أردني',
        ],
        'sections' => [
            'basics' => [
                'eyebrow' => 'المعلومات الأساسية',
                'title' => 'تفاصيل الورشة',
                'description' => 'عرّف المتعلمين على مضمون الورشة وسبب تميزها.',
            ],
            'pricing' => [
                'eyebrow' => 'التسعير والقدرة الاستيعابية',
                'title' => 'الإعدادات المالية',
                'description' => 'أخبر المشاركين بعدد المقاعد المتاحة لتشجيع التسجيل المبكر.',
            ],
            'schedule' => [
                'eyebrow' => 'الجدولة',
                'title' => 'المواعيد',
                'description' => 'تظهر هذه المواعيد للمتدربين فور نشر الورشة.',
            ],
            'delivery' => [
                'eyebrow' => 'طريقة التقديم',
                'title' => 'أونلاين عبر Google Meet',
                'description' => 'نساعدك على إنشاء رابط اجتماع Google Meet آمن خلال ثوانٍ.',
                'highlight' => 'ننشئ غرفة Google Meet مباشرة من حسابك في Google لتبقى أنت المضيف.',
                'host_account_label' => 'حساب المضيف',
                'host_account_missing' => 'أضف حساب Google الخاص بك ليتم إنشاء الاجتماع من تقويمك كمضيف.',
            ],
            'host' => [
                'eyebrow' => 'معلوماتك',
                'title' => 'تعريف بالمدرب',
            ],
            'image' => [
                'eyebrow' => 'صورة الورشة',
                'title' => 'إبهار بصري',
                'description' => 'حمّل صوراً عالية الجودة (حتى 25 ميجابايت) لتحفيز الحجز.',
                'preview_placeholder' => 'ستظهر المعاينة هنا بعد رفع الصورة.',
                'preview_alt' => 'معاينة غلاف الورشة',
            ],
            'publish' => [
                'eyebrow' => 'نشر الورشة',
                'title' => 'جاهزة للظهور؟',
                'auto_activate' => 'تفعيل الورشة فوراً بعد الحفظ',
            ],
        ],
        'fields' => [
            'title' => [
                'label' => 'عنوان الورشة *',
            ],
            'category' => [
                'label' => 'فئة الورشة *',
            ],
            'level' => [
                'label' => 'المستوى *',
            ],
            'duration' => [
                'label' => 'المدة (بالدقائق) *',
            ],
            'description' => [
                'label' => 'وصف ملهم *',
            ],
            'content' => [
                'label' => 'تفاصيل المحتوى (اختياري)',
            ],
            'learning_points' => [
                'label' => 'ماذا سيتعلم المشاركون؟',
            ],
            'requirements' => [
                'label' => 'متطلبات مسبقة',
            ],
            'price' => [
                'label' => 'سعر الورشة *',
            ],
            'currency' => [
                'label' => 'العملة *',
            ],
            'max_participants' => [
                'label' => 'الحد الأقصى للمشاركين *',
            ],
            'start_date' => [
                'label' => 'تاريخ البداية *',
            ],
            'end_date' => [
                'label' => 'تاريخ النهاية *',
            ],
            'registration_deadline' => [
                'label' => 'آخر موعد للتسجيل',
            ],
            'host_timezone' => [
                'label' => 'ما هي منطقتك الزمنية؟ *',
                'helper' => 'اكتشفنا :detected من جهازك. سيتم تحويل جميع المواعيد بناءً على هذه المنطقة الزمنية.',
                'unknown' => 'منطقتك الزمنية الحالية',
            ],
            'meeting_link' => [
                'label' => 'رابط الاجتماع',
            ],
            'recording_url' => [
                'label' => 'رابط التسجيل على Google Drive',
                'helper' => 'انسخ رابط "المشاهدة" من Google Drive بصيغة ‎/file/d/‎ وسيتم تحويله تلقائياً ليعمل داخل الصفحة العامة.',
            ],
            'location' => [
                'label' => 'الموقع *',
            ],
            'address' => [
                'label' => 'العنوان التفصيلي',
            ],
            'instructor' => [
                'label' => 'اسم المدرب',
            ],
            'instructor_bio' => [
                'label' => 'نبذة قصيرة',
            ],
            'image' => [
                'label' => 'رفع صورة رئيسية',
            ],
            'passcode' => [
                'label' => 'رمز الدخول:',
            ],
        ],
        'placeholders' => [
            'category' => 'مثل: مخبوزات، أطباق رئيسية، حلويات...',
            'description' => 'حدثنا عن أهداف الورشة، أسلوبك في الشرح، والقيمة التي سيخرج بها المشاركون.',
            'content' => 'قسّم المحتوى التفصيلي أو خطة الجلسة بشكل مرتب ليسهل قراءته.',
            'learning_points' => 'اذكر 3-5 نقاط تعلم واضحة سيخرج بها المشاركون.',
            'requirements' => 'وضح المهارات أو الأدوات المطلوبة قبل الانضمام.',
        ],
        'options' => [
            'online_label' => 'هذه الورشة أونلاين',
            'online_hint' => 'هذه الورشة أونلاين دائماً ولا يمكن تحويلها إلى حضورية.',
            'auto_generate_label' => 'توليد رابط Google Meet تلقائياً عند الحفظ',
        ],
        'buttons' => [
            'generate_link' => 'توليد رابط الآن',
            'remove_image' => 'حذف الصورة الحالية',
        ],
        'messages' => [
            'pricing_notice_title' => 'هام:',
            'pricing_notice_body' => 'يتم اقتطاع نسبة تتراوح بين :fee_range لصالح منصة Peahskill لتغطية بوابات الدفع، الدعم التقني والتسويق.',
            'pricing_notice_followup' => 'بعد خصم هذه النسبة يتم تحويل الصافي إليك خلال 7 أيام عمل من انتهاء الورشة.',
            'registration_deadline_notice' => 'سيتم إغلاق التسجيل تلقائياً قبل بداية الورشة بدقيقتين وفقاً لتوقيت المضيف.',
            'meeting_hint_auto' => 'نوفر لك روابط Google Meet تلقائياً من حسابك، فقط أضف حساب الـ Gmail الخاص بالمضيف وسنربطه.',
            'meeting_hint_manual' => 'يمكنك لصق رابط اجتماع جاهز إن رغبت.',
            'mobile_launch_title' => 'افتح في تطبيق Google Meet',
            'mobile_launch_body' => 'استخدم هذه الأزرار لفتح الرابط مباشرة داخل تطبيق Meet على جوالك.',
            'mobile_launch_android' => 'فتح على أندرويد',
            'mobile_launch_ios' => 'فتح على iOS',
            'google_ready' => 'تم تهيئة رابط Google Meet:',
            'managed_link_title' => 'الرابط يُدار من فريق Peahskill',
            'managed_link_description' => 'سنقوم بتوليد رابط الاجتماع وتأمينه تلقائياً بعد حفظ الورشة، ولن نظهر الرابط الخام في لوحة الشيف حفاظاً على السرية.',
            'calendar_connect_required_title' => 'يرجى ربط Google Calendar أولاً',
            'calendar_connect_required_body' => 'يرجى ربط حساب Google Calendar الخاص بك لإنشاء رابط الاجتماع من حسابك.',
            'calendar_connect_button' => 'ربط تقويم Google',
            'calendar_connected_title' => 'تم ربط Google Calendar',
            'calendar_connected_body' => 'سنقوم بتوليد رابط Google Meet من تقويمك وإضافته تلقائياً.',
            'calendar_connected_body_with_email' => 'سنقوم بتوليد رابط Google Meet من تقويمك (:email) وإضافته تلقائياً.',
            'image_max_size' => 'لا يمكن رفع صورة أكبر من 25 ميجابايت.',
        ],
        'js' => [
            'title_required' => 'يرجى إدخال عنوان الورشة أولاً.',
            'generate_failed' => 'تعذر توليد الرابط حالياً.',
            'generic_error' => 'حدث خطأ غير متوقع.',
        ],
    ],

    'recipes_create' => [
        'page_title' => 'إنشاء وصفة جديدة | استوديو Peahskill',
        'hero_badge' => 'استوديو Peahskill',
        'hero_heading' => 'شارك وصفة جديدة على Peahskill',
        'hero_description' => 'صِف القصة والمذاق والخطوات لننشرها بهويتك على Peahskill.',
        'back_to_index' => 'العودة إلى وصفاتي',
        'validation_heading' => 'راجع الحقول التالية قبل الإرسال:',
        'actions' => [
            'save_draft' => 'حفظ كمسودة',
            'submit_review' => 'إرسال للمراجعة في Peahskill',
        ],
    ],

    'recipe_form' => [
        'sections' => [
            'basics' => [
                'title' => 'معلومات الوصفة الأساسية',
                'description' => 'املأ تفاصيل الوصفة ليتمكن الفريق من مراجعتها بسرعة.',
            ],
            'steps' => [
                'title' => 'خطوات التحضير',
                'description' => 'أضف خطوات واضحة ومتسلسلة حتى تكون الوصفة سهلة المتابعة.',
                'add_button' => 'إضافة خطوة',
                'placeholder' => 'وصف الخطوة',
            ],
            'ingredients' => [
                'title' => 'المكونات',
                'description' => 'أدرج المكونات والكميات بشكل واضح.',
                'add_button' => 'إضافة مكون',
                'name_label' => 'اسم المكون',
                'name_placeholder' => 'مثال: دقيق متعدد الاستخدامات',
                'amount_label' => 'الكمية',
                'amount_placeholder' => 'مثال: كوبان',
                'remove_button' => 'إزالة',
            ],
            'tools' => [
                'title' => 'الأدوات المقترحة (اختياري)',
            ],
            'media' => [
                'title' => 'صور الوصفة',
                'description' => 'يمكنك رفع حتى 5 صور بجودة عالية لإبراز جمال الوصفة.',
                'primary' => 'الصورة الرئيسية',
                'additional' => 'صورة إضافية :number',
                'max_size_message' => 'لا يمكن رفع صورة أكبر من 25 ميجابايت.',
                'max_size_hint' => 'الحد الأقصى لحجم الصورة هو 25 ميجابايت.',
                'current_alt' => 'صورة الوصفة',
                'remove_current' => 'إزالة هذه الصورة',
            ],
            'external_image' => [
                'title' => 'رابط صورة خارجي (اختياري)',
                'description' => 'إذا كان لديك رابط مباشر للصورة (مثل Google Drive أو Unsplash) يمكنك إضافته هنا.',
                'placeholder' => 'https://example.com/your-image.jpg',
            ],
            'video' => [
                'title' => 'رابط فيديو يوتيوب (اختياري)',
                'description' => 'الصق أي رابط لفيديو على يوتيوب (Watch أو Shorts أو رابط المشاركة) وسنحوّله تلقائياً لصيغة قابلة للعرض داخل صفحة الوصفة.',
                'placeholder' => 'https://www.youtube.com/watch?v=example',
                'hint' => 'سيظهر الفيديو للزوار داخل صفحة الوصفة بعد حفظه.',
            ],
        ],
        'fields' => [
            'title' => [
                'label' => 'عنوان الوصفة *',
            ],
            'description' => [
                'label' => 'وصف مختصر للوصفة',
                'placeholder' => 'شارك قصة الوصفة أو نصائح التقديم',
            ],
            'category' => [
                'label' => 'التصنيف',
                'placeholder' => 'اختر التصنيف',
            ],
            'difficulty' => [
                'label' => 'مستوى الصعوبة',
                'placeholder' => 'اختر المستوى',
                'options' => [
                    'easy' => 'سهل',
                    'medium' => 'متوسط',
                    'hard' => 'صعب',
                ],
            ],
            'visibility' => [
                'label' => 'حالة الظهور',
                'options' => [
                    'public' => [
                        'label' => 'عام',
                        'description' => 'تظهر الوصفة لكل الزوار بعد اعتمادها',
                    ],
                    'private' => [
                        'label' => 'خاص',
                        'description' => 'تظل الوصفة مخفية عن الزوار حتى وإن كانت معتمدة',
                    ],
                ],
            ],
            'prep_time' => [
                'label' => 'مدة التحضير (بالدقائق)',
            ],
            'cook_time' => [
                'label' => 'مدة الطهي (بالدقائق)',
            ],
            'servings' => [
                'label' => 'عدد الحصص',
            ],
        ],
    ],

    'recipes' => [
        'tabs' => [
            'public' => '',
            'exclusive' => '',
        ],
        'public_empty' => '',
        'exclusive_empty' => 'لم تضف وصفات خاصة بعد. شارك وصفاتك الحصرية هنا لتكون مرجعك الشخصي.',
        'category_fallback' => 'وصفة',
        'no_rating' => 'لا يوجد تقييم بعد',
        'saves' => ':count حفظ',
        'likes' => ':count إعجاب',
        'made' => ':count جرّبوا الوصفة',
        'ratings' => ':count تقييم للورش',
        'rated_workshops_average' => 'متوسط :value من :count ورشة تم تقييمها',
        'private_tag' => 'وصفة خاصة',
        'private_details' => 'خطوات تفصيلية مميزة',
        'private_access' => 'مشاهدة خاصة',
        'view_recipe' => 'عرض الوصفة',
    ],

    'link_page' => [
        'title' => 'صفحة روابط وصفة',
        'description' => 'شارك رابطاً واحداً يجمع وصفاتك وورشاتك وروابط الحجز.',
        'features' => [
            'customize' => 'تصميم مخصص',
            'instant_updates' => 'تحديثات فورية',
            'shareable' => 'رابط قابل للمشاركة',
        ],
        'actions' => [
            'manage' => 'تخصيص الصفحة',
            'view' => 'عرض الرابط العام',
        ],
    ],

    'empty_state' => [
        'title' => 'لا توجد وصفات بعد',
        'description' => 'أضف أول وصفة لتظهر في هذه القائمة.',
        'cta' => 'إنشاء وصفة',
    ],

    'table' => [
        'headers' => [
            'recipe' => 'الوصفة',
            'status' => 'الحالة',
            'visibility' => 'الظهور',
            'updated_at' => 'آخر تحديث',
            'category' => 'التصنيف',
            'actions' => 'الإجراءات',
        ],
        'approved_on' => 'تمت الموافقة بتاريخ :date',
        'no_category' => 'بدون تصنيف',
        'actions' => [
            'edit' => 'تعديل',
            'submit_for_review' => 'إرسال للمراجعة',
            'delete_confirm' => 'هل أنت متأكد من حذف هذه الوصفة؟ لا يمكن التراجع عن ذلك.',
            'delete' => 'حذف',
        ],
    ],

    'popular' => [
        'title' => 'وصفات الشيف الأكثر مشاهدة',
        'subtitle' => 'استكشف أبرز الوصفات التي خطفت قلوب عشاق الطهي والمتابعين.',
        'view_details' => 'اكتشف التفاصيل',
    ],
];
