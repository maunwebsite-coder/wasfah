<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chef\LinkPageUpdateRequest;
use App\Support\BrandAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LinkPageController extends Controller
{
    /**
     * عرض نموذج تعديل صفحة الروابط الخاصة بالشيف.
     */
    public function edit(Request $request)
    {
        $user = $request->user();
        $page = $user->ensureLinkPage()->load([
            'user',
            'items' => static function ($query) {
            $query->orderBy('position');
        }]);

        $upcomingWorkshop = $user->nextUpcomingWorkshop();

        $heroImageUrl = $page->avatar_url ?? BrandAssets::logoAsset('webp');

        $linkPresets = [
            [
                'label' => 'حساب إنستغرام',
                'title' => 'حسابي على إنستغرام',
                'subtitle' => 'تابع قصصي اليومية ووصفاتي السريعة',
                'icon' => 'fab fa-instagram',
                'url' => 'https://www.instagram.com/thedolci.jo/',
            ],
            [
                'label' => 'قناة يوتيوب',
                'title' => 'قناتي على يوتيوب',
                'subtitle' => 'وصفات مصورة خطوة بخطوة',
                'icon' => 'fab fa-youtube',
                'url' => 'https://youtube.com/@',
            ],
            [
                'label' => 'مجتمع واتساب',
                'title' => 'مجتمع واتساب',
                'subtitle' => 'انضم لآخر التحديثات والعروض',
                'icon' => 'fab fa-whatsapp',
                'url' => 'https://wa.me/',
            ],
            [
                'label' => 'صفحة حجز ورش العمل',
                'title' => 'احجز ورشتي القادمة',
                'subtitle' => 'اكتشف الورشات المتاحة الآن',
                'icon' => 'fas fa-calendar-check',
                'url' => 'https://',
            ],
            [
                'label' => 'متجر إلكتروني',
                'title' => 'تسوّق منتجاتي',
                'subtitle' => 'منتجات مختارة بعناية لعشّاق الطهي',
                'icon' => 'fas fa-store',
                'url' => 'https://',
            ],
        ];

        return view('chef.links.edit', [
            'page' => $page,
            'items' => $page->items,
            'heroImageUrl' => $heroImageUrl,
            'publicUrl' => route('links.chef', $page),
            'accentColor' => $page->accent_color ?? '#0819ff',
            'linkPresets' => $linkPresets,
            'upcomingWorkshop' => $upcomingWorkshop,
        ]);
    }

    /**
     * حفظ التعديلات على صفحة الروابط.
     */
    public function update(LinkPageUpdateRequest $request)
    {
        $user = $request->user();
        $page = $user->ensureLinkPage();
        $hasUpcomingWorkshop = (bool) $user->nextUpcomingWorkshop();

        $data = $request->validated();

        // إزالة الصورة الحالية إذا طلب المستخدم ذلك
        if ($request->boolean('remove_hero_image') && $page->hero_image_path) {
            Storage::disk('public')->delete($page->hero_image_path);
            $page->hero_image_path = null;
        }

        // رفع صورة جديدة إن وُجدت
        if ($request->hasFile('hero_image')) {
            if ($page->hero_image_path) {
                Storage::disk('public')->delete($page->hero_image_path);
            }

            $page->hero_image_path = $request->file('hero_image')->store('chef-links', 'public');
        }

        $page->headline = $data['headline'] ?? null;
        $page->subheadline = $data['subheadline'] ?? null;
        $page->bio = $data['bio'] ?? null;
        $page->cta_label = $data['cta_label'] ?? null;
        $page->cta_url = $data['cta_url'] ?? null;
        $page->accent_color = $this->normalizeColor($data['accent_color'] ?? null);
        $page->is_published = $request->boolean('is_published');
        $page->show_upcoming_workshop = $hasUpcomingWorkshop && $request->boolean('show_upcoming_workshop');

        $page->save();

        return back()->with(
            'success',
            'تم تحديث صفحة روابط ' . config('app.name', 'Peahskill') . ' الخاصة بك بنجاح.'
        );
    }

    /**
     * Normalize the accent color string.
     */
    private function normalizeColor(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim(strtolower($value));

        if ($value === '') {
            return null;
        }

        if ($value[0] !== '#') {
            $value = '#' . $value;
        }

        if (!preg_match('/^#[0-9a-f]{3,8}$/', $value)) {
            return null;
        }

        return $value;
    }
}


