<?php

namespace App\Filament\RelationManagers;

use App\Filament\Services\HandleUploadedFiles;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;

class BaseFilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static string $directory = 'uploads';

    public function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 5,
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')->imageSize(250)
                ]),
            ])
            ->recordActions([
                DetachAction::make('delete'),
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
