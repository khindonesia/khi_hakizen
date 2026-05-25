<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Devdojo\Auth\Traits\HasConfigs;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component
{
    use HasConfigs;

    #[Validate('required')]
    public $token;

    #[Validate('required|email')]
    public $email = '';

    #[Validate('required|min:8|same:passwordConfirmation')]
    public $password = '';

    public $passwordConfirmation = '';

    public function mount(string $token): void
    {
        $this->loadConfigs();
        $this->email = request()->query('email', '');
        $this->token = $token;
    }

    public function resetPassword(): mixed
    {
        $this->validate();

        $response = Password::broker()->reset([
            'token' => $this->token,
            'email' => $this->email,
            'password' => $this->password,
        ], function ($user, $password): void {
            $user->password = Hash::make($password);
            $user->setRememberToken(Str::random(60));
            $user->save();

            event(new PasswordReset($user));

            Auth::guard()->login($user);
        });

        if ($response === Password::PASSWORD_RESET) {
            session()->flash(trans($response));

            return redirect('/');
        }

        $this->addError('email', trans($response));

        return null;
    }
};

?>

<x-layouts.marketing :seo="['title' => config('devdojo.auth.language.passwordReset.page_title')]">
    <div class="relative min-h-[75vh] flex flex-col items-center justify-center py-16 px-4 bg-slate-50 dark:bg-zinc-950">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute left-[15%] top-[10%] h-96 w-96 rounded-full bg-red-100/50 blur-3xl dark:bg-blue-900/10"></div>
            <div class="absolute right-[15%] bottom-[10%] h-96 w-96 rounded-full bg-purple-100/50 blur-3xl dark:bg-purple-900/10"></div>
        </div>

        <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-slate-100 dark:border-zinc-800 p-8 md:p-10 relative z-10 transition duration-300">
            @volt('auth.password.token')
                <x-auth::elements.container>
                    <x-auth::elements.heading
                        :text="($language->passwordReset->headline ?? 'Set new password')"
                        :description="($language->passwordReset->subheadline ?? 'No Description')"
                        :show_subheadline="($language->passwordReset->show_subheadline ?? false)"
                    />

                    <form wire:submit="resetPassword" class="space-y-5">
                        <x-auth::elements.input
                            :label="config('devdojo.auth.language.passwordReset.email')"
                            type="email"
                            id="email"
                            name="email"
                            data-auth="email-input"
                            wire:model="email"
                            autofocus="true"
                            autocomplete="email"
                            required
                        />

                        <x-auth::elements.input
                            :label="config('devdojo.auth.language.passwordReset.password')"
                            type="password"
                            id="password"
                            name="password"
                            data-auth="password-input"
                            wire:model="password"
                            autocomplete="new-password"
                            required
                        />

                        <x-auth::elements.input
                            :label="config('devdojo.auth.language.passwordReset.password_confirm')"
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            data-auth="password-confirm-input"
                            wire:model="passwordConfirmation"
                            autocomplete="new-password"
                            required
                        />

                        <x-auth::elements.button type="primary" data-auth="submit-button" rounded="full" submit="true">
                            {{ config('devdojo.auth.language.passwordReset.button') }}
                        </x-auth::elements.button>
                    </form>
                </x-auth::elements.container>
            @endvolt
        </div>
    </div>
</x-layouts.marketing>
