<aside class="w-64 bg-navy text-white flex-shrink-0 hidden md:flex flex-col min-h-[calc(100vh-4rem)]">
    <div class="p-4 space-y-1">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-primary text-white">
            <x-icon name="dashboard" />
            Ringkasan Eksekutif
        </a>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
            <x-icon name="visits" />
            Permohonan Kunjungan
        </a>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
            <x-icon name="lab" />
            Verifikasi Keamanan Lab
        </a>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
            <x-icon name="calendar" />
            Jadwal Fasilitas Riset
        </a>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
            <x-icon name="guests" />
            Buku Tamu & Identitas
        </a>
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition">
            <x-icon name="audit" />
            Log Audit & Keamanan
        </a>
    </div>
</aside>
