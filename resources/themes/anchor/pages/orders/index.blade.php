<?php
    use App\Models\Order;
    use Livewire\Volt\Component;
    use Livewire\WithPagination;
    use function Laravel\Folio\{middleware, name};
    
    middleware('auth');
    name('orders');
    
    new class extends Component
    {
        use WithPagination;
        
        public string $statusTab = 'all';
        
        public function setTab(string $tab): void
        {
            $this->statusTab = $tab;
            $this->resetPage();
        }
        
        public function getOrders()
        {
            $query = Order::query()
                ->with([
                    'items.product.images',
                    'items.variant.variantAttributes.attribute',
                    'items.variant.variantAttributes.attributeValue'
                ])
                ->where('user_id', auth()->id())
                ->latest();
                
            if ($this->statusTab === 'processing') {
                $query->whereIn('status', ['pending', 'processing']);
            } elseif ($this->statusTab === 'shipping') {
                $query->where('status', 'shipping');
            } elseif ($this->statusTab === 'completed') {
                $query->where('status', 'delivered');
            } elseif ($this->statusTab === 'cancelled') {
                $query->where('status', 'cancelled');
            }
            
            return $query->paginate(10);
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
        
        public function with(): array
        {
            return [
                'orders' => $this->getOrders(),
            ];
        }
    }
?>

<x-layouts.app>
    @volt('orders')
        <x-app.container>
            <!-- Page Header & Tab Filters -->
            <div class="flex flex-col lg:flex-row lg:items-end justify-between mb-8 gap-6 border-b border-zinc-200/60 dark:border-zinc-800/80 pb-6">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-zinc-900 dark:text-white">Order History</h1>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1.5">Track, return, or purchase items again.</p>
                </div>
                
                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-1.5 bg-zinc-100 dark:bg-zinc-900 border border-zinc-200/60 dark:border-zinc-800 rounded-xl p-1 shadow-sm w-fit shrink-0">
                    <button wire:click="setTab('all')" 
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $statusTab === 'all' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        All Orders
                    </button>
                    <button wire:click="setTab('processing')" 
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $statusTab === 'processing' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        Processing
                    </button>
                    <button wire:click="setTab('shipping')" 
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $statusTab === 'shipping' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        Shipping
                    </button>
                    <button wire:click="setTab('completed')" 
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $statusTab === 'completed' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        Completed
                    </button>
                    <button wire:click="setTab('cancelled')" 
                            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 {{ $statusTab === 'cancelled' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-sm' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100' }}">
                        Cancelled
                    </button>
                </div>
            </div>
            
            <!-- Orders List -->
            <div class="flex flex-col gap-6">
                @forelse ($orders as $order)
                    <article class="bg-white dark:bg-zinc-900/60 border border-zinc-200/80 dark:border-zinc-800 rounded-2xl shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-all duration-300">
                        <!-- Order Header Info -->
                        <div class="bg-zinc-50/50 dark:bg-zinc-900/50 px-6 py-4 border-b border-zinc-200/80 dark:border-zinc-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-x-8 gap-y-2 text-sm">
                                <div>
                                    <p class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Order Placed</p>
                                    <p class="font-medium text-zinc-700 dark:text-zinc-300 mt-0.5">{{ $order->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Total</p>
                                    <p class="font-bold text-zinc-900 dark:text-white mt-0.5">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <p class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Invoice ID</p>
                                    <p class="font-mono text-zinc-700 dark:text-zinc-300 mt-0.5">{{ $order->invoice_id }}</p>
                                </div>
                                @if ($order->resi)
                                    <div>
                                        <p class="text-[11px] font-bold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider">Nomor Resi</p>
                                        <p class="font-semibold text-emerald-650 dark:text-emerald-400 mt-0.5">{{ $order->resi }}</p>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Badges & Action -->
                            <div class="shrink-0 flex items-center gap-3">
                                <a href="/orders/{{ $order->id }}" wire:navigate
                                   class="border border-zinc-200/80 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold text-xs rounded-lg px-3 py-1.5 hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                                    </svg>
                                    <span>View Details</span>
                                </a>

                                @if ($order->status === 'shipping')
                                    <div class="flex items-center gap-1.5 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full text-xs font-bold border border-blue-200/60 dark:border-blue-500/20 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.302-4.832a2.25 2.25 0 0 0-.53-1.28l-3-3.75a2.25 2.25 0 0 0-1.729-.824H9.75v8.25"></path>
                                        </svg>
                                        <span>Shipping</span>
                                    </div>
                                @elseif ($order->status === 'processing')
                                    <div class="flex items-center gap-1.5 bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 px-3 py-1 rounded-full text-xs font-bold border border-amber-200/60 dark:border-amber-500/20 w-fit">
                                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span>Processing</span>
                                    </div>
                                @elseif ($order->status === 'pending')
                                    <div class="flex items-center gap-1.5 bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 px-3 py-1 rounded-full text-xs font-bold border border-zinc-200 dark:border-zinc-700 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                        </svg>
                                        <span>Pending</span>
                                    </div>
                                @elseif ($order->status === 'delivered')
                                    <div class="flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200/60 dark:border-emerald-500/20 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                        </svg>
                                        <span>Delivered</span>
                                    </div>
                                @elseif ($order->status === 'cancelled')
                                    <div class="flex items-center gap-1.5 bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 px-3 py-1 rounded-full text-xs font-bold border border-red-200/60 dark:border-red-500/20 w-fit">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"></path>
                                        </svg>
                                        <span>Cancelled</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Order Body Items -->
                        <div class="px-6 py-2 flex flex-col divide-y divide-zinc-100 dark:divide-zinc-800/80">
                            @foreach ($order->items as $item)
                                @php
                                    $productImage = $this->getImageUrl($item);
                                    $variantDesc = $this->getVariantDescription($item->variant);
                                @endphp
                                <div class="flex flex-col sm:flex-row gap-5 items-start sm:items-center py-4">
                                    <!-- Image Block -->
                                    <div class="w-20 h-20 shrink-0 rounded-xl overflow-hidden bg-zinc-50 dark:bg-zinc-950 border border-zinc-200/60 dark:border-zinc-850 flex items-center justify-center p-2">
                                        @if ($productImage)
                                            <img alt="{{ $item->product?->name }}" class="w-full h-full object-contain mix-blend-multiply dark:mix-blend-normal" src="{{ $productImage }}"/>
                                        @else
                                            <div class="text-zinc-400 dark:text-zinc-600 flex flex-col items-center justify-center">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Name and Attributes -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-bold text-zinc-900 dark:text-white truncate">
                                            {{ $item->product?->name }}
                                        </h3>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                            @if ($variantDesc)
                                                <span>{{ $variantDesc }}</span>
                                                <span class="text-zinc-300 dark:text-zinc-700 font-bold">•</span>
                                            @endif
                                            <span>Qty: {{ $item->quantity }}</span>
                                        </p>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white mt-1.5">
                                            Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Order Footer Action Bar -->
                        <div class="bg-zinc-50/50 dark:bg-zinc-900/30 px-6 py-4 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="text-xs text-zinc-550 dark:text-zinc-400 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625-12h16.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H3.375a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Z"></path>
                                </svg>
                                <span>Payment Status:</span>
                                @if ($order->payment_status === 'paid')
                                    <span class="font-bold uppercase tracking-wider text-emerald-650 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded text-[10px]">
                                        {{ $order->payment_status }}
                                    </span>
                                @elseif ($order->payment_status === 'failed')
                                    <span class="font-bold uppercase tracking-wider text-red-650 dark:text-red-400 bg-red-50 dark:bg-red-500/10 px-2 py-0.5 rounded text-[10px]">
                                        {{ $order->payment_status }}
                                    </span>
                                @else
                                    <span class="font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 rounded text-[10px]">
                                        {{ $order->payment_status }}
                                    </span>
                                @endif
                            </div>
                            @if ($order->payment_status === 'pending' && $order->payment_url)
                                <div class="flex items-center gap-3 shrink-0 w-full sm:w-auto">
                                    <a href="{{ $order->payment_url }}" target="_blank" rel="noopener noreferrer"
                                       class="flex-1 sm:flex-initial bg-red-700 hover:bg-red-800 dark:bg-red-600 dark:hover:bg-red-700 text-white font-semibold text-sm rounded-lg px-4 py-2 transition-colors text-center inline-flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-5.625-12h16.5a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5H3.375a1.5 1.5 0 0 1-1.5-1.5V6a1.5 1.5 0 0 1 1.5-1.5Z"></path>
                                        </svg>
                                        Pay Now
                                    </a>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="flex flex-col items-center justify-center py-20 px-4 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 rounded-3xl shadow-sm">
                        <div class="w-16 h-16 rounded-full bg-zinc-50 dark:bg-zinc-800 flex items-center justify-center mb-5 border border-zinc-100 dark:border-zinc-800/80">
                            <svg class="w-8 h-8 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900 dark:text-white">No orders found</h3>
                        <p class="text-sm text-zinc-550 dark:text-zinc-400 mt-1 max-w-sm text-center">
                            You don't have any orders listed in this tab at the moment.
                        </p>
                        <a href="/merchandise" class="mt-6 bg-red-700 hover:bg-red-800 text-white font-semibold text-sm rounded-lg px-6 py-2.5 transition-colors shadow-sm inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path>
                            </svg>
                            Browse Merchandise
                        </a>
                    </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            @if ($orders->hasPages())
                <div class="mt-8 border-t border-zinc-150 dark:border-zinc-800/80 pt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-app.container>
    @endvolt
</x-layouts.app>