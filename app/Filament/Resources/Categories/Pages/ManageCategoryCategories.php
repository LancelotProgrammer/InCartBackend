<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Actions\PublishActions;
use App\Filament\Components\TranslationComponent;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Services\HandleUploadedFiles;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ManageCategoryCategories extends ManageRelatedRecords
{
    protected static string $resource = CategoryResource::class;

    protected static string $relationship = 'children';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TranslationComponent::configure('title'),
                TranslationComponent::configure('description'),
                FileUpload::make('files')
                    ->columnSpanFull()
                    ->image()
                    ->multiple()
                    ->disk('public')
                    ->directory('advertisements')
                    ->visibility('public'),
                Hidden::make('parent_id')->default($this->getOwnerRecord()->id),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data, string $model): Model {
                        $uploadedPaths = $data['files'] ?? [];

                        unset($data['files']);

                        $created = $model::create($data);

                        HandleUploadedFiles::configure($uploadedPaths, $created, 'categories');

                        return $created;
                    }),
            ])
            ->recordActions([
                ...PublishActions::configure(),
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')
                        ->label('Image')
                        ->state(fn ($record) => $record->files->first()->url ?? null),
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('published_at')->dateTime(),
                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 4,
            ]);
    }
}
