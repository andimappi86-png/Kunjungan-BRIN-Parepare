<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kunjungan BRIN</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background font-sans text-slate-800 antialiased flex flex-col">

    {{-- 1. Full-width Header --}}
    <x-header />

    {{-- Main Container --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-between shadow-sm">
                <span>{{ session('success') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 font-bold">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-medium flex items-center justify-between shadow-sm">
                <span>{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
            </div>
        @endif

        {{-- 2. Calendar & Agenda Section (2 Columns in 1 White Card) --}}
        <div class="bg-surface rounded-xl border border-border shadow-sm p-6 grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Calendar Widget (Col 5) --}}
            <div class="lg:col-span-5 lg:border-r lg:border-border lg:pr-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-900 text-base">Kalender Jadwal Kunjungan</h3>
                    @php
                        $viewYear  = (int) request('cy', now()->year);
                        $viewMonth = (int) request('cm', now()->month);
                        $viewDate  = \Carbon\Carbon::createFromDate($viewYear, $viewMonth, 1);
                        $daysInMonth  = $viewDate->daysInMonth;
                        $startDow     = ($viewDate->dayOfWeekIso - 1);
                        $today        = now()->toDateString();
                        $selectedDay  = request('cd', (int) now()->format('d'));
                        $selectedDate = \Carbon\Carbon::createFromDate($viewYear, $viewMonth, min($selectedDay, $daysInMonth))->toDateString();
                    @endphp
                </div>

                {{-- Month navigation --}}
                <div class="flex items-center justify-between mb-3 bg-slate-50 p-2 rounded-lg border border-border">
                    <a href="{{ route('dashboard', array_merge(request()->except(['cy','cm','cd']), ['cy'=>$viewDate->copy()->subMonthNoOverflow()->year,'cm'=>$viewDate->copy()->subMonthNoOverflow()->month])) }}"
                       class="w-7 h-7 rounded-md bg-white border border-border flex items-center justify-center text-slate-600 hover:bg-slate-100 text-sm font-bold">&lsaquo;</a>
                    <span class="text-xs font-bold text-slate-700">{{ $viewDate->translatedFormat('F Y') }}</span>
                    <a href="{{ route('dashboard', array_merge(request()->except(['cy','cm','cd']), ['cy'=>$viewDate->copy()->addMonthNoOverflow()->year,'cm'=>$viewDate->copy()->addMonthNoOverflow()->month])) }}"
                       class="w-7 h-7 rounded-md bg-white border border-border flex items-center justify-center text-slate-600 hover:bg-slate-100 text-sm font-bold">&rsaquo;</a>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-xs font-bold text-slate-400 mb-2">
                    <span>Sn</span><span>Sl</span><span>Rb</span><span>Km</span><span>Jm</span><span>Sb</span><span>Mg</span>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                    @for($i = 0; $i < $startDow; $i++)
                        <div></div>
                    @endfor
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dateStr = $viewDate->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $hasVisit = isset($calendarData[$dateStr]);
                            $isToday = $dateStr === $today;
                            $isSel = $dateStr === $selectedDate;
                        @endphp
                        <a href="{{ route('dashboard', array_merge(request()->except('cd'), ['cd'=>$day,'cy'=>$viewYear,'cm'=>$viewMonth])) }}"
                           class="py-2 rounded-lg font-semibold block transition
                           {{ $isSel ? 'bg-primary text-white shadow-sm font-bold' : ($isToday ? 'bg-blue-100 text-primary border border-blue-300 font-bold' : ($hasVisit ? 'bg-blue-50 text-primary border border-blue-200' : 'text-slate-700 hover:bg-slate-100')) }}">
                            {{ $day }}
                            @if($hasVisit && !$isSel)
                                <div class="w-1 h-1 rounded-full bg-primary mx-auto mt-0.5"></div>
                            @endif
                        </a>
                    @endfor
                </div>
            </div>

            {{-- Agenda Panel (Col 7) --}}
            <div class="lg:col-span-7 flex flex-col justify-between h-full">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-slate-900 text-base">
                            Agenda — {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                        </h3>
                        <span class="text-xs px-2.5 py-1 bg-blue-50 text-primary rounded-full font-medium">WIB</span>
                    </div>
                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        @php
                            $agendaItems = \App\Models\Visit::where('status', \App\Models\Visit::STATUS_DISETUJUI)
                                ->whereDate('scheduled_date', $selectedDate)
                                ->orderBy('scheduled_date')
                                ->get();
                        @endphp
                        @forelse($agendaItems as $ag)
                            <div class="p-3.5 bg-slate-50/80 rounded-xl border border-border flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-sm text-slate-900">{{ $ag->applicant_name }} <span class="text-xs text-slate-500 font-normal">({{ $ag->institution }})</span></p>
                                    <p class="text-xs text-slate-600 mt-0.5">Fasilitas: <span class="font-medium text-primary">{{ $ag->facility_target ?? 'Fasilitas Riset Utama' }}</span> &bull; Peserta: {{ $ag->participants_count }} orang</p>
                                </div>
                                <x-badge :status="$ag->status" />
                            </div>
                        @empty
                            <div class="text-center py-12 text-slate-400 text-sm bg-slate-50/50 rounded-xl border border-dashed border-border">
                                <p class="font-medium text-slate-600">Tidak ada jadwal kunjungan pada tanggal ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Pilih tanggal lain pada kalender.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs text-slate-500">
                    <span>Total Agenda: <strong class="text-slate-800">{{ $agendaItems->count() }} kunjungan</strong></span>
                    <span class="font-medium text-emerald-600 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Sinkronisasi Aktif</span>
                </div>
            </div>
        </div>

        {{-- 3. Stats Cards Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stat-card title="Total Pengajuan" value="{{ $total }}" color="blue">
                <x-icon name="dashboard" />
            </x-stat-card>
            <x-stat-card title="Menunggu" value="{{ $waiting }}" color="amber">
                <x-icon name="clock" />
            </x-stat-card>
            <x-stat-card title="Disetujui" value="{{ $approved }}" color="emerald">
                <x-icon name="check" />
            </x-stat-card>
            <x-stat-card title="Ditolak" value="{{ $rejected }}" color="rose">
                <x-icon name="x" />
            </x-stat-card>
        </div>

        {{-- 4. Daftar Pengajuan Card & Toolbar & Table --}}
        <div class="bg-surface rounded-xl border border-border shadow-sm overflow-hidden">
            {{-- Card Header --}}
            <div class="p-5 border-b border-border flex flex-wrap items-center justify-between gap-4 bg-white">
                <div>
                    <h2 class="font-bold text-slate-900 text-base">Daftar Pengajuan Kunjungan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Kelola seluruh permohonan kunjungan dan fasilitas riset BRIN.</p>
                </div>
                <form method="POST" action="{{ route('sheets.sync') }}" class="inline" onsubmit="this.querySelector('button').disabled=true; this.querySelector('.sync-text').innerText='Sedang menyinkronkan...';">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold bg-primary text-white border border-primary hover:bg-primary/90">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 15l-5-5h3V9h4v4h3l-5 5z"/></svg>
                        <span class="sync-text">Sinkronisasi Sheets</span>
                    </button>
                </form>

            </div>

            {{-- 5. Search & Filter Toolbar Card --}}
            <form method="GET" action="{{ route('dashboard') }}" class="px-5 py-4 border-b border-border bg-slate-50/50 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @if(request('cy'))<input type="hidden" name="cy" value="{{ request('cy') }}">@endif
                @if(request('cm'))<input type="hidden" name="cm" value="{{ request('cm') }}">@endif
                @if(request('cd'))<input type="hidden" name="cd" value="{{ request('cd') }}">@endif

                <div class="md:col-span-4">
                    <x-input name="search" label="Pencarian" placeholder="Nama / Instansi..." value="{{ request('search') }}" />
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <x-select name="status">
                        <option value="">Semua</option>
                        <option value="MENUNGGU" {{ request('status') == 'MENUNGGU' ? 'selected' : '' }}>Menunggu</option>
                        <option value="DISETUJUI" {{ request('status') == 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                        <option value="DITOLAK" {{ request('status') == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                    </x-select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Fasilitas</label>
                    <select name="facility" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-slate-800 focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">Semua</option>
                        @foreach(\App\Models\Visit::select('facility_target')->whereNotNull('facility_target')->distinct()->pluck('facility_target') as $fac)
                            <option value="{{ $fac }}" {{ request('facility') == $fac ? 'selected' : '' }}>{{ $fac }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex items-end gap-2">
                    <x-button type="submit" variant="primary" class="w-full sm:w-auto">Filter Data</x-button>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-medium bg-slate-200 text-slate-700 hover:bg-slate-300 transition">Reset</a>
                    <div class="ml-auto text-xs text-slate-400 font-medium self-center">
                        Total: {{ $visits->total() }}
                    </div>
                </div>
            </form>

            {{-- 6. Data Table Card --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-blue-50/50 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                            <th class="p-4"><input type="checkbox" id="check-all" class="rounded border-border text-primary focus:ring-primary"></th>
                            <th class="p-4">Pemohon</th>
                            <th class="p-4">Instansi</th>
                            <th class="p-4">Kontak</th>
                            <th class="p-4 text-center">Peserta</th>
                            <th class="p-4">Tgl Kunjungan</th>
                            <th class="p-4 text-center">Status</th>
                            <th class="p-4">Pengaturan Jadwal</th>
                            <th class="p-4 text-center">WhatsApp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-sm text-slate-700">
                        @forelse($visits as $i => $visit)
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="p-4"><input type="checkbox" class="rounded border-border text-primary focus:ring-primary"></td>
                                <td class="p-4">
                                    <div class="font-semibold text-slate-900">{{ $visit->applicant_name }}</div>
                                    <div class="text-[11px] font-mono text-slate-500">{{ $visit->registration_number }}</div>
                                </td>
                                <td class="p-4 text-slate-800">{{ $visit->institution }}</td>
                                <td class="p-4 text-xs">
                                    <div class="text-slate-800">{{ $visit->phone }}</div>
                                    <div class="text-slate-500">{{ $visit->email }}</div>
                                </td>
                                <td class="p-4 text-center font-medium">{{ $visit->participants_count }}</td>
                                <td class="p-4 text-slate-800 whitespace-nowrap">{{ optional($visit->visit_date)->format('d M Y') ?? '-' }}</td>
                                <td class="p-4 text-center">
                                    <x-badge :status="$visit->status" />
                                </td>
                                <td class="p-4">
                                    <form action="{{ route('visits.update', $visit->id) }}" method="POST" class="flex flex-col gap-1.5 w-48">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="w-full rounded border border-border text-xs px-2 py-1 bg-white">
                                            <option value="MENUNGGU" {{ $visit->status == 'MENUNGGU' ? 'selected' : '' }}>Menunggu</option>
                                            <option value="DISETUJUI" {{ $visit->status == 'DISETUJUI' ? 'selected' : '' }}>Disetujui</option>
                                            <option value="DITOLAK" {{ $visit->status == 'DITOLAK' ? 'selected' : '' }}>Ditolak</option>
                                        </select>
                                        <div class="flex items-center gap-1">
                                            <input type="date" name="scheduled_date" value="{{ optional($visit->scheduled_date)->format('Y-m-d') }}" class="w-full rounded border border-border text-xs px-2 py-1 bg-white">
                                            <button type="submit" class="px-2 py-1 bg-primary text-white rounded text-[10px] hover:bg-blue-700 uppercase font-bold">Simpan</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="p-4 text-center">
                                    @php
                                        // Normalisasi nomor telepon ke format internasional (E.164 tanpa +)
                                        $cleanPhone = preg_replace('/[^0-9]/', '', (string)$visit->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }

                                        // Template pesan formal BRIN
                                        if ($visit->status === \App\Models\Visit::STATUS_DISETUJUI) {
                                            $tglJadwal = optional($visit->scheduled_date)->translatedFormat('d F Y') ?? optional($visit->visit_date)->translatedFormat('d F Y') ?? '-';
                                            $waMessage = "Halo Bapak/Ibu {$visit->applicant_name} ({$visit->institution}), permohonan kunjungan riset ke BRIN Parepare dengan nomor registrasi {$visit->registration_number} telah *DISETUJUI* untuk tanggal *{$tglJadwal}*. Silakan hadir sesuai jadwal dan membawa kelengkapan administrasi. Terima kasih.";
                                        } elseif ($visit->status === \App\Models\Visit::STATUS_DITOLAK) {
                                            $waMessage = "Halo Bapak/Ibu {$visit->applicant_name} ({$visit->institution}), mohon maaf permohonan kunjungan riset ke BRIN Parepare dengan nomor registrasi {$visit->registration_number} saat ini *BELUM DAPAT DISETUJUI*. Terima kasih atas perhatiannya.";
                                        } else {
                                            $waMessage = "Halo Bapak/Ibu {$visit->applicant_name} ({$visit->institution}), permohonan kunjungan riset ke BRIN Parepare dengan nomor registrasi {$visit->registration_number} saat ini sedang dalam proses review. Terima kasih.";
                                        }
                                        $waUrl = !empty($cleanPhone) ? "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waMessage) : null;
                                    @endphp

                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($waUrl)
                                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-2 py-1 rounded text-[10px] font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition" title="Buka WhatsApp">
                                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/></svg>
                                                Kirim WA
                                            </a>
                                        @else
                                            <span class="text-[10px] text-slate-400 italic">No HP invalid</span>
                                        @endif

                                        <form action="{{ route('visits.whatsapp', $visit->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="Tandai status konfirmasi" class="inline-flex items-center px-1.5 py-1 rounded text-[10px] font-medium border border-border {{ $visit->whatsapp_status == 'TERKIRIM' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                                                {{ $visit->whatsapp_status == 'TERKIRIM' ? '✓ Terkirim' : 'Tandai' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">Tidak ada pengajuan ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-border bg-slate-50/50">
                <x-pagination :paginator="$visits" />
            </div>
        </div>

        {{-- 7. Footer --}}
        <footer class="pt-6 pb-4 text-center text-xs text-slate-400 border-t border-border/60 mt-10">
            &copy; {{ date('Y') }} Badan Riset dan Inovasi Nasional (BRIN) &bull; Sistem Informasi Kunjungan Riset
        </footer>
    </main>

    <script>
    document.getElementById('check-all')?.addEventListener('change', function() {
        document.querySelectorAll('tbody input[type="checkbox"]').forEach(c => c.checked = this.checked);
    });
    </script>
</body>
</html>
