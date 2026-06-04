<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KnowledgeBaseResource\Pages;
use App\Models\KnowledgeBase;
use App\Services\MetaGeneratorService;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class KnowledgeBaseResource extends Resource
{
    protected static ?string $model = KnowledgeBase::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Knowledge Base';

    protected static ?int $navigationSort = 13;

    /*
    |--------------------------------------------------------------------------
    | FORM
    |--------------------------------------------------------------------------
    */

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Knowledge Data')
                ->schema([

                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('content')
                        ->required()
                        ->rows(12)
                        ->columnSpanFull(),

                    /*
                    |--------------------------------------------------------------------------
                    | AUTO META AI BUTTON
                    |--------------------------------------------------------------------------
                    */

                    Actions::make([
                        Action::make('generateMeta')
                            ->label('🤖 Generate Meta AI')
                            ->icon('heroicon-o-sparkles')
                            ->color('success')
                            ->action(function (callable $set, callable $get) {

                                $title = $get('title');
                                $content = $get('content');

                                if (!$title || !$content) {
                                    return;
                                }

                                $service = app(MetaGeneratorService::class);

                                $meta = $service->generate($title, $content);


                                // 🔥 paksa bersih total
                                $meta = [
                                    'intents' => $meta['intents'] ?? [],
                                    'keywords' => $meta['keywords'] ?? [],
                                    'synonyms' => collect($meta['synonyms'] ?? [])
                                        ->mapWithKeys(fn($v, $k) => [
                                            $k => is_array($v) ? implode(', ', $v) : $v
                                        ])
                                        ->toArray(),
                                ];

                                // dd($meta);

                                $set('meta.intents', $meta['intents']);
                                $set('meta.keywords', $meta['keywords']);
                                $set('meta.synonyms', $meta['synonyms']);
                            }),
                    ]),

                    /*
                    |--------------------------------------------------------------------------
                    | META FIELD
                    |--------------------------------------------------------------------------
                    */

                    TextInput::make('meta.intents'),
                    TextInput::make('meta.keywords'),
                    KeyValue::make('meta.synonyms')
                ])
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('meta')
                    ->label('Meta Keys')
                    ->formatStateUsing(function ($state) {

                        if (!$state) {
                            return '-';
                        }

                        $meta = is_array($state)
                            ? $state
                            : json_decode($state, true);

                        if (!is_array($meta)) {
                            return '-';
                        }

                        return collect($meta)->keys()->implode(', ');
                    })
                    ->limit(50),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PAGES
    |--------------------------------------------------------------------------
    */

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKnowledgeBases::route('/'),
            'create' => Pages\CreateKnowledgeBase::route('/create'),
            'edit' => Pages\EditKnowledgeBase::route('/{record}/edit'),
        ];
    }
}
