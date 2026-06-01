<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\User;
use Wave\Post;
use Wave\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Str;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'phosphor-pencil-line-duotone';

    protected static ?string $navigationGroup = 'Post Management';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Kolom Kiri: Konten Utama
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Content')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                                    ->required()
                                    ->maxLength(191),

                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(191),

                                Forms\Components\RichEditor::make('body')
                                    ->required()
                                    ->fileAttachmentsDirectory('posts/attachments')
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('excerpt')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Forms\Components\Section::make('SEO Metadata')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('seo_title')
                                    ->maxLength(191),

                                Forms\Components\Textarea::make('meta_description')
                                    ->rows(3),

                                Forms\Components\Textarea::make('meta_keywords')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan(['lg' => 2]),

                // Kolom Kanan: Pengaturan & Media (Sidebar)
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Status & Meta')
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'DRAFT' => 'Draft',
                                        'PUBLISHED' => 'Published',
                                        'PENDING' => 'Pending',
                                    ])
                                    ->native(false)
                                    ->default('DRAFT'),

                                Forms\Components\Toggle::make('featured')
                                    ->onIcon('heroicon-m-star')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->onColor('amber'),

                                Forms\Components\Select::make('author_id')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),

                        Forms\Components\Section::make('Featured Image')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('posts/covers')
                                    ->imageEditor()
                                    ->maxSize(2048),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Cover')
                    ->square()
                    ->disk('public'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn(Post $record): string => Str::limit(strip_tags($record->excerpt), 50)),

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PUBLISHED' => 'success',
                        'PENDING' => 'warning',
                        'DRAFT' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('amber')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Published At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')

            // Mengubah posisi layout filter agar terbuka di atas konten tabel (seperti pada gambar)
            ->filtersLayout(FiltersLayout::AboveContent)

            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'PUBLISHED' => 'Published',
                        'PENDING' => 'Pending',
                    ]),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),

                TernaryFilter::make('featured')
                    ->label('Featured Post')
                    ->placeholder('All Posts')
                    ->trueLabel('Only Featured')
                    ->falseLabel('Not Featured'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
