<?php

namespace App\Pipes;

use App\Http\Resources\Metadata\MetadataResource;
use App\Models\Product;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetProducts
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->validate([
            'category_id' => 'nullable|integer|exists:categories,id',
            'search' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'discounted' => 'nullable|boolean',
        ]);

        $query = Product::query();

        if ($request->filled('category_id')) {
            $query->whereHas('categories', function (Builder $query) use ($request): void {
                $query->where('categories.id', $request->input('category_id'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function (Builder $query) use ($search) {
                $query->where('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ar', 'like', "%{$search}%")
                    ->orWhere('description->en', 'like', "%{$search}%")
                    ->orWhere('description->ar', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('discounted')) {
            $query->whereHas('branchProducts', function ($query) {
                $query->where('discount', '>', 0);
            });
        }

        $result = $query->simplePaginate();

        return $next([
            collect($result->items())
                ->filter->isPublishedInBranches()
                ->map->toApiArray()
                ->values(),
            new MetadataResource(
                $result->perPage(),
                $result->currentPage(),
                $result->hasMorePages()
            ),
        ]);
    }
}
