@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Buat Permohonan Transaksi Baru'])

    <div class="container-fluid py-4">
        {{-- ========================================================================================= --}}
        {{-- FORM UTAMA: Menggunakan ID 'main-transaksi-form' untuk di-handle oleh JavaScript --}}
        {{-- ========================================================================================= --}}
        <form id="main-transaksi-form" action="{{ route('form-permohonan.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            {{-- Input tersembunyi untuk memberi tahu controller aksi mana yang diambil --}}
            <input type="hidden" name="submit_action" id="submit_action" value="">

            {{-- 1. CARD FORM UTAMA (Info Pemohon, Uraian, Total) --}}
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        {{-- Card Header --}}
                        <div class="card-header pb-0">
                            <div class="d-flex align-items-center">
                                <h6 class="mb-0">Form Pengajuan Transaksi</h6>
                                <a href="{{ route('list-permohonan') }}" class="btn btn-dark btn-sm ms-auto">
                                    <i class="fa fa-arrow-left me-1"></i>
                                    Batal
                                </a>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body">
                            {{-- Tampilkan error validasi Laravel (jika JS gagal dan form ter-submit) --}}
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

                            {{-- Baris 1: Info Pemohon & Perusahaan (Read-only) --}}
                            <p class="text-uppercase text-sm">Informasi Pemohon</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pemohon_name" class="form-control-label">Nama Pemohon</label>
                                        <input class="form-control" type="text" id="pemohon_name"
                                            value="{{ Auth::user()->name ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="perusahaan_nama" class="form-control-label">Nama Perusahaan</label>
                                        <input class="form-control" type="text" id="perusahaan_nama"
                                            value="{{ Auth::user()->perusahaan->nama_perusahaan ?? 'N/A' }}" disabled>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- Baris 2: Info Transaksi --}}
                            <p class="text-uppercase text-sm">Detail Transaksi</p>
                            <div class="row">
                                {{-- OPSI TANGGAL PENGAJUAN (BARU) --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-control-label">Tanggal Pengajuan</label>
                                        <div class="d-flex align-items-center">
                                            <div class="form-check me-3">
                                                <input class="form-check-input" type="radio" name="tanggal_option"
                                                    id="tanggal_hari_ini" value="today" checked>
                                                <label class="form-check-label" for="tanggal_hari_ini">
                                                    Hari Ini
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="tanggal_option"
                                                    id="tanggal_kustom" value="custom">
                                                <label class="form-check-label" for="tanggal_kustom">
                                                    Pilih Tanggal
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group" id="tanggal_kustom_input_wrapper" style="display: none;">
                                        <label for="tanggal_pengajuan_kustom" class="form-control-label">Pilih Tanggal Pengajuan</label>
                                        <input class="form-control" type="date" id="tanggal_pengajuan_kustom"
                                            name="tanggal_pengajuan_kustom" max="{{ date('Y-m-d') }}">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="uraian_transaksi" class="form-control-label">Uraian Transaksi</label>
                                        <textarea class="form-control" id="uraian_transaksi" name="uraian_transaksi" rows="3"
                                            placeholder="Cth: Pembelian ATK untuk kebutuhan operasional bulan November">{{ old('uraian_transaksi') }}</textarea>
                                        <span id="uraian-error" class="text-danger text-xs mt-1" style="display: none;">* Uraian Transaksi wajib diisi.</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dasar_transaksi" class="form-control-label">Dasar Transaksi</label>
                                        <input class="form-control" type="text" id="dasar_transaksi"
                                            name="dasar_transaksi" placeholder="Cth: Nota Dinas / PO / Invoice"
                                            value="{{ old('dasar_transaksi') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lawan_transaksi" class="form-control-label">Lawan Transaksi</label>
                                        <input class="form-control" type="text" id="lawan_transaksi"
                                            name="lawan_transaksi" placeholder="Cth: PT. Sinar Jaya Abadi"
                                            value="{{ old('lawan_transaksi') }}">
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- Baris 3: Info Pembayaran --}}
                            <p class="text-uppercase text-sm">Detail Pembayaran</p>
                            <div class="row">
                                {{-- TOTAL NOMINAL OTOMATIS (BARU) --}}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="input-total-form" class="form-control-label">Total Nominal (Rp) - Otomatis</label>
                                        {{-- Ini adalah input yang dilihat user (formatted) --}}
                                        <input class="form-control" type="text" id="input-total-form"
                                            placeholder="Akan terisi dari total rincian"
                                            value="{{ old('total_nominal', 0) }}" readonly>
                                        {{-- Ini adalah input yang dikirim ke backend (raw number) --}}
                                        <input type="hidden" name="total_nominal" id="input-total-form-raw"
                                               value="{{ old('total_nominal', 0) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="rekening_transaksi" class="form-control-label">Rekening Transaksi</label>
                                        <input class="form-control" type="text" id="rekening_transaksi"
                                            name="rekening_transaksi" placeholder="Cth: BCA 12345678 a/n PT..."
                                            value="{{ old('rekening_transaksi') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="rencana_tanggal_transaksi" class="form-control-label">Rencana Tgl. Transaksi</label>
                                        <input class="form-control" type="date" id="rencana_tanggal_transaksi"
                                            name="rencana_tanggal_transaksi"
                                            value="{{ old('rencana_tanggal_transaksi') }}" min="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="keterangan_form" class="form-control-label">Keterangan Tambahan</label>
                                        <textarea class="form-control" id="keterangan_form" name="keterangan_form"
                                            rows="2"
                                            placeholder="Keterangan tambahan jika ada...">{{ old('keterangan_form') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================================= --}}
            {{-- 2. CARD RINCIAN PENGAKUAN TRANSAKSI (Dihandle JS) --}}
            {{-- ========================================================================================= --}}
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6 class="mb-0">Rincian Pengakuan Transaksi</h6>
                        </div>
                        <div class="card-body">
                            {{-- 1. FORM UNTUK MENAMBAH ITEM (di-handle JS) --}}
                            <p class="text-uppercase text-sm">Tambah Item Rincian</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="input-pengakuan" class="form-control-label">Pengakuan Transaksi</label>
                                        <input class="form-control" type="text" id="input-pengakuan"
                                            placeholder="Cth: Biaya Akomodasi">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="input-nominal" class="form-control-label">Nominal (Rp)</label>
                                        <input class="form-control" type="text" id="input-nominal"
                                            placeholder="Cth: 5.000.000">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="input-keterangan" class="form-control-label">Keterangan Item</label>
                                        <input class="form-control" type="text" id="input-keterangan"
                                            placeholder="Cth: Hotel 5 malam">
                                    </div>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-group">
                                        <button type="button" id="btn-tambah-item" class="btn btn-primary btn-sm">Tambah</button>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            {{-- 2. TABEL DAFTAR ITEM YANG DITAMBAHKAN JS --}}
                            <p class="text-uppercase text-sm">Daftar Item</p>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pengakuan Transaksi</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Keterangan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nominal</th>
                                            <th class="text-secondary opacity-7"></th>
                                        </tr>
                                    </thead>
                                    {{-- ID 'rincian-tbody' digunakan oleh JS --}}
                                    <tbody id="rincian-tbody">
                                        {{-- Baris 'kosong' akan dihapus oleh JS --}}
                                        <tr id="row-empty">
                                            <td colspan="4" class="text-center py-3">
                                                <p class="mb-0 text-secondary">Belum ada rincian pengakuan transaksi.</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                    {{-- Footer Total --}}
                                    <tfoot>
                                        <tr class="bg-gray-100">
                                            <th colspan="2" class="text-uppercase text-sm font-weight-bolder opacity-7 ps-3">Total Rincian (Rp)</th>
                                            {{-- ID 'total-rincian-cell' digunakan oleh JS --}}
                                            <th id="total-rincian-cell" class="text-center text-uppercase text-sm font-weight-bolder opacity-7">0</th>
                                            <th></th>
                                        </tr>
                                        {{-- Peringatan (dikelola JS) --}}
                                        <tr id="row-warning-balance" class="bg-danger" style="display: none;">
                                            <th colspan="4" class="text-center text-uppercase text-sm font-weight-bolder text-white">
                                                PERINGATAN: Total Rincian (Rp <span id="warn-total-rincian">0</span>) tidak sama dengan Total Form (Rp <span id="warn-total-form">0</span>)!
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================================= --}}
            {{-- 3. CARD LAMPIRAN (FITUR BARU) --}}
            {{-- ========================================================================================= --}}
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header pb-0">
                            <h6 class="mb-0">Lampiran Transaksi</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-uppercase text-sm">Unggah Lampiran (Opsional)</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="lampiran-file-input" class="form-control-label">Pilih File (Bisa lebih dari satu)</label>
                                        {{-- 'multiple' => mengizinkan banyak file --}}
                                        {{-- 'name="lampiran[]"' => mengirim sebagai array --}}
                                        <input class="form-control" type="file" id="lampiran-file-input" name="lampiran[]" multiple>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                     <div class="form-group">
                                        <label for="lampiran-type-input" class="form-control-label">Tipe Lampiran (Berlaku untuk semua file)</label>
                                        <input class="form-control" type="text" id="lampiran-type-input" name="attachment_type" placeholder="Cth: Invoice, PO, Nota Dinas">
                                    </div>
                                </div>
                            </div>

                            {{-- Daftar file yang dipilih (dikelola JS) --}}
                            <p class="text-uppercase text-sm">File yang akan diunggah:</p>
                            <ul id="file-preview-list" class="list-group">
                                <li id="item-empty-lampiran" class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                    <h6 class="mb-0 text-secondary">Belum ada file dipilih.</h6>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================================= --}}
            {{-- 4. CARD TOMBOL AKSI (Simpan Draft / Submit) --}}
            {{-- ========================================================================================= --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body d-flex justify-content-end">
                            {{-- Tombol 'Simpan Draft' --}}
                            <button type="submit" id="btn-simpan-draft" class="btn btn-outline-secondary ms-2" disabled>Simpan Draft</button>

                            {{-- Tombol 'Submit Pengajuan' --}}
                            <button type="submit" id="btn-submit-pengajuan" class="btn btn-success ms-2" disabled>Submit Pengajuan</button>
                        </div>
                    </div>
                </div>
            </div>

        </form> {{-- Penutup </form> utama --}}

        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
    {{-- 1. CDN untuk AutoNumeric (Format Nominal) --}}
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0"></script>
    {{-- 2. CDN untuk SweetAlert (Konfirmasi Hapus) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ==================================================================
            // INISIALISASI VARIABEL & AUTONUMERIC
            // ==================================================================

            // Opsi AutoNumeric
            const autoNumericOptions = {
                decimalCharacter: ',',
                digitGroupSeparator: '.',
                decimalPlaces: 0,
                minimumValue: '0'
            };

            // Inisialisasi AutoNumeric pada field Total Form (READONLY)
            const anTotalForm = new AutoNumeric('#input-total-form', { ...autoNumericOptions, readOnly: true });
            const inputTotalFormRaw = document.getElementById('input-total-form-raw');

            // Inisialisasi AutoNumeric pada field Nominal Rincian
            const anRincian = new AutoNumeric('#input-nominal', autoNumericOptions);

            // Elemen Form Rincian
            const btnTambahItem = document.getElementById('btn-tambah-item');
            const inputPengakuan = document.getElementById('input-pengakuan');
            const inputNominal = document.getElementById('input-nominal');
            const inputKeterangan = document.getElementById('input-keterangan');

            // Elemen Tabel & Total
            const rincianTbody = document.getElementById('rincian-tbody');
            const rowEmpty = document.getElementById('row-empty');
            const totalRincianCell = document.getElementById('total-rincian-cell');
            const rowWarningBalance = document.getElementById('row-warning-balance');
            const warnTotalRincian = document.getElementById('warn-total-rincian');
            const warnTotalForm = document.getElementById('warn-total-form'); // Tidak ada, tapi kita biarkan

            // Elemen Form Utama & Tombol Submit
            const mainForm = document.getElementById('main-transaksi-form');
            const inputUraian = document.getElementById('uraian_transaksi');
            const uraianError = document.getElementById('uraian-error');
            const btnSimpanDraft = document.getElementById('btn-simpan-draft');
            const btnSubmitPengajuan = document.getElementById('btn-submit-pengajuan');
            const hiddenSubmitAction = document.getElementById('submit_action');

            // Elemen Lampiran (BARU)
            const inputLampiran = document.getElementById('lampiran-file-input');
            const filePreviewList = document.getElementById('file-preview-list');
            const itemEmptyLampiran = document.getElementById('item-empty-lampiran');

            // Variabel state
            let totalRincian = 0;
            let itemCounter = 0; // Untuk ID unik hidden input

            // ==================================================================
            // FUNGSI HELPER
            // ==================================================================

            // Fungsi Format Rupiah (untuk tampilan tabel)
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'decimal',
                    minimumFractionDigits: 0
                }).format(number);
            }

            // Fungsi Update Total & Validasi
            function updateTotalsAndValidation() {
                // 1. Hitung ulang total rincian dari hidden inputs
                totalRincian = 0;
                const rincianNominals = document.querySelectorAll('.hidden-nominal');
                rincianNominals.forEach(input => {
                    totalRincian += parseFloat(input.value) || 0;
                });

                // 2. Update Total Rincian (Tampilan)
                totalRincianCell.textContent = formatRupiah(totalRincian);

                // 3. (FITUR BARU) Update Total Form (Otomatis)
                anTotalForm.set(totalRincian);
                inputTotalFormRaw.value = totalRincian;

                // 4. Cek validasi balance
                // (Sekarang Total Form = Total Rincian, jadi kita hanya perlu cek jika > 0)
                const isBalanced = totalRincian > 0;
                const hasUraian = inputUraian.value.trim() !== '';

                // 5. Tampilkan/Sembunyikan peringatan
                // (Peringatan hanya muncul jika user coba submit tapi belum balance)
                // Kita sembunyikan saja karena sudah otomatis
                rowWarningBalance.style.display = 'none';

                // 6. Validasi Tombol "Simpan Draft" (Hanya butuh uraian)
                if (hasUraian) {
                    btnSimpanDraft.disabled = false;
                    uraianError.style.display = 'none';
                } else {
                    btnSimpanDraft.disabled = true;
                }

                // 7. Validasi Tombol "Submit Pengajuan" (Butuh uraian DAN balance > 0)
                if (hasUraian && isBalanced) {
                    btnSubmitPengajuan.disabled = false;
                } else {
                    btnSubmitPengajuan.disabled = true;
                }
            }

            // Fungsi Tambah Item ke Tabel
            function addItemToTable(pengakuan, nominal, keterangan) {
                // Hapus baris "kosong" jika ada
                const rowEmptyEl = document.getElementById('row-empty');
                if (rowEmptyEl) {
                    rowEmptyEl.remove();
                }

                itemCounter++;
                const newRow = rincianTbody.insertRow();
                // (PERBAIKAN NAMA INPUT)
                newRow.innerHTML = `
                    <td>
                        <p class="text-sm font-weight-bold mb-0 px-3">${pengakuan}</p>
                        <input type="hidden" name="details[${itemCounter}][pengakuan_transaksi]" value="${pengakuan}">
                    </td>
                    <td>
                        <p class="text-sm mb-0">${keterangan || '-'}</p>
                        <input type="hidden" name="details[${itemCounter}][keterangan_detail]" value="${keterangan || ''}">
                    </td>
                    <td class="text-center">
                        <span class="text-sm font-weight-bold">Rp ${formatRupiah(nominal)}</span>
                        <input type="hidden" class="hidden-nominal" name="details[${itemCounter}][nominal]" value="${nominal}">
                    </td>
                    <td class="align-middle">
                        <button type="button" class="btn btn-link text-danger font-weight-bold text-xs btn-hapus-item">
                            Hapus
                        </button>
                    </td>
                `;
            }

            // Fungsi Reset Form Rincian
            function resetFormRincian() {
                inputPengakuan.value = '';
                anRincian.set(null); // Reset AutoNumeric
                inputKeterangan.value = '';
                inputPengakuan.focus();
            }

            // ==================================================================
            // EVENT LISTENERS
            // ==================================================================

            // Listener untuk Tanggal Pengajuan
            document.querySelectorAll('input[name="tanggal_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.getElementById('tanggal_kustom_input_wrapper').style.display =
                        (this.value === 'custom') ? 'block' : 'none';
                });
            });

            // (DIHAPUS) Listener keyup di Total Form, karena sekarang readonly
            // anTotalForm.elements.input.addEventListener('keyup', updateTotalsAndValidation);

            // Listener untuk tombol "Tambah Item"
            btnTambahItem.addEventListener('click', function() {
                const pengakuan = inputPengakuan.value.trim();
                const nominal = anRincian.getNumber(); // Ambil angka mentah
                const keterangan = inputKeterangan.value.trim();

                // Validasi input tambah
                if (!pengakuan || !nominal || nominal <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Tidak Lengkap',
                        text: 'Pastikan "Pengakuan Transaksi" dan "Nominal" (lebih dari 0) sudah diisi.',
                    });
                    return;
                }

                addItemToTable(pengakuan, nominal, keterangan);
                resetFormRincian();
                updateTotalsAndValidation(); // (FUNGSI BARU)
            });

            // Listener untuk tombol "Hapus Item" (menggunakan event delegation)
            rincianTbody.addEventListener('click', function(event) {
                if (event.target.classList.contains('btn-hapus-item')) {
                    Swal.fire({
                        title: 'Anda yakin?',
                        text: "Anda akan menghapus item rincian ini.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        backdrop: 'rgba(0,0,0,0.4)' // Backdrop gelap
                    }).then((result) => {
                        if (result.isConfirmed) {
                            event.target.closest('tr').remove();
                            updateTotalsAndValidation(); // (FUNGSI BARU)

                            // Jika tabel jadi kosong, tampilkan lagi baris "empty"
                            if (rincianTbody.rows.length === 0) {
                                rincianTbody.innerHTML = `
                                    <tr id="row-empty">
                                        <td colspan="4" class="text-center py-3">
                                            <p class="mb-0 text-secondary">Belum ada rincian pengakuan transaksi.</p>
                                        </td>
                                    </tr>`;
                            }
                        }
                    });
                }
            });

            // Listener pada input Uraian (untuk validasi tombol real-time)
            inputUraian.addEventListener('keyup', updateTotalsAndValidation);

            // Listeners untuk tombol Submit Utama
            btnSimpanDraft.addEventListener('click', function(event) {
                hiddenSubmitAction.value = 'draft';
                // Validasi ulang sebelum kirim
                if (inputUraian.value.trim() === '') {
                    event.preventDefault(); // Hentikan submit
                    uraianError.style.display = 'block';
                    inputUraian.focus();
                    Swal.fire('Error', 'Uraian Transaksi wajib diisi untuk menyimpan Draft.', 'error');
                }
            });

            btnSubmitPengajuan.addEventListener('click', function(event) {
                hiddenSubmitAction.value = 'submit';
                // Validasi ulang sebelum kirim
                if (btnSubmitPengajuan.disabled) {
                    event.preventDefault(); // Hentikan submit
                    Swal.fire('Error', 'Data belum valid. Pastikan Uraian diisi dan Total Rincian lebih dari 0.', 'error');
                }
            });

            // (FITUR BARU) Event Listener Input Lampiran
            inputLampiran.addEventListener('change', function() {
                filePreviewList.innerHTML = ''; // Kosongkan list
                if (this.files.length > 0) {
                    Array.from(this.files).forEach(file => {
                        const li = document.createElement('li');
                        li.className = 'list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg';
                        li.innerHTML = `
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                    <i class="ni ni-single-copy-04 text-white opacity-10"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark text-sm">${file.name}</h6>
                                    <span class="text-xs">${(file.size / (1024*1024)).toFixed(2)} MB</span>
                                </div>
                            </div>
                        `;
                        filePreviewList.appendChild(li);
                    });
                } else {
                    filePreviewList.appendChild(itemEmptyLampiran);
                }
            });

            // Jalankan validasi saat halaman pertama kali dimuat (jika ada old value)
            updateTotalsAndValidation();

        });
    </script>
@endpush

