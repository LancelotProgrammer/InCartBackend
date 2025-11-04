<?php

namespace App\Filament\Responses;

use App\Filament\Pages\Welcome;
use Filament\Auth\Http\Responses\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        return redirect()->intended(Welcome::getUrl());
    }
}
