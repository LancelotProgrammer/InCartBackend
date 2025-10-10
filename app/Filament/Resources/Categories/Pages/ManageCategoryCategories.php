<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Actions\CategoriesActions;
use App\Filament\Actions\PublishActions;
use App\Filament\Components\TranslationComponent;
use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Services\HandleUploadedFiles;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\Action;
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
                TranslationComponent::configure('description', false),
                FileUpload::make('files')
                    ->columnSpanFull()
                    ->directory('categories')
                    ->minSize(1)
                    ->maxSize(1024)
                    ->minFiles(1)
                    ->maxFiles(1)
                    ->image()
                    ->multiple()
                    ->disk('public')
                    ->visibility('public')
                    ->required(),
                Hidden::make('parent_id')->default($this->getOwnerRecord()->id),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultPaginationPageOption(25)
            ->headerActions([
                CreateAction::make()
                    ->visible(function () {
                        if (Category::where('id', '=', $this->getOwnerRecord()->id)->first()->depth === 3) {
                            return false;
                        } else {
                            return true;
                        }
                    })
                    ->using(function (array $data, string $model): Model {
                        $uploadedPaths = $data['files'] ?? [];

                        unset($data['files']);

                        $created = $model::create($data);

                        HandleUploadedFiles::configure($uploadedPaths, $created, 'categories');

                        return $created;
                    }),
            ])
            ->recordActions([
                CategoriesActions::configureViewProductsAction()->iconButton(),
                CategoriesActions::configureViewCategoriesAction()->iconButton(),
                Action::make('go')->url(fn(Category $record) => CategoryResource::getUrl('edit', ['record' => $record->id])),
                ...PublishActions::configure(Category::class),
            ])
            ->columns([
                Stack::make([
                    ImageColumn::make('url')->label('Image')->state(fn($record) => $record->files->first()->url ?? null)->imageSize(200),
                    TextColumn::make('title')->searchable(),
                    TextColumn::make('published_at')->dateTime(),
                ]),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
                '2xl' => 5,
            ]);
    }
}
