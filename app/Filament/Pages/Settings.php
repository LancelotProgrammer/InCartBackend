<?php

namespace App\Filament\Pages;

use App\Enums\SettingType;
use App\Models\Setting;
use App\Services\CacheService;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Settings extends Page
{
    protected string $view = 'filament.pages.settings';

    protected static string|UnitEnum|null $navigationGroup = 'Configs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Cog8Tooth;

    public ?array $data = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->action(function () {
                    $data = $this->content->getState();

                    foreach ($data as $key => $value) {
                        if (is_array($value)) {
                            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
                        } elseif (is_bool($value)) {
                            $value = $value ? '1' : '0';
                        } else {
                            $value = (string) $value;
                        }
                        Setting::where('key', $key)->update([
                            'value' => $value,
                        ]);
                    }

                    CacheService::deleteSettingsCache();

                    Notification::make()
                        ->title('Saved')
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        $settings = Setting::all();

        $mapped = $settings->mapWithKeys(function ($setting) {
            return [
                $setting->key => match ($setting->type) {
                    SettingType::BOOL => (bool) $setting->value,
                    SettingType::INT => (int) $setting->value,
                    SettingType::FLOAT => (float) $setting->value,
                    SettingType::JSON => json_decode($setting->value, true),
                    default => $setting->value,
                },
            ];
        })->toArray();

        $this->content->fill($mapped);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components(SettingsService::getSettingsComponents());
    }

    public static function canAccess(): bool
    {
        return auth()->user()->canManageSettings();
    }
}
