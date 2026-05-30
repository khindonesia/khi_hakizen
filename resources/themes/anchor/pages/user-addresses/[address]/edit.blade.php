<?php

use App\Actions\UpdateUserAddressAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Livewire\Volt\Component;
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('user-addresses.edit');

new class extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public array $provinces = [];
    public array $cities = [];
    public array $districts = [];
    public array $subdistricts = [];
    public int|string|null $addressId = null;
    public bool $isPrimary = false;
    public bool $locationResolved = true;

    protected function action(): UpdateUserAddressAction
    {
        return app(UpdateUserAddressAction::class);
    }

    public function mount(string $address): void
    {
        $this->addressId = $address;
        $initialState = $this->action()->initialState(auth()->user(), $address);

        $this->isPrimary = $initialState['is_primary'];
        $this->locationResolved = $initialState['location_resolved'];
        $this->provinces = $initialState['provinces'];
        $this->cities = $initialState['cities'];
        $this->districts = $initialState['districts'];
        $this->subdistricts = $initialState['subdistricts'];
        $this->form->fill($initialState['form']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Edit address')
                    ->description('Update RajaOngkir region data, phone number, and the address line itself.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('address_line')
                                    ->label('Street address')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Jl. Sudirman No. 123')
                                    ->columnSpanFull(),
                                Hidden::make('province_name'),
                                Hidden::make('city_name'),
                                Hidden::make('district_name'),
                                Hidden::make('subdistrict_name'),
                                Select::make('address_type')
                                    ->label('Address type')
                                    ->options([
                                        'Home' => 'Home',
                                        'Office' => 'Office',
                                        'Other' => 'Other',
                                    ])
                                    ->required(),
                                Select::make('province_id')
                                    ->label('Province')
                                    ->options(fn (): array => collect($this->provinces)->pluck('name', 'code')->all())
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Select province')
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $province = collect($this->provinces)->firstWhere('code', $get('province_id'));
                                        $set('province_name', data_get($province, 'name'));
                                        $set('city_id', null);
                                        $set('city_name', null);
                                        $set('district_id', null);
                                        $set('district_name', null);
                                        $set('subdistrict_id', null);
                                        $set('subdistrict_name', null);
                                    }),
                                Select::make('city_id')
                                    ->label('City / Regency')
                                    ->options(fn (Get $get): array => filled($get('province_id'))
                                        ? collect($this->cities)->pluck('name', 'code')->all()
                                        : [])
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Select city or regency')
                                    ->disabled(fn (Get $get): bool => blank($get('province_id')))
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $city = collect($this->cities)->firstWhere('code', $get('city_id'));
                                        $set('city_name', data_get($city, 'name'));
                                        $set('district_id', null);
                                        $set('district_name', null);
                                        $set('subdistrict_id', null);
                                        $set('subdistrict_name', null);
                                    }),
                                Select::make('district_id')
                                    ->label('District / Kecamatan')
                                    ->options(fn (Get $get): array => filled($get('city_id'))
                                        ? collect($this->districts)->pluck('name', 'code')->all()
                                        : [])
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Select district')
                                    ->disabled(fn (Get $get): bool => blank($get('city_id')))
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $district = collect($this->districts)->firstWhere('code', $get('district_id'));
                                        $set('district_name', data_get($district, 'name'));
                                        $set('subdistrict_id', null);
                                        $set('subdistrict_name', null);
                                    }),
                                Select::make('subdistrict_id')
                                    ->label('Subdistrict / Kelurahan')
                                    ->options(fn (Get $get): array => filled($get('district_id'))
                                        ? collect($this->subdistricts)->pluck('name', 'code')->all()
                                        : [])
                                    ->searchable()
                                    ->reactive()
                                    ->required()
                                    ->placeholder('Select subdistrict')
                                    ->disabled(fn (Get $get): bool => blank($get('district_id')))
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $subdistrict = collect($this->subdistricts)->firstWhere('code', $get('subdistrict_id'));
                                        $set('subdistrict_name', data_get($subdistrict, 'name'));
                                        if (filled(data_get($subdistrict, 'zip_code'))) {
                                            $set('postal_code', data_get($subdistrict, 'zip_code'));
                                        }
                                    }),
                                TextInput::make('postal_code')
                                    ->label('Postal code')
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('12630'),
                                TextInput::make('phone_number')
                                    ->label('Phone number')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20)
                                    ->placeholder('+62812XXXXXXXX'),
                                Toggle::make('is_primary')
                                    ->label('Set as primary address')
                                    ->helperText('Primary address is used by default during checkout.')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false)
                                    ->disabled(fn (): bool => $this->isPrimary)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function update(): void
    {
        $state = $this->form->getState();

        try {
            $this->action()->update(auth()->user(), $this->addressId, $state);

            Notification::make()
                ->success()
                ->title('Address updated')
                ->body('Your address changes have been saved.')
                ->send();

            $this->redirect('/user-addresses');
        } catch (\Throwable $throwable) {
            Log::error('Unable to update user address', [
                'address_id' => $this->addressId,
                'user_id' => auth()->id(),
                'message' => $throwable->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Unable to update address')
                ->body('RajaOngkir data could not be resolved. Please re-select the region fields.')
                ->send();
        }
    }

    public function updatedDataProvinceId(?string $value): void
    {
        $this->cities = $this->action()->cities($value);
        $this->districts = [];
        $this->subdistricts = [];
    }

    public function updatedDataCityId(?string $value): void
    {
        $this->districts = $this->action()->districts($value);
        $this->subdistricts = [];
    }

    public function updatedDataDistrictId(?string $value): void
    {
        $this->subdistricts = $this->action()->subdistricts($value);
    }
};
?>

<x-layouts.app>
    @volt('user-addresses.edit')
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-50/90 via-blue-50/40 to-transparent"></div>
            <x-app.container class="relative py-8 sm:py-10 lg:py-12">
                <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="stitch-chip mb-3 inline-flex">Addresses</div>
                        <h1 class="text-3xl font-semibold tracking-tight text-zinc-900 md:text-[40px]">
                            Edit address
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500 md:text-base">
                            Keep your delivery address fresh and aligned with RajaOngkir data.
                        </p>
                    </div>

                    <x-button tag="a" href="/user-addresses" color="secondary" class="inline-flex items-center gap-2 self-start">
                        Back to addresses
                    </x-button>
                </div>

                @if (! $locationResolved)
                    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-900">
                        This address was loaded from older location names. Please re-select province, city, district, and subdistrict before saving.
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <div class="stitch-panel p-6 md:p-8">
                            <div class="mb-6 flex flex-col gap-3 border-b border-zinc-200/70 pb-5 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-zinc-900">Address form</h2>
                                    <p class="mt-1 text-sm leading-6 text-zinc-500">
                                        Update the hierarchy from the province down to the subdistrict, then save your changes.
                                    </p>
                                </div>
                            </div>

                            <form wire:submit.prevent="update" class="space-y-6">
                                {{ $this->form }}

                                <div class="flex flex-col-reverse gap-3 border-t border-zinc-200/70 pt-4 sm:flex-row sm:justify-end">
                                    <x-button tag="a" href="/user-addresses" color="secondary">
                                        Cancel
                                    </x-button>
                                    <x-button type="submit" class="bg-red-600 text-white hover:bg-red-500">
                                        Update address
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <aside class="lg:col-span-4 space-y-6">
                        @if ($isPrimary)
                            <div class="stitch-panel border-amber-200 bg-amber-50 p-6">
                                <div class="flex items-start gap-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <h3 class="text-sm font-semibold text-amber-900">Primary address</h3>
                                        <p class="mt-1 text-sm leading-6 text-amber-800">
                                            This address is marked as your default checkout address.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="stitch-panel p-6">
                            <h3 class="text-base font-semibold text-zinc-900">Editing tips</h3>
                            <ol class="mt-4 space-y-4 text-sm leading-6 text-zinc-600">
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">1</span>
                                    <span>Change province first if the whole region moved.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">2</span>
                                    <span>Each downstream field reloads from RajaOngkir once its parent changes.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">3</span>
                                    <span>Save after checking phone number and postal code.</span>
                                </li>
                            </ol>
                        </div>
                    </aside>
                </div>
            </x-app.container>
        </div>
    @endvolt
</x-layouts.app>
