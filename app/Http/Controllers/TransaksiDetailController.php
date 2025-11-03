<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransaksiForm;
use App\Models\TransaksiDetail;
use Illuminate\Support\Facades\Validator;

class TransaksiDetailController extends Controller
{
    /**
     * Menyimpan detail item baru
     */
    public function store(Request $request, TransaksiForm $transaksiForm)
    {
        // 1. Validasi (Hanya bisa tambah jika status 'Draft')
        if ($transaksiForm->status != 'Draft') {
            $message = 'Tidak dapat menambah detail. Transaksi sudah diajukan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return back()->with('error', $message);
        }

        // 2. Validasi input
        $validator = Validator::make($request->all(), [
            'pengakuan_transaksi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan_detail' => 'nullable|string|max:500',
        ]);

        // 3. Jika validasi gagal
        if ($validator->fails()) {
            if ($request->wantsJson()) {
                // Kirim error sebagai JSON
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }
            // Fallback non-js
            return back()->withErrors($validator)->withInput();
        }

        // 4. Buat detail baru
        $transaksiDetail = $transaksiForm->details()->create($validator->validated());

        // 5. Jika request adalah AJAX (wantsJson)
        if ($request->wantsJson()) {
            // Siapkan data untuk dikirim kembali ke JS
            $newTotal = $transaksiForm->details()->sum('nominal');

            return response()->json([
                'success' => true,
                'message' => 'Detail item berhasil ditambahkan.',
                // Data item baru untuk dimasukkan ke tabel
                'detail' => [
                    'id' => $transaksiDetail->id,
                    'pengakuan_transaksi' => $transaksiDetail->pengakuan_transaksi,
                    'keterangan_detail' => $transaksiDetail->keterangan_detail ?? '-',
                    'nominal_formatted' => 'Rp ' . number_format($transaksiDetail->nominal, 0, ',', '.'),
                    // URL untuk tombol hapus (yang masih non-AJAX)
                    'destroy_url' => route('permohonan.detail.destroy', $transaksiDetail)
                ],
                // Data baru untuk update total
                'new_total_rincian_formatted' => 'Rp ' . number_format($newTotal, 0, ',', '.'),
                'new_total_rincian_raw' => $newTotal,
                'total_form_raw' => (float) $transaksiForm->total_nominal
            ]);
        }

        // 6. Fallback untuk non-JS
        return back()->with('success', 'Detail item berhasil ditambahkan.');
    }

    public function destroy(Request $request, TransaksiDetail $transaksiDetail)
    {
        $transaksiForm = $transaksiDetail->form; // Ambil parent form

        // 1. Validasi (Hanya bisa hapus jika status 'Draft')
        if ($transaksiForm->status != 'Draft') {
            $message = 'Tidak dapat menghapus detail. Transaksi sudah diajukan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return back()->with('error', $message);
        }

        // 2. Hapus data
        $transaksiDetail->delete();

        // 3. Hitung ulang total
        $newTotal = $transaksiForm->details()->sum('nominal');

        // 4. Kirim balasan JSON jika ini request AJAX
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Detail item berhasil dihapus.',
                'new_total_rincian_formatted' => 'Rp ' . number_format($newTotal, 0, ',', '.'),
                'new_total_rincian_raw' => $newTotal,
                'total_form_raw' => (float) $transaksiForm->total_nominal
            ]);
        }

        // 5. Fallback untuk non-JS (jika ada)
        return back()->with('success', 'Detail item berhasil dihapus.');
    }
}
