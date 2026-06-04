<?php

use function Laravel\Folio\{middleware, name};
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Login;

middleware(['guest']);
name('auth.otp-challenge');

new class extends Component {
    #[Validate('required|numeric|digits:6')]
    public $otp = '';

    public $userModel;

    public function mount()
    {
        // Pastikan ada session id login, kalau tidak ada balikkan ke login
        if (!session()->has('login.id')) {
            return redirect()->route('auth.login');
        }

        $this->userModel = app(config('auth.providers.users.model'));
    }

    public function verifyOtp()
    {
        $this->validate();

        $userId = session()->get('login.id');
        $storedOtp = Cache::get('otp_' . $userId);

        // Validasi kecocokan Cache OTP
        if (!$storedOtp || $storedOtp != $this->otp) {
            $this->addError('otp', 'Kode OTP salah atau telah kedaluwarsa.');
            return;
        }

        // Hapus cache setelah sukses digunakan
        Cache::forget('otp_' . $userId);

        // Ambil data user & Login-kan secara resmi
        $user = $this->userModel->findOrFail($userId);
        $rememberMe = session()->get('login.remember', false);

        Auth::login($user, $rememberMe);

        event(new Login(auth()->guard('web'), $user, true));

        session()->forget(['login.id', 'login.remember']);
        session()->regenerate();

        return redirect()->intended(config('devdojo.auth.settings.redirect_after_auth', '/dashboard'));
    }

    public function resendOtp()
    {
        $userId = session()->get('login.id');
        if (!$userId) {
            return;
        }

        $user = $this->userModel->findOrFail($userId);

        // Generate ulang dan perbarui Cache
        $otp = rand(100000, 999999);
        Cache::put('otp_' . $userId, $otp, 300);

        $user->notify(new \App\Notifications\SendOtpEmail($otp));

        session()->flash('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }
};
?>

<x-layouts.marketing :seo="['title' => 'Verifikasi OTP']">
    <x-container class="relative z-10 flex min-h-[72vh] items-center justify-center">
        @volt('auth.otp-challenge')
            <div class="stitch-panel w-full max-w-md space-y-6 p-8 sm:p-10">
                <div class="space-y-1 text-center">
                    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                        Verifikasi Dua Langkah (2FA)
                    </h1>
                    <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                        Kami telah mengirimkan kode OTP 6 digit ke email Anda. Silakan masukkan di bawah ini.
                    </p>
                </div>

                @if (session()->has('success'))
                    <div class="p-3 text-sm text-green-600 bg-green-50 rounded-lg dark:bg-zinc-800 dark:text-green-400">
                        {{ session('success') }}
                    </div>
                @endif

                <form wire:submit="verifyOtp" class="space-y-5">
                    <x-auth::elements.input label="Kode OTP" type="text" wire:model="otp" id="otp" name="otp"
                        placeholder="123456" required />

                    <x-auth::elements.button type="primary" rounded="full" size="md" submit="true">
                        Verifikasi & Masuk
                    </x-auth::elements.button>
                </form>

                <div class="text-center text-sm">
                    <button type="button" wire:click="resendOtp"
                        class="text-zinc-600 dark:text-zinc-400 underline hover:text-zinc-900">
                        Kirim Ulang OTP
                    </button>
                </div>
            </div>
        @endvolt
    </x-container>
</x-layouts.marketing>
