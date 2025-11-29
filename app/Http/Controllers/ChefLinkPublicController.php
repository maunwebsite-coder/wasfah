<?php

namespace App\Http\Controllers;

use App\Models\ChefLinkPage;
use Illuminate\Http\Request;

class ChefLinkPublicController extends Controller
{
    /**
     * عرض صفحة روابط Wasfah الخاصة بالشيف.
     */
    public function show(Request $request, ChefLinkPage $chefLinkPage)
    {
        $user = $request->user();

        if (!$chefLinkPage->is_published && (!$user || ($user->id !== $chefLinkPage->user_id && !$user->isAdmin()))) {
            abort(404);
        }

        $chefLinkPage->load([
            'user',
            'items' => static function ($query) use ($user, $chefLinkPage) {
                if (!$user || ($user->id !== $chefLinkPage->user_id && !$user->isAdmin())) {
                    $query->where('is_active', true);
                }

                $query->orderBy('position');
            },
        ]);

        $accentColor = $chefLinkPage->accent_color ?: '#0819ff';
        $upcomingWorkshop = null;

        if ($chefLinkPage->show_upcoming_workshop) {
            $upcomingWorkshop = $chefLinkPage->user?->nextUpcomingWorkshop();
        }

        return view('chef-links.show', [
            'page' => $chefLinkPage,
            'accentColor' => $accentColor,
            'upcomingWorkshop' => $upcomingWorkshop,
        ]);
    }
}

