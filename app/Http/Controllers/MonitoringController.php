<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use App\Models\User; // <-- Untuk filter pemohon
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // <-- Untuk filter tanggal

class MonitoringController extends Controller
{
    /**
     * Menampilkan halaman monitoring dengan filter.
     */
    public function index(Request $request)
    {
        // === PERBAIKAN: Definisikan $user di sini ===
        $user = Auth::user();

        // Otorisasi: Hanya role selain Pemohon yang boleh akses
        $userRole = $user->role->role_name ?? null;
        if ($userRole == 'Pemohon') {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // Mulai query
        $query = TransaksiForm::query()->with(['pemohon', 'perusahaan']);

        // === Terapkan Filter ===

        // 1. Filter berdasarkan Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 2. Filter berdasarkan Rentang Tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_pengajuan', '>=', Carbon::parse($request->tanggal_dari));
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_pengajuan', '<=', Carbon::parse($request->tanggal_sampai));
        }

        // 3. Filter berdasarkan Pemohon
        if ($request->filled('pemohon_id')) {
            $query->where('pemohon_id', $request->pemohon_id);
        }

        // 4. (Opsional) Batasi view berdasarkan role jika bukan Admin
        if ($userRole != 'Admin') {
            // Approver (PYB1, PYB2, BO) hanya melihat transaksi yang pernah mereka tangani
            // atau yang sedang menjadi tugas mereka
            $query->where(function($q) use ($userRole, $user) { // <-- Sekarang $user sudah defined
                // Tugas mereka saat ini
                if ($userRole == 'PYB1') $q->where('status', 'Diajukan');
                if ($userRole == 'PYB2') $q->where('status', 'Disetujui PYB1');
                if ($userRole == 'BO') $q->where('status', 'Disetujui PYB2');

                // Atau yang pernah mereka proses (ada di histori)
                $q->orWhereHas('history', function($hist) use ($user) {
                    $hist->where('user_id', $user->id);
                });
            });
        }
        // Jika Admin, dia akan melihat semua (tidak ada filter role tambahan)


        // Ambil hasil query
        $transaksiForms = $query->latest('tanggal_pengajuan')->paginate(20)->withQueryString();

        // Ambil data untuk filter dropdown
        $pemohonList = User::whereHas('role', function($q) {
            $q->where('role_name', 'Pemohon');
        })->orderBy('name')->get();

        $statusList = [
            'Draft', 'Diajukan', 'Disetujui PYB1', 'Disetujui PYB2', 'Disetujui BO', 'Ditolak'
        ];

        // Kembalikan view dengan data
        return view('monitoring.index', [
            'transaksiForms' => $transaksiForms,
            'pemohonList' => $pemohonList,
            'statusList' => $statusList,
            'filters' => $request->only(['status', 'tanggal_dari', 'tanggal_sampai', 'pemohon_id']), // Untuk mengisi ulang form
        ]);
    }
}

