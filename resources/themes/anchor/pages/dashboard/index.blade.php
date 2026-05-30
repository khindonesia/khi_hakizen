<?php
    use function Laravel\Folio\{middleware, name};
    middleware('auth');
    name('dashboard');
?>
<x-layouts.app>
    @php
        $user = auth()->user();
        $orders = collect();
        $aspirasiList = collect();
        $upcomingEvents = collect();
        $eventsData = [];

        if ($user) {
            $orders = \App\Models\Order::where('user_id', $user->id)
                ->with('items.product')
                ->latest()
                ->take(2)
                ->get();

            $aspirasiList = \App\Models\Aspirasi::where('author_id', $user->id)
                ->latest()
                ->take(2)
                ->get();

            $upcomingEvents = \App\Models\Event::published()
                ->with('author')
                ->latest()
                ->take(2)
                ->get();

            foreach ($upcomingEvents as $event) {
                $eventsData[$event->id] = [
                    'id' => $event->id,
                    'title' => $event->title,
                    'image' => $event->image ? Storage::url(ltrim($event->image, '/')) : 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?q=80&w=1200&auto=format&fit=crop',
                    'month' => $event->start_datetime->format('M'),
                    'day' => $event->start_datetime->format('d'),
                    'time' => $event->start_datetime->format('H:i') . ' - ' . $event->end_datetime->format('H:i') . ' WIB',
                    'dateFormatted' => $event->start_datetime->format('l, d F Y'),
                    'body' => $event->body,
                    'organizer' => $event->author->name ?? 'KHI Team',
                    'isUpcoming' => $event->start_datetime->isFuture(),
                    'isOngoing' => $event->start_datetime->isPast() && $event->end_datetime->isFuture(),
                ];
            }
        }
    @endphp

    <div class="max-w-[1024px] mx-auto p-4 md:p-6 lg:p-8 space-y-8" 
        x-data="{ 
            activeEvent: null, 
            showModal: false, 
            events: {{ json_encode($eventsData) }} 
        }" 
        x-cloak>
        
        <!-- Welcome Header -->
        <section class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold tracking-tight text-ink-deep dark:text-white">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="text-base text-secondary dark:text-zinc-400">
                    Member since {{ $user->created_at->format('Y') }} • KHI {{ $user->roles()->first()?->name ?? 'Member' }} Tier
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-zinc-800 border border-hairline dark:border-zinc-700 hover:border-primary hover:text-primary rounded-xl text-xs font-bold text-charcoal dark:text-zinc-300 transition shadow-xs">
                    <x-phosphor-arrow-left class="w-4 h-4" />
                    <span>Kembali ke Website</span>
                </a>
            </div>
        </section>

        <!-- Bento Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Orders Card (Col span 2) -->
            <div class="md:col-span-2 bg-surface-container-lowest dark:bg-zinc-800 border border-hairline dark:border-zinc-700 rounded-2xl shadow-xs hover:shadow-sm transition p-6 flex flex-col">
                <div class="flex justify-between items-center mb-6 border-b border-hairline dark:border-zinc-700 pb-4">
                    <h2 class="text-lg font-bold text-charcoal dark:text-white flex items-center space-x-2">
                        <span class="material-symbols-outlined text-primary dark:text-primary-fixed" style="font-variation-settings: 'FILL' 1;">shopping_bag</span>
                        <span>Recent Orders</span>
                    </h2>
                    <a class="text-sm font-semibold text-primary dark:text-primary-fixed hover:underline" href="/orders">
                        View All
                    </a>
                </div>
                
                <div class="space-y-6 flex-grow">
                    @forelse($orders as $order)
                        @php
                            $statusClass = match ($order->status) {
                                'delivered' => 'bg-card-tint-mint text-[#107F5B] border-[#EBF9F4]',
                                'shipping' => 'bg-card-tint-sky text-[#006ADC] border-[#EBF5FF]',
                                'pending' => 'bg-card-tint-yellow text-[#B28200] border-[#FFF9E6]',
                                default => 'bg-card-tint-peach text-[#E06D3B] border-[#FFF0EA]',
                            };
                        @endphp
                        <div class="flex items-center justify-between group">
                            <div class="flex items-start space-x-4">
                                <div class="w-12 h-12 rounded-lg bg-card-tint-cream dark:bg-zinc-700 flex items-center justify-center border border-hairline dark:border-zinc-600 overflow-hidden shrink-0">
                                    @php
                                        $firstItem = $order->items->first();
                                        $productImage = $firstItem && $firstItem->product ? $firstItem->product->image : null;
                                    @endphp
                                    @if($productImage)
                                        <img alt="{{ $firstItem->product->name }}" class="w-full h-full object-cover" src="{{ filter_var($productImage, FILTER_VALIDATE_URL) ? $productImage : asset('storage/' . $productImage) }}"/>
                                    @else
                                        <span class="material-symbols-outlined text-primary dark:text-primary-fixed">shopping_bag</span>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-charcoal dark:text-white group-hover:text-primary dark:group-hover:text-primary-fixed transition-colors line-clamp-1">
                                        {{ $firstItem && $firstItem->product ? $firstItem->product->name : 'Order #' . $order->id }}
                                        @if($order->items->count() > 1)
                                            <span class="text-xs text-slate dark:text-zinc-400 font-normal">(+{{ $order->items->count() - 1 }} item lainnya)</span>
                                        @endif
                                    </h3>
                                    <p class="text-xs font-semibold text-slate dark:text-zinc-400 mt-1">Ordered on {{ $order->created_at->format('M d, Y') }}</p>
                                    @if($order->resi)
                                        <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs" style="font-size: 14px;">local_shipping</span>
                                            <span>No. Resi: {{ $order->resi }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-col items-end space-y-2">
                                <span class="rounded-full border px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $statusClass }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <a href="/orders/{{ $order->id }}" class="text-xs font-bold text-primary dark:text-primary-fixed hover:underline">
                                    Details
                                </a>
                            </div>
                        </div>
                        @if(!$loop->last)
                            <div class="w-full h-px bg-hairline dark:bg-zinc-700"></div>
                        @endif
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate dark:text-zinc-500 mb-2">shopping_bag</span>
                            <p class="text-sm text-charcoal dark:text-zinc-300">Belum ada pesanan terbaru.</p>
                            <p class="text-xs text-slate dark:text-zinc-400 mt-1">Dukung Komunitas Historia Indonesia dengan membeli official merchandise kami.</p>
                            <a href="/merchandise" class="mt-4 px-4 py-2 bg-primary hover:bg-primary/95 text-white dark:text-white text-xs font-bold rounded-lg transition shadow-xs">
                                Kunjungi Toko
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Member Profile Widget (Col span 1) -->
            <div class="bg-card-tint-lavender dark:bg-zinc-900 border border-hairline dark:border-zinc-700 rounded-2xl shadow-xs p-6 flex flex-col items-center text-center">
                <div class="w-20 h-20 rounded-full border-4 border-white dark:border-zinc-800 shadow-sm mb-4 overflow-hidden relative group cursor-pointer shrink-0">
                    <img alt="{{ $user->name }}" class="w-full h-full object-cover" src="{{ $user->avatar() }}"/>
                    <a href="/settings/profile" class="absolute inset-0 bg-black/40 hidden group-hover:flex items-center justify-center transition-all">
                        <span class="material-symbols-outlined text-white text-sm">edit</span>
                    </a>
                </div>
                <h3 class="text-base font-bold text-ink-deep dark:text-white">
                    {{ $user->name }}
                </h3>
                <p class="text-xs text-secondary dark:text-zinc-400 mt-1">
                    {{ $user->occupation ?? 'Teman Sejarah' }}
                </p>
                
                <div class="mt-5 w-full bg-white dark:bg-zinc-800 rounded-xl p-4 border border-hairline dark:border-zinc-700 text-left space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate dark:text-zinc-400">Tier</span>
                        <span class="text-[10px] font-bold text-primary dark:text-primary-fixed bg-primary-fixed/20 dark:bg-primary-fixed/10 px-2 py-0.5 rounded uppercase">
                            {{ $user->roles()->first()?->name ?? 'Member' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs text-slate dark:text-zinc-400">Member Since</span>
                        <span class="text-xs font-semibold text-charcoal dark:text-white">
                            {{ $user->created_at->format('Y') }}
                        </span>
                    </div>
                </div>
                
                <a href="/settings/profile" class="mt-5 w-full py-2.5 rounded-xl border border-hairline dark:border-zinc-700 text-charcoal dark:text-zinc-300 text-xs font-bold hover:bg-white/50 dark:hover:bg-zinc-800 transition block text-center">
                    Edit Profile
                </a>
            </div>

            <!-- Aspirasi Card (Col span 1) -->
            <div class="bg-surface-container-lowest dark:bg-zinc-800 border border-hairline dark:border-zinc-700 rounded-2xl shadow-xs p-6 flex flex-col justify-between">
                <div>
                    <div class="flex justify-between items-center mb-6 border-b border-hairline dark:border-zinc-700 pb-4">
                        <h2 class="text-lg font-bold text-charcoal dark:text-white flex items-center space-x-2">
                            <span class="material-symbols-outlined text-[#B28200]" style="font-variation-settings: 'FILL' 1;">edit_document</span>
                            <span>Aspirasi</span>
                        </h2>
                        <a class="text-xs font-bold text-primary dark:text-primary-fixed hover:underline" href="/aspirasi">
                            Lihat Semua
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($aspirasiList as $item)
                            @php
                                $tintMap = [
                                    'cagar-budaya' => ['bg' => 'bg-card-tint-peach text-[#E06D3B] border-[#FFF0EA]', 'label' => 'Cagar Budaya'],
                                    'edukasi' => ['bg' => 'bg-card-tint-sky text-[#006ADC] border-[#EBF5FF]', 'label' => 'Edukasi'],
                                    'komunitas' => ['bg' => 'bg-card-tint-mint text-[#107F5B] border-[#EBF9F4]', 'label' => 'Komunitas'],
                                    'lainnya' => ['bg' => 'bg-card-tint-lavender text-[#df1c24] border-[#fff5f5]', 'label' => 'Lainnya'],
                                ];
                                $style = $tintMap[$item->category->slug ?? 'lainnya'] ?? $tintMap['lainnya'];
                            @endphp
                            <div class="p-3.5 rounded-xl bg-card-tint-cream dark:bg-zinc-900/50 border border-hairline dark:border-zinc-700 hover:border-primary transition duration-200">
                                <h3 class="text-xs font-bold text-charcoal dark:text-white line-clamp-1">
                                    {{ $item->title }}
                                </h3>
                                <div class="flex justify-between items-center mt-2.5">
                                    <span class="text-[9px] font-bold text-slate dark:text-zinc-400">
                                        {{ $item->created_at->format('M d, Y') }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded text-[8px] font-bold uppercase tracking-wider {{ $style['bg'] }} border">
                                        {{ $style['label'] }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-6 text-center">
                                <span class="material-symbols-outlined text-3xl text-slate dark:text-zinc-500 mb-2">campaign</span>
                                <p class="text-xs text-charcoal dark:text-zinc-300">Belum ada aspirasi.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                
                <a href="/aspirasi" class="mt-6 w-full py-3 bg-primary hover:bg-primary/95 text-white text-xs font-bold rounded-xl transition flex justify-center items-center space-x-1.5 shadow-xs">
                    <span class="material-symbols-outlined text-sm">add</span>
                    <span>New Submission</span>
                </a>
            </div>

            <!-- Events Card (Col span 2) -->
            <div class="md:col-span-2 bg-card-tint-peach dark:bg-zinc-800/40 border border-hairline dark:border-zinc-700 rounded-2xl shadow-xs p-6 flex flex-col justify-between relative overflow-hidden">
                <!-- Decorative icon -->
                <div class="absolute -right-6 -bottom-6 opacity-[0.03] dark:opacity-[0.05] pointer-events-none">
                    <span class="material-symbols-outlined text-[120px] text-primary">history_edu</span>
                </div>
                
                <div>
                    <div class="flex justify-between items-center mb-6 border-b border-hairline dark:border-zinc-700 pb-4 relative z-10">
                        <h2 class="text-lg font-bold text-charcoal dark:text-white flex items-center space-x-2">
                            <span class="material-symbols-outlined text-primary dark:text-primary-fixed" style="font-variation-settings: 'FILL' 1;">event_available</span>
                            <span>Upcoming Events</span>
                        </h2>
                        <a class="text-xs font-bold text-primary dark:text-primary-fixed hover:underline animate-pulse" href="/events">
                            Browse All
                        </a>
                    </div>
                    
                    <div class="space-y-4 relative z-10">
                        @forelse($upcomingEvents as $event)
                            @php
                                $startDatetime = $event->start_datetime;
                                $month = $startDatetime ? $startDatetime->format('M') : 'Event';
                                $day = $startDatetime ? $startDatetime->format('d') : 'Date';
                                $time = $event->start_datetime->format('h:i A') . ' - ' . $event->end_datetime->format('h:i A');
                            @endphp
                            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-hairline dark:border-zinc-700 p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between group hover:border-primary transition">
                                <div class="flex items-start space-x-4 mb-4 sm:mb-0">
                                    <div class="bg-primary-fixed text-primary rounded-lg p-2 text-center min-w-[56px] border border-primary-fixed-dim shrink-0">
                                        <div class="text-[10px] font-bold uppercase tracking-wider">{{ $month }}</div>
                                        <div class="text-xl font-bold leading-none mt-1">{{ $day }}</div>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-semibold text-charcoal dark:text-white group-hover:text-primary dark:group-hover:text-primary-fixed transition-colors">
                                            {{ $event->title }}
                                        </h3>
                                        <div class="flex items-center space-x-1.5 text-slate dark:text-zinc-400 mt-1">
                                            <span class="material-symbols-outlined text-xs">schedule</span>
                                            <span class="text-[11px] font-medium">{{ $time }}</span>
                                        </div>
                                        <div class="flex items-center space-x-1.5 text-slate dark:text-zinc-400 mt-0.5">
                                            <span class="material-symbols-outlined text-xs">location_on</span>
                                            <span class="text-[11px] font-medium">Pulau Onrust, Kepulauan Seribu</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex sm:flex-col space-x-2 sm:space-x-0 sm:space-y-2 w-full sm:w-auto">
                                    <button @click="activeEvent = events[{{ $event->id }}]; showModal = true" class="flex-1 sm:flex-initial py-2 px-4 rounded-lg border border-hairline dark:border-zinc-700 text-charcoal dark:text-zinc-300 text-xs font-bold hover:bg-slate-50 dark:hover:bg-zinc-700 transition flex justify-center items-center space-x-1">
                                        <span class="material-symbols-outlined text-sm">confirmation_number</span>
                                        <span>Detail</span>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-6 text-center bg-white dark:bg-zinc-800 rounded-xl border border-dashed border-hairline dark:border-zinc-700 p-4">
                                <span class="material-symbols-outlined text-3xl text-slate dark:text-zinc-500 mb-2">event</span>
                                <p class="text-xs text-charcoal dark:text-zinc-300">Belum ada event mendatang.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        <!-- Event Detail Modal Overlay (Stitch Premium Design) -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6 md:p-10"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
             
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-[#050B1F]/60 backdrop-blur-md transition-opacity" @click="showModal = false"></div>

            <!-- Modal Panel -->
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                 class="relative bg-white dark:bg-zinc-900 rounded-3xl border border-[#E9E9E8] dark:border-zinc-800 shadow-2xl w-full max-w-4xl max-h-[85vh] overflow-hidden flex flex-col md:flex-row z-10">
                 
                <!-- Left Side: Cover, Title & Description (Scrollable) -->
                <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6 max-h-[50vh] md:max-h-none md:h-[80vh]">
                    <!-- Image -->
                    <div class="relative bg-zinc-100 rounded-2xl overflow-hidden aspect-[16/9] border border-[#E9E9E8] dark:border-zinc-800 shrink-0">
                        <img :src="activeEvent?.image" :alt="activeEvent?.title" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                    </div>

                    <!-- Header Titles & Metadata -->
                    <div class="space-y-3">
                        <!-- Category/Status Tag -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="bg-[#f9f1fc] dark:bg-zinc-800 text-[#df1c24] dark:text-[#ebddff] text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                                KHI Heritage Program
                            </span>
                            <span x-show="activeEvent?.isOngoing" class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Ongoing</span>
                            <span x-show="activeEvent?.isUpcoming" class="bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Upcoming</span>
                            <span x-show="!activeEvent?.isOngoing && !activeEvent?.isUpcoming" class="bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Past Event</span>
                        </div>

                        <h2 class="text-xl md:text-2xl font-bold text-[#37352F] dark:text-white tracking-tight leading-snug" x-text="activeEvent?.title"></h2>

                        <!-- Author & Date Info bar -->
                        <div class="flex items-center gap-3 border-y border-[#E9E9E8]/50 dark:border-zinc-800/80 py-3 mt-4">
                            <div class="w-8 h-8 rounded-full bg-[#fef2f2] dark:bg-zinc-800 flex items-center justify-center text-[#df1c24] dark:text-primary-fixed font-bold text-xs" x-text="activeEvent?.organizer ? activeEvent.organizer.substring(0, 1) : 'K'"></div>
                            <div>
                                <p class="text-xs text-[#37352F] dark:text-zinc-200 font-semibold">Organized by <span x-text="activeEvent?.organizer"></span></p>
                                <p class="text-[10px] text-[#979A9B] uppercase tracking-wider font-semibold mt-0.5">Community Curator</p>
                            </div>
                        </div>
                    </div>

                    <!-- Event Main Article / Body Content -->
                    <div class="prose prose-zinc dark:prose-invert max-w-none text-[#575e75] dark:text-zinc-300 leading-relaxed text-sm" x-html="activeEvent?.body"></div>
                </div>

                <!-- Right Side: Details Sidebar Panel -->
                <div class="w-full md:w-80 bg-[#fff5f5] dark:bg-zinc-800/50 p-6 md:p-8 flex flex-col justify-between border-t md:border-t-0 md:border-l border-[#E9E9E8] dark:border-zinc-700 shrink-0">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-bold text-[#37352F] dark:text-white tracking-tight">Event Details</h3>
                            <p class="text-xs text-[#979A9B] mt-0.5">Please review the details and RSVP status.</p>
                        </div>

                        <div class="space-y-4 py-4 border-y border-[#E9E9E8]/50 dark:border-zinc-700/80">
                            <!-- Calendar row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Date</p>
                                    <p class="text-xs text-[#575e75] dark:text-zinc-400 mt-0.5" x-text="activeEvent?.dateFormatted"></p>
                                </div>
                            </div>

                            <!-- Schedule row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">schedule</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Time & Hours</p>
                                    <p class="text-xs text-[#575e75] dark:text-zinc-400 mt-0.5" x-text="activeEvent?.time"></p>
                                </div>
                            </div>

                            <!-- Venue row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">location_on</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Location</p>
                                    <p class="text-xs text-[#575e75] dark:text-zinc-400 mt-0.5">Pulau Onrust, Kepulauan Seribu</p>
                                </div>
                            </div>

                            <!-- Cost row -->
                            <div class="flex gap-3">
                                <span class="material-symbols-outlined text-[#df1c24] dark:text-primary-fixed text-[20px] mt-0.5" style="font-variation-settings: 'FILL' 1;">confirmation_number</span>
                                <div>
                                    <p class="text-xs font-semibold text-[#37352F] dark:text-zinc-200">Admission</p>
                                    <p class="text-xs text-[#107F5B] dark:text-emerald-400 font-semibold mt-0.5">Free Registration</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RSVP / Action Buttons -->
                    <div class="flex flex-col gap-2.5 mt-6 md:mt-0">
                        <button type="button" 
                                x-show="activeEvent?.isUpcoming || activeEvent?.isOngoing"
                                @click="alert('Thank you for your interest! Registered members can automatically attend this event. Live schedule is synced to your calendar.')"
                                class="w-full bg-[#df1c24] text-white text-xs font-bold py-3 rounded-xl hover:bg-opacity-95 transition flex items-center justify-center gap-1.5 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">add_circle</span>
                            RSVP & Join Event
                        </button>
                        <button type="button" disabled
                                x-show="!activeEvent?.isUpcoming && !activeEvent?.isOngoing"
                                class="w-full bg-zinc-200 dark:bg-zinc-700 text-zinc-400 dark:text-zinc-500 text-xs font-bold py-3 rounded-xl flex items-center justify-center gap-1.5 cursor-not-allowed">
                            <span class="material-symbols-outlined text-[16px]">event_busy</span>
                            Event Has Ended
                        </button>

                        <button type="button" 
                                @click="showModal = false"
                                class="w-full bg-white dark:bg-zinc-800 border border-[#E9E9E8] dark:border-zinc-750 text-[#37352F] dark:text-zinc-300 text-xs font-bold py-3 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-700 transition flex items-center justify-center gap-1.5 shadow-xs">
                            Close Details
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-layouts.app>
