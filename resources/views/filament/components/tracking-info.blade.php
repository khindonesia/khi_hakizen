@php
    $record = isset($getRecord) ? (is_callable($getRecord) ? $getRecord() : $getRecord) : $getRecord();
    $service = app(\App\Services\RajaOngkirShippingService::class);
    
    $tracking = null;
    if ($record->resi && $record->courier) {
        $tracking = \Illuminate\Support\Facades\Cache::remember(
            "order-tracking:{$record->id}:{$record->resi}",
            now()->addMinutes(10),
            function () use ($service, $record) {
                return $service->trackWaybill(
                    $record->resi,
                    $record->courier,
                    $record->address?->phone_number
                );
            }
        );
    }
@endphp

<div class="fi-in-section rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-white/5 pb-4 mb-6">
        <div>
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-truck class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                <span>Lacak Resi Pengiriman (Real-time)</span>
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Status pengiriman terkini berdasarkan API RajaOngkir.</p>
        </div>
        <div class="flex items-center gap-2 text-sm bg-gray-50 dark:bg-gray-950 px-3 py-1.5 rounded-lg border border-gray-200/50 dark:border-white/5">
            <span class="font-bold text-gray-500 dark:text-gray-400 uppercase">{{ $record->courier }}</span>
            <span class="text-gray-300 dark:text-gray-700">|</span>
            <span class="font-mono text-gray-800 dark:text-gray-200 font-bold select-all">{{ $record->resi }}</span>
        </div>
    </div>

    @if ($tracking)
        <!-- Summary details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5 mb-8 p-4 bg-gray-50/50 dark:bg-gray-950/25 border border-gray-150/60 dark:border-white/5 rounded-xl">
            <div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Pengirim</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ data_get($tracking, 'summary.shipper_name', '-') }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">{{ data_get($tracking, 'summary.origin', '-') }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Penerima</span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ data_get($tracking, 'summary.receiver_name', '-') }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 block mt-0.5">{{ data_get($tracking, 'summary.destination', '-') }}</span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Status Terakhir</span>
                @php
                    $isDelivered = (bool) data_get($tracking, 'delivered', false);
                    $statusText = data_get($tracking, 'summary.status', 'ON PROCESS');
                @endphp
                @if ($isDelivered)
                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-bold px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-500/20 mt-1">
                        <span class="w-1.5 h-1.5 bg-emerald-600 rounded-full animate-ping"></span>
                        {{ $statusText }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-bold px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-500/20 mt-1">
                        <span class="w-1.5 h-1.5 bg-amber-600 rounded-full animate-pulse"></span>
                        {{ $statusText }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Manifest Timeline -->
        <div class="flow-root mt-6">
            <ul role="list" class="-mb-8">
                @php
                    $manifest = collect(data_get($tracking, 'manifest', []))->sortByDesc(function($item) {
                        return data_get($item, 'manifest_date') . ' ' . data_get($item, 'manifest_time');
                    });
                @endphp

                @forelse ($manifest as $index => $event)
                    <li>
                        <div class="relative pb-8">
                            @if (!$loop->last)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-800" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex space-x-3 items-start">
                                <div>
                                    <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-gray-900 {{ $loop->first ? 'bg-primary-600 dark:bg-primary-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                        @if ($loop->first)
                                            <span class="w-2 h-2 rounded-full bg-white"></span>
                                        @else
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-650"></span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0 pt-0.5">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                                        {{ data_get($event, 'manifest_description') }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2 flex-wrap text-xs text-gray-500 dark:text-gray-400">
                                        @if (data_get($event, 'city_name'))
                                            <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-950 px-2 py-0.5 rounded font-semibold border border-gray-200/30 dark:border-white/5 uppercase text-[10px]">
                                                {{ data_get($event, 'city_name') }}
                                            </span>
                                        @endif
                                        <span>•</span>
                                        <time datetime="{{ data_get($event, 'manifest_date') }} {{ data_get($event, 'manifest_time') }}">
                                            {{ \Carbon\Carbon::parse(data_get($event, 'manifest_date') . ' ' . data_get($event, 'manifest_time'))->format('d M Y, H:i') }}
                                        </time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="py-4 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Resi terdaftar, tetapi belum ada riwayat manifest pengiriman.</p>
                    </li>
                @endforelse
            </ul>
        </div>
    @else
        <!-- No Tracking / Error State -->
        <div class="py-6 flex flex-col items-center justify-center text-center">
            <div class="w-12 h-12 rounded-lg bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-white/5 flex items-center justify-center mb-3">
                <x-heroicon-o-information-circle class="w-6 h-6 text-gray-400 dark:text-gray-500 animate-pulse" />
            </div>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Informasi pelacakan resi belum tersedia</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs leading-relaxed">
                Kurir membutuhkan waktu untuk memproses resi yang baru diinput. Harap periksa beberapa saat lagi atau pastikan resi sudah benar.
            </p>
        </div>
    @endif
</div>
