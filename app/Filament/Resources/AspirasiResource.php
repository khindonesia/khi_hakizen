<?php
 
namespace App\Filament\Resources;
 
use App\Filament\Resources\AspirasiResource\Pages;
use App\Models\Aspirasi;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Str;
 
class AspirasiResource extends Resource
{
    protected static ?string $model = Aspirasi::class;
 
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
 
    protected static ?string $navigationLabel = 'Aspirasi';
    protected static ?string $navigationGroup = 'Post Management';
 
    protected static ?int $navigationSort = 4;
 
    protected static ?string $recordTitleAttribute = 'title';
 
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Left Column: Main Content
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
                                    ->fileAttachmentsDirectory('aspirasis/attachments')
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
 
                // Right Column: Sidebar
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
                                    ->default('PENDING'),
 
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
 
                                Forms\Components\Select::make('types')
                                    ->label('Types/Labels')
                                    ->relationship('types', 'name', fn ($query) => $query->where('for', 'aspirasi'))
                                    ->multiple()
                                    ->preload(),
                            ]),
 
                        Forms\Components\Section::make('Cover Image')
                            ->schema([
                                Forms\Components\FileUpload::make('image')
                                    ->image()
                                    ->directory('aspirasis/covers')
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
                    ->description(fn(Aspirasi $record): string => Str::limit(strip_tags($record->excerpt), 50)),
 
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
                    ->label('Created At')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
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
                    ->label('Featured')
                    ->placeholder('All')
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
            'index' => Pages\ListAspirasis::route('/'),
            'create' => Pages\CreateAspirasi::route('/create'),
            'edit' => Pages\EditAspirasi::route('/{record}/edit'),
        ];
    }
}
