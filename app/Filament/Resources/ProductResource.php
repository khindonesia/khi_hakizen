<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Merchandise Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Products';

    protected static ?string $modelLabel = 'Product';

    protected static ?string $pluralModelLabel = 'Products';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Product Management')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Section::make('Basic Information')
                                            ->schema([
                                                Forms\Components\TextInput::make('name')
                                                    ->label('Product Name')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Enter product name')
                                                    ->columnSpan(2),
                                                    
                                                Forms\Components\Select::make('category_id')
                                                    ->label('Category')
                                                    ->relationship('category', 'name')
                                                    ->options(
                                                        fn() => ProductCategory::where('status', 'active')
                                                            ->pluck('name', 'id')
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\Select::make('status')
                                                            ->options([
                                                                'active' => 'Active',
                                                                'inactive' => 'Inactive',
                                                            ])
                                                            ->default('active')
                                                            ->required(),
                                                    ]),
                                                
                                                Forms\Components\Select::make('status')
                                                    ->options([
                                                        'active' => 'Active',
                                                        'inactive' => 'Inactive',
                                                        'draft' => 'Draft',
                                                        'out_of_stock' => 'Out of Stock',
                                                        'discontinued' => 'Discontinued',
                                                    ])
                                                    ->default('draft')
                                                    ->required(),
                                            ])
                                            ->columns(2),
                                        
                                        Forms\Components\Section::make('Product Description')
                                            ->schema([
                                                Forms\Components\RichEditor::make('description')
                                                    ->label('Description')
                                                    ->fileAttachmentsDisk('public')
                                                    ->fileAttachmentsDirectory('products/attachments')
                                                    ->toolbarButtons([
                                                        'blockquote',
                                                        'bold',
                                                        'bulletList',
                                                        'codeBlock',
                                                        'h2',
                                                        'h3',
                                                        'italic',
                                                        'link',
                                                        'orderedList',
                                                        'redo',
                                                        'strike',
                                                        'underline',
                                                        'undo',
                                                    ])
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->columnSpan(['lg' => 2]),

                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\Section::make('Notes')
                                            ->schema([
                                                Forms\Components\Placeholder::make('next_steps')
                                                    ->content('After creating the product, you will be able to:
                                                    
• Add product variants
• Upload product images 
• Manage inventory')
                                                    ->columnSpanFull(),
                                            ]),
                                    ])
                                    ->columnSpan(['lg' => 1]),
                            ])
                            ->columns(3),
                            
                        Forms\Components\Tabs\Tab::make('Variants')
                            ->schema([
                                Forms\Components\Section::make('Product Variants')
                                    ->schema([
                                        // For new products, show placeholder
                                        Forms\Components\Placeholder::make('variants_placeholder')
                                            ->content('You can add product variants after saving the basic product information.')
                                            ->visible(fn ($record) => $record === null),
                                            
                                        // For existing products, show the variants relation manager embedding
                                        Forms\Components\View::make('filament.resources.product-resource.variants-embedded-form')
                                            ->visible(fn ($record) => $record !== null),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                            
                        Forms\Components\Tabs\Tab::make('Images')
                            ->schema([
                                Forms\Components\Section::make('Product Images')
                                    ->schema([
                                        // For new products, show placeholder
                                        Forms\Components\Placeholder::make('images_placeholder')
                                            ->content('You can add product images after saving the basic product information.')
                                            ->visible(fn ($record) => $record === null),
                                            
                                        // For existing products, show the images relation manager embedding
                                        Forms\Components\View::make('filament.resources.product-resource.images-embedded-form')
                                            ->visible(fn ($record) => $record !== null),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('defaultVariant.image_url')
                    ->label('Image')
                    ->defaultImageUrl(fn (Product $record) => $record->defaultVariant && $record->defaultVariant->image_url 
                        ? asset('storage/' . $record->defaultVariant->image_url) 
                        : (
                            $record->variants()->exists() && $record->variants()->first()->image_url
                            ? asset('storage/' . $record->variants()->first()->image_url) 
                            : (
                                $record->images()->where('is_primary', true)->first()
                                ? asset('storage/' . $record->images()->where('is_primary', true)->first()->image_url)
                                : (
                                    $record->images()->first() 
                                    ? asset('storage/' . $record->images()->first()->image_url)
                                    : asset('images/no-image.jpg')
                                )
                            )
                        )
                    )
                    ->circular(),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Product $record): ?string => 
                        $record->category ? "Category: {$record->category->name}" : null),
                
                Tables\Columns\TextColumn::make('displayPrice')
                    ->label('Price')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            return 'Rp ' . number_format($state['min'], 0, ',', '.') . ' - Rp ' . number_format($state['max'], 0, ',', '.');
                        }
                        return 'Rp ' . number_format($state, 0, ',', '.');
                    }),
                
                Tables\Columns\TextColumn::make('availableStock')
                    ->label('Stock')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'inactive' => 'danger',
                        'draft' => 'gray',
                        'out_of_stock' => 'warning',
                        'discontinued' => 'danger',
                        'active' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'draft' => 'Draft',
                        'out_of_stock' => 'Out of Stock',
                        'discontinued' => 'Discontinued',
                    ]),
                
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock')
                    ->query(function (Builder $query) {
                        return $query->whereHas('variants', function ($query) {
                            $query->where('stock_quantity', '<', 10);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-o-check-circle')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'draft' => 'Draft',
                                    'out_of_stock' => 'Out of Stock',
                                    'discontinued' => 'Discontinued',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->successNotification(
                            notification: \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Product updated successfully'),
                        ),
                ]),
            ])
            ->emptyStateIcon('heroicon-o-cube')
            ->emptyStateHeading('No Products Found')
            ->emptyStateDescription('Create your first product to get started.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create Product')
                    ->url(route('filament.admin.resources.products.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Tabs::make('Product Details')
                    ->tabs([
                        Infolists\Components\Tabs\Tab::make('Basic Information')
                            ->schema([
                                Infolists\Components\Section::make('Product Information')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('name')
                                            ->label('Product Name')
                                            ->weight(FontWeight::Bold)
                                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                        
                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),
                                            
                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'inactive' => 'danger',
                                                'draft' => 'gray',
                                                'out_of_stock' => 'warning',
                                                'discontinued' => 'danger',
                                                'active' => 'success',
                                                default => 'gray',
                                            }),
                                            
                                        Infolists\Components\TextEntry::make('created_at')
                                            ->label('Created At')
                                            ->dateTime(),
                                            
                                        Infolists\Components\TextEntry::make('updated_at')
                                            ->label('Last Updated')
                                            ->dateTime(),
                                    ])
                                    ->columns(3),
                                    
                                Infolists\Components\Section::make('Product Description')
                                    ->schema([
                                        Infolists\Components\TextEntry::make('description')
                                            ->label('')
                                            ->html()
                                            ->markdown()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Infolists\Components\Tabs\Tab::make('Variants')
                            ->schema([
                                // Using a custom view to display the variants
                                Infolists\Components\ViewEntry::make('variants')
                                    ->view('filament.resources.product-resource.variants-view'),
                            ]),
                            
                        Infolists\Components\Tabs\Tab::make('Images')
                            ->schema([
                                // Using a custom view to display the images
                                Infolists\Components\ViewEntry::make('images')
                                    ->view('filament.resources.product-resource.images-view'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
                // We're still keeping the relation managers for use with the embedded views
                // RelationManagers\VariantsRelationManager::class,
                // RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}