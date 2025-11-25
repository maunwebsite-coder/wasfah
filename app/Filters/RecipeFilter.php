<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipeFilter
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query)
    {
        $this->search($query);
        $this->category($query);
        $this->difficulty($query);
        $this->prepTime($query);
        $this->sort($query);

        return $query;
    }

    protected function search(Builder $query)
    {
        if ($this->request->filled('search')) {
            $searchTerm = $this->request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('author', 'like', "%{$searchTerm}%");
            });
        }
    }

    protected function category(Builder $query)
    {
        if ($this->request->filled('category')) {
            $query->whereHas('category', function ($q) {
                $q->where('category_id', $this->request->get('category'));
            });
        }
    }

    protected function difficulty(Builder $query)
    {
        if ($this->request->filled('difficulty')) {
            $query->where('difficulty', $this->request->get('difficulty'));
        }
    }

    protected function prepTime(Builder $query)
    {
        if ($this->request->filled('prep_time')) {
            $prepTime = $this->request->get('prep_time');
            switch ($prepTime) {
                case 'quick':
                    $query->where('prep_time', '<=', 30);
                    break;
                case 'medium':
                    $query->whereBetween('prep_time', [31, 60]);
                    break;
                case 'long':
                    $query->where('prep_time', '>', 60);
                    break;
            }
        }
    }

    protected function sort(Builder $query)
    {
        $sortBy = $this->request->get('sort', 'random_latest');
        $sortDirection = $this->request->get('direction', 'desc');
        $seed = $this->request->integer('seed');
        $randomSeed = $seed ?: random_int(1, 999999);
        $recentWindowDays = 21;
        $driver = DB::getDriverName();

        $randomOrder = match ($driver) {
            'pgsql', 'sqlite' => 'RANDOM()',
            default => 'RAND(' . $randomSeed . ')',
        };

        switch ($sortBy) {
            case 'helpful':
                $query->orderByRaw(sprintf(
                    '(COALESCE(saved_count, 0) * 1.4) + (COALESCE(made_count, 0) * 1.8) + (COALESCE(interactions_avg_rating, 0) * 10) %s',
                    $sortDirection === 'asc' ? 'asc' : 'desc'
                ));
                $query->orderBy('created_at', 'desc');
                break;
            case 'trending':
                // Trending: Most interactions in the last 7 days
                $query->withCount(['interactions as recent_interactions_count' => function ($q) {
                    $q->where('created_at', '>=', now()->subDays(7));
                }])
                ->orderBy('recent_interactions_count', 'desc')
                ->orderBy('created_at', 'desc');
                break;
            case 'title':
                $query->orderBy('title', $sortDirection);
                break;
            case 'prep_time':
                $query->orderBy('prep_time', $sortDirection);
                break;
            case 'rating':
                $query->orderBy('interactions_avg_rating', $sortDirection);
                break;
            case 'saved':
                $query->orderBy('saved_count', $sortDirection);
                break;
            case 'random_latest':
                $recentCutoff = now()->subDays($recentWindowDays);

                // Keep the newest recipes ahead while shuffling within buckets
                $query->orderByRaw('CASE WHEN created_at >= ? THEN 0 ELSE 1 END', [$recentCutoff]);
                $query->orderByRaw($randomOrder);
                $query->orderBy('created_at', 'desc');
                break;
            case 'random':
                $query->orderByRaw($randomOrder);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
        }
    }
}
