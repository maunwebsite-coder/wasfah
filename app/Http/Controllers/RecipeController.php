<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use App\Support\VideoEmbed;

class RecipeController extends Controller
{
    /**
     * Display a listing of recipes.
     */
    public function index(Request $request, \App\Filters\RecipeFilter $filters): View
    {
        $seed = $request->integer('seed');
        $randomSeed = $seed ?: random_int(1, 999999);

        // Build base query
        $query = Recipe::approved()
            ->public()
            ->whereNotNull('video_url')
            ->whereRaw("TRIM(video_url) <> ''")
            ->with(['category', 'interactions', 'chef'])
            ->withCount(['interactions as saved_count' => function ($query) {
                $query->where('is_saved', true);
            }])
            ->withCount(['interactions as made_count' => function ($query) {
                $query->where('is_made', true);
            }])
            ->withCount('interactions')
            ->withAvg('interactions', 'rating');

        // Apply filters
        $query->filter($filters);

        // Prepare pagination params
        $appendQuery = $request->except('page');
        $sortBy = $request->get('sort', 'random_latest');
        $isRandomSort = in_array($sortBy, ['random', 'random_latest'], true);

        if ($isRandomSort) {
            $appendQuery['seed'] = $randomSeed;
        } else {
            unset($appendQuery['seed']);
        }

        $recipes = $query
            ->paginate(12)
            ->appends($appendQuery);

        if (auth()->check()) {
            $user = auth()->user();
            $userInteractions = $user->interactions()
                ->whereIn('recipe_id', $recipes->pluck('recipe_id'))
                ->get()
                ->keyBy('recipe_id');

            $recipes->getCollection()->transform(function ($recipe) use ($userInteractions) {
                $interaction = $userInteractions->get($recipe->recipe_id);
                $recipe->is_saved = (bool) optional($interaction)->is_saved;
                $recipe->is_made = (bool) optional($interaction)->is_made;
                $recipe->user_rating = optional($interaction)->rating;
                return $recipe;
            });
        } else {
            $recipes->getCollection()->transform(function ($recipe) {
                $recipe->is_saved = false;
                $recipe->is_made = false;
                $recipe->user_rating = null;
                return $recipe;
            });
        }

        // Get categories for filter dropdown
        $categories = Category::orderBy('name')->get();

        return view('recipes', compact('recipes', 'categories'));
    }

    /**
     * API endpoint to get latest recipes for homepage
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $recipes = Recipe::approved()
                ->public()
                ->with(['category', 'interactions', 'chef'])
                ->withCount(['interactions as saved_count' => function ($query) {
                    $query->where('is_saved', true);
                }])
                ->withAvg('interactions', 'rating')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            // Add is_saved status for authenticated users
            if (auth()->check()) {
                $user = auth()->user();
                $savedRecipes = $user->interactions()
                    ->where('is_saved', true)
                    ->pluck('recipe_id')
                    ->toArray();

                $recipes->each(function ($recipe) use ($savedRecipes) {
                    $recipe->is_saved = in_array($recipe->recipe_id, $savedRecipes);
                });
            } else {
                $recipes->each(function ($recipe) {
                    $recipe->is_saved = false;
                });
            }

            return response()->json($recipes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch recipes'], 500);
        }
    }

    /**
     * Display a specific recipe (Blade template)
     */
    public function show(Recipe $recipe): View
    {
        try {
            $canViewPrivate = auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $recipe->user_id);

            // Restrict special recipe page to admins only
            if ($recipe->slug === 'new-video-1' && !(auth()->check() && auth()->user()->isAdmin())) {
                return redirect()->route('home');
            }

            if ((!$recipe->isApproved() || $recipe->isPrivate()) && !$canViewPrivate) {
                abort(404);
            }

            $recipe->load(['category', 'interactions', 'ingredients', 'chef']);
            $recipe->loadMissing(['workshops.chef']);
            
            // Load counts and averages
            $recipe->loadCount(['interactions as saved_count' => function ($query) {
                $query->where('is_saved', true);
            }]);
            
            $recipe->loadCount(['interactions as made_count' => function ($query) {
                $query->where('is_made', true);
            }]);
            
            $recipe->loadAvg('interactions', 'rating');

            // Add is_saved status for authenticated users
            if (auth()->check()) {
                $user = auth()->user();
                $userInteraction = $user->interactions()
                    ->where('recipe_id', $recipe->recipe_id)
                    ->first();
                
                $recipe->is_saved = $userInteraction ? (bool)$userInteraction->is_saved : false;
                $recipe->is_made = $userInteraction ? (bool)$userInteraction->is_made : false;
                $recipe->user_rating = $userInteraction ? $userInteraction->rating : null;
            } else {
                $recipe->is_saved = false;
                $recipe->is_made = false;
                $recipe->user_rating = null;
            }

            // Ensure steps is properly formatted as array
            if (is_string($recipe->steps)) {
                $recipe->steps = json_decode($recipe->steps, true) ?? [];
            }
            if (!is_array($recipe->steps)) {
                $recipe->steps = [];
            }

            // Ensure tools is properly formatted as array
            if (is_string($recipe->tools)) {
                $recipe->tools = json_decode($recipe->tools, true) ?? [];
            }
            if (!is_array($recipe->tools)) {
                $recipe->tools = [];
            }
            
            // تحويل IDs إلى بيانات كاملة للمعدات
            if (!empty($recipe->tools)) {
                $toolIds = array_filter($recipe->tools, 'is_numeric');
                if (!empty($toolIds)) {
                    $tools = \App\Models\Tool::whereIn('id', $toolIds)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
                    $recipe->tools = $tools;
                } else {
                    $recipe->tools = collect([]);
                }
            } else {
                $recipe->tools = collect([]);
            }
            
            // Ensure tools is always a collection for consistency
            if (is_array($recipe->tools)) {
                $recipe->tools = collect($recipe->tools);
            }

            if (!$recipe->isApproved()) {
                if (!auth()->check() || (!auth()->user()->isAdmin() && auth()->id() !== $recipe->user_id)) {
                    abort(404);
                }
            }

            // Get random recipes from the same category (excluding current recipe)
            $relatedRecipes = Recipe::approved()
                ->public()
                ->with(['category', 'interactions'])
                ->withCount(['interactions as saved_count' => function ($query) {
                    $query->where('is_saved', true);
                }])
                ->withAvg('interactions', 'rating')
                ->where('category_id', $recipe->category_id)
                ->where('recipe_id', '!=', $recipe->recipe_id)
                ->inRandomOrder()
                ->limit(6)
                ->get();

            // Add is_saved status for authenticated users for related recipes
            if (auth()->check()) {
                $user = auth()->user();
                $savedRecipes = $user->interactions()
                    ->where('is_saved', true)
                    ->pluck('recipe_id')
                    ->toArray();

                $relatedRecipes->each(function ($relatedRecipe) use ($savedRecipes) {
                    $relatedRecipe->is_saved = in_array($relatedRecipe->recipe_id, $savedRecipes);
                });
            } else {
                $relatedRecipes->each(function ($relatedRecipe) {
                    $relatedRecipe->is_saved = false;
                });
            }

            return view('recipe', compact('recipe', 'relatedRecipes'));
        } catch (\Exception $e) {
            \Log::error('Recipe show error: ' . $e->getMessage());
            abort(500, 'Failed to fetch recipe');
        }
    }

    /**
     * API endpoint to get a specific recipe
     */
    public function apiShow(Recipe $recipe): JsonResponse
    {
        try {
            $canViewPrivate = auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $recipe->user_id);

            if ((!$recipe->isApproved() || $recipe->isPrivate()) && !$canViewPrivate) {
                return response()->json(['message' => 'Recipe not found'], 404);
            }

            $recipe->load(['category', 'interactions', 'ingredients', 'chef']);
            
            // Load counts and averages
            $recipe->loadCount(['interactions as saved_count' => function ($query) {
                $query->where('is_saved', true);
            }]);
            
            $recipe->loadCount(['interactions as made_count' => function ($query) {
                $query->where('is_made', true);
            }]);
            
            $recipe->loadAvg('interactions', 'rating');

            // Add is_saved status for authenticated users
            if (auth()->check()) {
                $user = auth()->user();
                $userInteraction = $user->interactions()
                    ->where('recipe_id', $recipe->recipe_id)
                    ->first();
                
                $recipe->is_saved = $userInteraction ? (bool)$userInteraction->is_saved : false;
                $recipe->is_made = $userInteraction ? (bool)$userInteraction->is_made : false;
                $recipe->user_rating = $userInteraction ? $userInteraction->rating : null;
            } else {
                $recipe->is_saved = false;
                $recipe->is_made = false;
                $recipe->user_rating = null;
            }

            // Ensure steps is properly formatted as array
            if (is_string($recipe->steps)) {
                $recipe->steps = json_decode($recipe->steps, true) ?? [];
            }
            if (!is_array($recipe->steps)) {
                $recipe->steps = [];
            }

            // Ensure tools is properly formatted as array
            if (is_string($recipe->tools)) {
                $recipe->tools = json_decode($recipe->tools, true) ?? [];
            }
            if (!is_array($recipe->tools)) {
                $recipe->tools = [];
            }

            // تحويل IDs إلى بيانات كاملة للمعدات
            if (!empty($recipe->tools)) {
                $toolIds = array_filter($recipe->tools, 'is_numeric');
                if (!empty($toolIds)) {
                    $tools = \App\Models\Tool::whereIn('id', $toolIds)
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get()
                        ->toArray();
                    $recipe->tools = $tools;
                } else {
                    $recipe->tools = [];
                }
            } else {
                $recipe->tools = [];
            }

            // إضافة معلومات إضافية للتأكد من وصول البيانات
            $responseData = $recipe->toArray();
            $responseData['interactions_avg_rating'] = $recipe->interactions_avg_rating;
            $responseData['made_count'] = $recipe->made_count;
            $responseData['saved_count'] = $recipe->saved_count;
            
            return response()->json($responseData);
        } catch (\Exception $e) {
            \Log::error('Recipe show API error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch recipe: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Store a quick post (recipe) from the feed composer.
     */
    public function storeQuickPost(Request $request): JsonResponse
    {
        if (!auth()->user()?->isAdmin()) {
            return response()->json(['message' => 'غير مصرح لك بالنشر'], 403);
        }

        try {
            $type = $request->input('type');

            $rules = [
                'type' => 'required|in:article,photo,video',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|max:25600', // 25MB max
                'video_url' => 'nullable|url',
            ];

            if ($type === 'article') {
                $rules['description'] = 'required|string';
            } elseif ($type === 'photo') {
                $rules['image'] = 'required|image|max:25600';
            } elseif ($type === 'video') {
                $rules['video_url'] = 'required|url';
                $rules['description'] = 'required|string';
            }

            $validated = $request->validate($rules);

            $recipe = new Recipe();
            $recipe->user_id = auth()->id();
            $recipe->status = Recipe::STATUS_APPROVED; // Auto-approve for now, or use PENDING based on policy
            $recipe->visibility = Recipe::VISIBILITY_PUBLIC;
            
            // Set title based on type if not provided
            if (empty($validated['title'])) {
                $recipe->title = match($validated['type']) {
                    'photo' => 'New Photo',
                    'video' => 'New Video',
                    default => 'New Post',
                };
            } else {
                $recipe->title = $validated['title'];
            }

            $recipe->description = $validated['description'] ?? '';
            $recipe->author = auth()->user()?->name ?? 'Community Member';
            $recipe->video_url = VideoEmbed::normalize($validated['video_url'] ?? null);
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('recipes', 'public');
                $recipe->image = $path;
            }

            // Set default category (e.g., "General" or "Community")
            // You might want to make this dynamic or set a specific ID
            $recipe->category_id = Category::first()?->id ?? null; 

            $recipe->save();

            return response()->json([
                'message' => 'Post created successfully',
                'redirect' => route('recipes')
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'تحقق من البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Quick post error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create post'], 500);
        }
    }
}
