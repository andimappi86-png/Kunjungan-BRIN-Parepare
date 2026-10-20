<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $total = Visit::count();
        $waiting = Visit::where('status', Visit::STATUS_MENUNGGU)->count();
        $approved = Visit::where('status', Visit::STATUS_DISETUJUI)->count();
        $rejected = Visit::where('status', Visit::STATUS_DITOLAK)->count();

        $query = Visit::query();
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('facility')) {
            $query->where('facility_target', $request->input('facility'));
        }

        $visits = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $calendarData = Visit::where('status', Visit::STATUS_DISETUJUI)
            ->whereNotNull('scheduled_date')
            ->select('scheduled_date', Visit::raw('count(*) as total'))
            ->groupBy('scheduled_date')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->scheduled_date->format('Y-m-d') => $item->total,
            ]);

        return view('dashboard.index', compact('total','waiting','approved','rejected','visits','calendarData'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', [Visit::STATUS_MENUNGGU, Visit::STATUS_DISETUJUI, Visit::STATUS_DITOLAK]),
            'scheduled_date' => 'nullable|date',
        ]);

        $visit = Visit::findOrFail($id);
        $visit->status = $request->input('status');
        if ($request->has('scheduled_date')) {
            $visit->scheduled_date = $request->input('scheduled_date') ?: null;
        }
        $visit->save();
                // Automatic outbound sync after admin change
                $service = new \App\Services\GoogleSheetsSyncService();
                $service->syncOutboundToIntegrationTab();

                return back()->with('success', 'Status dan jadwal berhasil diperbarui.');
    }

    public function syncSheets()
    {
        try {
            $service = new \App\Services\GoogleSheetsSyncService();
            $result = $service->sync();
            $msg = "Sinkronisasi selesai: {$result['added']} baru, {$result['updated']} diperbarui.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} dilewati/duplikat)";
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            Log::error('Google Sheets Sync Failed: ' . $e->getMessage());
            return back()->with('error', 'Gagal sinkronisasi: ' . $e->getMessage());
        }
    }

    public function toggleWhatsApp(Request $request, $id)
    {
        $visit = Visit::findOrFail($id);
        if ($request->has('whatsapp_status')) {
            $request->validate(['whatsapp_status' => 'required|string']);
            $visit->whatsapp_status = $request->whatsapp_status;
        } else {
            $visit->whatsapp_status = $visit->whatsapp_status === Visit::WA_TERKIRIM ? Visit::WA_BELUM : Visit::WA_TERKIRIM;
        }
        $visit->save();

        // Automatic outbound sync after admin change
        $service = new \App\Services\GoogleSheetsSyncService();
        $service->syncOutboundToIntegrationTab();

        return back()->with('success', 'Konfirmasi WhatsApp berhasil diperbarui.');
    }
}
