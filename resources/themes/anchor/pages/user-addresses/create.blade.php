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
        
        public function mount(): void
        {
            $this->form->fill([
                'country' => 'Indonesia',
                'address_type' => 'Home',
                'is_primary' => !auth()->user()->userAddresses()->exists()
            ]);
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
                                        
                                    TextInput::make('city')
                                        ->required()
                                        ->maxLength(100),
                                        
                                    TextInput::make('state')
                                        ->label('State/Province')
                                        ->required()
                                        ->maxLength(100),
                                        
                                    TextInput::make('postal_code')
                                        ->label('Postal Code')
                                        ->required()
                                        ->maxLength(20),
                                        
                                    TextInput::make('country')
                                        ->required()
                                        ->maxLength(100),
                                        
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
            
            // Tambahkan user_id secara manual
            $data['user_id'] = auth()->id();
            
            try {
                // Coba simpan data
                $address = UserAddress::create($data);
                
                // Atur sebagai alamat utama jika diperlukan
                if ($address->is_primary) {
                    $address->setPrimary();
                }
                
                // Reset form
                $this->form->fill();
                
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