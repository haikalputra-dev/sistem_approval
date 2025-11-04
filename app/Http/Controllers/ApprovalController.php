<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use App\Models\User;
use App\Notifications\NotifikasiPermohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ApprovalController extends Controller
{
    /**
     * Menangani aksi (Approve / Reject) dari approver.
     */
    public function process(Request $request, TransaksiForm $transaksiForm)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $userRole = $user->role->role_name;
        $action = $request->input('action');
        $remarks = $request->input('remarks', '');

        // Tentukan status saat ini, status baru, dan notifikasi
        $currentStatus = $transaksiForm->status;
        $nextStatus = '';
        $actionMessage = '';
        $notificationMessage = '';
        $usersToNotify = null;
        $pemohon = $transaksiForm->pemohon;

        // ==========================================================
        // PERBAIKAN LOGIKA PESAN (ACTION MESSAGE) ADA DI SINI
        // ==========================================================

        if ($action === 'approve') {
            $actionMessage = "Disetujui oleh $userRole"; // Pesan histori general
            $remarks = $remarks ?: "Disetujui oleh $userRole.";

            switch ($currentStatus) {
                case 'Diajukan': // Tugasnya Direksi
                    if ($userRole !== 'Direksi') return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
                    $nextStatus = 'Disetujui Direksi';
                    $usersToNotify = User::whereHas('role', function($q) { $q->whereIn('role_name', ['PYB1', 'Admin']); })->get();
                    $notificationMessage = "Permohonan (ID: {$transaksiForm->id}) telah disetujui oleh Direksi dan menunggu persetujuan PYB1.";
                    break;

                case 'Disetujui Direksi': // Tugasnya PYB1
                    if ($userRole !== 'PYB1') return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
                    $nextStatus = 'Disetujui PYB1';
                    $usersToNotify = User::whereHas('role', function($q) { $q->whereIn('role_name', ['PYB2', 'Admin']); })->get();
                    $notificationMessage = "Permohonan (ID: {$transaksiForm->id}) telah disetujui oleh PYB1 dan menunggu persetujuan PYB2.";
                    break;

                case 'Disetujui PYB1': // Tugasnya PYB2
                    if ($userRole !== 'PYB2') return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
                    $nextStatus = 'Disetujui PYB2';
                    $usersToNotify = User::whereHas('role', function($q) { $q->whereIn('role_name', ['BO', 'Admin']); })->get();
                    $notificationMessage = "Permohonan (ID: {$transaksiForm->id}) telah disetujui oleh PYB2 dan menunggu persetujuan BO.";
                    break;

                case 'Disetujui PYB2': // Tugasnya BO
                    if ($userRole !== 'BO') return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
                    $nextStatus = 'Disetujui BO';
                    $usersToNotify = User::where('id', $pemohon->id)->get(); // Notif ke Pemohon
                    $notificationMessage = "Permohonan (ID: {$transaksiForm->id}) Anda telah disetujui sepenuhnya oleh BO.";
                    break;

                default:
                    return back()->with('error', 'Status transaksi tidak valid untuk persetujuan.');
            }

        } else { // action === 'reject'

            // ==========================================================
            // INI ADALAH PERBAIKANNYA:
            // Pesan aksi (actionMessage) sekarang langsung mengambil
            // $userRole, bukan berdasarkan $currentStatus.
            // ==========================================================
            $actionMessage = "Ditolak oleh $userRole";
            $remarks = $remarks ?: "Ditolak oleh $userRole.";
            $nextStatus = 'Ditolak';

            // Siapapun yang me-reject, notifikasi dikirim ke Pemohon
            $usersToNotify = User::where('id', $pemohon->id)->get();
            $notificationMessage = "Permohonan (ID: {$transaksiForm->id}) Anda ditolak oleh $userRole dengan alasan: " . $remarks;
        }


        // Simpan perubahan
        try {
            // 1. Update status form
            $transaksiForm->status = $nextStatus;
            $transaksiForm->save();

            // 2. Catat histori
            $transaksiForm->history()->create([
                'user_id' => $user->id,
                'action' => $actionMessage, // <-- Pesan yang sudah diperbaiki
                'remarks' => $remarks,
                'from_status' => $currentStatus,
                'to_status' => $nextStatus,
            ]);

            // 3. Kirim notifikasi
            if ($usersToNotify && $usersToNotify->isNotEmpty()) {
                $url = route('permohonan.detail', $transaksiForm);
                Notification::send($usersToNotify, new NotifikasiPermohonan($notificationMessage, $url));
            }

            return redirect()->route('list-permohonan')->with('success', 'Aksi berhasil dicatat.');

        } catch (\Exception $e) {
            Log::error("Gagal proses approval: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }


    /**
     * Menangani aksi "Submit" dari Pemohon (dari Draft).
     */
    public function submit(Request $request, TransaksiForm $transaksiForm)
    {
        $user = Auth::user();

        // 1. Validasi: Hanya Pemohon dan status Draft
        if ($user->id !== $transaksiForm->pemohon_id || $transaksiForm->status !== 'Draft') {
             return back()->with('error', 'Anda tidak memiliki wewenang untuk aksi ini.');
        }

        // 2. Validasi: Cek ulang semua field wajib
        $validator = \Validator::make($transaksiForm->toArray(), [
            'kategori_uraian' => 'required',
            'kategori_pengakuan' => 'required',
            'total_nominal' => 'required|numeric|min:1',
            'tipe_dasar_transaksi' => 'required',
            'lawan_transaksi' => 'required',
            'rekening_transaksi' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Data form belum lengkap. Silakan edit (Update) form sebelum mengajukan.');
        }

        // 3. Update status
        try {
            $transaksiForm->status = 'Diajukan';
            $transaksiForm->save();

            // 4. Catat histori
            $transaksiForm->history()->create([
                'user_id' => $user->id,
                'action' => 'Mengajukan',
                'remarks' => 'Permohonan diajukan dari Draft.',
                'from_status' => 'Draft',
                'to_status' => 'Diajukan',
            ]);

            // 5. Kirim Notifikasi ke Direksi (Alur Baru)
            try {
                $usersToNotify = User::whereHas('role', function($q) { $q->where('role_name', 'Direksi'); })->get();
                if ($usersToNotify->isNotEmpty()) {
                    $message = "Permohonan baru (ID: {$transaksiForm->id}) telah diajukan oleh {$user->name} dan menunggu persetujuan Anda.";
                    $url = route('permohonan.detail', $transaksiForm);
                    Notification::send($usersToNotify, new NotifikasiPermohonan($message, $url));
                }
            } catch (\Exception $e) {
                Log::error('Gagal kirim notifikasi submit: ' . $e->getMessage());
            }

            return redirect()->route('list-permohonan')->with('success', 'Permohonan berhasil diajukan.');

        } catch (\Exception $e) {
            Log::error("Gagal submit draft: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }
}

