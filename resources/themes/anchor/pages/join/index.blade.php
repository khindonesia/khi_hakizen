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
    @push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
    @endpush

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
                                    Keanggotaan KHI
                                </div>
                                <h1 class="text-4xl font-semibold leading-[1.1] tracking-tight text-zinc-900 md:text-[56px]">
                                    Become a Keeper of History
                                </h1>
                                <p class="text-sm leading-[1.6] text-zinc-500 md:text-base">
                                    Join a prestigious community dedicated to scholarly preservation and public advocacy. By becoming an official member, you directly fund local heritage restoration campaigns and independent archival expeditions.
                                </p>
                            </div>

                            <!-- Historical Archive Cabinet Image -->
                            <div class="relative max-h-[220px] overflow-hidden rounded-[28px] border border-zinc-200/80 shadow-sm">
                                <img src="https://static.wixstatic.com/media/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg/v1/fill/w_1000,h_666,al_c,q_85,usm_0.66_1.00_0.01/4dda9f_9ef8c0b1d37349c19b2c061f1f321e2d.jpg" 
                                     alt="Historical Archive Cabinet" class="w-full h-full object-cover">
                            </div>

                            <!-- Membership Privileges Card (bg-card-tint-cream) -->
                            <div class="stitch-panel space-y-6 p-8">
                                <h3 class="text-lg font-semibold tracking-tight text-zinc-900">Eksklusivitas Anggota KHI</h3>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <!-- Privilege 1 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">folder_open</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">Exclusive Archives</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">Full digital access to rare manuscripts and primary source documents.</p>
                                        </div>
                                    </div>

                                    <!-- Privilege 2 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">menu_book</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">Scholarly Journals</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">Quarterly physical delivery of KHI historical monographs.</p>
                                        </div>
                                    </div>

                                    <!-- Privilege 3 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">event_seat</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">Curated Events</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">Priority reservation and 20% discount on history tours.</p>
                                        </div>
                                    </div>

                                    <!-- Privilege 4 -->
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-700 shadow-xs">
                                            <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">how_to_vote</span>
                                        </div>
                                        <div>
                                            <h4 class="text-xs font-bold text-zinc-900">Voting Rights</h4>
                                            <p class="mt-0.5 text-[11px] leading-relaxed text-zinc-500">Vote on community restoration proposals and board decisions.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Registration Form Card -->
                        <div class="lg:col-span-6 stitch-panel space-y-8 p-8 md:p-12">
                            <div class="space-y-1.5">
                                <h2 class="text-2xl font-semibold tracking-tight text-zinc-900">Register Today</h2>
                                <p class="text-xs text-zinc-500">Secure your place in the modern archive.</p>
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
                                    Bergabung Sekarang <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                                </button>
                            </form>

                            <div class="border-t border-zinc-200/80 pt-6 text-center">
                                <p class="text-[10px] leading-relaxed text-zinc-400">
                                    By joining, you agree to our scholarly code of conduct.
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
                            </div>

                            <!-- User Info Summary -->
                            <div class="grid grid-cols-2 gap-4 border-t border-[#E9E9E8] pt-4">
                                <div>
                                    <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">Email Terdaftar</span>
                                    <span class="text-xs font-bold text-[#000000] mt-0.5 block truncate">{{ $registeredUser?->email }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] uppercase tracking-wider text-[#979A9B] block">Area Ketertarikan</span>
                                    <span class="text-xs font-bold text-[#000000] mt-0.5 block capitalize">{{ str_replace('-', ' ', $registeredUser?->reason_for_joining ?? 'General') }}</span>
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
