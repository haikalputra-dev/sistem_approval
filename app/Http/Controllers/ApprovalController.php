<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use App\Models\TransaksiHistory; // Pastikan ini di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class ApprovalController extends Controller
{
    /**
     * Mengajukan (submit) transaksi yang statusnya 'Draft'.
     * Ini hanya bisa dilakukan oleh Pemohon.
     */
    public function submit(TransaksiForm $transaksiForm)
    {
        $user = Auth::user();

        // 1. Otorisasi: Hanya pemohon yang bisa submit
        if ($transaksiForm->pemohon_id != $user->id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengajukan transaksi ini.');
        }

        // 2. Validasi Status: Hanya 'Draft' yang bisa di-submit
        if ($transaksiForm->status != 'Draft') {
            return back()->with('error', 'Hanya transaksi berstatus Draft yang bisa diajukan.');
        }

        // 3. Validasi Balance (PENTING)
        $totalRincian = $transaksiForm->details()->sum('nominal');
        if ((float) $transaksiForm->total_nominal != (float) $totalRincian || $totalRincian == 0) {
            return back()->with('error', 'Gagal mengajukan. Total rincian (Rp ' . number_format($totalRincian, 0, ',', '.') .
                                     ') tidak sama dengan Total Form (Rp ' . number_format($transaksiForm->total_nominal, 0, ',', '.') . ') atau rincian masih kosong.');
        }

        // 4. Proses Database
        DB::beginTransaction();
        try {
            $oldStatus = $transaksiForm->status;
            $newStatus = 'Diajukan';

            // Update status form
            $transaksiForm->status = $newStatus;
            $transaksiForm->save();

            // Catat ke history
            $transaksiForm->history()->create([
                'user_id' => $user->id,
                'action' => 'Pengajuan Diajukan',
                'remarks' => 'Pengajuan telah disubmit oleh pemohon.',
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
            ]);

            DB::commit();
            return redirect()->route('list-permohonan')->with('success', 'Transaksi berhasil diajukan dan sedang menunggu persetujuan PYB1.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menyetujui (approve) transaksi.
     * Ini bisa dilakukan oleh PYB1, PYB2, atau BO.
     */
    public function approve(TransaksiForm $transaksiForm)
    {
        $user = Auth::user();
        $userRole = $user->role->role_name ?? null;

        // Tentukan status lama dan baru berdasarkan role
        $approvalMap = [
            'PYB1' => ['oldStatus' => 'Diajukan', 'newStatus' => 'Disetujui PYB1', 'nextApprover' => 'PYB2'],
            'PYB2' => ['oldStatus' => 'Disetujui PYB1', 'newStatus' => 'Disetujui PYB2', 'nextApprover' => 'BO'],
            'BO' => ['oldStatus' => 'Disetujui PYB2', 'newStatus' => 'Disetujui BO', 'nextApprover' => 'Selesai'],
        ];

        // 1. Otorisasi: Cek apakah user punya role yang ada di map
        if (!array_key_exists($userRole, $approvalMap)) {
            return back()->with('error', 'Anda tidak memiliki izin untuk melakukan persetujuan.');
        }

        $map = $approvalMap[$userRole];
        $oldStatus = $map['oldStatus'];
        $newStatus = $map['newStatus'];

        // 2. Validasi Status: Cek apakah status transaksi sesuai
        if ($transaksiForm->status != $oldStatus) {
            return back()->with('error', "Gagal. Transaksi ini tidak dalam status '$oldStatus'. Status saat ini: {$transaksiForm->status}");
        }

        // 3. Proses Database
        DB::beginTransaction();
        try {
            // Update status form
            $transaksiForm->status = $newStatus;
            $transaksiForm->save();

            // Catat ke history
            $remarks = "Disetujui oleh $userRole.";
            $transaksiForm->history()->create([
                'user_id' => $user->id,
                'action' => 'Disetujui',
                'remarks' => $remarks,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
            ]);

            DB::commit();

            $message = "Transaksi berhasil disetujui. Status: $newStatus.";
            if ($map['nextApprover'] != 'Selesai') {
                $message .= " Menunggu persetujuan {$map['nextApprover']}.";
            } else {
                $message .= " Transaksi selesai disetujui.";
            }

            return redirect()->route('list-permohonan')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menolak (reject) transaksi.
     * Ini bisa dilakukan oleh PYB1, PYB2, atau BO.
     */
    public function reject(Request $request, TransaksiForm $transaksiForm)
    {
        $user = Auth::user();
        $userRole = $user->role->role_name ?? null;

        // Validasi input 'remarks' dari SweetAlert
        $validated = $request->validate([
            'remarks' => 'required|string|min:5|max:500',
        ]);

        $validRoles = ['PYB1', 'PYB2', 'BO'];
        $currentStatus = $transaksiForm->status;
        $allowedStatuses = ['Diajukan', 'Disetujui PYB1', 'Disetujui PYB2'];

        // 1. Otorisasi: Cek role
        if (!in_array($userRole, $validRoles)) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menolak transaksi.');
        }

        // 2. Validasi Status: Cek apakah transaksi sedang dalam proses approval
        if (!in_array($currentStatus, $allowedStatuses)) {
            return back()->with('error', "Gagal. Transaksi dengan status '$currentStatus' tidak dapat ditolak.");
        }

        // (Opsional) Otorisasi lebih ketat: PYB1 hanya bisa reject 'Diajukan', dst.
        // if (($userRole == 'PYB1' && $currentStatus != 'Diajukan') ||
        //     ($userRole == 'PYB2' && $currentStatus != 'Disetujui PYB1') ||
        //     ($userRole == 'BO' && $currentStatus != 'Disetujui PYB2')) {
        //     return back()->with('error', "Anda tidak dapat menolak transaksi pada tahap ini.");
        // }


        // 3. Proses Database
        DB::beginTransaction();
        try {
            $oldStatus = $transaksiForm->status;
            $newStatus = 'Ditolak';

            // Update status form
            $transaksiForm->status = $newStatus;
            $transaksiForm->save();

            // Catat ke history
            $remarks = "Ditolak oleh $userRole. Alasan: " . $validated['remarks'];
            $transaksiForm->history()->create([
                'user_id' => $user->id,
                'action' => 'Ditolak',
                'remarks' => $remarks,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
            ]);

            DB::commit();
            return redirect()->route('list-permohonan')->with('success', 'Transaksi telah ditolak.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak transaksi: ' . $e->getMessage());
        }
    }
}

