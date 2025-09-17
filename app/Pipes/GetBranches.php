<?php

namespace App\Pipes;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GetBranches
{
    public function __invoke(Request $request, Closure $next): Collection
    {
        $request->validate([
            'city_id' => 'required|int|exists:cities,id',
        ]);

        $cityId = $request->input('city_id');

        return $next(Branch::where('city_id', '=', $cityId)->published()->get()->map(function (Branch $branch) {
            return [
                'id' => $branch->id,
                'name' => $branch->title,
            ];
        }));
    }
}
