<?php

namespace App\Pipes;

use App\Models\Category;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetCategories
{
    /**
     * Validate request (level, id).
     * Determine level (default: 1).
     * Build base query using Category::published().
     * If level > 1 and id is provided then filter by ID and return result.
     * Apply level-specific parent relationship filters.
     * Fetch categories.
     * @param Request $request
     * @param Closure $next
     * @return Collection
     */
    public function __invoke(Request $request, Closure $next): Collection
    {
        $request->validate([
            'level' => 'nullable|integer|min:2|max:3',
            'id' => 'nullable|integer|exists:categories,id',
        ]);

        $level = (int) $request->query('level', 1);

        $query = Category::query()->published();

        $id = (int) $request->query('id', 1);
        if ($id && $level > 1) {
            $query->where('id', '=', $id);
        }

        if ($level === 1) {
            $query->whereNull('parent_id');
        } elseif ($level === 2) {
            $query->whereHas('parent', fn (Builder $query): Builder => $query->whereNull('parent_id'));
        } elseif ($level === 3) {
            $query->whereHas('parent.parent', fn (Builder $query): Builder => $query->whereNull('parent_id'));
        }

        $categories = $query->get()->map(function (Category $category): array {
            return [
                'id' => $category->id,
                'title' => $category->title,
                'image' => optional($category->files->first())->url,
                'categories' => $category->children->map(fn (Category $child): array => [
                    'id' => $child->id,
                    'title' => $child->title,
                    'image' => optional($child->files->first())->url,
                ])->toArray(),
            ];
        });

        return $next($categories);
    }
}
