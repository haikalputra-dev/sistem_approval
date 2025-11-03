<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use App\Models\TransaksiAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    /**
     * Menyimpan file lampiran baru.
     */
    public function store(Request $request, TransaksiForm $transaksiForm)
    {
        // 1. Otorisasi sederhana: Hanya pemohon & approver yang bisa upload
        $this->authorizeUpload($transaksiForm);

        // 2. Validasi file
        $request->validate([
            'lampiran' => 'required|file|mimes:pdf,jpg,jpeg,png,xls,xlsx,doc,docx|max:10240', // Max 10MB
            'attachment_type' => 'nullable|string|max:100'
        ]);

        $file = $request->file('lampiran');
        $originalName = $file->getClientOriginalName();
        // Simpan di: storage/app/public/attachments/[FORM_ID]/[NAMA_FILE]
        $path = $file->storeAs("public/attachments/{$transaksiForm->id}", $originalName);

        // 3. Simpan record ke database
        $attachment = $transaksiForm->attachments()->create([
            'file_name' => $originalName,
            'file_path' => $path, // Simpan path dari Storage
            'attachment_type' => $request->attachment_type ?? 'Lainnya',
            'uploaded_by' => Auth::id(),
        ]);

        // 4. Beri balasan JSON untuk AJAX
        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'file_name' => $attachment->file_name,
                'attachment_type' => $attachment->attachment_type,
                'uploader' => Auth::user()->name, // Langsung kirim nama user
                'download_url' => route('lampiran.download', $attachment),
                'delete_url' => route('lampiran.destroy', $attachment),
            ]
        ]);
    }

    /**
     * Menghapus file lampiran.
     */
    public function destroy(TransaksiAttachment $transaksiAttachment)
    {
        // 1. Otorisasi: Hanya pemohon (jika status draft) atau pengupload
        $this->authorizeDelete($transaksiAttachment);

        // 2. Hapus file dari storage
        Storage::delete($transaksiAttachment->file_path);

        // 3. Hapus record dari database
        $transaksiAttachment->delete();

        return response()->json(['success' => true, 'message' => 'Lampiran berhasil dihapus.']);
    }

    /**
     * Mengunduh file lampiran.
     */
    public function download(TransaksiAttachment $transaksiAttachment)
    {
        // Otorisasi: Cek apakah user boleh lihat form ini (bisa diperketat)
        $this->authorizeUpload($transaksiAttachment->form);

        // Validasi file ada
        if (!Storage::exists($transaksiAttachment->file_path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        // 4. Download file
        return Storage::download($transaksiAttachment->file_path, $transaksiAttachment->file_name);
    }

    // --- Helper Otorisasi ---
    private function authorizeUpload(TransaksiForm $form)
    {
        $user = Auth::user();
        $userRole = $user->role->role_name ?? null;

        // Pemohon bisa upload kapan saja (kecuali selesai/ditolak)
        if ($form->pemohon_id == $user->id && !in_array($form->status, ['Disetujui BO', 'Ditolak'])) {
            return true;
        }
        // Approver (PYB, BO) bisa upload
        if (in_array($userRole, ['PYB1', 'PYB2', 'BO', 'Admin'])) {
            return true;
        }
        abort(403, 'Anda tidak memiliki izin untuk mengunggah lampiran ke form ini.');
    }

    private function authorizeDelete(TransaksiAttachment $attachment)
    {
        $user = Auth::user();

        // Admin bisa hapus
        if ($user->role->role_name == 'Admin') {
            return true;
        }
        // Pengupload bisa hapus
        if ($attachment->uploaded_by == $user->id) {
            // Pemohon hanya bisa hapus jika status 'Draft'
            if ($user->role->role_name == 'Pemohon' && $attachment->form->status != 'Draft') {
                 abort(403, 'Anda tidak bisa menghapus lampiran jika status bukan Draft.');
            }
            return true;
        }
        abort(403, 'Anda tidak memiliki izin untuk menghapus lampiran ini.');
    }
}
