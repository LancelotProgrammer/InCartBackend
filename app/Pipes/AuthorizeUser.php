<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use Closure;
use Illuminate\Http\Request;

class AuthorizeUser
{
    public function __construct(
        private string $ability,
        private array $arguments = []
    ) {}

    public function __invoke(Request $request, Closure $next): array
    {
        $user = $request->user();

        // if (! $user->can($this->ability, $this->arguments)) {
        //     throw new LogicalException(
        //         'Not authorized',
        //         "You cannot perform [{$this->ability}]",
        //         403
        //     );
        // }

        return $next($request);
    }
}
