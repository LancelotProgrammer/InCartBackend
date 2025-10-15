<?php

namespace App\Filament\Responses;

use App\Filament\Pages\DeliveryOrders;
use App\Models\Role;
use Filament\Auth\Http\Responses\LoginResponse as BaseLoginResponse;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        if (Filament::auth()->user()->role->code === Role::ROLE_DELIVERY_CODE) {
            return redirect()->intended(DeliveryOrders::getUrl());
        }
        return redirect()->intended(Filament::getUrl());
    }
}
