<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use App\Models\TransaksiAttachment;
use App\Models\User; // Untuk Notifikasi
use App\Notifications\NotifikasiPermohonan; // Untuk Notifikasi
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; // Untuk Notifikasi
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage; // Untuk hapus file
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TransaksiFormController extends Controller
{
    /**
     * Menampilkan daftar "Tugas" (To-Do List)
     */
    public function index()
    {
        $query = TransaksiForm::query();
        $user = Auth::user();
        $userRole = $user->role->role_name ?? null;

        // Logika filter berdasarkan role (Alur Baru: Direksi -> PYB1 -> PYB2 -> BO)
        switch ($userRole) {
            case 'Pemohon':
                // Pemohon melihat semua miliknya (Draft, Diajukan, Ditolak, dll)
                $query->where('pemohon_id', $user->id);
                break;
            case 'Direksi':
                // Direksi melihat yang statusnya 'Diajukan' (tugas dia)
                $query->where('status', 'Diajukan');
                break;
            case 'PYB1':
                // PYB1 melihat yang statusnya 'Disetujui Direksi' (tugas dia)
                $query->where('status', 'Disetujui Direksi');
                break;
            case 'PYB2':
                // PYB2 melihat yang statusnya 'Disetujui PYB1' (tugas dia)
                $query->where('status', 'Disetujui PYB1');
                break;
            case 'BO':
                // BO melihat yang statusnya 'Disetujui PYB2' (tugas dia)
                $query->where('status', 'Disetujui PYB2');
                break;
            case 'Admin':
                // Admin melihat semua (nanti difilter di Monitoring)
                // Di 'Tugas', Admin tidak perlu melihat apa-apa
                $query->whereRaw('1 = 0'); // Kosong
                break;
            default:
                $query->whereRaw('1 = 0'); // Role tidak dikenal
                break;
        }

        $transaksiForms = $query->with(['pemohon', 'perusahaan'])
                                ->latest()
                                ->paginate(10);

        return view('transaksi.list_permohonan', compact('transaksiForms'));
    }

    /**
     * Menampilkan form untuk membuat permohonan baru.
     */
    public function create()
    {
        // Menggunakan view 'transaksi.create'
        return view('transaksi.create');
    }

    /**
     * Menyimpan permohonan baru (dari create.blade.php).
     */
    public function store(Request $request)
    {
        $submitAction = $request->input('submit_action', 'draft');
        $isSubmitting = ($submitAction === 'submit');

        // Validasi dasar (wajib untuk draft)
        $rules = [
            'uraian_transaksi' => 'required|string|max:500',
            'submit_action' => 'required|in:draft,submit',
            'kategori_uraian' => 'required|string',
            'tipe_dasar_transaksi' => 'required|string',
        ];

        // Aturan validasi tambahan (wajib untuk submit)
        if ($isSubmitting) {
            $rules['kategori_pengakuan'] = 'required|string';
            $rules['total_nominal'] = 'required|numeric|min:1';
            $rules['lawan_transaksi'] = 'required|string|max:255';
            $rules['rekening_transaksi'] = 'required|string|max:100';
            $rules['keterangan_dasar_transaksi'] = [
                Rule::requiredIf($request->input('tipe_dasar_transaksi') === 'pernyataan_direksi'),
                'nullable',
                'string',
            ];
            $rules['lampiran'] = [
                Rule::requiredIf(in_array($request->input('tipe_dasar_transaksi'), ['invoice', 'nota'])),
                'nullable',
                'array',
            ];
            $rules['lampiran.*'] = 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120'; // Max 5MB
        } else {
            // Aturan opsional untuk Draft
            $rules['total_nominal'] = 'nullable|numeric|min:0';
            $rules['kategori_pengakuan'] = 'nullable|string';
            $rules['lawan_transaksi'] = 'nullable|string|max:255';
            $rules['rekening_transaksi'] = 'nullable|string|max:100';
            $rules['lampiran'] = 'nullable|array';
            $rules['lampiran.*'] = 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120';
        }

        // Aturan yang berlaku untuk keduanya
        $rules['tanggal_option'] = 'required|in:today,custom';
        $rules['tanggal_pengajuan_kustom'] = [
            Rule::requiredIf($request->input('tanggal_option') === 'custom'),
            'nullable',
            'date'
        ];
        $rules['rencana_tanggal_transaksi'] = 'nullable|date';
        $rules['keterangan_form'] = 'nullable|string|max:1000';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('permohonan.create')
                        ->withErrors($validator)
                        ->withInput();
        }

        $user = Auth::user();
        $data = $validator->validated();

        // Hapus 'submit_action' dan 'lampiran' dari data utama
        unset($data['submit_action'], $data['lampiran'], $data['tanggal_option'], $data['tanggal_pengajuan_kustom']);

        $data['pemohon_id'] = $user->id;
        $data['perusahaan_id'] = $user->perusahaan_id;
        $data['status'] = $isSubmitting ? 'Diajukan' : 'Draft';

        if ($request->input('tanggal_option') === 'custom' && $request->filled('tanggal_pengajuan_kustom')) {
            $data['tanggal_pengajuan'] = Carbon::parse($request->input('tanggal_pengajuan_kustom'))->startOfDay();
        } else {
            $data['tanggal_pengajuan'] = Carbon::today();
        }

        DB::beginTransaction();
        try {
            $transaksiForm = TransaksiForm::create($data);

            if ($request->hasFile('lampiran')) {
                $this->uploadFiles($transaksiForm, $request->file('lampiran'), $request->input('tipe_dasar_transaksi'));
            }

            if ($isSubmitting) {
                $this->buatHistori($transaksiForm, $user, 'Mengajukan', 'Permohonan baru diajukan.', 'Draft', 'Diajukan');
                $this->kirimNotifikasi($transaksiForm, 'Direksi', "Permohonan baru (ID: {$transaksiForm->id}) telah diajukan oleh {$user->name} dan menunggu persetujuan Anda.");
            }

            DB::commit();

            $message = $isSubmitting ? 'Permohonan berhasil diajukan.' : 'Permohonan berhasil disimpan sebagai Draft.';
            return redirect()->route('permohonan.list')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal simpan transaksi baru: " . $e->getMessage());
            return redirect()->route('permohonan.create')
                        ->with('error', 'Terjadi kesalahan server saat menyimpan data. Error: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Menampilkan detail transaksi (read-only).
     */
    public function show(TransaksiForm $transaksiForm)
    {
        $user = Auth::user();
        $role = $user->role->role_name;

        // Otorisasi: Admin bisa lihat semua. Pemohon hanya lihat miliknya.
        // Approver (Direksi, PYB1, PYB2, BO) bisa lihat jika sudah jadi tugasnya atau ada di historinya.
        $canView = false;
        if ($role == 'Admin') {
            $canView = true;
        } elseif ($role == 'Pemohon' && $transaksiForm->pemohon_id == $user->id) {
            $canView = true;
        } elseif (in_array($role, ['Direksi', 'PYB1', 'PYB2', 'BO'])) {
            $isVisibleInHistory = $transaksiForm->history()->where('user_id', $user->id)->exists();
            $isCurrentTask = ($role == 'Direksi' && $transaksiForm->status == 'Diajukan') ||
                             ($role == 'PYB1' && $transaksiForm->status == 'Disetujui Direksi') ||
                             ($role == 'PYB2' && $transaksiForm->status == 'Disetujui PYB1') ||
                             ($role == 'BO' && $transaksiForm->status == 'Disetujui PYB2');

            if ($isVisibleInHistory || $isCurrentTask) {
                $canView = true;
            }
        }

        if (!$canView) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES UNTUK MELIHAT TRANSAKSI INI.');
        }

        // Load relasi
        $transaksiForm->load(['pemohon', 'perusahaan', 'attachments.uploader', 'history.user.role']);

        return view('transaksi.show', compact('transaksiForm'));
    }

    /**
     * (BARU) Menampilkan form untuk mengedit 'Draft' atau 'Ditolak'.
     */
    public function edit(TransaksiForm $transaksiForm)
    {
        $user = Auth::user();

        // Otorisasi: Hanya Pemohon yang bisa edit,
        // dan hanya jika statusnya 'Draft' atau 'Ditolak'.
        if ($transaksiForm->pemohon_id !== $user->id || !in_array($transaksiForm->status, ['Draft', 'Ditolak'])) {
            abort(403, 'ANDA TIDAK BISA MENGEDIT TRANSAKSI INI.');
        }

        // Load lampiran untuk ditampilkan di form edit
        $transaksiForm->load('attachments');

        // Kita pakai ulang view 'create' dan mengisinya dengan data
        return view('transaksi.create', compact('transaksiForm'));
    }

    /**
     * (BARU) Menyimpan perubahan dari form edit.
     */
    public function update(Request $request, TransaksiForm $transaksiForm)
    {
        $user = Auth::user();

        // Otorisasi: Cek ulang
        if ($transaksiForm->pemohon_id !== $user->id || !in_array($transaksiForm->status, ['Draft', 'Ditolak'])) {
            abort(403, 'ANDA TIDAK BISA MENGEDIT TRANSAKSI INI.');
        }

        $originalStatus = $transaksiForm->status; // Simpan status awal ('Draft' atau 'Ditolak')

        // Validasi (sama seperti 'store')
        $submitAction = $request->input('submit_action', 'draft');
        $isSubmitting = ($submitAction === 'submit');

        $rules = [
            'uraian_transaksi' => 'required|string|max:500',
            'submit_action' => 'required|in:draft,submit',
            'kategori_uraian' => 'required|string',
            'tipe_dasar_transaksi' => 'required|string',
        ];

        if ($isSubmitting) {
            // ... (validasi ketat untuk submit) ...
            $rules['kategori_pengakuan'] = 'required|string';
            $rules['total_nominal'] = 'required|numeric|min:1';
            $rules['lawan_transaksi'] = 'required|string|max:255';
            $rules['rekening_transaksi'] = 'required|string|max:100';
            $rules['keterangan_dasar_transaksi'] = [
                Rule::requiredIf($request->input('tipe_dasar_transaksi') === 'pernyataan_direksi'),
                'nullable', 'string',
            ];
            // Cek apakah file baru diupload
            $rules['lampiran'] = 'nullable|array';
            $rules['lampiran.*'] = 'file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120';
        } else {
             // ... (validasi longgar untuk draft) ...
            $rules['total_nominal'] = 'nullable|numeric|min:0';
            // ... (field opsional lainnya) ...
        }

        $rules['tanggal_option'] = 'required|in:today,custom';
        $rules['tanggal_pengajuan_kustom'] = [ Rule::requiredIf($request->input('tanggal_option') === 'custom'), 'nullable', 'date'];
        $rules['rencana_tanggal_transaksi'] = 'nullable|date';
        $rules['keterangan_form'] = 'nullable|string|max:1000';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('permohonan.edit', $transaksiForm)
                        ->withErrors($validator)
                        ->withInput();
        }

        $data = $validator->validated();
        unset($data['submit_action'], $data['lampiran'], $data['tanggal_option'], $data['tanggal_pengajuan_kustom']);

        // Tentukan Status Baru
        if ($originalStatus === 'Ditolak') {
            // Jika aslinya Ditolak, aksi apapun (Draft/Submit) akan dianggap "Ajukan Ulang"
            $data['status'] = 'Diajukan';
        } else {
            // Jika aslinya Draft
            $data['status'] = $isSubmitting ? 'Diajukan' : 'Draft';
        }

        // Handle Tanggal
        if ($request->input('tanggal_option') === 'custom' && $request->filled('tanggal_pengajuan_kustom')) {
            $data['tanggal_pengajuan'] = Carbon::parse($request->input('tanggal_pengajuan_kustom'))->startOfDay();
        } else {
            $data['tanggal_pengajuan'] = Carbon::today();
        }

        DB::beginTransaction();
        try {
            // 1. Update Form Utama
            $transaksiForm->update($data);

            // 2. Handle Lampiran Baru (jika ada)
            if ($request->hasFile('lampiran')) {
                $this->uploadFiles($transaksiForm, $request->file('lampiran'), $request->input('tipe_dasar_transaksi'));
            }

            // 3. Catat Histori
            if ($originalStatus === 'Ditolak') {
                $this->buatHistori($transaksiForm, $user, 'Mengajukan Ulang (Revisi)', 'Permohonan diperbarui dan diajukan ulang.', 'Ditolak', 'Diajukan');
                $this->kirimNotifikasi($transaksiForm, 'Direksi', "Permohonan (ID: {$transaksiForm->id}) telah direvisi dan diajukan ulang oleh {$user->name}.");
            } elseif ($data['status'] === 'Diajukan' && $originalStatus === 'Draft') {
                $this->buatHistori($transaksiForm, $user, 'Mengajukan', 'Permohonan diajukan dari Draft.', 'Draft', 'Diajukan');
                $this->kirimNotifikasi($transaksiForm, 'Direksi', "Permohonan baru (ID: {$transaksiForm->id}) telah diajukan oleh {$user->name}.");
            } else {
                // Hanya update draft, tidak perlu histori
            }

            DB::commit();

            $message = 'Permohonan berhasil diperbarui.';
            if ($data['status'] === 'Diajukan') $message = 'Permohonan berhasil diajukan ulang.';

            return redirect()->route('permohonan.detail', $transaksiForm)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal update transaksi: " . $e->getMessage());
            return redirect()->route('permohonan.edit', $transaksiForm)
                        ->with('error', 'Terjadi kesalahan server saat update data. Error: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * (BARU) Menghapus permohonan (hanya 'Draft').
     */
    public function destroy(TransaksiForm $transaksiForm)
    {
        $user = Auth::user();

        // Otorisasi: Hanya Pemohon dan status Draft
        if ($transaksiForm->pemohon_id !== $user->id || $transaksiForm->status !== 'Draft') {
            return redirect()->route('permohonan.list')->with('error', 'Anda tidak bisa menghapus transaksi ini.');
        }

        DB::beginTransaction();
        try {
            // 1. Hapus file fisik di storage
            foreach ($transaksiForm->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                // Hapus direktori jika kosong
                $directory = 'attachments/' . $transaksiForm->id;
                if (empty(Storage::disk('public')->files($directory))) {
                    Storage::disk('public')->deleteDirectory($directory);
                }
            }
            // 2. Hapus catatan lampiran (otomatis via cascade)
            // 3. Hapus histori (otomatis via cascade)

            // 4. Hapus form utama
            $transaksiForm->delete();

            DB::commit();
            return redirect()->route('permohonan.list')->with('success', 'Permohonan (Draft) berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal hapus transaksi: " . $e->getMessage());
            return redirect()->route('permohonan.list')->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Helper Functions (Internal)
    |--------------------------------------------------------------------------
    */

    /**
     * Helper untuk upload file.
     */
    private function uploadFiles(TransaksiForm $transaksiForm, array $files, $attachmentType)
    {
        foreach ($files as $file) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            // ===================================
            // INI PERBAIKANNYA
            // ===================================
            $filePath = $file->storeAs('attachments/' . $transaksiForm->id, $fileName, 'public');

            TransaksiAttachment::create([
                'transaksi_form_id' => $transaksiForm->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'attachment_type' => $attachmentType, // 'invoice', 'nota', 'pernyataan_direksi'
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    /**
     * Helper untuk mencatat histori.
     */
    private function buatHistori(TransaksiForm $transaksiForm, User $user, $action, $remarks, $fromStatus, $toStatus)
    {
        $transaksiForm->history()->create([
            'user_id' => $user->id,
            'action' => $action,
            'remarks' => $remarks,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
        ]);
    }

    /**
     * Helper untuk mengirim notifikasi.
     */
    private function kirimNotifikasi(TransaksiForm $transaksiForm, $roleName, $message)
    {
        try {
            $usersToNotify = User::whereHas('role', function($q) use ($roleName) {
                $q->where('role_name', $roleName);
            })->get();

            if ($usersToNotify->isNotEmpty()) {
                $url = route('permohonan.detail', $transaksiForm);
                Notification::send($usersToNotify, new NotifikasiPermohonan($message, $url));
            }
        } catch (\Exception $e) {
            Log::error("Gagal kirim notifikasi ke $roleName: " . $e->getMessage());
        }
    }
}

