<?php

use Devdojo\Auth\Traits\HasConfigs;
use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component
{
    use HasConfigs;

    #[Validate('required|email')]
    public $email = null;

    public $emailSentMessage = false;

    public function mount(): void
    {
        $this->loadConfigs();
    }

    public function sendResetPasswordLink(): void
    {
        $this->validate();

        $response = Password::broker()->sendResetLink(['email' => $this->email]);

        if ($response === Password::RESET_LINK_SENT) {
            $this->emailSentMessage = trans($response);

            return;
        }

        $this->addError('email', trans($response));
    }
};

?>

<x-layouts.marketing :seo="['title' => config('devdojo.auth.language.passwordResetRequest.page_title')]">
    <div class="relative min-h-[75vh] flex flex-col items-center justify-center py-16 px-4 bg-slate-50 dark:bg-zinc-950">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute left-[15%] top-[10%] h-96 w-96 rounded-full bg-red-100/50 blur-3xl dark:bg-blue-900/10"></div>
            <div class="absolute right-[15%] bottom-[10%] h-96 w-96 rounded-full bg-purple-100/50 blur-3xl dark:bg-purple-900/10"></div>
        </div>

        <div class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-slate-100 dark:border-zinc-800 p-8 md:p-10 relative z-10 transition duration-300">
            @volt('auth.password.reset')
                <x-auth::elements.container>
                    <x-auth::elements.heading
                        :text="($language->passwordResetRequest->headline ?? 'Reset password')"
                        :description="($language->passwordResetRequest->subheadline ?? 'No Description')"
                        :show_subheadline="($language->passwordResetRequest->show_subheadline ?? false)"
                    />

                    @if ($emailSentMessage)
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">
                            {{ $emailSentMessage }}
                        </div>
                    @else
                        <form wire:submit="sendResetPasswordLink" class="space-y-5">
                            <x-auth::elements.input
                                :label="config('devdojo.auth.language.passwordResetRequest.email')"
                                type="email"
                                id="email"
                                name="email"
                                data-auth="email-input"
                                wire:model="email"
                                autofocus="true"
                                autocomplete="email"
                                required
                            />

                            <x-auth::elements.button type="primary" data-auth="submit-button" rounded="full" submit="true">
                                {{ config('devdojo.auth.language.passwordResetRequest.button') }}
                            </x-auth::elements.button>
                        </form>
                    @endif

                    <div class="mt-4 space-x-0.5 text-sm leading-5 text-center text-slate-600 dark:text-slate-400">
                        <span>{{ config('devdojo.auth.language.passwordResetRequest.or') }}</span>
                        <x-auth::elements.text-link data-auth="login-link" href="{{ route('auth.login') }}">
                            {{ config('devdojo.auth.language.passwordResetRequest.return_to_login') }}
                        </x-auth::elements.text-link>
                    </div>
                </x-auth::elements.container>
            @endvolt
        </div>
    </div>
</x-layouts.marketing>
