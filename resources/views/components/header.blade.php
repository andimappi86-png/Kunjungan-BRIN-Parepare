<header class="w-full text-white sticky top-0 z-30" style="background-color: #102B46; border-bottom: 1px solid rgba(255,255,255,0.08); box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[68px] flex items-center justify-between gap-4">



        {{-- Brand / Identity --}}
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center font-bold text-white text-sm tracking-tight border border-white/15">
                B
            </div>
            <div class="min-w-0">
                <h1 class="text-[16px] sm:text-[17px] font-semibold tracking-tight text-white leading-snug truncate">
                    Dashboard Kunjungan BRIN
                </h1>
                <p class="text-[11px] text-blue-200/80 hidden sm:block leading-tight mt-0.5">
                    Sistem Informasi Pengelolaan Kunjungan dan Fasilitas Riset
                </p>
            </div>
        </div>

        {{-- Right: User Info & Logout --}}
        <div class="flex items-center gap-4 flex-shrink-0">
            <div class="text-right hidden sm:block leading-tight">
                <span class="text-[13px] font-semibold text-white block">{{ Auth::user()->name ?? 'Administrator' }}</span>
                <span class="text-[11px] text-blue-200/70 block mt-0.5">Administrator Operasional</span>
            </div>
            
            <div class="w-px h-6 bg-white/15 hidden sm:block"></div>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium text-white bg-white/10 hover:bg-white/15 transition border border-white/20 focus:outline-none focus:ring-2 focus:ring-white/40">
                    <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>

    </div>
</header>