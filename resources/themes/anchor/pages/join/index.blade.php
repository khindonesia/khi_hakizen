<?php
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use Filament\Notifications\Notification;
use function Laravel\Folio\{middleware, name};

middleware(['guest', 'throttle:login']);
name('join');

new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $interest = 'cagar-budaya';
    public string $phone_number = '';
    public string $birth_year = '';
    public string $country = '';
    public string $province = '';
    public string $province_code = '';
    public string $city = '';

    // State for successful registration
    public bool $isRegistered = false;
    public ?User $registeredUser = null;

    public function mount()
    {
        if (Auth::check()) {
            return redirect(config('devdojo.auth.settings.redirect_after_auth', '/dashboard'));
        }
    }

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:6',
        'interest' => 'required|in:cagar-budaya,edukasi,kegiatan-komunitas,lainnya',
        'phone_number' => 'required|min:8|max:20',
        'birth_year' => 'required|integer|min:1900|max:2026',
        'country' => 'required|string|size:2',
        'province' => 'required|string|min:2|max:100',
        'city' => 'required|string|min:2|max:100',
    ];

    protected $validationAttributes = [
        'name' => 'nama lengkap',
        'email' => 'alamat email',
        'password' => 'password',
        'interest' => 'area ketertarikan',
        'phone_number' => 'nomor telepon',
        'birth_year' => 'tahun lahir',
        'country' => 'negara',
        'province' => 'provinsi',
        'city' => 'kota',
    ];

    public function registerMember(): void
    {
        $this->validate();

        // Create the user
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'reason_for_joining' => $this->interest,
            'verified' => 0,
            'phone_number' => $this->phone_number,
            'birth_year' => $this->birth_year,
            'country' => $this->country,
            'province' => $this->province,
            'city' => $this->city,
        ]);

        $this->registeredUser = $user;
        $this->isRegistered = true;

        Notification::make()
            ->success()
            ->title('Pendaftaran Berhasil!')
            ->body('Akun Anda sedang menunggu verifikasi oleh admin Komunitas Historia Indonesia.')
            ->send();
    }
};
?>

<x-layouts.marketing :seo="[
    'title' => 'Join Membership - Komunitas Historia Indonesia',
    'description' => 'Bergabung sebagai anggota resmi Komunitas Historia Indonesia. Akses arsip sejarah, tur, dan kontribusi nyata.',
]">
<div class="relative min-h-screen py-12 md:py-24">
        <x-container class="max-w-6xl">
            @volt('join')
            <div>
                @if(!$isRegistered)
                    <!-- Split 2-Column Registration Hero -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                        
                        <!-- Left Column: Copy & Privileges -->
                        <div class="lg:col-span-6 space-y-8">
                            <div class="space-y-4">
                                <div class="stitch-chip">
                                    <span class="material-symbols-outlined text-[14px]">history_edu</span>
                                    {{ setting('join_page_chip', 'Keanggotaan KHI') }}
                                </div>
                                <h1 class="text-4xl font-semibold leading-[1.1] tracking-tight text-zinc-900 md:text-[56px]">
                                    {{ setting('join_page_title', 'Become a Keeper of History') }}
                                </h1>
                                <p class="text-sm leading-[1.6] text-zinc-500 md:text-base">
                                    {{ setting('join_page_subtitle', 'Join a prestigious community dedicated to scholarly preservation and public advocacy. By becoming an official member, you directly fund local heritage restoration campaigns and independent archival expeditions.') }}
                                </p>
                            </div>

                            <!-- Historical Archive Cabinet Image -->
                            <div class="relative max-h-[220px] overflow-hidden rounded-[28px] border border-zinc-200/80 shadow-sm">
                                <img src="{{ setting('join_page_image', 'https://static.wixstatic.com/media/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg/v1/fill/w_1000,h_666,al_c,q_85,usm_0.66_1.00_0.01/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg') }}" 
                                     alt="Historical Archive Cabinet" class="w-full h-full object-cover">
                            </div>

                            <!-- Membership Privileges Card (bg-card-tint-cream) -->
                            <div class="stitch-panel space-y-6 p-8">
                                <h3 class="text-lg font-semibold tracking-tight text-zinc-900">{{ setting('join_page_privileges_title', 'Eksklusivitas Anggota KHI') }}</h3>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <!-- Privilege 1 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">folder_open</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">{{ setting('join_page_privilege_1_title', 'Exclusive Archives') }}</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">{{ setting('join_page_privilege_1_desc', 'Full digital access to rare manuscripts and primary source documents.') }}</p>
                                        </div>
                                    </div>

                                    <!-- Privilege 2 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">{{ setting('join_page_privilege_2_title', 'Scholarly Journals') }}</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">{{ setting('join_page_privilege_2_desc', 'Quarterly physical delivery of KHI historical monographs.') }}</p>
                                        </div>
                                    </div>

                                    <!-- Privilege 3 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">event_seat</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">{{ setting('join_page_privilege_3_title', 'Curated Events') }}</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">{{ setting('join_page_privilege_3_desc', 'Priority reservation and 20% discount on history tours.') }}</p>
                                        </div>
                                    </div>

                                    <!-- Privilege 4 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">how_to_vote</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">{{ setting('join_page_privilege_4_title', 'Voting Rights') }}</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">{{ setting('join_page_privilege_4_desc', 'Vote on community restoration proposals and board decisions.') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Registration Form Card -->
                        <div class="lg:col-span-6 stitch-panel space-y-8 p-8 md:p-12">
                            <div class="space-y-1.5">
                                <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">{{ setting('join_page_form_title', 'Register Today') }}</h2>
                                <p class="text-xs text-zinc-500">{{ setting('join_page_form_subtitle', 'Secure your place in the modern archive.') }}</p>
                            </div>

                            <form wire:submit.prevent="registerMember" class="space-y-5">
                                <!-- Full Name -->
                                <div class="space-y-1.5">
                                    <label for="name" class="text-xs font-semibold text-zinc-700">Nama Lengkap</label>
                                    <input type="text" id="name" wire:model.defer="name" placeholder="Dr. Johannes van der Bosch"
                                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                    @error('name') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Email Address -->
                                <div class="space-y-1.5">
                                    <label for="email" class="text-xs font-semibold text-zinc-700">Alamat Email</label>
                                    <input type="email" id="email" wire:model.defer="email" placeholder="scholar@institute.edu"
                                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                    @error('email') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Password -->
                                <div class="space-y-1.5">
                                    <label for="password" class="text-xs font-semibold text-zinc-700">Password Keanggotaan</label>
                                    <input type="password" id="password" wire:model.defer="password" placeholder="••••••••"
                                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                    @error('password') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- Birth Year & Phone Number -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="space-y-1.5">
                                        <label for="birth_year" class="text-xs font-semibold text-zinc-700">Tahun Lahir</label>
                                        <input type="number" id="birth_year" wire:model.defer="birth_year" placeholder="Contoh: 1995" min="1900" max="2026"
                                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                        @error('birth_year') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="space-y-1.5">
                                        <label for="phone_number" class="text-xs font-semibold text-zinc-700">Nomor Telepon</label>
                                        <input type="tel" id="phone_number" wire:model.defer="phone_number" placeholder="08123456789"
                                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                        @error('phone_number') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Negara, Provinsi, & Kota (Dynamic Nested Selection) -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-data="{
                                    countries: {{ file_get_contents(resource_path('data/countries.json')) }},
                                    countrySearch: '',
                                    selectedCountryCode: @entangle('country'),
                                    selectedCountryName: '',
                                    showCountryDropdown: false,
                                    
                                    provinces: [],
                                    provinceSearch: '',
                                    selectedProvinceCode: @entangle('province_code'),
                                    selectedProvinceName: @entangle('province'),
                                    showProvinceDropdown: false,
                                    isLoadingProvinces: false,
                                    hasProvinceError: false,
                                    fallbackToProvinceTextInput: false,
                                    
                                    citySearch: '',
                                    selectedCity: @entangle('city'),
                                    cities: [],
                                    showCityDropdown: false,
                                    isLoadingCities: false,
                                    hasCityError: false,
                                    fallbackToTextInput: false,
                                    
                                    get filteredCountries() {
                                        if (!this.countrySearch) return this.countries;
                                        return this.countries.filter(c => c.name.toLowerCase().includes(this.countrySearch.toLowerCase()));
                                    },
                                    
                                    get filteredProvinces() {
                                        if (!this.provinceSearch) return this.provinces;
                                        return this.provinces.filter(p => p.name.toLowerCase().includes(this.provinceSearch.toLowerCase()));
                                    },
                                    
                                    init() {
                                        if (this.selectedCountryCode) {
                                            let found = this.countries.find(c => c.code === this.selectedCountryCode);
                                            if (found) {
                                                this.selectedCountryName = found.name;
                                                this.countrySearch = found.name;
                                            }
                                            this.fetchProvinces();
                                        }
                                        if (this.selectedProvinceName) {
                                            this.provinceSearch = this.selectedProvinceName;
                                        }
                                        if (this.selectedCity) {
                                            this.citySearch = this.selectedCity;
                                        }
                                        
                                        this.$watch('selectedCountryCode', (newVal) => {
                                            this.selectedProvinceCode = '';
                                            this.selectedProvinceName = '';
                                            this.provinceSearch = '';
                                            this.provinces = [];
                                            this.fallbackToProvinceTextInput = false;
                                            this.hasProvinceError = false;
                                            
                                            this.selectedCity = '';
                                            this.citySearch = '';
                                            this.cities = [];
                                            this.fallbackToTextInput = false;
                                            this.hasCityError = false;
                                            
                                            if (newVal) {
                                                let found = this.countries.find(c => c.code === newVal);
                                                if (found) {
                                                    this.selectedCountryName = found.name;
                                                    this.countrySearch = found.name;
                                                }
                                                this.fetchProvinces();
                                            } else {
                                                this.selectedCountryName = '';
                                                this.countrySearch = '';
                                            }
                                        });
                                        
                                        this.$watch('selectedProvinceCode', (newVal) => {
                                            this.selectedCity = '';
                                            this.citySearch = '';
                                            this.cities = [];
                                            this.fallbackToTextInput = false;
                                            this.hasCityError = false;
                                        });
                                        
                                        this.$watch('provinceSearch', (newVal) => {
                                             if (this.fallbackToProvinceTextInput) {
                                                 this.selectedProvinceName = newVal;
                                                 return;
                                             }
                                             if (newVal !== this.selectedProvinceName) {
                                                 this.selectedProvinceCode = '';
                                                 this.selectedProvinceName = '';
                                             }
                                         });
                                        
                                        let debounceTimer;
                                        this.$watch('citySearch', (newVal) => {
                                            if (this.fallbackToTextInput) {
                                                this.selectedCity = newVal;
                                                return;
                                            }
                                            clearTimeout(debounceTimer);
                                            if (!newVal || newVal.length < 1) {
                                                this.cities = [];
                                                return;
                                            }
                                            debounceTimer = setTimeout(() => {
                                                this.fetchCities();
                                            }, 500);
                                        });
                                    },
                                    
                                    selectCountry(country) {
                                        this.selectedCountryCode = country.code;
                                        this.selectedCountryName = country.name;
                                        this.countrySearch = country.name;
                                        this.showCountryDropdown = false;
                                    },
                                    
                                    selectProvince(prov) {
                                        this.selectedProvinceCode = prov.adminCode1;
                                        this.selectedProvinceName = prov.name;
                                        this.provinceSearch = prov.name;
                                        this.showProvinceDropdown = false;
                                    },
                                    
                                    selectCity(city) {
                                        this.selectedCity = city.name;
                                        this.citySearch = city.name;
                                        this.showCityDropdown = false;
                                    },
                                    
                                    fetchProvinces() {
                                        if (!this.selectedCountryCode) return;
                                        this.isLoadingProvinces = true;
                                        this.hasProvinceError = false;
                                        this.fallbackToProvinceTextInput = false;
                                        
                                        fetch(`/api/geonames/provinces?country=${this.selectedCountryCode}`)
                                            .then(async res => {
                                                if (!res.ok) {
                                                    throw new Error('API limit reached or downstream error');
                                                }
                                                return res.json();
                                            })
                                            .then(data => {
                                                if (data.geonames) {
                                                    this.provinces = data.geonames.map(p => ({
                                                        name: p.name,
                                                        adminCode1: p.adminCode1
                                                    }));
                                                } else {
                                                    this.provinces = [];
                                                }
                                                this.isLoadingProvinces = false;
                                            })
                                            .catch(err => {
                                                this.isLoadingProvinces = false;
                                                this.hasProvinceError = true;
                                                this.fallbackToProvinceTextInput = true;
                                            });
                                    },
                                    
                                    fetchCities() {
                                        if (!this.selectedCountryCode) return;
                                        this.isLoadingCities = true;
                                        this.hasCityError = false;
                                        
                                        let url = `/api/geonames/cities?country=${this.selectedCountryCode}&name_startsWith=${encodeURIComponent(this.citySearch)}`;
                                        if (this.selectedProvinceCode) {
                                            url += `&adminCode1=${this.selectedProvinceCode}`;
                                        }
                                        
                                        fetch(url)
                                            .then(async res => {
                                                if (!res.ok) {
                                                    throw new Error('API limit reached or downstream error');
                                                }
                                                return res.json();
                                            })
                                            .then(data => {
                                                if (data.geonames) {
                                                    this.cities = data.geonames;
                                                } else {
                                                    this.cities = [];
                                                }
                                                this.isLoadingCities = false;
                                            })
                                            .catch(err => {
                                                this.isLoadingCities = false;
                                                this.hasCityError = true;
                                                this.fallbackToTextInput = true;
                                            });
                                    }
                                }">
                                    <!-- Negara Field -->
                                    <div class="space-y-1.5">
                                        <label for="country" class="text-xs font-semibold text-zinc-700">Pilih Negara</label>
                                        <select id="country" 
                                            x-model="selectedCountryCode"
                                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                            <option value="">Pilih Negara...</option>
                                            <template x-for="c in countries" :key="c.code">
                                                <option :value="c.code" x-text="c.name" :selected="c.code === selectedCountryCode"></option>
                                            </template>
                                        </select>
                                        @error('country') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Provinsi Field -->
                                    <div class="space-y-1.5 relative">
                                        <label for="province" class="text-xs font-semibold text-zinc-700">Pilih Provinsi</label>
                                        <input type="text" 
                                            id="province"
                                            x-model="provinceSearch"
                                            :disabled="!selectedCountryCode || isLoadingProvinces"
                                            @focus="if(selectedCountryCode && !fallbackToProvinceTextInput) showProvinceDropdown = true"
                                            @click="if(selectedCountryCode && !fallbackToProvinceTextInput) showProvinceDropdown = true"
                                            placeholder="Cari provinsi..."
                                            :class="(!selectedCountryCode || isLoadingProvinces) ? 'bg-zinc-50 text-zinc-400 cursor-not-allowed' : ''"
                                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">

                                        <!-- Province Dropdown -->
                                        <div x-show="showProvinceDropdown && !fallbackToProvinceTextInput && selectedCountryCode" 
                                            @click.away="showProvinceDropdown = false" 
                                            x-transition
                                            class="absolute right-0 left-0 z-50 mt-1 max-h-60 overflow-auto rounded-2xl border border-zinc-200 bg-white py-1 text-sm shadow-lg focus:outline-none">
                                            <template x-for="p in filteredProvinces" :key="p.adminCode1">
                                                <button type="button" 
                                                    @click="selectProvince(p)" 
                                                    class="w-full px-4 py-2 text-left text-zinc-900 hover:bg-zinc-100 transition-colors">
                                                    <span x-text="p.name"></span>
                                                </button>
                                            </template>
                                            <div x-show="filteredProvinces.length === 0 && provinceSearch" class="px-4 py-2 text-zinc-500">
                                                Provinsi tidak ditemukan
                                            </div>
                                        </div>

                                        <!-- Loading Indicator -->
                                        <div x-show="isLoadingProvinces" class="mt-1 flex items-center gap-1.5 text-xs text-zinc-400">
                                            <svg class="animate-spin h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Memuat provinsi...</span>
                                        </div>

                                        <!-- Fallback Warning Message -->
                                        <div x-show="hasProvinceError" class="mt-1 text-[11px] text-amber-600 flex items-start gap-1">
                                            <span class="material-symbols-outlined text-[14px]">warning</span>
                                            <span>Gagal memuat provinsi secara otomatis, silakan ketik manual di atas.</span>
                                        </div>
                                        @error('province') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Kota Field -->
                                    <div class="space-y-1.5 relative">
                                        <label for="city" class="text-xs font-semibold text-zinc-700">Pilih Kota</label>
                                        <input type="text" 
                                            id="city"
                                            x-model="citySearch"
                                            :disabled="!selectedCountryCode || (!selectedProvinceName && !fallbackToProvinceTextInput)"
                                            @focus="if(selectedCountryCode && !fallbackToTextInput) showCityDropdown = true"
                                            @click="if(selectedCountryCode && !fallbackToTextInput) showCityDropdown = true"
                                            placeholder="Cari kota..."
                                            :class="(!selectedCountryCode || (!selectedProvinceName && !fallbackToProvinceTextInput)) ? 'bg-zinc-50 text-zinc-400 cursor-not-allowed' : ''"
                                            class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 placeholder:text-zinc-400 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                        
                                        <!-- Loading Indicator -->
                                        <div x-show="isLoadingCities" class="mt-1 flex items-center gap-1.5 text-xs text-zinc-400">
                                            <svg class="animate-spin h-3.5 w-3.5 text-red-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span>Memuat kota...</span>
                                        </div>

                                        <!-- Fallback Warning Message -->
                                        <div x-show="hasCityError" class="mt-1 text-[11px] text-amber-600 flex items-start gap-1">
                                            <span class="material-symbols-outlined text-[14px]">warning</span>
                                            <span>Gagal memuat kota secara otomatis, silakan ketik manual di atas.</span>
                                        </div>

                                        <!-- City Dropdown -->
                                        <div x-show="showCityDropdown && !fallbackToTextInput && selectedCountryCode" 
                                            @click.away="showCityDropdown = false" 
                                            x-transition
                                            class="absolute right-0 left-0 z-50 mt-1 max-h-60 overflow-auto rounded-2xl border border-zinc-200 bg-white py-1 text-sm shadow-lg focus:outline-none">
                                            <template x-for="city in cities" :key="city.geonameId">
                                                <button type="button" 
                                                    @click="selectCity(city)" 
                                                    class="w-full px-4 py-2 text-left text-zinc-900 hover:bg-zinc-100 transition-colors">
                                                    <span x-text="city.name"></span>
                                                    <span x-show="city.adminName1" class="text-xs text-zinc-400" x-text="', ' + city.adminName1"></span>
                                                </button>
                                            </template>
                                            <div x-show="cities.length === 0 && citySearch && !isLoadingCities" class="px-4 py-2 text-zinc-500">
                                                Kota tidak ditemukan
                                            </div>
                                            <div x-show="!citySearch" class="px-4 py-2 text-zinc-400 text-xs">
                                                Ketik untuk mencari kota...
                                            </div>
                                        </div>
                                        @error('city') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <!-- Area of Interest Dropdown -->
                                <div class="space-y-1.5">
                                    <label for="interest" class="text-xs font-semibold text-zinc-700">Area Ketertarikan Utama</label>
                                    <select id="interest" wire:model.defer="interest"
                                        class="w-full rounded-2xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:border-red-400 focus:outline-none focus:ring-4 focus:ring-red-100 transition-all">
                                        <option value="cagar-budaya">Pelestarian Cagar Budaya</option>
                                        <option value="edukasi">Edukasi Sejarah di Sekolah</option>
                                        <option value="kegiatan-komunitas">Kegiatan Tur & Komunitas</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                    @error('interest') <span class="text-xs text-error font-medium">{{ $message }}</span> @enderror
                                </div>

                                <!-- CTA Button -->
                                <button type="submit"
                                    class="mt-8 flex w-full items-center justify-center gap-2 rounded-full bg-red-600 py-4 font-semibold text-white transition duration-200 hover:bg-red-500 shadow-sm">
                                    {{ setting('join_page_form_btn_text', 'Bergabung Sekarang') }} <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </button>
                            </form>

                            <div class="border-t border-zinc-200/80 pt-6 text-center">
                                <p class="text-[10px] leading-relaxed text-zinc-400">
                                    {{ setting('join_page_form_footer', 'By joining, you agree to our scholarly code of conduct.') }}
                                </p>
                            </div>
                        </div>

                    </div>
                @else
                    <!-- Success State: Waiting for Admin Verification -->
                    <div class="relative mx-auto max-w-xl space-y-8 overflow-hidden rounded-[32px] border border-zinc-200/80 bg-white p-8 text-center shadow-2xl md:p-12">
                        <!-- Red-amber gradient header indicator -->
                        <div class="absolute left-0 right-0 top-0 h-2 bg-gradient-to-r from-red-500 via-amber-500 to-orange-500"></div>
                        
                        <div class="space-y-3">
                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-600 animate-pulse">
                                <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">hourglass_top</span>
                            </div>
                            <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">Pendaftaran Berhasil!</h2>
                            <p class="text-xs text-zinc-500">Terima Kasih, Keeper of History.</p>
                        </div>

                        <!-- Verification Status Details -->
                        <div class="border border-[#E9E9E8] rounded-2xl bg-[#FFFDF5] p-6 text-left relative space-y-6">
                            <!-- Status Header -->
                            <div class="flex justify-between items-center border-b border-dashed border-[#E9E9E8] pb-4">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">KHI MEMBERSHIP STATUS</span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    Menunggu Verifikasi
                                </span>
                            </div>

                            <!-- Context Message -->
                            <div class="space-y-3 text-xs leading-relaxed text-zinc-600">
                                <p>
                                    Halo <strong class="text-zinc-900">{{ $registeredUser?->name }}</strong>, akun Anda telah berhasil didaftarkan dan saat ini sedang berada dalam antrean peninjauan oleh Admin Komunitas Historia Indonesia (KHI).
                                </p>
                                <p>
                                    Tim kami akan memverifikasi kelayakan data keanggotaan Anda. Proses ini biasanya memakan waktu maksimal 24 jam. Anda akan menerima email pemberitahuan segera setelah akun Anda disetujui.
                                </p>
                                
                                <!-- User Info Summary -->
                                <div class="grid grid-cols-2 gap-4 border-t border-[#E9E9E8] pt-4">
                                    <div>
                                        <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">Email Terdaftar</span>
                                        <span class="text-xs font-bold text-[#000000] mt-0.5 block truncate">{{ $registeredUser?->email }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">No. Telepon</span>
                                        <span class="text-xs font-bold text-[#000000] mt-0.5 block truncate">{{ $registeredUser?->phone_number }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">Tahun Lahir</span>
                                        <span class="text-xs font-bold text-[#000000] mt-0.5 block truncate">{{ $registeredUser?->birth_year }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">Domisili</span>
                                        <span class="text-xs font-bold text-[#000000] mt-0.5 block truncate">{{ $registeredUser?->city }}, {{ $registeredUser?->province }}, {{ $registeredUser?->country }}</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">Area Ketertarikan</span>
                                        <span class="text-xs font-bold text-[#000000] mt-0.5 block capitalize">{{ str_replace('-', ' ', $registeredUser?->reason_for_joining ?? 'General') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Actions -->
                        <div class="pt-4">
                            <a href="{{ url('/') }}" class="w-full bg-[#df1c24] hover:bg-opacity-95 text-white font-bold py-3.5 rounded-xl transition duration-200 flex items-center justify-center gap-1.5 shadow-sm">
                                <span class="material-symbols-outlined text-[18px]">home</span>
                                Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                @endif
            </div>
            @endvolt
        </x-container>
    </div>
</x-layouts.marketing>
