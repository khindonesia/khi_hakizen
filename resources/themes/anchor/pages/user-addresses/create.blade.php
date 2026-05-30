<?php

use App\Actions\CreateUserAddressAction;
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
name('user-addresses.create');

new class extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public array $provinces = [];
    public array $cities = [];
    public array $districts = [];
    public array $subdistricts = [];

    protected function action(): CreateUserAddressAction
    {
        return app(CreateUserAddressAction::class);
    }

    public function mount(): void
    {
        $initialState = $this->action()->initialState(auth()->user());

        $this->provinces = $initialState['provinces'];
        $this->form->fill($initialState['form']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Address details')
                    ->description('Pick region data from RajaOngkir. Postal code stays manual because the public API does not expose it.')
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
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $state = $this->form->getState();

        try {
            $this->action()->create(auth()->user(), $state);

            Notification::make()
                ->success()
                ->title('Address saved')
                ->body('Your new address has been added.')
                ->send();

            $this->redirect('/user-addresses');
        } catch (\Throwable $throwable) {
            Log::error('Unable to save user address', [
                'user_id' => auth()->id(),
                'message' => $throwable->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Unable to save address')
                ->body('RajaOngkir data could not be resolved. Please try again.')
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
    @volt('user-addresses.create')
        <div class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-50/90 via-blue-50/40 to-transparent"></div>
            <x-app.container class="relative py-8 sm:py-10 lg:py-12">
                <div class="mb-8 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-3xl">
                        <div class="stitch-chip mb-3 inline-flex">Addresses</div>
                        <h1 class="text-3xl font-semibold tracking-tight text-zinc-900 md:text-[40px]">
                            Add new address
                        </h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-500 md:text-base">
                            Pull region data from RajaOngkir, keep your address record current, and mark one address as primary for checkout.
                        </p>
                    </div>

                    <x-button tag="a" href="/user-addresses" color="secondary" class="inline-flex items-center gap-2 self-start">
                        Back to addresses
                    </x-button>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <div class="stitch-panel p-6 md:p-8">
                            <div class="mb-6 flex flex-col gap-3 border-b border-zinc-200/70 pb-5 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <h2 class="text-xl font-semibold text-zinc-900">Address form</h2>
                                    <p class="mt-1 text-sm leading-6 text-zinc-500">
                                        Select province, city, district, and subdistrict in order. Postal code is manual.
                                    </p>
                                </div>
                            </div>

                            <form wire:submit.prevent="create" class="space-y-6">
                                {{ $this->form }}

                                <div class="flex flex-col-reverse gap-3 border-t border-zinc-200/70 pt-4 sm:flex-row sm:justify-end">
                                    <x-button tag="a" href="/user-addresses" color="secondary">
                                        Cancel
                                    </x-button>
                                    <x-button type="submit" class="bg-red-600 text-white hover:bg-red-500">
                                        Save address
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <aside class="lg:col-span-4 space-y-6">
                        <div class="stitch-panel p-6">
                            <h3 class="text-base font-semibold text-zinc-900">Input flow</h3>
                            <ol class="mt-4 space-y-4 text-sm leading-6 text-zinc-600">
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">1</span>
                                    <span>Pick province first so regency list can load.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">2</span>
                                    <span>Continue down the hierarchy until subdistrict is selected.</span>
                                </li>
                                <li class="flex gap-3">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-zinc-100 text-xs font-semibold text-zinc-700">3</span>
                                    <span>Enter postal code and phone number, then save.</span>
                                </li>
                            </ol>
                        </div>
                    </aside>
                </div>
            </x-app.container>
        </div>
    @endvolt
</x-layouts.app>
