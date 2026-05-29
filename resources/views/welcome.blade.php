<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Inventory v1.1.0') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">

    <!-- Navigation Bar -->
    <nav
        class="sticky top-0 z-50 backdrop-blur-md bg-white/70 dark:bg-zinc-950/70 border-b border-zinc-200 dark:border-zinc-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand Logo & Version -->
                <div class="flex items-center gap-2.5">
                    <div
                        class="h-9 w-9 rounded-xl bg-amber-500 flex items-center justify-center text-black font-black text-xl shadow-lg shadow-amber-500/20">
                        I
                    </div>
                    <div class="flex items-baseline gap-2">
                        <span
                            class="font-bold text-xl tracking-tight bg-gradient-to-r from-zinc-900 to-zinc-600 dark:from-white dark:to-zinc-400 bg-clip-text text-transparent">
                            Inventory
                        </span>
                        <span
                            class="text-[11px] font-medium px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-500 border border-zinc-200 dark:border-zinc-700 font-mono">
                            v1.1.0
                        </span>
                    </div>
                </div>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-4">
                    @auth
                    <a href="{{ url('/admin') }}"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-black bg-amber-500 hover:bg-amber-400 rounded-xl transition-all duration-200 shadow-md shadow-amber-500/10 active:scale-95">
                        Dashboard
                    </a>
                    @else
                    <a href="{{ url('/admin/login') }}"
                        class="inline-flex items-center justify-center px-5 py-2 text-sm font-semibold text-black bg-amber-500 hover:bg-amber-400 rounded-xl transition-all duration-200 shadow-md shadow-amber-500/10 active:scale-95">
                        Login
                    </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="relative overflow-hidden pt-16 pb-24 lg:pt-24 lg:pb-32">
        <div
            class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-amber-500/10 to-transparent blur-3xl pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <span
                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 mb-6">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                Core Inventory Management Engine
            </span>

            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight max-w-4xl mx-auto leading-tight">
                Real-Time Stock Tracking & <span
                    class="bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent">Multi-Store
                    Control</span>
            </h1>

            <p class="mt-6 text-lg sm:text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                Sinkronisasikan stok gudang, kelola inventaris produk otomatis, dan pantau performa penjualan retail
                Anda langsung dari satu tempat.
            </p>

            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ url('/admin/login') }}"
                    class="px-6 py-3 text-base font-semibold text-black bg-amber-500 hover:bg-amber-400 rounded-xl transition-all duration-200 shadow-lg shadow-amber-500/20 active:scale-95">
                    Start Managing
                </a>
                <a href="#features"
                    class="px-6 py-3 text-base font-semibold text-zinc-700 bg-zinc-200/80 hover:bg-zinc-200 dark:text-zinc-300 dark:bg-zinc-900 dark:hover:bg-zinc-800 rounded-xl transition-all duration-200 active:scale-95 border border-zinc-300/30">
                    View Features
                </a>
            </div>

            <div
                class="mt-16 border border-zinc-200 dark:border-zinc-800 rounded-2xl bg-zinc-900 p-2 shadow-2xl max-w-5xl mx-auto">
                <div
                    class="rounded-xl overflow-hidden bg-zinc-950 border border-zinc-800 aspect-video flex flex-col justify-between p-4 sm:p-6 text-left text-zinc-400 font-mono text-xs sm:text-sm">
                    <div class="flex items-center justify-between border-b border-zinc-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500/80 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-500/80 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-green-500/80 inline-block"></span>
                            <span class="ml-2 text-zinc-500 text-[11px] sm:text-xs">Console //
                                inventory-worker-v1.1.0</span>
                        </div>
                        <span class="text-amber-500/90 text-[11px] sm:text-xs font-semibold">● STOCK LIVE</span>
                    </div>
                    <div class="flex-1 py-4 space-y-2 overflow-hidden select-none">
                        <p class="text-zinc-500">[2026-05-29] Booting Inventory core system version 1.1.0...</p>
                        <p class="text-green-400">&gt; Database connection optimized. Scanning stock logs...</p>
                        <p class="text-zinc-300">&gt; Master inventory items indexed successfully [OK]</p>
                        <p class="text-amber-400">&gt; Alert system: Zero stock conflict anomalies found.</p>
                        <p class="text-zinc-600 animate-pulse">&gt; Waiting for incoming transaction logs... _</p>
                    </div>
                    <div
                        class="border-t border-zinc-800 pt-3 flex justify-between items-center text-zinc-500 text-[11px]">
                        <span>App Environment: Production</span>
                        <span>Laravel 13 + Filament v5</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <section id="features"
        class="py-20 border-t border-zinc-200 dark:border-zinc-800 bg-zinc-100/50 dark:bg-zinc-900/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-bold tracking-tight sm:text-4xl">Fitur Utama Inventory</h2>
                <p class="mt-4 text-zinc-600 dark:text-zinc-400">Arsitektur modern yang dirancang khusus untuk
                    mempermudah kontrol stok barang Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm hover:border-amber-500/40 dark:hover:border-amber-500/30 transition-all duration-300">
                    <div
                        class="h-10 w-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 mb-5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Manajemen Stok Akurat</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Catat barang masuk dan keluar,
                        atur batas minimum stok, dan dapatkan notifikasi otomatis saat barang mulai habis.</p>
                </div>

                <div
                    class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm hover:border-amber-500/40 dark:hover:border-amber-500/30 transition-all duration-300">
                    <div
                        class="h-10 w-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 mb-5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Valuasi Aset Otomatis</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Hitung nilai total aset
                        inventaris Anda secara real-time berdasarkan harga modal modal dan harga jual pasar.</p>
                </div>

                <div
                    class="p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm hover:border-amber-500/40 dark:hover:border-amber-500/30 transition-all duration-300">
                    <div
                        class="h-10 w-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 mb-5">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Performa Kilat</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Dibangun di atas Laravel 12 dan
                        dibungkus UI reaktif untuk memastikan pencarian data produk berjalan tanpa lemot.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-zinc-200 dark:border-zinc-800 py-8 bg-white dark:bg-zinc-950">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs sm:text-sm text-zinc-500">
            <p>&copy; 2026 Inventory. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition-colors">Terms of Service</a>
            </div>
        </div>
    </footer>

</body>

</html>