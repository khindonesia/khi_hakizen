<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Tiket: {{ $event->title }}</title>
    <!-- Google Fonts: Plus Jakarta Sans & Space Grotesk for ticket style typography -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"Space Grotesk"', 'monospace'],
                    }
                }
            }
        }
    </script>

    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        /* Custom Ticket styling */
        .ticket-wrapper {
            perspective: 1000px;
        }
        .ticket-card {
            position: relative;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            width: 860px;
            height: 340px;
            display: flex;
        }
        
        /* Circular notches on the perforated line */
        .ticket-card::before, .ticket-card::after {
            content: '';
            position: absolute;
            width: 28px;
            height: 28px;
            background: #0f1013; /* Must match the body background */
            border-radius: 50%;
            z-index: 10;
            transition: background-color 0.3s ease;
        }
        .ticket-card::before {
            top: -14px;
            right: 246px; /* Position matching the vertical divider */
        }
        .ticket-card::after {
            bottom: -14px;
            right: 246px;
        }

        /* Perforated separator line */
        .perforated-line {
            border-left: 2px dashed #E4E4E7;
            height: 100%;
            position: relative;
        }

        /* Background watermarks & gradients */
        .radial-glow {
            background: radial-gradient(circle at 100% 0%, rgba(223, 28, 36, 0.08) 0%, transparent 60%);
        }

        /* Print optimization */
        @media print {
            body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-area {
                box-shadow: none !important;
                background: transparent !important;
                min-height: 100vh !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            .ticket-card {
                box-shadow: none !important;
                border: 1px solid #E4E4E7 !important;
                background: #ffffff !important;
                margin: auto !important;
            }
            .ticket-card::before, .ticket-card::after {
                background: #ffffff !important;
                border: 1px solid #E4E4E7 !important;
            }
            @page {
                size: landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body class="bg-[#0f1013] text-zinc-900 font-sans min-h-screen flex flex-col items-center justify-center p-4 md:p-8 transition-colors duration-300 radial-glow">

    <!-- Top floating toolbar / Navigation -->
    <div class="no-print w-full max-w-[860px] flex items-center justify-between mb-8">
        <a href="{{ route('dashboard.events') }}" class="flex items-center gap-2 text-sm font-semibold text-zinc-400 hover:text-white transition-colors duration-200">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Dashboard
        </a>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="flex items-center gap-2 bg-[#df1c24] hover:bg-[#df1c24]/90 text-white text-sm font-bold px-5 py-2.5 rounded-full shadow-lg hover:shadow-red-900/20 transition-all duration-200 hover:-translate-y-0.5">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Cetak Tiket
            </button>
        </div>
    </div>

    <!-- Printable Ticket Container -->
    <div class="print-area ticket-wrapper flex items-center justify-center">
        
        <!-- Ticket Card Box -->
        <div class="ticket-card relative">
            
            <!-- Left Side: Main Event Information (70% width) -->
            <div class="w-[600px] h-full p-6 flex flex-col justify-between relative bg-white overflow-hidden">
                
                <!-- Background visual watermark/artwork -->
                <div class="absolute -right-16 -top-16 w-64 h-64 bg-red-50 rounded-full opacity-35 filter blur-3xl pointer-events-none"></div>
                <div class="absolute left-0 top-0 w-2.5 h-full bg-[#df1c24]"></div>
                
                <!-- Main Header -->
                <div class="pl-4 flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[#df1c24] font-extrabold tracking-widest text-[11px] uppercase font-mono">KOMUNITAS HISTORIA INDONESIA</span>
                            <span class="h-1 w-1 bg-zinc-300 rounded-full"></span>
                            <span class="bg-emerald-50 text-emerald-700 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Terkonfirmasi</span>
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 mt-1.5 leading-snug line-clamp-1">
                            {{ $event->title }}
                        </h1>
                    </div>
                </div>

                <!-- Event Details Grid -->
                <div class="pl-4 grid grid-cols-2 gap-x-6 gap-y-4 my-auto">
                    <!-- Date detail -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-[#df1c24] shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[18px]">calendar_today</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Hari & Tanggal</span>
                            <span class="text-xs font-bold text-zinc-800">{{ $event->start_datetime->translatedFormat('l, d F Y') }}</span>
                        </div>
                    </div>

                    <!-- Time detail -->
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-[#df1c24] shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[18px]">schedule</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Waktu Acara</span>
                            <span class="text-xs font-bold text-zinc-800">{{ $event->start_datetime->format('H:i') }} - {{ $event->end_datetime->format('H:i') }} WIB</span>
                        </div>
                    </div>

                    <!-- Venue Location detail -->
                    <div class="flex items-start gap-3 col-span-2">
                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-[#df1c24] shrink-0 mt-0.5">
                            <span class="material-symbols-outlined text-[18px]">location_on</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Lokasi Pertemuan</span>
                            <span class="text-xs font-bold text-zinc-800 line-clamp-1">{{ $event->location ?? 'Kota Tua Jakarta, DKI Jakarta' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Attendee & Ticket Info Bar -->
                <div class="pl-4 pt-4 border-t border-zinc-100 flex items-center justify-between">
                    <div>
                        <span class="text-[9px] font-bold text-zinc-450 uppercase tracking-wider block">Nama Peserta</span>
                        <span class="text-sm font-extrabold text-zinc-900 leading-none block mt-0.5">{{ $user->name }}</span>
                        <span class="text-[10px] text-zinc-400 block mt-0.5">{{ $user->email }}</span>
                    </div>
                    
                    <div class="text-right">
                        <span class="text-[9px] font-bold text-zinc-450 uppercase tracking-wider block">Harga</span>
                        <span class="inline-flex items-center gap-1 mt-0.5">
                            @if($event->type === 'PAID')
                                <span class="bg-amber-550 text-amber-700 bg-amber-50 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-amber-200">Rp {{ number_format($event->price, 0, ',', '.') }}</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-emerald-100">Gratis</span>
                            @endif
                        </span>
                    </div>
                </div>

            </div>

            <!-- Perforated separator column -->
            <div class="h-full flex items-center justify-center bg-white">
                <div class="perforated-line"></div>
            </div>

            <!-- Right Side: Ticket Stub (30% width) -->
            <div class="w-[260px] h-full p-6 flex flex-col justify-between items-center bg-[#FAF9F9] relative overflow-hidden shrink-0">
                
                <!-- Background accent circles -->
                <div class="absolute -right-12 -bottom-12 w-28 h-28 bg-[#df1c24]/5 rounded-full pointer-events-none"></div>
                
                <!-- Logo & Short event indicator -->
                <div class="text-center w-full">
                    <span class="text-[9px] font-extrabold text-zinc-400 uppercase tracking-widest block font-mono">ADMISSION STUB</span>
                    <span class="text-[10px] font-extrabold text-[#df1c24] mt-0.5 tracking-wider block uppercase">KHI GATEPASS</span>
                </div>

                <!-- Custom SVG QR Code Placeholder -->
                <div class="my-auto bg-white p-2.5 rounded-2xl border border-zinc-200/60 shadow-sm flex items-center justify-center">
                    <svg class="w-[105px] h-[105px]" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- QR Code Frame background -->
                        <rect width="100" height="100" rx="10" fill="#FFFFFF"/>
                        <!-- Top Left Anchor -->
                        <rect x="8" y="8" width="22" height="22" rx="4" fill="#df1c24"/>
                        <rect x="12" y="12" width="14" height="14" rx="2" fill="#ffffff"/>
                        <rect x="15" y="15" width="8" height="8" rx="1" fill="#df1c24"/>
                        <!-- Top Right Anchor -->
                        <rect x="70" y="8" width="22" height="22" rx="4" fill="#df1c24"/>
                        <rect x="74" y="12" width="14" height="14" rx="2" fill="#ffffff"/>
                        <rect x="77" y="15" width="8" height="8" rx="1" fill="#df1c24"/>
                        <!-- Bottom Left Anchor -->
                        <rect x="8" y="70" width="22" height="22" rx="4" fill="#df1c24"/>
                        <rect x="12" y="74" width="14" height="14" rx="2" fill="#ffffff"/>
                        <rect x="15" y="77" width="8" height="8" rx="1" fill="#df1c24"/>
                        <!-- Simulated QR Code data points -->
                        <rect x="36" y="8" width="6" height="6" rx="1" fill="#18181B"/>
                        <rect x="46" y="8" width="10" height="6" rx="1" fill="#18181B"/>
                        <rect x="58" y="8" width="6" height="10" rx="1" fill="#18181B"/>
                        
                        <rect x="36" y="18" width="16" height="6" rx="1" fill="#18181B"/>
                        <rect x="58" y="20" width="6" height="6" rx="1" fill="#df1c24"/>
                        
                        <rect x="8" y="36" width="6" height="6" rx="1" fill="#18181B"/>
                        <rect x="18" y="36" width="12" height="6" rx="1" fill="#18181B"/>
                        <rect x="8" y="46" width="6" height="12" rx="1" fill="#18181B"/>
                        <rect x="18" y="46" width="6" height="6" rx="1" fill="#18181B"/>
                        
                        <rect x="36" y="36" width="24" height="24" rx="4" fill="#df1c24" fill-opacity="0.1"/>
                        <circle cx="48" cy="48" r="5" fill="#df1c24"/>
                        
                        <rect x="66" y="36" width="12" height="6" rx="1" fill="#18181B"/>
                        <rect x="82" y="36" width="10" height="6" rx="1" fill="#18181B"/>
                        <rect x="66" y="46" width="6" height="12" rx="1" fill="#18181B"/>
                        <rect x="76" y="46" width="16" height="6" rx="1" fill="#18181B"/>
                        
                        <rect x="36" y="66" width="6" height="12" rx="1" fill="#18181B"/>
                        <rect x="46" y="66" width="12" height="6" rx="1" fill="#18181B"/>
                        <rect x="36" y="82" width="24" height="6" rx="1" fill="#18181B"/>
                        
                        <rect x="66" y="66" width="6" height="6" rx="1" fill="#18181B"/>
                        <rect x="76" y="66" width="16" height="16" rx="2" fill="#df1c24"/>
                        <rect x="66" y="76" width="6" height="12" rx="1" fill="#18181B"/>
                    </svg>
                </div>

                <!-- Custom Vector Barcode -->
                <div class="w-full text-center mt-2.5">
                    <svg class="w-full h-8 opacity-85" viewBox="0 0 200 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="10" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="14" y="5" width="4" height="30" fill="#18181B"/>
                        <rect x="20" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="23" y="5" width="3" height="30" fill="#18181B"/>
                        <rect x="28" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="32" y="5" width="6" height="30" fill="#18181B"/>
                        <rect x="40" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="43" y="5" width="4" height="30" fill="#18181B"/>
                        <rect x="49" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="53" y="5" width="3" height="30" fill="#18181B"/>
                        <rect x="58" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="61" y="5" width="5" height="30" fill="#18181B"/>
                        <rect x="68" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="72" y="5" width="4" height="30" fill="#18181B"/>
                        
                        <rect x="80" y="5" width="2" height="30" fill="#df1c24"/>
                        <rect x="84" y="5" width="2" height="30" fill="#df1c24"/>
                        
                        <rect x="90" y="5" width="3" height="30" fill="#18181B"/>
                        <rect x="95" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="98" y="5" width="4" height="30" fill="#18181B"/>
                        <rect x="104" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="108" y="5" width="6" height="30" fill="#18181B"/>
                        <rect x="116" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="119" y="5" width="3" height="30" fill="#18181B"/>
                        <rect x="124" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="128" y="5" width="5" height="30" fill="#18181B"/>
                        <rect x="135" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="138" y="5" width="4" height="30" fill="#18181B"/>
                        <rect x="144" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="148" y="5" width="3" height="30" fill="#18181B"/>
                        <rect x="153" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="156" y="5" width="6" height="30" fill="#18181B"/>
                        <rect x="164" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="168" y="5" width="4" height="30" fill="#18181B"/>
                        <rect x="174" y="5" width="1" height="30" fill="#18181B"/>
                        <rect x="177" y="5" width="3" height="30" fill="#18181B"/>
                        <rect x="182" y="5" width="2" height="30" fill="#18181B"/>
                        <rect x="186" y="5" width="4" height="30" fill="#18181B"/>
                        <rect x="192" y="5" width="2" height="30" fill="#18181B"/>
                    </svg>
                    <span class="text-[9px] font-bold text-zinc-500 uppercase tracking-widest block font-mono mt-1">
                        {{ $registration->external_id ?? 'KHI-EVT-'.$event->id.'-'.$user->id }}
                    </span>
                </div>

            </div>
            
        </div>
        
    </div>

    <!-- Short Help / Instruction section -->
    <div class="no-print mt-8 text-center text-xs text-zinc-500 max-w-md">
        <p class="mb-2"><strong>Tips:</strong> Klik "Cetak Tiket" untuk membuka menu cetak browser. Anda dapat memilih printer fisik atau <strong>"Simpan sebagai PDF"</strong> untuk mengunduh tiket digital Anda.</p>
        <p>Gunakan tiket ini untuk verifikasi kehadiran di lokasi acara.</p>
    </div>

</body>
</html>
