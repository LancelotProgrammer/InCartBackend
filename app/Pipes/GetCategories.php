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
            'level' => 'nullable|integer|min:1|max:3',
        ]);

        $level = (int) $request->query('level', 1);

        $query = Category::query()->with('children');

        if ($level === 1) {
            $query->whereNull('parent_id');
        } elseif ($level === 2) {
            $query->whereHas('parent', fn($q) => $q->whereNull('parent_id'));
        } elseif ($level === 3) {
            $query->whereHas('parent.parent', fn($q) => $q->whereNull('parent_id'));
        }

        $categories = $query->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'title' => $category->title,
                'categories' => $category->children->map(fn($child) => [
                    'id' => $child->id,
                    'title' => $child->title,
                ])->toArray(),
            ];
        });

        return $next($categories);
    }
}
