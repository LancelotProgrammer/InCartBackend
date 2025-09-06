<?php

namespace App\Pipes;

use App\Models\UserAddress;
use Closure;
use Illuminate\Http\Request;

class DeleteUserAddress
{
    public function __invoke(Request $request, Closure $next)
    {
        $request->merge(['id' => $request->route('id')]);

        $validated = $request->validate([
            'id' => 'required|exists:user_addresses,id',
        ]);

        $address = UserAddress::where('id', $validated['id'])
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $address->delete();

        return $next(['deleted' => true]);
    }
}
