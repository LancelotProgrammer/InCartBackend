<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        $isLocal = $this->app->environment('local');

        Telescope::night();

        if (!$isLocal) {
            Telescope::hideRequestParameters(['_token']);
            Telescope::hideRequestHeaders([
                'cookie',
                'x-csrf-token',
                'x-xsrf-token',
            ]);
        }

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return true;
        });

        Telescope::tag(function (IncomingEntry $entry) {
            $tags = [];
            $tags[] = request()->is('api/*') ? 'api' : 'dashboard';
            return $tags;
        });
    }

    protected function authorization()
    {
        $this->gate();

        Telescope::auth(function (Request $request) {
            return Gate::check('viewTelescope', [$request->user()]);
        });
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', function (User $user) {
            return $user->canManageDeveloperSettings();
        });
    }
}
