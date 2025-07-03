<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EbookResource\Pages;
use App\Models\Ebook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class EbookResource extends Resource
{
    protected static ?string $model = Ebook::class;

    protected static ?string $navigationIcon = 'phosphor-book-open-duotone';
    protected static ?string $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
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
                Forms\Components\TextInput::make('author')
                    ->required()
                    ->maxLength(191),
                Forms\Components\FileUpload::make('cover_image')
                    ->image()
                    ->required()
                    ->disk('public') // Menggunakan disk public
                    ->directory('ebook-covers'), // Menyimpan di folder ebook-covers
                Forms\Components\RichEditor::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('ebook_file')
                    ->required()
                    ->disk('public') // Menggunakan disk public
                    ->directory('ebook-files') // Menyimpan di folder ebook-files
                    ->acceptedFileTypes(['application/pdf', 'application/epub+zip', 'application/x-mobipocket-ebook'])
                    ->helperText('Upload PDF, EPUB, atau MOBI file (max: 50MB)')
                    ->maxSize(50 * 1024), // 50MB maksimum
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'DRAFT' => 'Draft',
                        'PUBLISHED' => 'Published',
                        'PENDING' => 'Pending',
                    ])
                    ->default('DRAFT'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('cover_image'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'PUBLISHED' => 'success',
                        'DRAFT' => 'gray',
                        'PENDING' => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'DRAFT' => 'Draft',
                        'PUBLISHED' => 'Published',
                        'PENDING' => 'Pending',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->url(fn(Ebook $record): string => $record->ebook_file)
                    ->icon('heroicon-o-arrow-down-tray')
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->action(fn(Collection $records) => $records->each->update(['status' => 'PUBLISHED']))
                        ->action(fn(collection $records) => $records->each->update(['status' => 'PUBLISHED']))
                        ->requiresConfirmation()
                        ->color('success')
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListEbooks::route('/'),
            'create' => Pages\CreateEbook::route('/create'),
            'edit' => Pages\EditEbook::route('/{record}/edit'),
        ];
    }
}
