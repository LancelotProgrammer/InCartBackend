<?php

namespace App\Filament\Pages;

use App\Enums\SettingType;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\Cache;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
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

                    Cache::deleteSettingsCache();

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
                }
            ];
        })->toArray();

        $this->content->fill($mapped);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(2)
            ->components([
                Section::make('Service')
                    ->description('Is system online or offline')
                    ->components([
                        Checkbox::make('is_system_online'),
                    ]),
                Section::make('Dashboard Managers')
                    ->description('User who can login to the admin panel')
                    ->components([
                        Select::make('dashboard_managers')
                            ->label('users')
                            ->options(User::where('role_id', '=', Role::where('code', '=', 'manager')->first()->id)->pluck('name', 'id'))
                            ->multiple(),
                    ]),
                Section::make('Notification Managers')
                    ->description('User who will receive system notifications')
                    ->components([
                        Select::make('notification_managers')
                            ->label('users')
                            ->options(User::where('role_id', '=', Role::where('code', '=', 'manager')->first()->id)->pluck('name', 'id'))
                            ->multiple(),
                    ]),
                Section::make('Social Media Links')
                    ->description('User who will receive system notifications')
                    ->columns(3)
                    ->components([
                        TextInput::make('whatsapp')->url(),
                        TextInput::make('telegram')->url(),
                        TextInput::make('facebook')->url(),
                    ]),
                Section::make('Order Config')
                    ->description('Is is system online')
                    ->columns(5)
                    ->components([
                        TextInput::make('service_fee')->numeric(),
                        TextInput::make('tax_rate')->numeric(),
                        TextInput::make('min_distance')->numeric(),
                        TextInput::make('max_distance')->numeric(),
                        TextInput::make('price_per_kilometer')->numeric(),
                    ]),
                Section::make('Support')
                    ->description('Support and feedback allowed for user per day')
                    ->columns(2)
                    ->components([
                        TextInput::make('allowed_ticket_count')->integer(),
                        TextInput::make('allowed_feedback_count')->integer(),
                    ]),
                Section::make('Legal')
                    ->description('Legal text')
                    ->columnSpanFull()
                    ->components([
                        RichEditor::make('privacy_policy'),
                        RichEditor::make('terms_of_services'),
                    ]),
            ]);
    }
}
