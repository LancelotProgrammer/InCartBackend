<?php

namespace App\Pipes;

use App\Http\Resources\Metadata\MetadataResource;
use App\Models\Scopes\BranchScope;
use Closure;
use Illuminate\Http\Request;

class GetTickets
{
    public function __invoke(Request $request, Closure $next): array
    {
        $tickets = $request->user()->tickets()->withoutGlobalScope(BranchScope::class)->select([
            'id',
            'question',
            'reply',
            'created_at'
        ])->latest()->simplePaginate();

        return $next([
            $tickets->items(),
            new MetadataResource(
                $tickets->perPage(),
                $tickets->currentPage(),
                $tickets->hasMorePages()
            ),
        ]);
    }
}
