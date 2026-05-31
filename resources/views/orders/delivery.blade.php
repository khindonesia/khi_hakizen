<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Delivery Order #{{ $order->invoice_id }} - Komunitas Historia Indonesia</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-variant": "#fbdbd9",
                        "secondary-container": "#e2dfde",
                        "on-error-container": "#93000a",
                        "outline-variant": "#e5bdba",
                        "surface": "#fff8f7",
                        "surface-container-low": "#fff0ef",
                        "outline": "#906f6c",
                        "on-background": "#281716",
                        "primary-fixed": "#ffdad7",
                        "on-tertiary-fixed": "#1b1c1c",
                        "on-primary": "#ffffff",
                        "primary-container": "#ce2029",
                        "surface-tint": "#bd0f1f",
                        "secondary": "#5f5e5e",
                        "error": "#ba1a1a",
                        "tertiary-fixed-dim": "#c7c6c6",
                        "secondary-fixed-dim": "#c8c6c5",
                        "on-tertiary": "#ffffff",
                        "on-surface-variant": "#5c403d",
                        "on-secondary-fixed-variant": "#474746",
                        "on-primary-container": "#ffe5e2",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#ffe9e7",
                        "on-secondary-fixed": "#1c1b1b",
                        "secondary-fixed": "#e5e2e1",
                        "inverse-on-surface": "#ffedeb",
                        "on-error": "#ffffff",
                        "tertiary-fixed": "#e4e2e2",
                        "on-primary-fixed": "#410004",
                        "on-secondary-container": "#636262",
                        "on-secondary": "#ffffff",
                        "primary": "#a80017",
                        "on-tertiary-fixed-variant": "#464747",
                        "surface-dim": "#f2d3d0",
                        "on-primary-fixed-variant": "#930013",
                        "surface-container-highest": "#fbdbd9",
                        "surface-container-high": "#ffe2df",
                        "primary-fixed-dim": "#ffb3ad",
                        "on-surface": "#281716",
                        "tertiary-container": "#6a6a6a",
                        "tertiary": "#525252",
                        "surface-bright": "#fff8f7",
                        "on-tertiary-container": "#edebeb",
                        "inverse-surface": "#3f2c2a",
                        "error-container": "#ffdad6",
                        "background": "#fff8f7",
                        "inverse-primary": "#ffb3ad"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "max-width": "1024px",
                        "gutter": "24px",
                        "margin-desktop": "64px",
                        "margin-mobile": "16px",
                        "unit": "8px"
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        @media print {
            @page {
                size: portrait;
                margin: 0;
            }
            .no-print { display: none !important; }
            body {
                background-color: #ffffff;
                margin: 0 !important;
                padding: 0 !important;
            }
            main {
                padding: 1.2cm 2.0cm !important;
                max-width: 100% !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .print-avoid-break {
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
            /* Visual packaging/cutting guidelines */
            .delivery-slip-border {
                border: 2px dashed #000000 !important;
                padding: 16px !important;
                break-inside: avoid !important;
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body class="bg-surface-container-lowest text-on-background antialiased min-h-screen flex flex-col">
    <!-- Main Content -->
    <main class="flex-grow w-full max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <!-- Action Bar (Hidden when printing) -->
        <div class="no-print mb-8 flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-container-low p-4 rounded-xl border border-outline-variant shadow-sm">
            <a href="/admin/orders/{{ $order->id }}" class="flex items-center gap-2 text-primary font-semibold hover:text-surface-tint transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Back to Order Details
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-full font-bold shadow-md hover:bg-surface-tint active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">print</span>
                Print Delivery Order
            </button>
        </div>

        <!-- Main Delivery Slip Wrapper -->
        <div class="bg-white p-6 md:p-12 rounded-2xl border border-outline-variant shadow-sm delivery-slip-border">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 mb-6 print:mb-4 border-b-2 border-primary gap-4">
                <div class="flex items-center gap-4">
                    <img alt="KHI Logo" class="h-16 w-auto object-contain" src="{{ url('/images/logo.jpg') }}"/>
                    <div>
                        <div class="text-xl md:text-2xl font-bold text-primary">Komunitas Historia Indonesia</div>
                        <div class="text-xs text-on-surface-variant font-medium tracking-wide">SURAT JALAN &bull; DELIVERY SLIP</div>
                    </div>
                </div>
                <div class="text-left md:text-right">
                    <h1 class="text-2xl font-extrabold text-primary tracking-tight">DELIVERY ORDER</h1>
                    <p class="text-sm font-semibold text-on-surface-variant">No: {{ $order->invoice_id }}</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Date: {{ $order->created_at->format('d F Y') }}</p>
                </div>
            </div>

            <!-- Courier Badge and Delivery Details -->
            @if ($order->courier)
            <div class="mb-6 print:mb-4 flex flex-wrap gap-4 items-center bg-surface-container-low px-4 py-3 rounded-lg border border-outline-variant no-print print:flex">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold bg-primary text-white uppercase tracking-wider">
                    {{ $order->courier }}
                </span>
                <span class="text-sm font-semibold text-on-background">
                    Service: <span class="font-extrabold text-primary">{{ strtoupper($order->service) }}</span>
                </span>
            </div>
            @endif

            <!-- Address Block (Sender & Recipient Grid) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6 print:mb-4">
                <!-- Left: Sender Details -->
                <div class="p-6 rounded-xl border border-outline-variant bg-surface-container-lowest print-avoid-break">
                    <h2 class="text-xs font-bold text-primary uppercase tracking-wider mb-4 pb-2 border-b border-outline-variant flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm font-semibold">storefront</span>
                        PENGIRIM (SENDER)
                    </h2>
                    <p class="text-base font-bold text-on-background">{{ config('app.name', 'Komunitas Historia Indonesia') }} (KHI Store)</p>
                    <p class="text-sm text-on-surface-variant mt-1.5 leading-relaxed">
                        Jakarta, DKI Jakarta<br/>
                        Indonesia
                    </p>
                    <div class="mt-4 pt-3 border-t border-outline-variant/60 flex items-center gap-2 text-sm text-on-background font-medium">
                        <span class="material-symbols-outlined text-base text-primary">call</span>
                        <span>0812-3456-7890</span>
                    </div>
                </div>

                <!-- Right: Recipient Details (Large and High Contrast) -->
                <div class="p-6 rounded-xl border-2 border-primary bg-surface-container-lowest print-avoid-break">
                    <h2 class="text-xs font-black text-primary uppercase tracking-widest mb-4 pb-2 border-b-2 border-primary flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-sm font-black">local_shipping</span>
                        PENERIMA (RECIPIENT)
                    </h2>
                    <!-- Large clear typography for recipient name -->
                    <p class="text-2xl font-black text-zinc-950 tracking-tight leading-none mb-2">
                        {{ $order->user?->name }}
                    </p>
                    <!-- Large readable address details -->
                    <p class="text-base font-bold text-zinc-900 leading-relaxed space-y-1">
                        @if ($order->address)
                            <span class="block text-zinc-950 font-black text-lg">{{ $order->address->address_line }}</span>
                            @if ($order->address->village || $order->address->district)
                                <span class="block">Kel. {{ $order->address->village ?? '-' }}, Kec. {{ $order->address->district ?? '-' }}</span>
                            @endif
                            <span class="block">{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}</span>
                            <span class="block text-sm text-zinc-700 tracking-wider font-semibold uppercase">{{ $order->address->country }}</span>
                        @else
                            <span class="text-error font-medium italic">No shipping address provided.</span>
                        @endif
                    </p>
                    <div class="mt-5 pt-3 border-t border-primary/50 flex items-center gap-2 text-base text-zinc-950 font-black">
                        <span class="material-symbols-outlined text-lg text-primary">call</span>
                        <span>{{ $order->address?->phone_number ?? $order->user?->phone ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-6 print:mb-4 print-avoid-break">
                <h2 class="text-sm font-bold text-primary uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">format_list_bulleted</span>
                    DAFTAR BARANG (ITEMS SUMMARY)
                </h2>
                <table class="w-full text-left border-collapse rounded-lg overflow-hidden border border-outline-variant">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider w-4/5 border border-primary">Nama Produk (Product Name)</th>
                            <th class="py-3 px-4 text-xs font-bold uppercase tracking-wider text-center w-1/5 border border-primary">Jumlah (Qty)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($order->items as $item)
                        <tr class="hover:bg-surface-container-low transition-colors duration-150">
                            <td class="py-4 px-4 border border-outline-variant">
                                <p class="text-sm font-bold text-on-background">{{ $item->product?->name ?? 'Product Unavailable' }}</p>
                                @if ($item->variant && !$item->variant->is_default)
                                    @php
                                        $attributes = [];
                                        foreach ($item->variant->variantAttributes as $va) {
                                            if ($va->attribute && $va->attributeValue) {
                                                $attributes[] = $va->attribute->name . ': ' . $va->attributeValue->value;
                                            }
                                        }
                                        $variantDesc = implode(', ', $attributes);
                                    @endphp
                                    @if (!empty($variantDesc))
                                        <p class="text-xs text-on-surface-variant font-medium mt-1 bg-surface-container px-2 py-0.5 rounded inline-block">{{ $variantDesc }}</p>
                                    @endif
                                @endif
                            </td>
                            <td class="py-4 px-4 text-center text-base font-black text-on-background border border-outline-variant">
                                {{ $item->quantity }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="py-6 px-4 text-center text-sm text-on-surface-variant italic">No items found in this order.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Quote -->
            <div class="mt-6 print:mt-4 text-center text-[10px] text-on-surface-variant italic leading-relaxed">
                "Terima kasih atas pembelian Anda. Dukungan Anda membantu Komunitas Historia Indonesia dalam upaya pelestarian sejarah bangsa."
            </div>
        </div>
    </main>

    <!-- Automatic print trigger on load -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Wait slightly for fonts and styles to render
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
