<?php

namespace App\Providers;

use App\Filament\Responses\LoginResponse;
use App\Policies\AuditPolicy;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentTimezone;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;

class AppServiceProvider extends ServiceProvider
{
    public $singletons = [
        LoginResponseContract::class => LoginResponse::class,
    ];

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
        $this->registerPolicies();
        $this->configureModel();
        $this->configureQueryBuilder();
        $this->configureTimezone();
        $this->configureDB();
        $this->configureURL();
        $this->configureFilamentTable();
        $this->configureFilamentAsset();
    }

    private function bindServices(): void {}

    private function registerPolicies(): void
    {
        Gate::policy(Audit::class, AuditPolicy::class);
    }

    private function configureModel(): void
    {
        Model::shouldBeStrict();
        Model::automaticallyEagerLoadRelationships();
    }

    private function configureQueryBuilder(): void
    {
        Builder::macro('branchScope', function () {
            return $this->where('branch_id', '=', request()->attributes->get('currentBranchId'));
        });

        Builder::macro('publishedScope', function () {
            return $this->whereNotNull('published_at');
        });
    }

    private function configureTimezone(): void
    {
        Carbon::macro('inApplicationTimezone', function () {
            return $this->tz(config('app.timezone_display'));
        });
        Carbon::macro('inApplicationTodayRange', function () {
            $this->tz(config('app.timezone_display'));

            return [
                $this->copy()->startOfDay()->timezone('UTC')->toDateTimeString(),
                $this->copy()->endOfDay()->timezone('UTC')->toDateTimeString(),
            ];
        });
        FilamentTimezone::set(config('app.timezone_display'));
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

    private function configureFilamentTable(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->paginationPageOptions([10, 25]);
        });
    }

    private function configureFilamentAsset(): void
    {
        FilamentAsset::register([
            Css::make('leaflet-stylesheet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'),
            Css::make('leaflet-draw-plugin-stylesheet', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css'),
            Css::make('leaflet-search-plugin-stylesheet', 'https://unpkg.com/leaflet.pinsearch/src/Leaflet.PinSearch.css'),
            Js::make('leaflet-script', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'),
            Js::make('leaflet-draw-plugin-script', 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js'),
            Js::make('leaflet-search-plugin-script', 'https://unpkg.com/leaflet.pinsearch/src/Leaflet.PinSearch.js'),
        ]);
    }
}
