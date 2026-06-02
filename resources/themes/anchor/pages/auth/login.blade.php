<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Auth;
use function Laravel\Folio\{middleware, name};
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Devdojo\Auth\Traits\HasConfigs;

if(!isset($_GET['preview']) || (isset($_GET['preview']) && $_GET['preview'] != true) || !app()->isLocal()){
    middleware(['guest', 'throttle:login']);
}

name('auth.login');

new class extends Component
{
    use HasConfigs;

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required')]
    public $password = '';

    #[Validate('bool')]
    public $rememberMe = false;

    public $language = [];

    public $twoFactorEnabled = true;

    public $userModel = null;

    public function mount(){
        $this->loadConfigs();
        $this->twoFactorEnabled = $this->settings->enable_2fa;
        $this->userModel = app(config('auth.providers.users.model'));
    }

    public function authenticate()
    {
        $this->validate();

        $credentials = ['email' => $this->email, 'password' => $this->password];

        $userAttemptingLogin = $this->userModel->where('email', $this->email)->first();

        if(!isset($userAttemptingLogin->id)){
            $this->addError('password', trans('auth.failed'));
            return;
        }

        if (\Illuminate\Support\Facades\Hash::check($this->password, $userAttemptingLogin->password)) {
            if (!$userAttemptingLogin->verified) {
                $this->addError('email', 'Akun Anda sedang menunggu persetujuan admin.');
                return;
            }
        }

        if($this->twoFactorEnabled && !is_null($userAttemptingLogin->two_factor_confirmed_at)){
            // We want this user to login via 2fa
            session()->put([
                'login.id' => $userAttemptingLogin->getKey()
            ]);

            return redirect()->route('auth.two-factor-challenge');

        } else {
            if (! Auth::attempt($credentials, $this->rememberMe)) {
                $this->addError('password', trans('auth.failed'));
                return;
            }

            event(new Login(auth()->guard('web'), $this->userModel->where('email', $this->email)->first(), true));

            if(session()->get('url.intended') != route('logout.get')){
                session()->regenerate();
                redirect()->intended(config('devdojo.auth.settings.redirect_after_auth'));
            } else {
                session()->regenerate();
                return redirect(config('devdojo.auth.settings.redirect_after_auth'));
            }
        }

    }
}

?>

<x-layouts.marketing :seo="['title' => config('devdojo.auth.language.login.page_title')]">
    <div class="relative overflow-hidden py-16 md:py-20">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute left-1/2 top-12 h-72 w-72 -translate-x-1/2 rounded-full bg-red-100/60 blur-3xl dark:bg-blue-900/10"></div>
        </div>

        <x-container class="relative z-10 flex min-h-[72vh] items-center justify-center">
            @volt('auth.login')
                <div class="stitch-panel w-full max-w-md space-y-6 p-8 sm:p-10">
                    <div class="flex justify-center">
                        <x-auth::elements.logo
                            :height="config('devdojo.auth.appearance.logo.height')"
                            :isImage="(config('devdojo.auth.appearance.logo.type') == 'image')"
                            :imageSrc="config('devdojo.auth.appearance.logo.image_src')"
                            :svgString="config('devdojo.auth.appearance.logo.svg_string')"
                        />
                    </div>

                    <div class="space-y-1 text-center">
                        <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
                            {{ $language->login->headline ?? 'Sign In' }}
                        </h1>
                        <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                            {{ $language->login->subheadline ?? 'No Description' }}
                        </p>
                    </div>

                    <x-auth::elements.session-message />

                    @if(config('devdojo.auth.settings.login_show_social_providers') && config('devdojo.auth.settings.social_providers_location') == 'top')
                        <x-auth::elements.social-providers />
                    @endif

                    <form wire:submit="authenticate" class="space-y-5">
                        <x-auth::elements.input
                            :label="config('devdojo.auth.language.login.email_address')"
                            type="email"
                            wire:model="email"
                            autofocus="true"
                            data-auth="email-input"
                            id="email"
                            name="email"
                            autocomplete="email"
                            required
                        />

                        <x-auth::elements.input
                            :label="config('devdojo.auth.language.login.password')"
                            type="password"
                            wire:model="password"
                            data-auth="password-input"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        />

                        <div class="flex items-center justify-between gap-4">
                            <x-auth::elements.checkbox
                                :label="config('devdojo.auth.language.login.remember_me')"
                                wire:model="rememberMe"
                                id="remember-me"
                                data-auth="remember-me-input"
                            />

                            <x-auth::elements.text-link href="{{ route('auth.password.request') }}" data-auth="forgot-password-link">
                                {{ config('devdojo.auth.language.login.forget_password') }}
                            </x-auth::elements.text-link>
                        </div>

                        <x-auth::elements.button type="primary" data-auth="submit-button" rounded="full" size="md" submit="true">
                            {{ config('devdojo.auth.language.login.button') }}
                        </x-auth::elements.button>
                    </form>

                    @if(config('devdojo.auth.settings.registration_enabled', true))
                        <div class="text-center text-sm leading-6 text-zinc-600 dark:text-zinc-400">
                            <span>{{ config('devdojo.auth.language.login.dont_have_an_account') }}</span>
                            <x-auth::elements.text-link data-auth="register-link" href="{{ route('auth.register') }}">
                                {{ config('devdojo.auth.language.login.sign_up') }}
                            </x-auth::elements.text-link>
                        </div>
                    @endif

                    @if(config('devdojo.auth.settings.login_show_social_providers') && config('devdojo.auth.settings.social_providers_location') != 'top')
                        <x-auth::elements.social-providers />
                    @endif
                </div>
            @endvolt
        </x-container>
    </div>
</x-layouts.marketing>
