<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Invoice #{{ $order->invoice_id }} - Komunitas Historia Indonesia</title>
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
                    },
                    "fontFamily": {
                        "label-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-lg": ["Inter"],
                        "body-lg": ["Inter"],
                        "display-lg": ["Inter"],
                        "label-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-lg-mobile": ["Inter"]
                    },
                    "fontSize": {
                        "label-md": ["14px", { "lineHeight": "1.4", "letterSpacing": "0.01em", "fontWeight": "500" }],
                        "headline-md": ["24px", { "lineHeight": "1.3", "fontWeight": "600" }],
                        "headline-lg": ["32px", { "lineHeight": "1.2", "letterSpacing": "-0.01em", "fontWeight": "600" }],
                        "body-lg": ["18px", { "lineHeight": "1.6", "fontWeight": "400" }],
                        "display-lg": ["48px", { "lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "700" }],
                        "label-sm": ["12px", { "lineHeight": "1", "letterSpacing": "0.03em", "fontWeight": "600" }],
                        "body-md": ["16px", { "lineHeight": "1.5", "fontWeight": "400" }],
                        "headline-lg-mobile": ["28px", { "lineHeight": "1.2", "fontWeight": "600" }]
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
        }
    </style>
</head>
<body class="bg-surface-container-lowest text-on-background antialiased min-h-screen flex flex-col">
    <!-- Main Content -->
    <main class="flex-grow w-full max-w-max-width mx-auto px-margin-mobile md:px-margin-desktop py-12 md:py-16">
        <!-- Action Bar (Hidden when printing) -->
        <div class="no-print mb-8 flex flex-col sm:flex-row gap-4 justify-between items-center bg-surface-container-low p-4 rounded-xl border border-outline-variant shadow-sm">
            <a href="/orders/{{ $order->id }}" class="flex items-center gap-2 text-primary font-semibold hover:text-surface-tint transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
                Back to Order Details
            </a>
            <button onclick="window.print()" class="flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-full font-bold shadow-md hover:bg-surface-tint active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">print</span>
                Print Invoice
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 print:mb-4 gap-6 pb-6 border-b-2 border-primary">
            <div class="flex items-center gap-4">
                <img alt="KHI Logo" class="h-16 w-auto object-contain" src="{{ url('/images/logo.jpg') }}"/>
                <div class="font-headline-md text-headline-md font-bold text-primary">
                    Komunitas Historia Indonesia
                </div>
            </div>
            <div class="text-left md:text-right mt-4 md:mt-0">
                <h1 class="font-display-lg text-display-lg text-primary mb-2">Invoice</h1>
                <p class="font-body-md text-body-md text-on-surface-variant">#{{ $order->invoice_id }}</p>
            </div>
        </div>

        <!-- Two-Column Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12 print:mb-4">
            <!-- Left Column: Shipping -->
            <div class="bg-surface-container-low p-6 rounded-lg border border-outline-variant print-avoid-break">
                <div>
                    <h2 class="font-label-md text-label-md text-primary font-bold uppercase tracking-wider mb-2">Shipping To</h2>
                    <p class="font-body-md text-body-md text-on-background font-medium mb-1">{{ $order->user?->name }}</p>
                    <p class="font-body-md text-body-md text-on-surface-variant leading-relaxed">
                        @if ($order->address)
                            {{ $order->address->address_line }}<br/>
                            @if ($order->address->village || $order->address->district)
                                {{ $order->address->village }}{{ $order->address->village && $order->address->district ? ', ' : '' }}{{ $order->address->district }}<br/>
                            @endif
                            {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}<br/>
                            {{ $order->address->country }}
                        @else
                            No shipping address provided.
                        @endif
                    </p>
                    @if ($order->user?->email)
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $order->user->email }}</p>
                    @endif
                    @if ($order->user?->phone)
                        <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $order->user->phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Right Column: Order Details -->
            <div class="bg-surface-container p-6 rounded-lg border border-outline-variant print-avoid-break">
                <div class="grid grid-cols-2 gap-y-4">
                    <div>
                        <h2 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-1">Order Date</h2>
                        <p class="font-body-md text-body-md text-on-background font-medium">{{ $order->created_at->format('F d, Y') }}</p>
                    </div>
                    <div>
                        <h2 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-1">Payment Method</h2>
                        <p class="font-body-md text-body-md text-on-background font-medium">
                            @if($order->external_id)
                                Bank Transfer / Virtual Account
                            @else
                                Manual Transfer
                            @endif
                        </p>
                    </div>
                    <div>
                        <h2 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-1">Status</h2>
                        @php
                            $badgeClass = match($order->payment_status) {
                                'paid' => 'bg-surface-container-highest text-primary border-outline-variant',
                                'pending' => 'bg-surface-variant text-on-surface-variant border-outline',
                                default => 'bg-error-container text-error border-error'
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badgeClass }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    @if ($order->courier)
                    <div>
                        <h2 class="font-label-md text-label-md text-on-surface-variant uppercase tracking-wider mb-1">Courier</h2>
                        <p class="font-body-md text-body-md text-on-background font-medium uppercase">{{ $order->courier }} ({{ $order->service }})</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Item Table -->
        <div class="overflow-x-auto mb-12 print:mb-4">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-primary">
                        <th class="py-4 print:py-2 px-4 font-label-md text-label-md text-primary font-bold uppercase tracking-wider w-1/2">Item &amp; Description</th>
                        <th class="py-4 print:py-2 px-4 font-label-md text-label-md text-primary font-bold uppercase tracking-wider text-center">Qty</th>
                        <th class="py-4 print:py-2 px-4 font-label-md text-label-md text-primary font-bold uppercase tracking-wider text-right">Price</th>
                        <th class="py-4 print:py-2 px-4 font-label-md text-label-md text-primary font-bold uppercase tracking-wider text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant">
                    @foreach($order->items as $item)
                    <tr class="hover:bg-surface-container-low transition-colors duration-200 print-avoid-break">
                        <td class="py-6 print:py-2 px-4">
                            <p class="font-body-md text-body-md text-on-background font-medium">{{ $item->product?->name ?? 'Product Unavailable' }}</p>
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
                                    <p class="font-label-md text-label-md text-on-surface-variant mt-1">{{ $variantDesc }}</p>
                                @endif
                            @endif
                        </td>
                        <td class="py-6 print:py-2 px-4 text-center font-body-md text-body-md text-on-background">{{ $item->quantity }}</td>
                        <td class="py-6 print:py-2 px-4 text-right font-body-md text-body-md text-on-background">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="py-6 print:py-2 px-4 text-right font-body-md text-body-md text-on-background">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="flex justify-end">
            <div class="w-full md:w-1/3 print:w-1/2 bg-surface-container-lowest p-6 print:p-4 rounded-lg border border-outline-variant print-avoid-break">
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="font-body-md text-body-md text-on-surface-variant">Subtotal</span>
                        <span class="font-body-md text-body-md text-on-background">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-body-md text-body-md text-on-surface-variant">Shipping @if($order->courier)({{ strtoupper($order->courier) }} {{ $order->service }})@else(JNE Reguler)@endif</span>
                        <span class="font-body-md text-body-md text-on-background">Rp {{ number_format($order->shipping_fee, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t-2 border-primary pt-4 mt-4 flex justify-between items-center">
                        <span class="font-headline-md text-headline-md text-on-background font-bold">Grand Total</span>
                        <span class="font-headline-md text-headline-md text-primary font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-16 print:mt-6 text-center print-avoid-break">
            <p class="font-body-md text-body-md text-on-surface-variant italic">Thank you for your purchase. Your support helps us preserve and share Indonesia's rich history.</p>
        </div>
    </main>

    <!-- Automatic print trigger on load -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Wait slightly for fonts and styles to render
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>