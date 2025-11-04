<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->role->role_name ?? 'Undefined';
        $stats = [];

        // Inisialisasi data default
        $stats['card1_title'] = 'Total Permohonan Saya';
        $stats['card1_value'] = 0;
        $stats['card1_icon'] = 'ni-paper-diploma';
        $stats['card1_color'] = 'bg-gradient-primary';
        $stats['card1_link'] = route('permohonan.list');

        $stats['card2_title'] = 'Total Menunggu Persetujuan';
        $stats['card2_value'] = 0;
        $stats['card2_icon'] = 'ni-time-alarm';
        $stats['card2_color'] = 'bg-gradient-warning';
        $stats['card2_link'] = route('permohonan.list');

        $stats['card3_title'] = 'Total Permohonan Disetujui';
        $stats['card3_value'] = 0;
        $stats['card3_icon'] = 'ni-check-bold';
        $stats['card3_color'] = 'bg-gradient-success';
        $stats['card3_link'] = route('monitoring.index', ['status' => 'Disetujui BO']);

        $stats['card4_title'] = 'Total Permohonan Ditolak';
        $stats['card4_value'] = 0;
        $stats['card4_icon'] = 'ni-fat-remove';
        $stats['card4_color'] = 'bg-gradient-danger';
        $stats['card4_link'] = route('monitoring.index', ['status' => 'Ditolak']);

        // Logika query berdasarkan role
        switch ($role) {
            case 'Pemohon':
                $stats['card1_value'] = TransaksiForm::where('pemohon_id', $user->id)->count();
                $stats['card2_value'] = TransaksiForm::where('pemohon_id', $user->id)
                                                    ->whereIn('status', ['Diajukan', 'Disetujui Direksi', 'Disetujui PYB1', 'Disetujui PYB2'])
                                                    ->count();
                $stats['card3_value'] = TransaksiForm::where('pemohon_id', $user->id)->where('status', 'Disetujui BO')->count();
                $stats['card4_value'] = TransaksiForm::where('pemohon_id', $user->id)->where('status', 'Ditolak')->count();
                break;

            case 'Direksi':
            case 'PYB1':
            case 'PYB2':
            case 'BO':
                // Untuk Approver, Card 1 adalah "Total Tugas Menunggu Saya"
                $stats['card1_title'] = 'Tugas Menunggu Saya';
                $stats['card1_color'] = 'bg-gradient-danger';
                $stats['card1_icon'] = 'ni-bell-55';
                $stats['card1_value'] = $this->getTugasCount($role);

                // Card 2 adalah "Total Diproses Bulan Ini"
                $stats['card2_title'] = 'Diproses Bulan Ini';
                $stats['card2_color'] = 'bg-gradient-info';
                $stats['card2_icon'] = 'ni-calendar-grid-58';
                $stats['card2_value'] = TransaksiForm::whereHas('history', function($q) use ($user) {
                                                        $q->where('user_id', $user->id);
                                                    })
                                                    ->whereMonth('updated_at', now()->month)
                                                    ->count();
                // Card 3 & 4 tetap sama (Total Disetujui & Ditolak di sistem)
                $stats['card3_value'] = TransaksiForm::where('status', 'Disetujui BO')->count();
                $stats['card4_value'] = TransaksiForm::where('status', 'Ditolak')->count();
                break;

            case 'Admin':
                // Admin melihat data keseluruhan
                $stats['card1_title'] = 'Total Semua Permohonan';
                $stats['card1_value'] = TransaksiForm::count();

                $stats['card2_title'] = 'Total Menunggu Approval';
                $stats['card2_value'] = TransaksiForm::whereIn('status', ['Diajukan', 'Disetujui Direksi', 'Disetujui PYB1', 'Disetujui PYB2'])->count();

                $stats['card3_value'] = TransaksiForm::where('status', 'Disetujui BO')->count();
                $stats['card4_value'] = TransaksiForm::where('status', 'Ditolak')->count();
                break;
        }

        return view('pages.dashboard', compact('stats'));
    }

    /**
     * Helper function untuk menghitung tugas approver
     */
    private function getTugasCount($role)
    {
        switch ($role) {
            case 'Direksi':
                return TransaksiForm::where('status', 'Diajukan')->count();
            case 'PYB1':
                return TransaksiForm::where('status', 'Disetujui Direksi')->count();
            case 'PYB2':
                return TransaksiForm::where('status', 'Disetujui PYB1')->count();
            case 'BO':
                return TransaksiForm::where('status', 'Disetujui PYB2')->count();
            default:
                return 0;
        }
    }
}

