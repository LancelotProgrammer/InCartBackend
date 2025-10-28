<?php

namespace App\Filament\Resources\Audits;

use App\Filament\Resources\Audits\Pages\ManageAudits;
use App\Models\BranchProduct;
use App\Models\CartProduct;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderArchive;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Setting;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Models\Audit;
use UnitEnum;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;

    protected static string|UnitEnum|null $navigationGroup = 'Configs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'event';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Info')
                    ->columns(1)
                    ->schema([
                        RepeatableEntry::make('modified')
                            ->schema([
                                TextEntry::make('attribute')
                                    ->weight('bold'),
                                TextEntry::make('old')
                                    ->placeholder('N/A')
                                    ->extraAttributes(['style' => 'color:#d9534f; font-weight:bold;']),
                                TextEntry::make('new')
                                    ->placeholder('N/A')
                                    ->extraAttributes(['style' => 'color:#28a745; font-weight:bold;']),
                            ])
                            ->grid(2)
                            ->columns(3)
                            ->state(function ($record) {
                                $modified = $record->getModified(true);
                                if (is_string($modified)) {
                                    $decoded = json_decode($modified, true);
                                    $modified = is_array($decoded) ? $decoded : [];
                                }
                                if (!is_array($modified)) {
                                    return [];
                                }
                                $rows = [];
                                foreach ($modified as $attribute => $values) {
                                    if (!is_array($values)) {
                                        continue;
                                    }
                                    $old = $values['old'] ?? null;
                                    $new = $values['new'] ?? null;
                                    if (is_array($old)) {
                                        $old = json_encode($old, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                    }
                                    if (is_array($new)) {
                                        $new = json_encode($new, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                                    }
                                    $rows[] = [
                                        'attribute' => $attribute,
                                        'old' => $old ?? '',
                                        'new' => $new ?? '',
                                    ];
                                }
                                return $rows;
                            })
                    ]),
            ]);;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->label('Changed By'),
                TextColumn::make('ip_address')
                    ->label('IP Address'),
                TextColumn::make('auditable_type')
                    ->label('Auditable Model'),
                TextColumn::make('auditable_id')
                    ->searchable()
                    ->label('Auditable Model Info')
                    ->placeholder('Deleted Model')
                    ->state(fn($record) => match ($record->auditable_type) {
                        CartProduct::class => $record->auditable?->cart->order->order_number,
                        BranchProduct::class => $record->auditable?->product->title,
                        Coupon::class => $record->auditable?->title,
                        Order::class => $record->auditable?->order_number,
                        OrderArchive::class => $record->auditable?->order_number,
                        Role::class => $record->auditable?->title,
                        RolePermission::class => $record->auditable?->role->title,
                        Setting::class => $record->auditable?->key,
                        User::class => $record->auditable?->name,
                        default => $record->auditable_id,
                    }),
                TextColumn::make('event')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until')->after('created_from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                SelectFilter::make('auditable_type')->options([
                    CartProduct::class => 'CartProduct model',
                    BranchProduct::class => 'BranchProduct model',
                    Coupon::class => 'Coupon model',
                    Order::class => 'Order model',
                    OrderArchive::class => 'OrderArchive model',
                    Role::class => 'Role model',
                    Setting::class => 'Setting model',
                    User::class => 'User model',
                ]),
                SelectFilter::make('event')->options([
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'deleted' => 'Deleted',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAudits::route('/'),
        ];
    }
}
