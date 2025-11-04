<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = TransaksiForm::query()->with(['pemohon', 'perusahaan']);

        $user = Auth::user();
        $userRole = $user->role->role_name ?? null;

        // Daftar status untuk filter dropdown
        $statusList = [
            'Draft',
            'Diajukan',
            'Disetujui Direksi',
            'Disetujui PYB1',
            'Disetujui PYB2',
            'Disetujui BO',
            'Ditolak'
        ];

        // Filter riwayat berdasarkan role
        switch ($userRole) {
            case 'Pemohon':
                // Pemohon hanya melihat miliknya
                $query->where('pemohon_id', $user->id);
                break;
            case 'Admin':
                // Admin melihat semua (tanpa filter)
                break;

            case 'Direksi':
                $query->where(function($q) use ($user) {
                    $q->where('status', 'Diajukan') // Tugasnya
                      ->orWhereHas('history', function($hist) use ($user) { // Riwayatnya
                          $hist->where('user_id', $user->id);
                      });
                });
                break;
            case 'PYB1':
                 $query->where(function($q) use ($user) {
                    $q->where('status', 'Disetujui Direksi') // Tugasnya
                      ->orWhereHas('history', function($hist) use ($user) { // Riwayatnya
                          $hist->where('user_id', $user->id);
                      });
                });
                break;
            case 'PYB2':
                 $query->where(function($q) use ($user) {
                    $q->where('status', 'Disetujui PYB1') // Tugasnya
                      ->orWhereHas('history', function($hist) use ($user) { // Riwayatnya
                          $hist->where('user_id', $user->id);
                      });
                });
                break;
            case 'BO':
                 $query->where(function($q) use ($user) {
                    $q->where('status', 'Disetujui PYB2') // Tugasnya
                      ->orWhereHas('history', function($hist) use ($user) { // Riwayatnya
                          $hist->where('user_id', $user->id);
                      });
                });
                break;

            default:
                // Role tidak dikenal, jangan tampilkan apa-apa
                $query->whereRaw('1 = 0');
                break;
        }


        // == FILTER FORM ==
        // 1. Filter by Status
        if ($request->filled('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }

        // 2. Filter by Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_pengajuan', [$request->start_date, $request->end_date]);
        }

        // 3. Filter by Pemohon (Hanya untuk Admin/Approver)
        if ($userRole != 'Pemohon' && $request->filled('pemohon_id') && $request->pemohon_id != 'all') {
            $query->where('pemohon_id', $request->pemohon_id);
        }

        // Ambil data
        $transaksiForms = $query->latest()->paginate(20)->withQueryString();

        // Ambil data pemohon untuk dropdown filter (hanya jika perlu)
        $pemohonList = [];
        if ($userRole != 'Pemohon') {
            $pemohonList = User::whereHas('role', function($q) {
                $q->where('role_name', 'Pemohon');
            })->orderBy('name')->get();
        }

        // ==========================================================
        // PERBAIKAN: Kirim $statusList ke view
        // ==========================================================
        return view('monitoring.index', compact('transaksiForms', 'pemohonList', 'statusList'));
    }
}

