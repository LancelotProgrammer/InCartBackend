<?php

namespace App\Providers;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->bindServices();
        $this->configureModel();
        $this->configureBuilder();
        $this->configureFilamentTable();
        $this->configureGate();
        $this->configureDB();
        $this->configureURL();
    }

    private function bindServices(): void {}

    private function configureModel(): void
    {
        Model::shouldBeStrict();
    }

    private function configureBuilder(): void
    {
        Builder::macro('branchScope', function ()
        {
            return $this->where('branch_id', '=', request()->attributes->get('currentBranchId'));
        });

        Builder::macro('publishedScope', function ()
        {
            return $this->whereNotNull('published_at');
        });
    }

    private function configureFilamentTable(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->paginationPageOptions([10, 25]);
        });
    }

    private function configureGate(): void
    {
        // $roles = Role::with('permissions')->get();
        // $permissionsArray = [];
        // foreach ($roles as $role) {
        //     foreach ($role->permissions as $permissions) {
        //         $permissionsArray[$permissions->title][] = $role->id;
        //     }
        // }
        // foreach ($permissionsArray as $title => $roles) {
        //     Gate::define($title, function ($user) use ($roles) {
        //         return count(array_intersect([$user->role_id], $roles)) > 0;

        //         // NOTE:
        //         // use this code if user has multiple roles
        //         // return count(array_intersect($user->roles->pluck('id')->toArray(), $roles)) > 0;
        //         // add roles function to user model

        //     });
        // }
    }

    private function configureDB(): void
    {
        if ($this->app->isProduction()) {
            DB::prohibitDestructiveCommands();
        }
    }

    private function configureURL(): void
    {
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
