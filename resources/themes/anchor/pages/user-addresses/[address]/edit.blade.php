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
    
    new class extends Component implements HasForms
    {
        use InteractsWithForms;
        
        public ?array $data = [];
        
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
            
            // Simpan state yang diperlukan
            $this->addressModel = $addressModel;
            $this->isPrimary = $addressModel->is_primary;
            
            // Fill form
            $this->form->fill($addressModel->toArray());
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
                                        ->inline(false)
                                        ->disabled(fn() => $this->isPrimary),
                                ]),
                        ]),
                ])
                ->statePath('data');
        }
        
        public function update(): void
        {
            // Validasi form
            $data = $this->form->getState();
            
            try {
                // Get address model from DB again to avoid stale data
                $address = UserAddress::findOrFail($this->addressId);
                
                // Update alamat
                $address->update($data);
                
                // Atur sebagai alamat utama jika diperlukan
                if ($data['is_primary'] && !$address->is_primary) {
                    $address->setPrimary();
                }
                
                // Kirim notifikasi
                Notification::make()
                    ->success()
                    ->title('Address updated successfully')
                    ->body('Your address has been updated')
                    ->send();
                    
                // Redirect ke halaman utama - Fixed
                $this->redirect('/user-addresses');
                
            } catch (\Exception $e) {
                // Tangkap error
                Notification::make()
                    ->danger()
                    ->title('Error updating address')
                    ->body($e->getMessage())
                    ->send();
            }
        }
    }
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
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Update Address
                            </span>
                        </x-button>
                    </div>
                </form>
            </div>
            
            @if($isPrimary)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                <div class="flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-500 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="font-medium">This is your primary address</p>
                        <p class="mt-1">This address is currently set as your primary address and will be used as the default for shipping and billing.</p>
                    </div>
                </div>
            </div>
            @endif
        </x-app.container>
    @endvolt
</x-layouts.app>