<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Recipe;
use App\Models\Workshop;
use App\Models\Tool;
use App\Support\ImageUploadConstraints;
use App\Support\VideoEmbed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    /**
     * Display the chef recipe dashboard.
     */
    public function index()
    {
        $chefId = Auth::id();

        $recipes = Recipe::with('category')
            ->where('user_id', $chefId)
            ->orderByDesc('updated_at')
            ->paginate(10);

        $statusCounts = Recipe::select('status', DB::raw('count(*) as total'))
            ->where('user_id', $chefId)
            ->groupBy('status')
            ->pluck('total', 'status');

        $workshopBaseQuery = Workshop::query()->where('user_id', $chefId);

        $workshopAggregate = (clone $workshopBaseQuery)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN is_online = 1 THEN 1 ELSE 0 END) as online')
            ->selectRaw('SUM(CASE WHEN start_date >= ? THEN 1 ELSE 0 END) as upcoming', [now()])
            ->first();

        $workshopStats = [
            'total' => (int) ($workshopAggregate->total ?? 0),
            'active' => (int) ($workshopAggregate->active ?? 0),
            'online' => (int) ($workshopAggregate->online ?? 0),
            'upcoming' => (int) ($workshopAggregate->upcoming ?? 0),
        ];

        $upcomingWorkshops = (clone $workshopBaseQuery)
            ->withCount([
                'bookings as confirmed_bookings' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->whereNotNull('start_date')
            ->where('start_date', '>=', now()->subDay())
            ->orderBy('start_date')
            ->take(3)
            ->get();

        $latestWorkshop = (clone $workshopBaseQuery)
            ->orderByDesc('created_at')
            ->first();

        return view('chef.recipes.index', [
            'recipes' => $recipes,
            'statusCounts' => $statusCounts,
            'workshopStats' => $workshopStats,
            'upcomingWorkshops' => $upcomingWorkshops,
            'latestWorkshop' => $latestWorkshop,
        ]);
    }

    /**
     * Show the form for creating a new recipe.
     */
    public function create()
    {
        $this->ensureAdminOnly();

        $categories = Category::orderBy('name')->get();
        $tools = Tool::where('is_active', true)->orderBy('name')->get();

        return view('chef.recipes.create', compact('categories', 'tools'));
    }

    /**
     * Store a newly created recipe in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdminOnly();

        $data = $this->validateRecipe($request);

        $status = $request->input('submit_action') === 'submit'
            ? Recipe::STATUS_PENDING
            : Recipe::STATUS_DRAFT;

        $imagePaths = $this->handleImages($request);

        $recipe = Recipe::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'author' => Auth::user()->name,
            'prep_time' => $data['prep_time'] ?? null,
            'cook_time' => $data['cook_time'] ?? null,
            'servings' => $data['servings'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'image_url' => $this->cleanImageUrl($data['image_url'] ?? null),
            'video_url' => $this->normalizeVideoUrl($data['video_url'] ?? null),
            'image' => $imagePaths['image'],
            'image_2' => $imagePaths['image_2'],
            'image_3' => $imagePaths['image_3'],
            'image_4' => $imagePaths['image_4'],
            'image_5' => $imagePaths['image_5'],
            'category_id' => $data['category_id'] ?? null,
            'steps' => $this->normalizeSteps($data['steps'] ?? []),
            'tools' => $this->normalizeTools($data['tools'] ?? []),
            'user_id' => Auth::id(),
            'status' => $status,
            'visibility' => $data['visibility'] ?? Recipe::VISIBILITY_PUBLIC,
            'approved_at' => null,
        ]);

        $this->syncIngredients($recipe, $data['ingredients'] ?? []);

        return redirect()
            ->route('chef.recipes.index')
            ->with(
                'success',
                $status === Recipe::STATUS_PENDING
                    ? 'تم إرسال الوصفة للمراجعة. سنقوم بإشعارك بعد الموافقة عليها.'
                    : 'تم حفظ الوصفة كمسودة. يمكنك تعديلها أو إرسالها للمراجعة لاحقاً.'
            );
    }

    /**
     * Show the form for editing the specified recipe.
     */
    public function edit(Recipe $recipe)
    {
        $this->authorizeRecipe($recipe);

        $categories = Category::orderBy('name')->get();
        $tools = Tool::where('is_active', true)->orderBy('name')->get();
        $recipe->load('ingredients');

        return view('chef.recipes.edit', compact('recipe', 'categories', 'tools'));
    }

    /**
     * Update the specified recipe in storage.
     */
    public function update(Request $request, Recipe $recipe): RedirectResponse
    {
        $this->authorizeRecipe($recipe);

        $data = $this->validateRecipe($request, $recipe->recipe_id);

        $requestedAction = $request->input('submit_action', 'draft');
        $status = $requestedAction === 'submit'
            ? Recipe::STATUS_PENDING
            : Recipe::STATUS_DRAFT;

        // إذا كانت الوصفة معتمدة وتم تعديلها، نعيدها للحالة المعلقة للمراجعة
        if ($recipe->status === Recipe::STATUS_APPROVED && $requestedAction === 'draft') {
            $status = Recipe::STATUS_DRAFT;
        } elseif ($recipe->status === Recipe::STATUS_APPROVED && $requestedAction === 'submit') {
            $status = Recipe::STATUS_PENDING;
        }

        $removeImages = $request->input('remove_images', []);
        $imagePaths = $this->handleImages($request, [
            'image' => $recipe->image,
            'image_2' => $recipe->image_2,
            'image_3' => $recipe->image_3,
            'image_4' => $recipe->image_4,
            'image_5' => $recipe->image_5,
        ], $removeImages);

        $recipe->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'author' => Auth::user()->name,
            'prep_time' => $data['prep_time'] ?? null,
            'cook_time' => $data['cook_time'] ?? null,
            'servings' => $data['servings'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'image_url' => $this->cleanImageUrl($data['image_url'] ?? null),
            'video_url' => $this->normalizeVideoUrl($data['video_url'] ?? null),
            'image' => $imagePaths['image'],
            'image_2' => $imagePaths['image_2'],
            'image_3' => $imagePaths['image_3'],
            'image_4' => $imagePaths['image_4'],
            'image_5' => $imagePaths['image_5'],
            'category_id' => $data['category_id'] ?? null,
            'steps' => $this->normalizeSteps($data['steps'] ?? []),
            'tools' => $this->normalizeTools($data['tools'] ?? []),
            'status' => $status,
            'visibility' => $data['visibility'] ?? Recipe::VISIBILITY_PUBLIC,
            'approved_at' => $status === Recipe::STATUS_APPROVED ? $recipe->approved_at : null,
        ]);

        $this->syncIngredients($recipe, $data['ingredients'] ?? []);

        return redirect()
            ->route('chef.recipes.index')
            ->with(
                'success',
                $status === Recipe::STATUS_PENDING
                    ? 'تم تحديث الوصفة وإرسالها للمراجعة.'
                    : 'تم تحديث الوصفة وحفظها كمسودة.'
            );
    }

    /**
     * Remove the specified recipe from storage.
     */
    public function destroy(Recipe $recipe): RedirectResponse
    {
        $this->authorizeRecipe($recipe);

        if (!in_array($recipe->status, [Recipe::STATUS_DRAFT, Recipe::STATUS_REJECTED], true)) {
            return redirect()
                ->route('chef.recipes.index')
                ->with('error', 'لا يمكن حذف الوصفات المعتمدة أو قيد المراجعة.');
        }

        $this->deleteImages($recipe);
        $recipe->ingredients()->delete();
        $recipe->delete();

        return redirect()
            ->route('chef.recipes.index')
            ->with('success', 'تم حذف الوصفة بنجاح.');
    }

    /**
     * Submit a draft recipe for approval.
     */
    public function submit(Recipe $recipe): RedirectResponse
    {
        $this->authorizeRecipe($recipe);

        if (!in_array($recipe->status, [Recipe::STATUS_DRAFT, Recipe::STATUS_REJECTED], true)) {
            return redirect()
                ->route('chef.recipes.index')
                ->with('error', 'لا يمكن إعادة إرسال هذه الوصفة في الوقت الحالي.');
        }

        $recipe->update([
            'status' => Recipe::STATUS_PENDING,
            'approved_at' => null,
        ]);

        return redirect()
            ->route('chef.recipes.index')
            ->with('success', 'تم إرسال الوصفة للمراجعة. سنقوم بإشعارك بعد الموافقة.');
    }

    /**
     * Validate the recipe form request.
     */
    private function validateRecipe(Request $request, ?int $recipeId = null): array
    {
        $imageRule = array_merge(['nullable'], ImageUploadConstraints::rules());

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'prep_time' => ['nullable', 'integer', 'min:0'],
            'cook_time' => ['nullable', 'integer', 'min:0'],
            'servings' => ['nullable', 'integer', 'min:0'],
            'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard'])],
            'image_url' => ['nullable', 'url'],
            'video_url' => ['nullable', 'url'],
            'image' => $imageRule,
            'image_2' => $imageRule,
            'image_3' => $imageRule,
            'image_4' => $imageRule,
            'image_5' => $imageRule,
            'steps' => ['nullable', 'array'],
            'steps.*' => ['nullable', 'string', 'max:1000'],
            'tools' => ['nullable', 'array'],
            'tools.*' => ['nullable', 'exists:tools,id'],
            'ingredients' => ['nullable', 'array'],
            'ingredients.*.name' => ['nullable', 'string', 'max:255'],
            'ingredients.*.amount' => ['nullable', 'string', 'max:255'],
            'visibility' => ['required', Rule::in([Recipe::VISIBILITY_PUBLIC, Recipe::VISIBILITY_PRIVATE])],
            'submit_action' => ['required', Rule::in(['draft', 'submit'])],
        ];

        $messages = array_merge(
            ImageUploadConstraints::messagesFor([
                'image' => [
                    'ar' => 'الصورة الرئيسية',
                    'en' => 'primary image',
                ],
                'image_2' => [
                    'ar' => 'الصورة الثانية',
                    'en' => 'image 2',
                ],
                'image_3' => [
                    'ar' => 'الصورة الثالثة',
                    'en' => 'image 3',
                ],
                'image_4' => [
                    'ar' => 'الصورة الرابعة',
                    'en' => 'image 4',
                ],
                'image_5' => [
                    'ar' => 'الصورة الخامسة',
                    'en' => 'image 5',
                ],
            ]),
            [
                'submit_action.in' => 'حدث خطأ في اختيار حالة الحفظ، يرجى المحاولة مرة أخرى.',
            ]
        );

        return $request->validate($rules, $messages);
    }

    /**
     * Ensure the recipe belongs to the current user or admin.
     */
    private function authorizeRecipe(Recipe $recipe): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        abort_if(!$user->isChef(), 403, 'يجب اعتمادك كشيف للوصول إلى هذه الصفحة.');
        abort_if($recipe->user_id !== $user->id, 403, 'ليس لديك صلاحية للوصول إلى هذه الوصفة.');
    }

    /**
     * Restrict recipe creation to administrators only.
     */
    private function ensureAdminOnly(): void
    {
        abort_if(!Auth::user()?->isAdmin(), 403, 'هذه الصفحة مخصصة للمسؤولين فقط.');
    }

    /**
     * Normalize steps array.
     */
    private function normalizeSteps(array $steps): array
    {
        $steps = array_map(static fn ($step) => is_string($step) ? trim($step) : $step, $steps);
        $steps = array_filter($steps, static fn ($step) => !empty($step));

        return array_values($steps);
    }

    /**
     * Normalize tools array to hold numeric IDs.
     */
    private function normalizeTools(array $tools): array
    {
        $tools = array_map('intval', array_filter($tools));
        return array_values($tools);
    }

    /**
     * Sync recipe ingredients with provided list.
     */
    private function syncIngredients(Recipe $recipe, array $ingredients): void
    {
        $recipe->ingredients()->delete();

        foreach ($ingredients as $ingredient) {
            $name = isset($ingredient['name']) ? trim($ingredient['name']) : '';
            $quantity = isset($ingredient['amount']) ? trim($ingredient['amount']) : '';

            if ($name === '' && $quantity === '') {
                continue;
            }

            $recipe->ingredients()->create([
                'name' => $name,
                'quantity' => $quantity,
            ]);
        }
    }

    /**
     * Handle image uploads and replacements.
     */
    private function handleImages(Request $request, array $existing = [], array $remove = []): array
    {
        $imageFields = ['image', 'image_2', 'image_3', 'image_4', 'image_5'];
        $paths = [];

        foreach ($imageFields as $field) {
            $currentPath = $existing[$field] ?? null;

            if (in_array($field, $remove, true)) {
                if ($currentPath) {
                    Storage::disk('public')->delete($currentPath);
                }
                $currentPath = null;
            }

            if ($request->hasFile($field)) {
                if ($currentPath) {
                    Storage::disk('public')->delete($currentPath);
                }

                $currentPath = $request->file($field)->store('recipes', 'public');
            }

            $paths[$field] = $currentPath;
        }

        return $paths;
    }

    /**
     * Remove stored images for a recipe.
     */
    private function deleteImages(Recipe $recipe): void
    {
        foreach (['image', 'image_2', 'image_3', 'image_4', 'image_5'] as $field) {
            if ($recipe->{$field}) {
                Storage::disk('public')->delete($recipe->{$field});
            }
        }
    }

    /**
     * Clean Google/GDrive image URLs.
     */
    private function cleanImageUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (str_contains($url, 'google.com/url')) {
            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $query);
                if (isset($query['url'])) {
                    return urldecode($query['url']);
                }
            }
        }

        if (str_contains($url, 'drive.google.com/file/d/')) {
            if (preg_match('/\/file\/d\/([a-zA-Z0-9-_]+)/', $url, $matches) && isset($matches[1])) {
                return 'https://lh3.googleusercontent.com/d/' . $matches[1];
            }
        }

        if (str_contains($url, 'drive.google.com') && str_contains($url, 'id=')) {
            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $query);
                if (isset($query['id'])) {
                    return 'https://lh3.googleusercontent.com/d/' . $query['id'];
                }
            }
        }

        return $url;
    }

    /**
     * Normalize supported video URLs into embeddable formats.
     */
    private function normalizeVideoUrl(?string $url): ?string
    {
        return VideoEmbed::normalize($url);
    }
}
