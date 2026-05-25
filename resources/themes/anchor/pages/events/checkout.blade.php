<?php
use function Laravel\Folio\{middleware, name};

middleware('auth');
name('events.checkout');
?>

@php
    $user = auth()->user();
    $eventId = request('event');
    
    // Abort if no event parameter is provided
    abort_unless($eventId, 404, 'Event parameter is required.');

    $event = \App\Models\Event::with(['author'])->findOrFail($eventId);

    // Check if the user is already registered for this event
    $isAlreadyRegistered = \Illuminate\Support\Facades\DB::table('event_user')
        ->where('event_id', $event->id)
        ->where('user_id', $user->id)
        ->where('status', 'active')
        ->exists();

    $eventImage = $event->image
        ? Storage::url(ltrim($event->image, '/'))
        : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=1200&auto=format&fit=crop';
@endphp

<x-layouts.marketing :seo="[
    'title' => 'Event Booking - ' . $event->title,
    'description' => 'Complete your registration securely for ' . $event->title,
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

    <div class="relative min-h-screen py-12 md:py-20 bg-[#fffafb]" x-data="eventCheckoutPage({{ $event->id }}, {{ $event->price ?? 0 }}, '{{ $user->phone_number ?? '' }}')">
        <x-container>
            
            <!-- Top Back to Event Link -->
            <a href="{{ route('events.show', ['slug' => $event->slug]) }}" wire:navigate class="inline-flex items-center gap-2 text-sm font-semibold text-[#575e75] hover:text-[#df1c24] mb-8 transition-colors">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                Back to event details
            </a>

            @if($isAlreadyRegistered)
                <!-- Already Registered Message Panel -->
                <div class="stitch-panel max-w-2xl mx-auto p-8 py-16 text-center bg-white shadow-sm border border-zinc-200">
                    <span class="material-symbols-outlined mb-4 block text-6xl text-emerald-600" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <h2 class="text-2xl font-bold text-zinc-900">Anda Sudah Terdaftar</h2>
                    <p class="mt-2 text-zinc-500 max-w-md mx-auto">Anda telah berhasil terdaftar dan membooking tempat untuk event <strong>"{{ $event->title }}"</strong>. Tiket dan jadwal Anda tersedia di dashboard.</p>
                    <div class="mt-6 flex justify-center gap-4">
                        <a href="{{ route('dashboard.events') }}" wire:navigate class="rounded-full bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-zinc-800">
                            Go to My Events
                        </a>
                        <a href="{{ route('events') }}" wire:navigate class="rounded-full border border-zinc-200 bg-white px-6 py-2.5 text-sm font-semibold text-zinc-700 transition-all hover:border-zinc-300">
                            Browse More Events
                        </a>
                    </div>
                </div>
            @else
                <!-- Event Checkout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                    
                    <!-- Left Column: Attendee Information -->
                    <div class="lg:col-span-7 flex flex-col gap-8">
                        <div>
                            <div class="stitch-chip mb-3 inline-flex">
                                <span class="material-symbols-outlined text-[14px] mr-1">confirmation_number</span>
                                Event Registration
                            </div>
                            <h1 class="text-3xl font-semibold tracking-tight text-zinc-900 md:text-[36px]">Konfirmasi Pendaftaran</h1>
                            <p class="mt-1.5 text-sm leading-[1.55] text-zinc-500">Lengkapi pendaftaran Anda dengan aman. Tiket digital akan dikirimkan otomatis setelah proses selesai.</p>
                        </div>

                        <!-- Attendee Info Panel -->
                        <section class="stitch-panel p-6 md:p-8 bg-white border border-[#E9E9E8] rounded-2xl shadow-sm">
                            <div class="mb-6 flex items-center gap-2 border-b border-zinc-200/70 pb-4">
                                <span class="material-symbols-outlined text-[#df1c24]" style="font-variation-settings: 'FILL' 1;">person</span>
                                <h2 class="text-xl font-semibold text-zinc-900">Informasi Peserta</h2>
                            </div>

                            <div class="space-y-5">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Nama Lengkap</label>
                                        <input type="text" readonly value="{{ $user->name }}" 
                                               class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 focus:outline-none cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Alamat Email</label>
                                        <input type="email" readonly value="{{ $user->email }}" 
                                               class="w-full rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm text-zinc-600 focus:outline-none cursor-not-allowed">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-zinc-400 mb-1.5">Nomor Handphone</label>
                                    <input type="text" x-model="phoneNumber" placeholder="Contoh: 08123456789" 
                                           class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-[#df1c24]/20 focus:border-[#df1c24]">
                                    <p class="mt-1 text-xs text-zinc-400">Nomor handphone wajib diisi dan akan otomatis disimpan ke akun Anda.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Terms and Conditions Panel -->
                        <section class="stitch-panel p-6 md:p-8 bg-white border border-[#E9E9E8] rounded-2xl shadow-sm">
                            <div class="mb-4 flex items-center gap-2 border-b border-zinc-200/70 pb-4">
                                <span class="material-symbols-outlined text-[#df1c24]" style="font-variation-settings: 'FILL' 1;">gavel</span>
                                <h2 class="text-xl font-semibold text-zinc-900">Syarat & Ketentuan</h2>
                            </div>
                            <div class="text-xs text-zinc-500 leading-relaxed space-y-2">
                                <p>1. Pendaftaran event bersifat final dan tidak dapat dibatalkan atau dialihkan tanpa persetujuan dari panitia KHI.</p>
                                <p>2. Untuk event berbayar, pembayaran wajib diselesaikan melalui payment gateway Xendit sebelum batas waktu invoice berakhir.</p>
                                <p>3. E-Ticket akan otomatis dikirimkan ke email terdaftar Anda setelah pembayaran atau pendaftaran gratis berhasil diverifikasi.</p>
                            </div>
                        </section>
                    </div>

                    <!-- Right Column: Registration Summary Sidebar -->
                    <aside class="lg:col-span-5 lg:sticky lg:top-[100px]">
                        <div class="bg-[#fff5f5] border border-[#E9E9E8] rounded-2xl p-6 md:p-8 shadow-sm flex flex-col gap-6">
                            
                            <div>
                                <h2 class="text-xl md:text-[24px] font-semibold text-[#37352F]">Ringkasan Pendaftaran</h2>
                                <p class="text-xs text-[#979A9B] mt-0.5">Harap periksa kembali detail pesanan Anda.</p>
                            </div>

                            <!-- Event Details Card -->
                            <div class="flex gap-4 p-4 bg-white border border-[#E9E9E8]/80 rounded-xl shadow-xs">
                                <div class="w-16 h-16 rounded-lg overflow-hidden flex-shrink-0 bg-zinc-100 border border-zinc-200/50">
                                    <img src="{{ $eventImage }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h4 class="font-bold text-sm text-zinc-900 truncate leading-tight mb-1">{{ $event->title }}</h4>
                                    <div class="flex items-center gap-1 text-[11px] text-[#575e75]">
                                        <span class="material-symbols-outlined text-[12px] text-[#df1c24]">calendar_today</span>
                                        <span>{{ $event->start_datetime->format('d M Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[11px] text-[#575e75] mt-0.5">
                                        <span class="material-symbols-outlined text-[12px] text-[#df1c24]">location_on</span>
                                        <span class="truncate">{{ $event->location ?? 'Jakarta, Indonesia' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cost Breakdown -->
                            <div class="border-t border-[#E9E9E8] pt-4 flex flex-col gap-3">
                                <div class="flex justify-between items-center text-sm text-[#575e75]">
                                    <span>Tipe Tiket</span>
                                    <span class="font-semibold text-zinc-900 uppercase tracking-wider text-xs bg-zinc-100 rounded-full px-2.5 py-0.5">
                                        {{ $event->type === 'PAID' ? 'Berbayar' : 'Gratis' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center text-sm text-[#575e75]">
                                    <span>Harga Tiket</span>
                                    <span class="font-bold text-zinc-900">
                                        @if($event->type === 'PAID')
                                            Rp{{ number_format($event->price, 0, ',', '.') }}
                                        @else
                                            Gratis
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Grand Total -->
                            <div class="border-t border-[#E9E9E8] pt-4 flex justify-between items-baseline mb-2">
                                <span class="text-base font-semibold text-zinc-900">Total Pembayaran</span>
                                <span class="text-2xl font-bold text-[#df1c24]">
                                    @if($event->type === 'PAID')
                                        Rp{{ number_format($event->price, 0, ',', '.') }}
                                    @else
                                        Rp 0
                                    @endif
                                </span>
                            </div>

                            <!-- Complete Booking Action Button -->
                            <button class="w-full bg-[#df1c24] text-white font-semibold py-3.5 rounded-xl hover:bg-opacity-95 transition-all flex justify-center items-center gap-2 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="isProcessing"
                                    @click="completeBooking">
                                <span class="material-symbols-outlined text-[18px]">lock</span>
                                <span x-text="isProcessing ? 'Memproses Pendaftaran...' : 'Selesaikan Booking'"></span>
                            </button>

                            <p class="text-[11px] text-[#979A9B] text-center flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">verified_user</span> 
                                Pembayaran aman diproses oleh KHI & Xendit Gateway.
                            </p>
                        </div>
                    </aside>
                </div>
            @endif

        </x-container>
    </div>

    <script>
        /**
         * Event Checkout Page Alpine.js Controller
         * @param {number} eventId - The database ID of the Event
         * @param {number} price - The price of the event
         * @param {string} initialPhoneNumber - The initial phone number of the user
         * @return {Object} Alpine component instance
         */
        function eventCheckoutPage(eventId, price = 0, initialPhoneNumber = '') {
            return {
                eventId: eventId,
                price: price,
                phoneNumber: initialPhoneNumber,
                isProcessing: false,

                completeBooking() {
                    if (!this.phoneNumber || !this.phoneNumber.trim()) {
                        alert('Nomor handphone wajib diisi untuk melanjutkan pendaftaran.');
                        return;
                    }

                    this.isProcessing = true;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');

                    fetch('/api/events/checkout/create-invoice', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                        },
                        body: JSON.stringify({
                            event_id: this.eventId,
                            phone_number: this.phoneNumber
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(err.message || 'Gagal memproses pendaftaran.');
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.status === 'success' && data.data && data.data.invoice_url) {
                            // Redirect to payment URL (either Xendit payment link or user dashboard for free events)
                            window.location.href = data.data.invoice_url;
                        } else {
                            alert(data.message || 'Terjadi kesalahan saat memproses pembayaran.');
                            this.isProcessing = false;
                        }
                    })
                    .catch(error => {
                        alert(error.message || 'Terjadi kesalahan jaringan.');
                        this.isProcessing = false;
                    });
                }
            }
        }
    </script>
</x-layouts.marketing>
