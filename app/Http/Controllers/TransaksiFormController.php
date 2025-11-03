<?php

namespace App\Http\Controllers;

use App\Models\TransaksiForm;
use App\Models\TransaksiDetail; // Pastikan ini di-import
use App\Models\TransaksiAttachment; // <-- TAMBAHKAN INI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Wajib untuk Transaction
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // Untuk error kustom

class TransaksiFormController extends Controller
{
    /**
     * Menampilkan daftar transaksi.
     */
    public function index()
    {
        $query = TransaksiForm::query();
        $userRole = Auth::user()->role->role_name ?? null;

        // Filter berdasarkan role
        switch ($userRole) {
            case 'Pemohon':
                // Pemohon hanya melihat miliknya
                $query->where('pemohon_id', Auth::id());
                break;
            case 'PYB1':
                // PYB1 melihat yang 'Diajukan'
                $query->where('status', 'Diajukan');
                break;
            case 'PYB2':
                // PYB2 melihat yang 'Disetujui PYB1'
                $query->where('status', 'Disetujui PYB1');
                break;
            case 'BO':
                // BO melihat yang 'Disetujui PYB2'
                $query->where('status', 'Disetujui PYB2');
                break;
            case 'Admin':
                // Admin melihat semua (atau sesuai logic admin)
                // tidak perlu filter
                break;
            default:
                // Jika role tidak dikenal, jangan tampilkan apa-apa
                $query->whereRaw('1 = 0'); // false query
                break;
        }

        $transaksiForms = $query->with(['pemohon', 'perusahaan'])
            ->latest()
            ->paginate(20); // Tampilkan 20 per halaman

        return view('transaksi.list_permohonan', compact('transaksiForms'));
    }

    /**
     * Menampilkan form untuk membuat transaksi baru.
     */
    public function create()
    {
        return view('transaksi.create');
    }

    /**
     * Menyimpan transaksi baru ke database.
     * Ini adalah method kompleks yang menangani 'Draft' dan 'Submit'.
     */
    public function store(Request $request)
    {
        // 1. Tentukan Aksi (Draft atau Submit)
        $action = $request->input('submit_action', 'draft');

        // 2. Validasi Dasar (Minimal untuk Draft)
        $baseRules = [
            'uraian_transaksi' => 'required|string|max:500',

            // (BARU) Pindahkan validasi lampiran ke sini agar bisa disimpan saat 'Draft'
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:5120', // Max 5MB per file
            'attachment_type' => 'nullable|string|max:100',
        ];

        // 3. Validasi Penuh (Wajib untuk Submit)
        $fullRules = [
            'total_nominal' => 'required|numeric|min:0',
            'dasar_transaksi' => 'required|string|max:255',
            'lawan_transaksi' => 'required|string|max:255',
            'rekening_transaksi' => 'required|string|max:100',
            'rencana_tanggal_transaksi' => 'nullable|date',
            'keterangan_form' => 'nullable|string|max:1000',

            // Validasi Rincian (details)
            'details' => 'required|array|min:1',
            'details.*.pengakuan_transaksi' => 'required|string|max:255',
            'details.*.nominal' => 'required|numeric|min:0',
            'details.*.keterangan_detail' => 'nullable|string',

            // (HAPUS DARI SINI) Validasi lampiran sudah dipindah ke $baseRules
        ];

        // Tentukan aturan validasi berdasarkan aksi
        $rules = ($action == 'submit') ? array_merge($baseRules, $fullRules) : $baseRules;
        $validatedData = $request->validate($rules);

        // 4. Validasi Keseimbangan (Balance) - HANYA JIKA SUBMIT
        if ($action == 'submit') {
            $totalRincian = collect($validatedData['details'])->sum('nominal');
            if ( (float) $totalRincian != (float) $validatedData['total_nominal'] ) {
                // Buat error validasi kustom jika tidak balance
                throw ValidationException::withMessages([
                    'total_nominal' => 'Total Nominal Form (Rp ' . number_format($validatedData['total_nominal'], 0, ',', '.') .
                                     ') tidak sama dengan Total Rincian (Rp ' . number_format($totalRincian, 0, ',', '.') . ')'
                ]);
            }
        }

        // 5. Proses Penyimpanan (Gunakan Transaction)
        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Siapkan data untuk tabel 'transaksi_forms'
            $formData = [
                'pemohon_id' => $user->id,
                'perusahaan_id' => $user->perusahaan_id,
                'uraian_transaksi' => $validatedData['uraian_transaksi'],
                'status' => ($action == 'submit') ? 'Diajukan' : 'Draft',

                // Isi data lain jika ada (jika 'draft', mungkin kosong)
                'total_nominal' => $validatedData['total_nominal'] ?? 0,
                'dasar_transaksi' => $validatedData['dasar_transaksi'] ?? null,
                'lawan_transaksi' => $validatedData['lawan_transaksi'] ?? null,
                'rekening_transaksi' => $validatedData['rekening_transaksi'] ?? null,
                'rencana_tanggal_transaksi' => $validatedData['rencana_tanggal_transaksi'] ?? null,
                'keterangan_form' => $validatedData['keterangan_form'] ?? null,
            ];

            // Handle Opsi Tanggal Pengajuan
            if ($request->input('tanggal_option') == 'custom' && $request->filled('tanggal_pengajuan_kustom')) {
                $formData['tanggal_pengajuan'] = Carbon::parse($request->input('tanggal_pengajuan_kustom'));
            } else {
                $formData['tanggal_pengajuan'] = Carbon::now();
            }

            // Buat Form Utama
            $transaksiForm = TransaksiForm::create($formData);

            // Jika ada rincian (details) di request, simpan
            if (isset($validatedData['details']) && is_array($validatedData['details'])) {
                $detailsData = [];
                foreach ($validatedData['details'] as $detail) {
                    $detailsData[] = new TransaksiDetail([
                        'pengakuan_transaksi' => $detail['pengakuan_transaksi'],
                        'nominal' => $detail['nominal'],
                        'keterangan_detail' => $detail['keterangan_detail'] ?? null,
                    ]);
                }
                // Simpan semua rincian terkait form utama
                $transaksiForm->details()->saveMany($detailsData);
            }

            // (BARU) Logika untuk menyimpan Lampiran
            if ($request->hasFile('lampiran')) {
                $attachmentType = $validatedData['attachment_type'] ?? 'Lampiran';
                $user = Auth::user();

                foreach ($validatedData['lampiran'] as $file) {
                    if ($file && $file->isValid()) { // Pastikan file valid
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        // Simpan file ke storage/app/public/attachments/ID_FORM/
                        // Pastikan Anda sudah menjalankan 'php artisan storage:link'
                        $filePath = $file->storeAs('attachments/' . $transaksiForm->id, $fileName, 'public');

                        // Catat di database
                        TransaksiAttachment::create([
                            'transaksi_form_id' => $transaksiForm->id,
                            'file_name' => $fileName,
                            'file_path' => $filePath,
                            'attachment_type' => $attachmentType,
                            'uploaded_by' => $user->id,
                        ]);
                    }
                }
            }
            // (AKHIR BLOK BARU)


            // Jika action = 'submit', rekam history
            if ($action == 'submit') {
                $transaksiForm->history()->create([
                    'user_id' => $user->id,
                    'action' => 'Pengajuan Dibuat',
                    'remarks' => 'Pengajuan baru telah disubmit oleh pemohon.',
                    'from_status' => 'Baru',
                    'to_status' => 'Diajukan',
                ]);
            }

            // Jika semua sukses
            DB::commit();

            $message = ($action == 'submit') ? 'Permohonan baru berhasil diajukan.' : 'Permohonan berhasil disimpan sebagai Draft.';
            return redirect()->route('list-permohonan')->with('success', $message);

        } catch (\Exception $e) {
            // Jika terjadi error, batalkan semua
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Menampilkan detail satu transaksi.
     */
    public function show(TransaksiForm $transaksiForm)
    {
        // Gunakan Eager Loading untuk memuat relasi
        $transaksiForm->load([
            'details', // Rincian pengakuan
            'pemohon', // Info user pemohon
            'perusahaan', // Info perusahaan
            'history' => function ($query) {
                $query->with('user')->latest(); // Relasi history + user pembuat history
            }
        ]);

        return view('transaksi.show', compact('transaksiForm'));
    }

    /**
     * Show the form for editing the specified resource.
     * (Nanti kita isi untuk 'Draft')
     */
    public function edit(TransaksiForm $transaksiForm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * (Nanti kita isi untuk 'Draft')
     */
    public function update(Request $request, TransaksiForm $transaksiForm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * (Nanti kita isi untuk 'Draft')
     */
    public function destroy(TransaksiForm $transaksiForm)
    {
        //
    }
}


