@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

{{-- Tentukan judul halaman berdasarkan mode (Create atau Edit) --}}
@php
    $isEditMode = isset($transaksiForm);
    $pageTitle = $isEditMode ? 'Edit Permohonan (ID: ' . $transaksiForm->id . ')' : 'Form Pengajuan Transaksi Baru';
@endphp

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => $pageTitle])

    <div class="container-fluid py-4">
        <form id="transaksi-form" role="form" method="POST"
            @if ($isEditMode)
                action="{{ route('permohonan.update', $transaksiForm) }}"
            @else
                action="{{ route('permohonan.store') }}"
            @endif
            enctype="multipart/form-data">

            @csrf

            {{-- Jika mode Edit, tambahkan method spoofing 'PUT' --}}
            @if ($isEditMode)
                @method('PUT')
            @endif

            {{-- Hidden input untuk menentukan aksi (Draft atau Submit) --}}
            <input type="hidden" name="submit_action" id="submit_action" value="draft">

            <div class="row">
                {{-- =================================== --}}
                {{-- CARD 1: DATA TRANSAKSI (FORM UTAMA) --}}
                {{-- =================================== --}}
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0">Data Transaksi</h6>
                                <a href="{{ route('permohonan.list') }}" class="btn btn-dark btn-sm ms-auto">
                                    <i class="fa fa-arrow-left me-1"></i>
                                    Kembali ke Daftar
                                </a>
                            </div>
                        </div>
                        <div class="card-body">

                            {{-- Tampilkan error validasi --}}
                            @if ($errors->any())
                                <div class="alert alert-danger" role="alert">
                                    <strong class="text-white">Validasi Gagal! Periksa kembali data Anda.</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- BARIS 1: KATEGORI & URAIAN --}}
                            <p class="text-uppercase text-sm">1. Detail Pengajuan</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kategori_uraian" class="form-control-label">Kategori Uraian <span class="text-danger">*</span></label>
                                        <select class="form-control" id="kategori_uraian" name="kategori_uraian" required>
                                            <option value="" disabled {{ old('kategori_uraian', $transaksiForm->kategori_uraian ?? '') == '' ? 'selected' : '' }}>-- Pilih Kategori --</option>
                                            <option value="Biaya Operasional" {{ old('kategori_uraian', $transaksiForm->kategori_uraian ?? '') == 'Biaya Operasional' ? 'selected' : '' }}>Biaya Operasional</option>
                                            <option value="Biaya Pajak" {{ old('kategori_uraian', $transaksiForm->kategori_uraian ?? '') == 'Biaya Pajak' ? 'selected' : '' }}>Biaya Pajak</option>
                                            <option value="Biaya Produksi" {{ old('kategori_uraian', $transaksiForm->kategori_uraian ?? '') == 'Biaya Produksi' ? 'selected' : '' }}>Biaya Produksi</option>
                                            <option value="Lainnya" {{ old('kategori_uraian', $transaksiForm->kategori_uraian ?? '') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="uraian_transaksi" class="form-control-label">Uraian Transaksi <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="uraian_transaksi" name="uraian_transaksi" rows="3"
                                            placeholder="Cth: Pembayaran langganan software XYZ untuk tim IT" required>{{ old('uraian_transaksi', $transaksiForm->uraian_transaksi ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- BARIS 2: KEUANGAN & TANGGAL --}}
                            <p class="text-uppercase text-sm">2. Detail Keuangan & Tanggal</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="kategori_pengakuan" class="form-control-label">Kategori Pengakuan Transaksi <span class="text-danger">*</span></label>
                                        <select class="form-control" id="kategori_pengakuan" name="kategori_pengakuan">
                                            <option value="" disabled {{ old('kategori_pengakuan', $transaksiForm->kategori_pengakuan ?? '') == '' ? 'selected' : '' }}>-- Pilih Kategori --</option>
                                            <option value="Beban Operasional" {{ old('kategori_pengakuan', $transaksiForm->kategori_pengakuan ?? '') == 'Beban Operasional' ? 'selected' : '' }}>Beban Operasional</option>
                                            <option value="Beban Honorarium" {{ old('kategori_pengakuan', $transaksiForm->kategori_pengakuan ?? '') == 'Beban Honorarium' ? 'selected' : '' }}>Beban Honorarium</option>
                                            <option value="Beban Transportasi" {{ old('kategori_pengakuan', $transaksiForm->kategori_pengakuan ?? '') == 'Beban Transportasi' ? 'selected' : '' }}>Beban Transportasi</option>
                                            <option value="Beban Produksi" {{ old('kategori_pengakuan', $transaksiForm->kategori_pengakuan ?? '') == 'Beban Produksi' ? 'selected' : '' }}>Beban Produksi</Akses>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="total_nominal_display" class="form-control-label">Total Nominal (Rp) <span class="text-danger">*</span></label>
                                        {{-- Input yang dilihat user (dengan format) --}}
                                        <input class="form-control" type="text" id="total_nominal_display"
                                            value="{{ old('total_nominal', $transaksiForm->total_nominal ?? '') }}">
                                        {{-- Input tersembunyi untuk menyimpan angka mentah --}}
                                        <input type="hidden" name="total_nominal" id="total_nominal_raw"
                                            value="{{ old('total_nominal', $transaksiForm->total_nominal ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="rencana_tanggal_transaksi" class="form-control-label">Rencana Tgl. Transaksi</label>
                                        <input class="form-control" type="date" id="rencana_tanggal_transaksi" name="rencana_tanggal_transaksi"
                                            value="{{ old('rencana_tanggal_transaksi', $transaksiForm->rencana_tanggal_transaksi ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- BARIS 3: LAWAN TRANSAKSI --}}
                            <p class="text-uppercase text-sm">3. Detail Penerima</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lawan_transaksi" class="form-control-label">Lawan Transaksi / Penerima <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="lawan_transaksi" name="lawan_transaksi"
                                            placeholder="Cth: PT. Sinar Jaya"
                                            value="{{ old('lawan_transaksi', $transaksiForm->lawan_transaksi ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="rekening_transaksi" class="form-control-label">Rekening Transaksi <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" id="rekening_transaksi" name="rekening_transaksi"
                                            placeholder="Cth: BCA 123456789 a/n PT. Sinar Jaya"
                                            value="{{ old('rekening_transaksi', $transaksiForm->rekening_transaksi ?? '') }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- BARIS 4: DASAR TRANSAKSI & LAMPIRAN (KONDISIONAL) --}}
                            <p class="text-uppercase text-sm">4. Detail Dasar Transaksi</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="tanggal_option" class="form-control-label">Tanggal Pengajuan <span class="text-danger">*</span></label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tanggal_option" id="tanggal_today" value="today"
                                                {{ old('tanggal_option', $isEditMode ? '' : 'today') == 'today' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tanggal_today">Hari Ini</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tanggal_option" id="tanggal_custom" value="custom"
                                                {{ old('tanggal_option', $isEditMode ? 'custom' : '') == 'custom' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tanggal_custom">Pilih Tanggal Lain</label>
                                        </div>
                                        <input class="form-control mt-2" type="date" id="tanggal_pengajuan_kustom" name="tanggal_pengajuan_kustom"
                                            value="{{ old('tanggal_pengajuan_kustom', $isEditMode ? $transaksiForm->tanggal_pengajuan->format('Y-m-d') : '') }}"
                                            style="{{ old('tanggal_option', $isEditMode ? 'custom' : 'today') == 'custom' ? '' : 'display: none;' }}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="tipe_dasar_transaksi" class="form-control-label">Tipe Dasar Transaksi <span class="text-danger">*</span></label>
                                                <select class="form-control" id="tipe_dasar_transaksi" name="tipe_dasar_transaksi">
                                                    <option value="" disabled {{ old('tipe_dasar_transaksi', $transaksiForm->tipe_dasar_transaksi ?? '') == '' ? 'selected' : '' }}>-- Pilih Tipe --</option>
                                                    <option value="invoice" {{ old('tipe_dasar_transaksi', $transaksiForm->tipe_dasar_transaksi ?? '') == 'invoice' ? 'selected' : '' }}>Invoice</option>
                                                    <option value="nota" {{ old('tipe_dasar_transaksi', $transaksiForm->tipe_dasar_transaksi ?? '') == 'nota' ? 'selected' : '' }}>Nota / Kwitansi</option>
                                                    <option value="pernyataan_direksi" {{ old('tipe_dasar_transaksi', $transaksiForm->tipe_dasar_transaksi ?? '') == 'pernyataan_direksi' ? 'selected' : '' }}>Pernyataan Direksi</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{-- Field Keterangan (Hanya muncul jika 'Pernyataan Direksi') --}}
                                        <div class="col-12" id="field-keterangan-direksi" style="display: none;">
                                            <div class="form-group">
                                                <label for="keterangan_dasar_transaksi" class="form-control-label">Keterangan Tambahan (Pernyataan Direksi) <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="keterangan_dasar_transaksi" name="keterangan_dasar_transaksi" rows="3"
                                                    placeholder="Jelaskan pernyataan direksi...">{{ old('keterangan_dasar_transaksi', $transaksiForm->keterangan_dasar_transaksi ?? '') }}</textarea>
                                            </div>
                                        </div>
                                        {{-- Field Upload (Hanya muncul jika 'Invoice' atau 'Nota') --}}
                                        <div class="col-12" id="field-upload-lampiran" style="display: none;">
                                            <div class="form-group">
                                                <label for="lampiran" class="form-control-label">Upload Lampiran (Invoice/Nota) <span class="text-danger">*</span></label>
                                                <input class="form-control" type="file" id="lampiran" name="lampiran[]" multiple>
                                                <div id="file-list-preview" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- BARIS 5: KETERANGAN FORM --}}
                            <p class="text-uppercase text-sm">5. Keterangan Tambahan (Opsional)</p>
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="keterangan_form" class="form-control-label">Keterangan (jika ada)</label>
                                        <textarea class="form-control" id="keterangan_form" name="keterangan_form" rows="3"
                                            placeholder="Keterangan tambahan untuk keseluruhan form...">{{ old('keterangan_form', $transaksiForm->keterangan_form ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- =================================== --}}
                {{-- CARD 2: LAMPIRAN YANG SUDAH ADA (MODE EDIT) --}}
                {{-- =================================== --}}
                @if ($isEditMode && $transaksiForm->attachments->count() > 0)
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6 class="mb-0">Lampiran yang Sudah Terupload</h6>
                            <p class="text-sm">Centang file untuk menghapusnya saat menekan tombol "Simpan Draft" atau "Submit".</p>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama File</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipe</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tgl. Upload</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hapus?</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transaksiForm->attachments as $attachment)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <h6 class="mb-0 text-sm">
                                                        <a href="{{ route('permohonan.attachment.download', $attachment) }}" target="_blank" class="text-primary">
                                                            <i class="fa fa-download me-2"></i>{{ Str::limit($attachment->file_name, 50) }}
                                                        </a>
                                                    </h6>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0">{{ $attachment->attachment_type }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-secondary text-xs font-weight-bold">{{ $attachment->created_at->format('d/m/Y') }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input class="form-check-input" type="checkbox" name="delete_attachments[]" value="{{ $attachment->id }}">
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif


                {{-- =================================== --}}
                {{-- CARD 3: TOMBOL AKSI --}}
                {{-- =================================== --}}
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('permohonan.list') }}" class="btn btn-outline-secondary me-2">Batal</a>

                                {{-- Hanya tampilkan 'Simpan Draft' jika statusnya 'Draft' atau mode Create --}}
                                @if(!$isEditMode || $transaksiForm->status == 'Draft')
                                <button type="button" id="btn-save-draft" class="btn btn-info me-2">Simpan Draft</button>
                                @endif

                                {{-- Ganti teks tombol submit berdasarkan status --}}
                                <button type="button" id="btn-submit" class="btn btn-success">
                                    @if($isEditMode && $transaksiForm->status == 'Ditolak')
                                        <i class="fa fa-paper-plane me-1"></i>
                                        Ajukan Ulang (Revisi)
                                    @else
                                        <i class="fa fa-check me-1"></i>
                                        Submit Pengajuan
                                    @endif
                                </button>
                            </div>
                            <p id="validation-message" class="text-danger text-sm mt-2 text-end" style="display: none;"></p>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    @include('layouts.footers.auth.footer')

@endsection

@push('js')
{{-- CDN untuk SweetAlert dan AutoNumeric --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ==================================
        // 1. INISIALISASI
        // ==================================

        // Inisialisasi AutoNumeric untuk Total Nominal
        const anNominal = new AutoNumeric('#total_nominal_display', {
            decimalCharacter: ',',
            digitGroupSeparator: '.',
            currencySymbol: 'Rp ',
            currencySymbolPlacement: 'p',
            unformatOnSubmit: true,
            minimumValue: '0'
        });

        // Ambil elemen-elemen
        const form = document.getElementById('transaksi-form');
        const hiddenSubmitAction = document.getElementById('submit_action');
        const btnSaveDraft = document.getElementById('btn-save-draft');
        const btnSubmit = document.getElementById('btn-submit');
        const validationMsg = document.getElementById('validation-message');

        const radioToday = document.getElementById('tanggal_today');
        const radioCustom = document.getElementById('tanggal_custom');
        const inputCustomDate = document.getElementById('tanggal_pengajuan_kustom');

        const selectTipeDasar = document.getElementById('tipe_dasar_transaksi');
        const fieldKeteranganDireksi = document.getElementById('field-keterangan-direksi');
        const inputKeteranganDireksi = document.getElementById('keterangan_dasar_transaksi');
        const fieldUploadLampiran = document.getElementById('field-upload-lampiran');
        const inputLampiran = document.getElementById('lampiran');
        const fileListPreview = document.getElementById('file-list-preview');

        // Mode Edit?
        const isEditMode = {{ $isEditMode ? 'true' : 'false' }};
        const currentStatus = "{{ $transaksiForm->status ?? 'Draft' }}";

        // ==================================
        // 2. FUNGSI HELPER
        // ==================================

        // Fungsi untuk menampilkan/menyembunyikan field kondisional
        function checkConditionalFields() {
            const tipe = selectTipeDasar.value;

            // Keterangan Pernyataan Direksi
            if (tipe === 'pernyataan_direksi') {
                fieldKeteranganDireksi.style.display = 'block';
            } else {
                fieldKeteranganDireksi.style.display = 'none';
            }

            // Upload Lampiran
            if (tipe === 'invoice' || tipe === 'nota') {
                fieldUploadLampiran.style.display = 'block';
            } else {
                fieldUploadLampiran.style.display = 'none';
            }
        }

        // Fungsi untuk menampilkan/menyembunyikan input tanggal kustom
        function checkTanggalOption() {
            if (radioCustom.checked) {
                inputCustomDate.style.display = 'block';
            } else {
                inputCustomDate.style.display = 'none';
            }
        }

        // Fungsi untuk validasi form sebelum submit
        function validateForm(action) {
            validationMsg.style.display = 'none';
            validationMsg.innerHTML = '';
            let errors = [];

            // 1. Validasi Uraian (Wajib untuk Draft & Submit)
            if (document.getElementById('uraian_transaksi').value.trim() === '') {
                errors.push('Uraian Transaksi wajib diisi.');
            }
            if (document.getElementById('kategori_uraian').value.trim() === '') {
                errors.push('Kategori Uraian wajib diisi.');
            }
            if (document.getElementById('tipe_dasar_transaksi').value.trim() === '') {
                errors.push('Tipe Dasar Transaksi wajib diisi.');
            }

            // 2. Validasi Kétat (Hanya untuk Submit)
            if (action === 'submit') {
                if (document.getElementById('kategori_pengakuan').value.trim() === '') {
                    errors.push('Kategori Pengakuan wajib diisi.');
                }
                if (anNominal.getNumber() <= 0) {
                    errors.push('Total Nominal harus lebih dari 0.');
                }
                if (document.getElementById('lawan_transaksi').value.trim() === '') {
                    errors.push('Lawan Transaksi wajib diisi.');
                }
                if (document.getElementById('rekening_transaksi').value.trim() === '') {
                    errors.push('Rekening Transaksi wajib diisi.');
                }

                // Validasi Kondisional Tipe Dasar
                const tipeDasar = selectTipeDasar.value;
                if (tipeDasar === 'pernyataan_direksi' && inputKeteranganDireksi.value.trim() === '') {
                    errors.push('Keterangan Tambahan (Pernyataan Direksi) wajib diisi.');
                }

                // Validasi Lampiran (Hanya jika 'invoice' atau 'nota' DAN mode Create)
                // Di mode Edit, kita asumsikan lampiran lama masih valid
                if (!isEditMode && (tipeDasar === 'invoice' || tipeDasar === 'nota') && inputLampiran.files.length === 0) {
                     errors.push('Lampiran (Invoice/Nota) wajib di-upload.');
                }
            }

            if (errors.length > 0) {
                validationMsg.innerHTML = '<strong>Validasi Gagal:</strong><br>' + errors.join('<br>');
                validationMsg.style.display = 'block';
                Swal.fire({
                    icon: 'error',
                    title: 'Data Belum Lengkap!',
                    html: errors.join('<br>'),
                    confirmButtonColor: '#f5365c' // Argon danger color
                });
                return false; // Gagal
            }

            return true; // Sukses
        }

        // Fungsi untuk preview file
        function updateFilePreview() {
            fileListPreview.innerHTML = ''; // Kosongkan list
            if (inputLampiran.files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-group';
                for (const file of inputLampiran.files) {
                    const item = document.createElement('li');
                    item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';

                    const fileName = document.createElement('span');
                    fileName.textContent = file.name;

                    const fileSize = document.createElement('span');
                    fileSize.className = 'badge bg-primary rounded-pill';
                    fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';

                    item.appendChild(fileName);
                    item.appendChild(fileSize);
                    list.appendChild(item);
                }
                fileListPreview.appendChild(list);
            }
        }

        // ==================================
        // 3. EVENT LISTENERS
        // ==================================

        // Listener untuk Tombol 'Simpan Draft'
        if (btnSaveDraft) {
            btnSaveDraft.addEventListener('click', function(e) {
                e.preventDefault();
                // Validasi longgar (hanya uraian)
                if (validateForm('draft')) {
                    hiddenSubmitAction.value = 'draft';
                    form.submit();
                }
            });
        }

        // Listener untuk Tombol 'Submit' / 'Ajukan Ulang'
        btnSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            // Validasi ketat
            if (validateForm('submit')) {
                hiddenSubmitAction.value = 'submit';
                // Tampilkan konfirmasi
                const title = (currentStatus === 'Ditolak') ? 'Ajukan Ulang Permohonan?' : 'Submit Permohonan?';
                const text = (currentStatus === 'Ditolak') ? 'Pastikan Anda sudah memperbaiki data permohonan.' : 'Data akan dikirim untuk persetujuan.';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2dce89', // Argon success color
                    cancelButtonColor: '#f5365c', // Argon danger color
                    confirmButtonText: 'Ya, Lanjutkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }
        });

        // Listener untuk sinkronisasi AutoNumeric ke hidden input
        anNominal.domElement.addEventListener('autoNumeric:rawValueModified', function(e) {
            document.getElementById('total_nominal_raw').value = anNominal.getNumber();
        });

        // Listener untuk radio tanggal
        radioToday.addEventListener('change', checkTanggalOption);
        radioCustom.addEventListener('change', checkTanggalOption);

        // Listener untuk dropdown Tipe Dasar Transaksi
        selectTipeDasar.addEventListener('change', checkConditionalFields);

        // Listener untuk input file (preview)
        inputLampiran.addEventListener('change', updateFilePreview);


        // ==================================
        // 4. JALANKAN SAAT LOAD (INIT)
        // ==================================

        // Panggil fungsi ini saat halaman dimuat (untuk mode Edit)
        checkTanggalOption();
        checkConditionalFields();

        // Set nilai AutoNumeric di mode Edit
        @if($isEditMode)
            anNominal.set({{ $transaksiForm->total_nominal ?? 0 }});
        @endif

    });
</script>
@endpush

