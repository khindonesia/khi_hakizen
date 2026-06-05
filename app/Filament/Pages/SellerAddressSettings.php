<?php

namespace App\Filament\Pages;

use App\Http\Controllers\RajaOngkirLocationLookup;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SellerAddressSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'phosphor-map-pin-duotone';
    protected static ?string $navigationLabel = 'Seller Address';
    protected static ?string $title = 'Seller Address Settings';
    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.seller-address-settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('seller-address.view') ?? false;
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = \Wave\Setting::where('key', 'like', 'shop.seller_%')->pluck('value', 'key')->all();

        $this->form->fill([
            'seller_name' => $settings['shop.seller_name'] ?? null,
            'seller_phone' => $settings['shop.seller_phone'] ?? null,
            'seller_address' => $settings['shop.seller_address'] ?? null,
            'province_id' => $settings['shop.seller_province_id'] ?? null,
            'city_id' => $settings['shop.seller_city_id'] ?? null,
            'district_id' => $settings['shop.seller_district_id'] ?? null,
            'subdistrict_id' => $settings['shop.seller_subdistrict_id'] ?? null,
            'seller_postal_code' => $settings['shop.seller_postal_code'] ?? null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Seller Information')
                    ->description('Set the seller name and phone number for packaging and receipts.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('seller_name')
                                    ->label('Seller Name')
                                    ->required()
                                    ->maxLength(191)
                                    ->placeholder('e.g., KHI Official Store'),
                                TextInput::make('seller_phone')
                                    ->label('Seller Phone Number')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('e.g., +62812XXXXXXXX'),
                            ]),
                    ]),

                Section::make('Seller Address & Region')
                    ->description('Pick region data dynamically from RajaOngkir. This acts as the origin for shipping cost calculation.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('seller_address')
                                    ->label('Street Address')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Jl. Jendral Sudirman No. 456')
                                    ->columnSpanFull(),

                                Select::make('province_id')
                                    ->label('Province')
                                    ->options(function (RajaOngkirLocationLookup $lookup): array {
                                        return $lookup->provinceOptions();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('city_id', null);
                                        $set('district_id', null);
                                        $set('subdistrict_id', null);
                                    })
                                    ->placeholder('Select province'),

                                Select::make('city_id')
                                    ->label('City / Regency')
                                    ->options(function (Get $get, RajaOngkirLocationLookup $lookup): array {
                                        $provinceId = $get('province_id');
                                        if (blank($provinceId)) {
                                            return [];
                                        }
                                        return $lookup->cityOptions($provinceId);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->disabled(fn(Get $get): bool => blank($get('province_id')))
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('district_id', null);
                                        $set('subdistrict_id', null);
                                    })
                                    ->placeholder('Select city or regency'),

                                Select::make('district_id')
                                    ->label('District / Kecamatan')
                                    ->options(function (Get $get, RajaOngkirLocationLookup $lookup): array {
                                        $cityId = $get('city_id');
                                        if (blank($cityId)) {
                                            return [];
                                        }
                                        return $lookup->districtOptions($cityId);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->disabled(fn(Get $get): bool => blank($get('city_id')))
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('subdistrict_id', null);
                                    })
                                    ->placeholder('Select district'),

                                Select::make('subdistrict_id')
                                    ->label('Subdistrict / Kelurahan')
                                    ->options(function (Get $get, RajaOngkirLocationLookup $lookup): array {
                                        $districtId = $get('district_id');
                                        if (blank($districtId)) {
                                            return [];
                                        }
                                        return $lookup->subdistrictOptions($districtId);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->disabled(fn(Get $get): bool => blank($get('district_id')))
                                    ->placeholder('Select subdistrict'),

                                TextInput::make('seller_postal_code')
                                    ->label('Postal Code')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('12345'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $lookup = app(RajaOngkirLocationLookup::class);

        $provinceName = $state['province_id'] ? data_get($lookup->provinceByCode($state['province_id']), 'name') : null;
        $cityName = ($state['province_id'] && $state['city_id']) ? data_get($lookup->cityByCode($state['province_id'], $state['city_id']), 'name') : null;
        $districtName = ($state['city_id'] && $state['district_id']) ? data_get($lookup->districtByCode($state['city_id'], $state['district_id']), 'name') : null;
        $subdistrictName = ($state['district_id'] && $state['subdistrict_id']) ? data_get($lookup->subdistrictByCode($state['district_id'], $state['subdistrict_id']), 'name') : null;

        $settings = [
            'shop.seller_name' => [
                'display_name' => 'Seller Name',
                'value' => $state['seller_name'],
                'type' => 'text',
            ],
            'shop.seller_phone' => [
                'display_name' => 'Seller Phone',
                'value' => $state['seller_phone'],
                'type' => 'text',
            ],
            'shop.seller_address' => [
                'display_name' => 'Seller Address',
                'value' => $state['seller_address'],
                'type' => 'text',
            ],
            'shop.seller_province_id' => [
                'display_name' => 'Seller Province ID',
                'value' => $state['province_id'],
                'type' => 'text',
            ],
            'shop.seller_province_name' => [
                'display_name' => 'Seller Province Name',
                'value' => $provinceName,
                'type' => 'text',
            ],
            'shop.seller_city_id' => [
                'display_name' => 'Seller City ID',
                'value' => $state['city_id'],
                'type' => 'text',
            ],
            'shop.seller_city_name' => [
                'display_name' => 'Seller City Name',
                'value' => $cityName,
                'type' => 'text',
            ],
            'shop.seller_district_id' => [
                'display_name' => 'Seller District ID',
                'value' => $state['district_id'],
                'type' => 'text',
            ],
            'shop.seller_district_name' => [
                'display_name' => 'Seller District Name',
                'value' => $districtName,
                'type' => 'text',
            ],
            'shop.seller_subdistrict_id' => [
                'display_name' => 'Seller Subdistrict ID',
                'value' => $state['subdistrict_id'],
                'type' => 'text',
            ],
            'shop.seller_subdistrict_name' => [
                'display_name' => 'Seller Subdistrict Name',
                'value' => $subdistrictName,
                'type' => 'text',
            ],
            'shop.seller_postal_code' => [
                'display_name' => 'Seller Postal Code',
                'value' => $state['seller_postal_code'],
                'type' => 'text',
            ],
        ];

        foreach ($settings as $key => $data) {
            \Wave\Setting::updateOrCreate(
                ['key' => $key],
                [
                    'display_name' => $data['display_name'],
                    'value' => $data['value'],
                    'type' => $data['type'],
                    'group' => 'Shop',
                ]
            );
        }

        \Illuminate\Support\Facades\Cache::forget('wave_settings');

        Notification::make()
            ->title('Settings Saved')
            ->body('Seller address settings have been updated successfully.')
            ->success()
            ->send();
    }
}
