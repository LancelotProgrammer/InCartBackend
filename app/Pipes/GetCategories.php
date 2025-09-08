<?php

namespace App\Pipes;

use App\Models\Category;
use Closure;
use Illuminate\Http\Request;

class GetCategories
{
    public function __invoke(Request $request, Closure $next)
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
            $query->whereHas('parent', fn ($q) => $q->whereNull('parent_id'));
        } elseif ($level === 3) {
            $query->whereHas('parent.parent', fn ($q) => $q->whereNull('parent_id'));
        }

        $categories = $query->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'title' => $category->title,
                'categories' => $category->children->map(fn ($child) => [
                    'id' => $child->id,
                    'title' => $child->title,
                ])->toArray(),
            ];
        });

        return $next($categories);
    }
}
