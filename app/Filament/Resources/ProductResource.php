<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
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
use Illuminate\Support\Str;

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
                                                    ->live(onBlur: true)
                                                    ->afterStateUpdated(function (string $state, callable $set) {
                                                        // Only set the slug automatically if it doesn't exist yet
                                                        $set('slug', Str::slug($state));
                                                    })
                                                    ->columnSpan(2),

                                                Forms\Components\TextInput::make('slug')
                                                    ->label('Slug')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('product-slug')
                                                    ->helperText('Will be used in URL: example.com/merchandise/[slug]')
                                                    ->unique(ignoreRecord: true)
                                                    ->columnSpan(2),

                                                Forms\Components\Select::make('category_id')
                                                    ->label('Category')
                                                    ->relationship(
                                                        'category',
                                                        'name',
                                                        fn (Builder $query) => $query->where('status', 'active'),
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
                                        Forms\Components\Placeholder::make('variant_info')
                                            ->content(fn(callable $get) => $get('has_variants')
                                                ? 'Add variants with specific combinations of attributes (e.g., Color: Blue, Size: Large)'
                                                : 'Configure the single variant for this simple product')
                                            ->columnSpanFull(),

                                        Forms\Components\Repeater::make('variants')
                                            ->relationship()
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('sku')
                                                            ->label('SKU')
                                                            ->required()
                                                            ->maxLength(50)
                                                            ->unique(ignoreRecord: true)
                                                            ->helperText('Stock Keeping Unit - must be unique')
                                                            ->autocomplete(false),

                                                        Forms\Components\TextInput::make('price')
                                                            ->label('Price')
                                                            ->required()
                                                            ->numeric()
                                                            ->prefix('Rp')
                                                            ->minValue(0),

                                                        Forms\Components\TextInput::make('stock_quantity')
                                                            ->label('Stock Quantity')
                                                            ->required()
                                                            ->numeric()
                                                            ->minValue(0)
                                                            ->default(0),

                                                        Forms\Components\FileUpload::make('image_url')
                                                            ->label('Variant Image')
                                                            ->image()
                                                            ->directory('products/variants')
                                                            ->visibility('public')
                                                            ->imageResizeMode('cover')
                                                            ->imageCropAspectRatio('1:1')
                                                            ->imageResizeTargetWidth('600')
                                                            ->imageResizeTargetHeight('600'),

                                                        Forms\Components\Toggle::make('is_default')
                                                            ->label('Default Variant')
                                                            ->helperText('This variant will be used as the default for this product')
                                                            ->default(false)
                                                            ->reactive()
                                                            ->afterStateUpdated(function (callable $set, callable $get, $state, $context) {
                                                                if ($context === 'create') {
                                                                    return;
                                                                }

                                                                // If turning on this toggle, ensure we update form to reset other defaults
                                                                if ($state === true) {
                                                                    // Note: This will be processed when the form is submitted
                                                                    // The actual logic to ensure only one default exists should be in the model
                                                                }
                                                            }),

                                                        Forms\Components\Select::make('status')
                                                            ->label('Status')
                                                            ->options([
                                                                'active' => 'Active',
                                                                'inactive' => 'Inactive',
                                                                'out_of_stock' => 'Out of Stock',
                                                            ])
                                                            ->default('active')
                                                            ->required(),
                                                    ]),

                                                Forms\Components\Section::make('Variant Attributes')
                                                    ->schema([
                                                        Forms\Components\Repeater::make('variantAttributes')
                                                            ->relationship()
                                                            ->schema([
                                                                Forms\Components\Select::make('attribute_id')
                                                                    ->label('Attribute')
                                                                    ->relationship('attribute', 'name')
                                                                    // ->required()
                                                                    ->searchable()
                                                                    ->preload()
                                                                    ->reactive()
                                                                    ->afterStateUpdated(function (callable $set, $state) {
                                                                        $set('attribute_value_id', null);
                                                                    }),

                                                                Forms\Components\Select::make('attribute_value_id')
                                                                    ->label('Value')
                                                                    ->relationship('attributeValue', 'value', function (Builder $query, callable $get) {
                                                                        $attributeId = $get('attribute_id');

                                                                        if ($attributeId) {
                                                                            $query->where('attribute_id', $attributeId);
                                                                        }
                                                                    })
                                                                    // ->required()
                                                                    ->searchable()
                                                                    ->preload()
                                                                    ->disabled(fn(callable $get) => !$get('attribute_id')),
                                                            ])
                                                            ->columns(2)
                                                            ->itemLabel(fn(array $state): ?string =>
                                                            isset($state['attribute_id']) && isset($state['attribute_value_id'])
                                                                ? \App\Models\Attribute::find($state['attribute_id'])?->name . ': ' .
                                                                \App\Models\AttributeValue::find($state['attribute_value_id'])?->value
                                                                : 'Attribute Value')
                                                            ->afterStateUpdated(function ($state, callable $get, callable $set, $livewire) {
                                                                // Mencegah duplikasi attributes dalam satu varian
                                                                $attributes = collect($state)->pluck('attribute_id')->filter()->toArray();
                                                                $uniqueAttributes = array_unique($attributes);

                                                                if (count($attributes) !== count($uniqueAttributes)) {
                                                                    \Filament\Notifications\Notification::make()
                                                                        ->warning()
                                                                        ->title('Duplicate attributes detected')
                                                                        ->body('Each variant can only have one value per attribute type.')
                                                                        ->send();
                                                                }
                                                            })
                                                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $livewire) {
                                                                // Dapatkan ID produk dari parent record
                                                                $productId = $livewire->record?->id;

                                                                if (!$productId) {
                                                                    return $data;
                                                                }

                                                                // Tambahkan attribute dan value ke product_attribute jika belum ada
                                                                $attributeId = $data['attribute_id'];
                                                                $attributeValueId = $data['attribute_value_id'];

                                                                if ($attributeId && $attributeValueId) {
                                                                    // Cek apakah kombinasi atribut-nilai sudah ada di product_attribute
                                                                    $exists = \App\Models\ProductAttribute::where('product_id', $productId)
                                                                        ->where('attribute_id', $attributeId)
                                                                        ->where('attribute_value_id', $attributeValueId)
                                                                        ->exists();

                                                                    // Jika belum ada, buat product_attribute baru
                                                                    if (!$exists) {
                                                                        \App\Models\ProductAttribute::create([
                                                                            'product_id' => $productId,
                                                                            'attribute_id' => $attributeId,
                                                                            'attribute_value_id' => $attributeValueId,
                                                                        ]);

                                                                        // Berikan notifikasi bahwa product attribute telah ditambahkan
                                                                        \Filament\Notifications\Notification::make()
                                                                            ->success()
                                                                            ->title('Product attribute added')
                                                                            ->body('Attribute has been automatically added to the product')
                                                                            ->send();
                                                                    }
                                                                }

                                                                return $data;
                                                            }),
                                                    ])
                                                    ->collapsible(),
                                            ])
                                            ->columns(1)
                                            ->itemLabel(fn(array $state): ?string =>
                                            isset($state['sku']) ? "Variant: {$state['sku']}" . (isset($state['is_default']) && $state['is_default'] ? ' (Default)' : '') : null)
                                            ->addActionLabel('Add Variant')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $livewire) {
                                                // Jika ini adalah varian pertama, atur sebagai default
                                                static $isFirst = true;

                                                if ($isFirst && !$livewire->record?->variants()->exists()) {
                                                    $data['is_default'] = true;
                                                    $isFirst = false;
                                                }

                                                return $data;
                                            })
                                    ])
                            ]),



                        Forms\Components\Tabs\Tab::make('Images')
                            ->schema([
                                Forms\Components\Section::make('Product Images')
                                    ->schema([
                                        Forms\Components\Repeater::make('images')
                                            ->relationship()
                                            ->schema([
                                                Forms\Components\FileUpload::make('image_url')
                                                    ->label('Image')
                                                    ->image()
                                                    ->required()
                                                    ->directory('products/images')
                                                    ->disk('public')
                                                    ->visibility('public')
                                                    ->imageResizeMode('cover')
                                                    ->imageCropAspectRatio('1:1')
                                                    ->imageResizeTargetWidth('1200')
                                                    ->imageResizeTargetHeight('1200')
                                                    ->columnSpanFull()
                                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/gif']),

                                                Forms\Components\TextInput::make('sort_order')
                                                    ->label('Sort Order')
                                                    ->integer()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->helperText('Lower numbers appear first')
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(1)
                                            ->itemLabel(
                                                fn(array $state): ?string =>
                                                isset($state['image_url'])
                                                    ? "Product Image" . (isset($state['sort_order']) ? " (Order: {$state['sort_order']})" : "")
                                                    : null
                                            )
                                            ->addActionLabel('Add Image')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->cloneable(),

                                        Forms\Components\View::make('filament.resources.product-resource.images-note')
                                            ->columnSpanFull(),
                                    ])
                            ])
                    ])
                    ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('defaultVariant.image_url')
                    ->label('Image')
                    ->defaultImageUrl(
                        fn(Product $record) => $record->defaultVariant && $record->defaultVariant->image_url
                            ? asset('storage/' . $record->defaultVariant->image_url)
                            : (
                                $record->variants()->exists() && $record->variants()->first()->image_url
                                ? asset('storage/' . $record->variants()->first()->image_url)
                                : (
                                    $record->images()->first()
                                    ? asset('storage/' . $record->images()->first()->image_url)
                                    : asset('images/no-image.jpg')
                                )
                            )
                    )
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->description(fn(Product $record): ?string =>
                    $record->category ? "Category: {$record->category->name}" : null),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('displayPrice')
                    ->label('Price')
                    ->formatStateUsing(function ($state, Product $record) {
                        $defaultVariant = $record->variants()->where('is_default', true)->first();

                        if ($defaultVariant) {
                            return 'Rp ' . number_format((float)$defaultVariant->price, 0, ',', '.');
                        }

                        // Fallback if no default variant exists
                        return 'Rp 0';
                    }),

                Tables\Columns\TextColumn::make('availableStock')
                    ->label('Stock')
                    ->formatStateUsing(function ($state, Product $record) {
                        $defaultVariant = $record->variants()->where('is_default', true)->first();

                        if ($defaultVariant) {
                            return $defaultVariant->stock_quantity;
                        }

                        // Fallback if no default variant exists
                        return 0;
                    })
                    ->sortable(),


                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
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

                                        Infolists\Components\TextEntry::make('slug')
                                            ->label('Slug')
                                            ->color('gray')
                                            ->copyable()
                                            ->url(fn(Product $record) => url("/merchandise/{$record->slug}"))
                                            ->openUrlInNewTab(),

                                        Infolists\Components\TextEntry::make('category.name')
                                            ->label('Category'),

                                        Infolists\Components\TextEntry::make('status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
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
        return [];
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
        return ['name', 'slug', 'description'];
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
