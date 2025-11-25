<?php

namespace App\Http\Controllers\Api;

use App\Models\UserInteraction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class UserInteractionController extends Controller
{
    /**
     * إضافة أو تعديل تفاعل المستخدم مع وصفة
     * - إذا كان هناك صف موجود (user_id + recipe_id) يعدله
     * - إذا ما في صف، ينشئ صف جديد
     */
    public function store(Request $request)
    {
        // التحقق من صحة المدخلات
        $request->validate([
            'recipe_id' => 'required|exists:recipes,recipe_id',
            'is_saved'  => 'nullable|boolean',
            'is_made'   => 'nullable|boolean',
            'rating'    => 'nullable|integer|min:1|max:5',
        ]);
    
        $userId = Auth::id();
        
        $data = [];

        // نتحقق من وجود كل حقل قبل إضافته للبيانات
        if ($request->has('is_saved')) {
            $data['is_saved'] = (bool)$request->is_saved;
        }
        
        if ($request->has('is_made')) {
            $data['is_made'] = (bool)$request->is_made;
        }
        
        if ($request->has('rating')) {
            $data['rating'] = $request->rating;
        }

        // إنشاء أو تحديث التفاعل
        $interaction = UserInteraction::updateOrCreate(
            [
                'user_id'   => $userId,
                'recipe_id' => $request->recipe_id,
            ],
            $data
        );
    
        return response()->json([
            'success' => true,
            'message' => $interaction->is_saved
                ? __('home.alerts.save_success')
                : __('home.alerts.save_removed'),
            'is_saved' => (bool) $interaction->is_saved,
            'is_made' => (bool) $interaction->is_made,
            'rating' => $interaction->rating,
            'interaction' => $interaction,
        ], 200);
    }

    /**
     * جلب كل التفاعلات الخاصة بوصفة معينة
     */
    public function recipeInteractions($recipe_id)
    {
        $interactions = UserInteraction::where('recipe_id', $recipe_id)
            ->with('user')
            ->get();

        return response()->json($interactions);
    }

    /**
     * جلب كل التفاعلات الخاصة بمستخدم معين
     * - يظهر كل الوصفات اللي تفاعل معها المستخدم
     */
    public function userInteractions()
    {
        $interactions = UserInteraction::where('user_id', Auth::id())->get();
        return response()->json($interactions);
    }

    /**
     * إلغاء تقييم المستخدم لوصفة معينة
     */
    public function removeRating(Request $request)
    {
        // التحقق من صحة المدخلات
        $request->validate([
            'recipe_id' => 'required|exists:recipes,recipe_id',
        ]);

        $userId = Auth::id();
        
        // البحث عن التفاعل الموجود
        $interaction = UserInteraction::where('user_id', $userId)
            ->where('recipe_id', $request->recipe_id)
            ->first();

        if (!$interaction) {
            return response()->json([
                'success' => false,
                'message' => 'لا يوجد تقييم لإلغائه'
            ], 404);
        }

        // إزالة التقييم فقط (ترك الحفظ والجربت كما هي)
        $interaction->rating = null;
        $interaction->save();

        return response()->json([
            'success' => true,
            'message' => 'تم إلغاء التقييم بنجاح'
        ]);
    }
}
