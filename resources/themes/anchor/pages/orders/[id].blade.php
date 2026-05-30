<?php
    use App\Models\Order;
    use Livewire\Volt\Component;
    use function Laravel\Folio\{middleware, name};
    
    middleware('auth');
    name('orders.view');
    
    new class extends Component
    {
        public Order $order;
        
        public function mount($id)
        {
            $this->order = Order::query()
                ->with([
                    'user',
                    'address', 
                    'items.product.images',
                    'items.variant.variantAttributes.attribute',
                    'items.variant.variantAttributes.attributeValue'
                ])
                ->where('user_id', auth()->id())
                ->findOrFail($id);
        }
        
        public function getImageUrl($item): ?string
        {
            $variantImage = $item->variant?->image_url;
            $path = $variantImage ?: $item->product?->images->sortBy('sort_order')->first()?->image_url;
            
            if (!$path) {
                return null;
            }
            
            return \Illuminate\Support\Str::startsWith($path, ['http://', 'https://']) 
                ? $path 
                : \Illuminate\Support\Facades\Storage::url(ltrim($path, '/'));
        }
        
        public function getVariantDescription($variant): string
        {
            if (!$variant || $variant->is_default) {
                return '';
            }
            
            $attrs = [];
            foreach ($variant->variantAttributes as $va) {
                if ($va->attribute && $va->attributeValue) {
                    $attrs[] = "{$va->attribute->name}: {$va->attributeValue->value}";
                }
            }
            
            return implode(' • ', $attrs);
        }

        public function getTrackingData(): ?array
        {
            if (!$this->order->resi || !$this->order->courier) {
                return null;
            }

            return \Illuminate\Support\Facades\Cache::remember(
                "order-tracking:{$this->order->id}:{$this->order->resi}",
                now()->addMinutes(10),
                function () {
                    $service = app(\App\Services\RajaOngkirShippingService::class);
                    return $service->trackWaybill(
                        $this->order->resi,
                        $this->order->courier,
                        $this->order->address?->phone_number
                    );
                }
            );
        }
    }
?>

<x-layouts.app>
    @volt('orders.view')
        <x-app.container>
            <!-- Header -->
            <header class="mb-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <a class="text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200 flex items-center gap-2 mb-2 font-semibold text-sm transition-colors" href="/orders" wire:navigate>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                            </svg>
                            Back to Orders
                        </a>
                        <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">Order #{{ $order->invoice_id }}</h1>
                        <p class="text-zinc-500 dark:text-zinc-400 text-sm mt-1">Placed on {{ $order->created_at->format('F d, Y') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="/orders/{{ $order->id }}/print-invoice" target="_blank"
                           class="bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border border-zinc-200/80 dark:border-zinc-800 px-4 py-2.5 rounded-xl font-semibold text-sm hover:bg-zinc-50 dark:hover:bg-zinc-950 transition-colors flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                            </svg>
                            Download Invoice
                        </a>
                        @if ($order->payment_status === 'pending' && $order->payment_url)
                            <a href="{{ $order->payment_url }}" target="_blank" rel="noopener noreferrer"
                               class="bg-red-700 hover:bg-red-800 dark:bg-red-600 dark:hover:bg-red-700 text-white font-semibold text-sm rounded-xl px-5 py-2.5 transition-colors shadow-sm inline-flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625-12h16.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H3.375a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Z"></path>
                                </svg>
                                Pay Now
                            </a>
                        @endif
                    </div>
                </div>
            </header>
                       <div class="flex flex-col lg:flex-row gap-8 items-start">
                <!-- Left Column: Tracking & Items -->
                <div class="w-full lg:flex-1 space-y-8">
                    <!-- Status Tracker Card -->
                    <section class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-6">Order Status</h2>
                        
                        @if ($order->status === 'cancelled')
                            <div class="bg-red-50 dark:bg-red-500/10 border border-red-200/60 dark:border-red-500/20 rounded-xl p-4 mb-6 flex items-start gap-3">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-bold text-red-800 dark:text-red-400">Order Cancelled</h3>
                                    <p class="text-xs text-red-750 dark:text-red-400/80 mt-0.5">
                                        This order has been cancelled and is no longer being processed.
                                    </p>
                                </div>
                            </div>
                        @endif
                        
                        @php
                            $step1Active = true;
                            $step2Active = in_array($order->status, ['shipping', 'delivered']);
                            $step3Active = $order->status === 'delivered';
                            
                            $progressPercent = 0;
                            if ($order->status === 'processing') {
                                $progressPercent = 25;
                            } elseif ($order->status === 'shipping') {
                                $progressPercent = 50;
                            } elseif ($order->status === 'delivered') {
                                $progressPercent = 100;
                            }
                        @endphp
                        
                        <div class="relative flex items-center justify-between w-full mt-6 mb-6">
                            <!-- Progress Line Base -->
                            <div class="absolute left-0 w-full bg-zinc-200 dark:bg-zinc-800 z-0 rounded-full" style="top: 18px; height: 3px;"></div>
                            <!-- Progress Line Active -->
                            <div class="absolute left-0 bg-red-600 z-0 rounded-full transition-all duration-500" style="top: 18px; height: 3px; width: {{ $progressPercent }}%;"></div>
                            
                            <!-- Step 1: Placed -->
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center ring-4 ring-white dark:ring-zinc-900 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                                    </svg>
                                </div>
                                <span class="mt-2 text-xs font-semibold text-zinc-850 dark:text-zinc-350 text-center">Order Placed</span>
                            </div>
                            
                            <!-- Step 2: Shipped -->
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-zinc-900 shadow-sm transition-all duration-300 {{ $step2Active ? 'bg-red-600 text-white' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 border border-zinc-300 dark:border-zinc-700' }}">
                                    @if ($step2Active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.302-4.832a2.25 2.25 0 0 0-.53-1.28l-3-3.75a2.25 2.25 0 0 0-1.729-.824H9.75v8.25"></path>
                                        </svg>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-semibold text-center transition-all duration-300 {{ $step2Active ? 'text-zinc-850 dark:text-zinc-350' : 'text-zinc-400 dark:text-zinc-500' }}">Shipping</span>
                            </div>
                            
                            <!-- Step 3: Delivered -->
                            <div class="relative z-10 flex flex-col items-center">
                                <div class="w-9 h-9 rounded-full flex items-center justify-center ring-4 ring-white dark:ring-zinc-900 shadow-sm transition-all duration-300 {{ $step3Active ? 'bg-emerald-600 text-white' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500 border border-zinc-300 dark:border-zinc-700' }}">
                                    @if ($step3Active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <span class="mt-2 text-xs font-semibold text-center transition-all duration-300 {{ $step3Active ? 'text-zinc-850 dark:text-zinc-350' : 'text-zinc-400 dark:text-zinc-500' }}">Delivered</span>
                            </div>
                        </div>
                    </section>

                    @php
                        $tracking = $this->getTrackingData();
                    @endphp

                    @if ($order->resi)
                        <section class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-zinc-150 dark:border-zinc-800/80 pb-4 mb-6">
                                <div>
                                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.302-4.832a2.25 2.25 0 0 0-.53-1.28l-3-3.75a2.25 2.25 0 0 0-1.729-.824H9.75v8.25"></path>
                                        </svg>
                                        Shipment Tracking
                                    </h2>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 font-medium">Real-time status updates powered by RajaOngkir</p>
                                </div>
                                <div class="flex items-center gap-2 text-sm bg-zinc-50 dark:bg-zinc-950 px-3.5 py-1.5 rounded-xl border border-zinc-200/40 dark:border-zinc-800/40">
                                    <span class="font-bold text-zinc-500 dark:text-zinc-400 uppercase">{{ $order->courier }}</span>
                                    <span class="text-zinc-300 dark:text-zinc-700">|</span>
                                    <span class="font-mono text-zinc-750 dark:text-zinc-300 font-bold select-all">{{ $order->resi }}</span>
                                </div>
                            </div>

                            @if ($tracking)
                                <!-- Info Summary Grid -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5 mb-8 p-4 bg-zinc-50/50 dark:bg-zinc-950/25 border border-zinc-150/60 dark:border-zinc-850/60 rounded-2xl">
                                    <div>
                                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider block">Shipper</span>
                                        <span class="text-sm font-bold text-zinc-800 dark:text-zinc-300">{{ data_get($tracking, 'summary.shipper_name', '-') }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-450 block mt-0.5">{{ data_get($tracking, 'summary.origin', '-') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider block">Receiver</span>
                                        <span class="text-sm font-bold text-zinc-800 dark:text-zinc-300">{{ data_get($tracking, 'summary.receiver_name', '-') }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-450 block mt-0.5">{{ data_get($tracking, 'summary.destination', '-') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider block">Status</span>
                                        @php
                                            $isDelivered = (bool) data_get($tracking, 'delivered', false);
                                            $statusText = data_get($tracking, 'summary.status', 'ON PROCESS');
                                        @endphp
                                        @if ($isDelivered)
                                            <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 text-xs font-extrabold px-3 py-1 rounded-full border border-emerald-200/60 dark:border-emerald-500/20 mt-1">
                                                <span class="w-1.5 h-1.5 bg-emerald-600 dark:bg-emerald-450 rounded-full animate-ping"></span>
                                                {{ $statusText }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-extrabold px-3 py-1 rounded-full border border-amber-200/60 dark:border-amber-500/20 mt-1">
                                                <span class="w-1.5 h-1.5 bg-amber-600 dark:bg-amber-450 rounded-full animate-pulse"></span>
                                                {{ $statusText }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Timeline -->
                                <div class="flow-root">
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
                                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-zinc-200 dark:bg-zinc-850" aria-hidden="true"></span>
                                                    @endif
                                                    <div class="relative flex space-x-3 items-start">
                                                        <div>
                                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white dark:ring-zinc-900 {{ $loop->first ? 'bg-red-700 text-white shadow-md shadow-red-200/35 dark:shadow-none' : 'bg-zinc-150 dark:bg-zinc-800 text-zinc-550 dark:text-zinc-400' }}">
                                                                @if ($loop->first)
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"></path>
                                                                    </svg>
                                                                @else
                                                                    <div class="w-2 h-2 rounded-full bg-zinc-400 dark:bg-zinc-600"></div>
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <div class="flex-1 min-w-0 pt-0.5">
                                                            <p class="text-sm font-bold text-zinc-900 dark:text-white leading-snug">
                                                                {{ data_get($event, 'manifest_description') }}
                                                            </p>
                                                            <div class="mt-1 flex items-center gap-2 flex-wrap text-xs text-zinc-500 dark:text-zinc-400">
                                                                @if (data_get($event, 'city_name'))
                                                                    <span class="inline-flex items-center gap-1 bg-zinc-100 dark:bg-zinc-950 px-2 py-0.5 rounded font-semibold border border-zinc-200/30 dark:border-zinc-850/30 uppercase text-[10px]">
                                                                        {{ data_get($event, 'city_name') }}
                                                                    </span>
                                                                @endif
                                                                <span>•</span>
                                                                <time datetime="{{ data_get($event, 'manifest_date') }} {{ data_get($event, 'manifest_time') }}">
                                                                    {{ \Carbon\Carbon::parse(data_get($event, 'manifest_date') . ' ' . data_get($event, 'manifest_time'))->format('M d, Y - H:i') }}
                                                                </time>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="py-4 text-center">
                                                <p class="text-sm text-zinc-500 dark:text-zinc-400">Waybill is registered, but no tracking events have been reported yet.</p>
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            @else
                                <!-- Loading or unavailable -->
                                <div class="py-6 flex flex-col items-center justify-center text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200/60 dark:border-zinc-800/60 flex items-center justify-center mb-3">
                                        <svg class="w-6 h-6 text-zinc-400 dark:text-zinc-500 animate-pulse" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-300">Tracking information currently unavailable</p>
                                    <p class="text-xs text-zinc-550 dark:text-zinc-450 mt-1 max-w-xs leading-relaxed">
                                        Couriers can take up to a few hours to activate waybills. Please check back later or verify your receipt number.
                                    </p>
                                </div>
                            @endif
                        </section>
                    @endif
                    
                    <!-- Items Ordered Card -->
                    <section class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
                        <div class="flex justify-between items-end mb-6 pb-4 border-b border-zinc-150 dark:border-zinc-800/80">
                            <h2 class="text-lg font-bold text-zinc-900 dark:text-white">Items Ordered</h2>
                            <a href="/merchandise" class="text-red-700 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-semibold text-sm flex items-center gap-1 transition-colors" wire:navigate>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path>
                                </svg>
                                Shop More
                            </a>
                        </div>
                        
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            @foreach ($order->items as $item)
                                @php
                                    $productImage = $this->getImageUrl($item);
                                    $variantDesc = $this->getVariantDescription($item->variant);
                                @endphp
                                <div class="flex flex-col sm:flex-row gap-5 py-5 items-start sm:items-center">
                                    <!-- Image Block -->
                                    <div class="w-24 h-24 shrink-0 rounded-xl overflow-hidden bg-zinc-50 dark:bg-zinc-950 border border-zinc-200/60 dark:border-zinc-850 flex items-center justify-center p-2">
                                        @if ($productImage)
                                            <img alt="{{ $item->product?->name }}" class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal" src="{{ $productImage }}"/>
                                        @else
                                            <div class="text-zinc-400 dark:text-zinc-600 flex flex-col items-center justify-center">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Details Block -->
                                    <div class="flex-grow min-w-0">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2">
                                            <div>
                                                <h3 class="text-base font-bold text-zinc-900 dark:text-white hover:text-red-700 dark:hover:text-red-400 transition-colors">
                                                    @if ($item->product)
                                                        <a href="/merchandise/{{ $item->product->slug }}" wire:navigate>{{ $item->product->name }}</a>
                                                    @else
                                                        <span>Product Unavailable</span>
                                                    @endif
                                                </h3>
                                                <p class="text-xs text-zinc-550 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                    @if ($variantDesc)
                                                        <span>{{ $variantDesc }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-left sm:text-right shrink-0">
                                                <p class="text-base font-bold text-zinc-900 dark:text-white">
                                                    Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}
                                                </p>
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                                    Rp {{ number_format($item->price, 0, ',', '.') }} / item
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center mt-3 pt-3 border-t border-zinc-100/50 dark:border-zinc-800/40">
                                            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Qty: {{ $item->quantity }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                </div>
                
                <!-- Right Column: Info & Summary -->
                <div class="w-full lg:w-[380px] lg:shrink-0 space-y-8">
                    <!-- Information Card -->
                    <section class="bg-indigo-50/40 dark:bg-indigo-950/15 border border-indigo-100/80 dark:border-indigo-900/30 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-700 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.852l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"></path>
                            </svg>
                            Order Details
                        </h2>
                        
                        <div class="space-y-6">
                            <!-- Shipping Information -->
                            <div>
                                <h3 class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-2.5">Shipping Address</h3>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-350">{{ $order->user?->name }}</p>
                                @if ($order->address)
                                    <p class="text-sm text-zinc-550 dark:text-zinc-400 mt-1.5 leading-relaxed">
                                        {{ $order->address->address_line }},<br/>
                                        {{ $order->address->village }}, {{ $order->address->district }},<br/>
                                        {{ $order->address->city }}, {{ $order->address->state }}, {{ $order->address->postal_code }}
                                    </p>
                                @else
                                    <p class="text-sm text-zinc-550 dark:text-zinc-400 mt-1.5 italic">
                                        No shipping address provided.
                                    </p>
                                @endif
                            </div>
                            
                            <hr class="border-zinc-200/80 dark:border-zinc-800/80"/>

                            <!-- Shipping Method & Resi -->
                            @if ($order->courier || $order->resi)
                            <div>
                                <h3 class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-2.5">Shipping Method</h3>
                                <p class="text-sm font-semibold text-zinc-800 dark:text-zinc-350 uppercase">
                                    {{ $order->courier ?: '-' }} ({{ $order->service ?: '-' }})
                                </p>
                                @if ($order->resi)
                                    <div class="mt-2 flex flex-col gap-1">
                                        <span class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">No. Resi</span>
                                        <p class="text-sm font-bold text-emerald-650 dark:text-emerald-400 flex items-center gap-1">
                                            <svg class="w-4 h-4 text-emerald-550" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                            </svg>
                                            <span>{{ $order->resi }}</span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                            
                            <hr class="border-zinc-200/80 dark:border-zinc-800/80"/>
                            @endif
                            
                            <!-- Payment Information -->
                            <div>
                                <h3 class="text-xs font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-3">Payment Method</h3>
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300 font-medium">
                                        <svg class="w-5 h-5 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625-12h16.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H3.375a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Z"></path>
                                        </svg>
                                        <span class="uppercase">{{ $order->courier ?: 'Virtual Account' }} ({{ $order->service ?: 'Xendit' }})</span>
                                    </div>
                                    
                                    @if ($order->payment_status === 'paid')
                                        <span class="bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200/60 dark:border-emerald-500/20">Paid</span>
                                    @elseif ($order->payment_status === 'failed')
                                        <span class="bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 px-3 py-1 rounded-full text-xs font-bold border border-red-200/60 dark:border-red-500/20">Failed</span>
                                    @else
                                        <span class="bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 px-3 py-1 rounded-full text-xs font-bold border border-amber-200/60 dark:border-amber-500/20">Pending</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Order Summary Card -->
                    <section class="bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-zinc-900 dark:text-white mb-6">Order Summary</h2>
                        <div class="space-y-3.5 text-sm text-zinc-700 dark:text-zinc-300 font-medium">
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-450">Subtotal</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500 dark:text-zinc-450">Shipping</span>
                                <span class="font-semibold text-zinc-900 dark:text-white">Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <hr class="border-zinc-200/80 dark:border-zinc-800/80 my-5"/>
                        
                        <div class="flex justify-between items-end">
                            <span class="text-base font-extrabold text-zinc-900 dark:text-white">Grand Total</span>
                            <span class="text-2xl font-extrabold text-red-700 dark:text-red-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </section>
                </div>
            </div>
        </x-app.container>
    @endvolt
</x-layouts.app>
