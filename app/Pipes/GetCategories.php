<?php

namespace App\Pipes;

use App\Models\Category;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetCategories
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'level' => 'nullable|integer|min:2|max:3',
            'id' => 'nullable|integer|exists:categories,id',
        ]);

        $level = (int) $request->query('level', 1);

        $query = Category::query();

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
                'categories' => $category->children->map(fn (Category $child): array => [
                    'id' => $child->id,
                    'title' => $child->title,
                ])->toArray(),
            ];
        });

        return $next($categories);
    }
}
