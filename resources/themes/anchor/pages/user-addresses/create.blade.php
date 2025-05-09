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
name('user-addresses.create');

new class extends Component implements HasForms
{
    use InteractsWithForms;
    
    public ?array $data = [];
    public $provinces = [];
    public $cities = [];
    public $districts = [];
    public $villages = [];
    public $postalCode = null;
    
    public function mount(): void
    {
        $this->provinces = \Indonesia::allProvinces();
        
        $this->form->fill([
            'address_type' => 'Home',
            'is_primary' => !auth()->user()->userAddresses()->exists()
        ]);
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
                // Initialize postal_code if it doesn't exist yet
                $this->postalCode = null;
                $this->data['postal_code'] = null;
            }
        } else {
            // Initialize postal_code if it doesn't exist yet
            $this->postalCode = null;
            $this->data['postal_code'] = null;
        }
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Address Information')
                    ->description('Add a new shipping or billing address to your account')
                    ->schema([
                        Grid::make()
                            ->columns(2)
                            ->schema([
                                TextInput::make('address_line')
                                    ->label('Address Line')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Jl. Sudirman No. 123'),
                                    
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
                                    ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),
                                
                                Select::make('city_id')
                                    ->label('City')
                                    ->options(function () {
                                        return collect($this->cities)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search city')
                                    ->disabled(fn (callable $get) => !$get('province_id'))
                                    ->afterStateUpdated(fn (callable $set) => $set('district_id', null)),
                                
                                Select::make('district_id')
                                    ->label('District')
                                    ->options(function () {
                                        return collect($this->districts)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search district')
                                    ->disabled(fn (callable $get) => !$get('city_id'))
                                    ->afterStateUpdated(fn (callable $set) => $set('village_id', null)),
                                
                                Select::make('village_id')
                                    ->label('Village')
                                    ->options(function () {
                                        return collect($this->villages)->pluck('name', 'id')->toArray();
                                    })
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Search village')
                                    ->disabled(fn (callable $get) => !$get('district_id')),
                                    
                                TextInput::make('postal_code')
                                    ->label('Postal Code')
                                    ->required()
                                    ->maxLength(20)
                                    ->default(fn() => $this->postalCode)
                                    ->helperText('Will be auto-filled based on village selection when available'),
                                    
                                TextInput::make('phone_number')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('+62812XXXXXXXX'),
                                    
                                Toggle::make('is_primary')
                                    ->label('Set as Primary Address')
                                    ->helperText('This address will be used as your default shipping and billing address')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
    
    public function create()
    {
        // Validasi form
        $data = $this->form->getState();
        
        // Ensure is_primary is set, default to false if not present
        $data['is_primary'] = $data['is_primary'] ?? false;
        
        try {
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
            
            // Ensure postal_code is set
            if (empty($data['postal_code'])) {
                $data['postal_code'] = '';
            }
            
            // Format alamat lengkap
            $fullAddress = $data['address_line'] . ', ' . 
                           $village->name . ', ' . 
                           $district->name . ', ' . 
                           $city->name . ', ' . 
                           $province->name . ' ' . 
                           $data['postal_code'];
                           
            $data['full_address'] = $fullAddress;
            
            // Tambahkan user_id secara manual
            $data['user_id'] = auth()->id();
            
            // Coba simpan data
            $address = UserAddress::create($data);
            
            // Atur sebagai alamat utama jika diperlukan
            if ($data['is_primary']) {
                $address->setPrimary();
            }
            
            // Kirim notifikasi
            Notification::make()
                ->success()
                ->title('Address added successfully')
                ->body('Your new address has been saved to your account')
                ->send();
                
            // Redirect ke halaman utama
            return redirect()->to('/user-addresses');
            
        } catch (\Exception $e) {
            // Tangkap error
            Notification::make()
                ->danger()
                ->title('Error adding address')
                ->body($e->getMessage())
                ->send();
        }
    }
}
?>

<x-layouts.app>
    @volt('user-addresses.create')
        <x-app.container class="max-w-3xl">
            <div class="flex items-center justify-between mb-5">
                <x-app.heading title="Add New Address" description="Create a new shipping or billing address for your account" :border="false" />
            </div>
            
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <form wire:submit.prevent="create" class="space-y-6 p-6">
                    {{ $this->form }}
                    
                    <div class="flex justify-end gap-x-3 pt-4 border-t">
                        <x-button tag="a" href="/user-addresses" color="secondary">
                            Cancel
                        </x-button>
                        <x-button type="submit" class="text-white bg-primary-600 hover:bg-primary-500">
                            <span class="flex items-center gap-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Save Address
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>