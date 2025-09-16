<?php

namespace App\Pipes;

use App\Exceptions\LogicalException;
use App\Models\UserAddress;
use Closure;
use Illuminate\Http\Request;

class DeleteUserAddress
{
    public function __invoke(Request $request, Closure $next): array
    {
        $request->merge(['id' => $request->route('id')]);

        $validated = $request->validate([
            'id' => 'required|exists:user_addresses,id',
        ]);

        $address = UserAddress::where('id', $validated['id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            throw new LogicalException('Address not found or does not belong to the user.');
        }

        $address->delete();

        return $next([]);
    }
}
