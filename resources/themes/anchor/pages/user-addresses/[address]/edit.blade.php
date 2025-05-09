<?php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};
use App\Models\UserAddress;

middleware('auth');
name('user-addresses.edit');

new class extends Component implements HasForms {
    use InteractsWithForms;

    public ?array $data = [];
    public $provinces = [];
    public $cities = [];
    public $districts = [];
    public $villages = [];
    public $postalCode = null;

    // Menggunakan properti terpisah untuk ID dan model
    public $addressId;
    public $addressModel;
    public $isPrimary = false;

    public function mount($address): void
    {
        // Set address ID
        $this->addressId = $address;

        // Find the address by ID
        $addressModel = UserAddress::findOrFail($address);

        // Ensure the user can only edit their own addresses
        if ($addressModel->user_id !== auth()->id()) {
            $this->redirect('/user-addresses');
            return;
        }

        // Load provinces
        $this->provinces = \Indonesia::allProvinces();

        // Simpan state yang diperlukan
        $this->addressModel = $addressModel;
        $this->isPrimary = $addressModel->is_primary;

        // Get province_id, city_id, district_id, and village_id based on names
        $province = $this->provinces->where('name', $addressModel->state)->first();

        if ($province) {
            $province_id = $province->id;
            $this->cities = \Indonesia::findProvince($province_id, ['cities'])->cities;

            $city = $this->cities->where('name', $addressModel->city)->first();
            if ($city) {
                $city_id = $city->id;
                $this->districts = \Indonesia::findCity($city_id, ['districts'])->districts;

                // Find district by name
                $district = $this->districts->where('name', $addressModel->district)->first();
                if ($district) {
                    $district_id = $district->id;
                    $this->villages = \Indonesia::findDistrict($district_id, ['villages'])->villages;

                    // Find village by name
                    $village = $this->villages->where('name', $addressModel->village)->first();
                    $village_id = $village ? $village->id : null;

                    // Initialize data array with found IDs
                    $this->form->fill([
                        'address_line' => $addressModel->address_line,
                        'address_type' => $addressModel->address_type,
                        'phone_number' => $addressModel->phone_number,
                        'postal_code' => $addressModel->postal_code,
                        'is_primary' => $addressModel->is_primary,
                        'province_id' => $province_id,
                        'city_id' => $city_id,
                        'district_id' => $district_id,
                        'village_id' => $village_id,
                    ]);

                    $this->postalCode = $addressModel->postal_code;
                    return;
                }
            }
        }

        // Fallback if we can't find matching location IDs
        $this->form->fill($addressModel->toArray());
    }
    public function updatedDataProvinceId($value)
    {
        $this->cities = \Indonesia::findProvince($value, ['cities'])->cities;
        $this->data['city_id'] = null;
        $this->data['district_id'] = null;
        $this->data['village_id'] = null;
        $this->districts = [];
        $this->villages = [];
        $this->postalCode = null;
    }

    public function updatedDataCityId($value)
    {
        $this->districts = \Indonesia::findCity($value, ['districts'])->districts;
        $this->data['district_id'] = null;
        $this->data['village_id'] = null;
        $this->villages = [];
        $this->postalCode = null;
    }

    public function updatedDataDistrictId($value)
    {
        $this->villages = \Indonesia::findDistrict($value, ['villages'])->villages;
        $this->data['village_id'] = null;
        $this->postalCode = null;
    }

    public function updatedDataVillageId($value)
    {
        if ($value) {
            $village = \Indonesia::findVillage($value);

            // Periksa tipe data meta dan sesuaikan
            $meta = $village->meta;

            // Jika meta berbentuk string JSON, decode terlebih dahulu
            if (is_string($meta) && !empty($meta)) {
                $meta = json_decode($meta, true);
            }

            // Jika meta sudah berbentuk array, gunakan langsung
            if (is_array($meta) && isset($meta['pos']) && $meta['pos'] !== 'NULL') {
                $this->postalCode = $meta['pos'];
                $this->data['postal_code'] = $this->postalCode;
            } else {
                // Jika tidak ada pos di meta, tetap gunakan postal_code yang ada
                $this->postalCode = $this->data['postal_code'] ?? null;
            }
        } else {
            $this->postalCode = $this->data['postal_code'] ?? null;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Edit Address')
                    ->description('Update your address information')
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('address_line')->label('Address Line')->required()->maxLength(255)->placeholder('e.g. Jl. Sudirman No. 123'),

                                Select::make('address_type')
                                    ->label('Address Type')
                                    ->options([
                                        'Home' => 'Home',
                                        'Office' => 'Office',
                                        'Other' => 'Other',
                                    ])
                                    ->required(),

                                Select::make('province_id')
                                    ->label('Province')
                                    ->options(function () {
                                        return $this->provinces->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search province')
                                    ->afterStateUpdated(fn(callable $set) => $set('city_id', null)),

                                Select::make('city_id')
                                    ->label('City')
                                    ->options(function () {
                                        return collect($this->cities)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search city')
                                    ->disabled(fn(callable $get) => !$get('province_id'))
                                    ->afterStateUpdated(fn(callable $set) => $set('district_id', null)),

                                Select::make('district_id')
                                    ->label('District')
                                    ->options(function () {
                                        return collect($this->districts)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search district')
                                    ->disabled(fn(callable $get) => !$get('city_id'))
                                    ->afterStateUpdated(fn(callable $set) => $set('village_id', null)),

                                Select::make('village_id')
                                    ->label('Village')
                                    ->options(function () {
                                        return collect($this->villages)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search village')
                                    ->disabled(fn(callable $get) => !$get('district_id')),

                                TextInput::make('postal_code')->label('Postal Code')->required()->maxLength(20)->default(fn() => $this->postalCode)->helperText('Will be auto-filled based on village selection when available'),
                                
                                TextInput::make('phone_number')->label('Phone Number')->tel()->required()->maxLength(20)->placeholder('+62812XXXXXXXX'),

                                Toggle::make('is_primary')->label('Set as Primary Address')->helperText('This address will be used as your default shipping and billing address')->onColor('success')->offColor('danger')->inline(false)->disabled(fn() => $this->isPrimary),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function update(): void
    {
        // Validasi form
        $data = $this->form->getState();

        // Ensure is_primary is set, default to false if not present
        $data['is_primary'] = $data['is_primary'] ?? false;

        try {
            // Get address model from DB again to avoid stale data
            $address = UserAddress::findOrFail($this->addressId);

            // Mendapatkan nama lokasi dari ID
            $province = \Indonesia::findProvince($data['province_id']);
            $city = \Indonesia::findCity($data['city_id']);
            $district = \Indonesia::findDistrict($data['district_id']);
            $village = \Indonesia::findVillage($data['village_id']);

            // Menyusun alamat lengkap
            $data['state'] = $province->name;
            $data['city'] = $city->name;
            $data['country'] = 'Indonesia'; // Tetap menyimpan sebagai Indonesia di database
            $data['district'] = $district->name; // Add this line
            $data['village'] = $village->name; // Add this line

            // Format alamat lengkap
            $fullAddress = $data['address_line'] . ', ' . $village->name . ', ' . $district->name . ', ' . $city->name . ', ' . $province->name . ' ' . $data['postal_code'];

            $data['full_address'] = $fullAddress;

            // Update alamat
            $address->update($data);

            // Atur sebagai alamat utama jika diperlukan
            if ($data['is_primary'] && !$address->is_primary) {
                $address->setPrimary();
            }

            // Kirim notifikasi
            Notification::make()->success()->title('Address updated successfully')->body('Your address has been updated')->send();

            // Redirect ke halaman utama
            $this->redirect('/user-addresses');
        } catch (\Exception $e) {
            // Tangkap error
            Notification::make()->danger()->title('Error updating address')->body($e->getMessage())->send();
        }
    }
};
?>

<x-layouts.app>
    @volt('user-addresses.edit')
        <x-app.container class="max-w-3xl">
            <div class="flex items-center justify-between mb-5">
                <x-app.heading title="Edit Address" description="Update your address information" :border="false" />
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <form wire:submit.prevent="update" class="space-y-6 p-6">
                    {{ $this->form }}

                    <div class="flex justify-end gap-x-3 pt-4 border-t">
                        <x-button tag="a" href="/user-addresses" color="secondary">
                            Cancel
                        </x-button>
                        <x-button type="submit" class="text-white bg-primary-600 hover:bg-primary-500">
                            <span class="flex items-center gap-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                Update Address
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>

            @if ($isPrimary)
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mt-0.5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium">This is your primary address</p>
                            <p class="mt-1">This address is currently set as your primary address and will be used as the
                                default for shipping and billing.</p>
                        </div>
                    </div>
                </div>
            @endif
        </x-app.container>
    @endvolt
</x-layouts.app>
