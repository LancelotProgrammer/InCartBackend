<?php

namespace App\Filament\RelationManagers;

use App\Services\HandleUploadedFiles;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BaseFilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static string $directory = 'uploads';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Manage your resource files here')
            ->description('There must be at least one file attached to the model')
            ->reorderable('order')
            ->defaultSort('order')
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 5,
            ])
            ->columns([
                Stack::make([
                    TextColumn::make('order')->prefix('Order: '),
                    ImageColumn::make('url')->imageSize(200),
                ]),
            ])
            ->recordActions([
                DetachAction::make('delete')
                    ->disabled(fn() => $this->getOwnerRecord()->files()->get()->count() <= 1),
            ])
            ->headerActions([
                Action::make('create')
                    ->schema(function () {
                        return [
                            FileUpload::make('files')
                                ->columnSpanFull()
                                ->directory(static::$directory)
                                ->minSize(1)
                                ->maxSize(1024)
                                ->minFiles(1)
                                ->maxFiles(1)
                                ->image()
                                ->multiple()
                                ->disk('public')
                                ->visibility('public')
                                ->required(),
                        ];
                    })
                    ->action(function (array $data) {
                        HandleUploadedFiles::configure(
                            $data['files'],
                            $this->getOwnerRecord(),
                            static::$directory
                        );
                        Notification::make()
                            ->title('Image saved')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
