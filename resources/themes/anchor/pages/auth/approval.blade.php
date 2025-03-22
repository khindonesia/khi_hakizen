<?php

use Illuminate\Support\Facades\Auth;
use Devdojo\Auth\Traits\HasConfigs;
use function Laravel\Folio\{middleware, name};
use Livewire\Volt\Component;

//middleware(['auth', 'throttle:6,1']);
name('auth.approval');

new class extends Component
{
    use HasConfigs;

    public function mount(){
        $this->loadConfigs();
    }
};

?>

<x-auth::layouts.app title="Menunggu Persetujuan Admin">

    @volt('auth.approval')
        <x-auth::elements.container>

            <x-auth::elements.heading
                :text="'Akun Menunggu Persetujuan'"
                :description="'Silahkan menunggu akun Anda disetujui oleh administrator'"
                :show_subheadline="true" />

            <div class="text-sm leading-6 text-gray-700 dark:text-gray-400">
                <p>Akun Anda saat ini sedang dalam proses peninjauan oleh administrator. Anda akan mendapatkan notifikasi setelah akun disetujui.</p>
            </div>

            <div class="mt-2 space-x-0.5 text-sm leading-5 text-center text-gray-600 translate-y-4 dark:text-gray-400">
                <span>atau</span>
                <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-gray-500 underline cursor-pointer dark:text-gray-400 dark:hover:text-gray-300 hover:text-gray-800">
                  Keluar
                </button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>

        </x-auth::elements.container>
    @endvolt

</x-auth::layouts.app>