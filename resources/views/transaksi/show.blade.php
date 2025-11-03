@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

{{--
    Catatan:
    File ini adalah file 'show' (Detail) yang canggih.
    Dia menangani 5 hal:
    1. Menampilkan data utama (Card 1)
    2. Menampilkan & mengelola Rincian (Card 2) -> (AJAX untuk Tambah/Hapus)
    3. Menampilkan & mengelola Lampiran (Card 3) -> (AJAX untuk Upload/Hapus)
    4. Menampilkan Aksi untuk Pemohon (Card 4) -> (Tombol Submit Draft)
    5. Menampilkan Aksi untuk Approver (Card 5) -> (Tombol Approve/Reject)
    6. Menampilkan Riwayat/History (Card 6)
--}}

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Detail Permohonan Transaksi'])

    <div class="container-fluid py-4">
        <div class="row">

            {{-- Card 1: Detail Utama (Read-Only) --}}
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Detail Transaksi (ID: {{ $transaksiForm->id }})</h6>
                            <a href="{{ route('list-permohonan') }}" class="btn btn-dark btn-sm ms-auto">
                                <i class="fa fa-arrow-left me-1"></i>
                                Kembali ke Daftar
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Baris 1: Info Pemohon & Perusahaan (Read-only) --}}
                        <p class="text-uppercase text-sm">Informasi Pemohon</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pemohon_name" class="form-control-label">Nama Pemohon</label>
                                    <input class="form-control" type="text" id="pemohon_name"
                                        value="{{ $transaksiForm->pemohon->name ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="perusahaan_nama" class="form-control-label">Nama Perusahaan</label>
                                    <input class="form-control" type="text" id="perusahaan_nama"
                                        value="{{ $transaksiForm->perusahaan->nama_perusahaan ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_pengajuan" class="form-control-label">Tanggal Pengajuan</label>
                                    <input class="form-control" type="text" id="tanggal_pengajuan"
                                        value="{{ $transaksiForm->tanggal_pengajuan->format('d M Y H:i:s') }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status" class="form-control-label">Status Saat Ini</label>
                                    <input class="form-control" type="text" id="status"
                                        value="{{ $transaksiForm->status }}" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- Baris 2: Info Transaksi --}}
                        <p class="text-uppercase text-sm">Detail Transaksi</p>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="uraian_transaksi" class="form-control-label">Uraian Transaksi</label>
                                    <textarea class="form-control" id="uraian_transaksi" rows="3"
                                        disabled>{{ $transaksiForm->uraian_transaksi }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dasar_transaksi" class="form-control-label">Dasar Transaksi</label>
                                    <input class="form-control" type="text" id="dasar_transaksi"
                                        value="{{ $transaksiForm->dasar_transaksi }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lawan_transaksi" class="form-control-label">Lawan Transaksi</label>
                                    <input class="form-control" type="text" id="lawan_transaksi"
                                        value="{{ $transaksiForm->lawan_transaksi }}" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- Baris 3: Info Pembayaran --}}
                        <p class="text-uppercase text-sm">Detail Pembayaran</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_nominal_display" class="form-control-label">Total Nominal</label>
                                    <input class="form-control" type="text" id="total_nominal_display"
                                        value="Rp {{ number_format($transaksiForm->total_nominal, 0, ',', '.') }}" disabled>
                                    {{-- Data asli untuk JS --}}
                                    <input type="hidden" id="total-form-nominal-raw" value="{{ $transaksiForm->total_nominal }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rekening_transaksi" class="form-control-label">Rekening Transaksi</label>
                                    <input class="form-control" type="text" id="rekening_transaksi"
                                        value="{{ $transaksiForm->rekening_transaksi }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rencana_tanggal_transaksi" class="form-control-label">Rencana Tgl.
                                        Transaksi</label>
                                    <input class="form-control" type="text" id="rencana_tanggal_transaksi"
                                        value="{{ $transaksiForm->rencana_tanggal_transaksi ? \Carbon\Carbon::parse($transaksiForm->rencana_tanggal_transaksi)->format('d M Y') : 'N/A' }}"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="keterangan_form" class="form-control-label">Keterangan Tambahan</label>
                                    <textarea class="form-control" id="keterangan_form" rows="2"
                                        disabled>{{ $transaksiForm->keterangan_form ?? '-' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Rincian Pengakuan Transaksi (AJAX) --}}
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Rincian Pengakuan Transaksi</h6>
                    </div>
                    <div class="card-body">

                        {{-- Container untuk notifikasi error AJAX --}}
                        <div id="error-ajax-container" class="alert alert-danger" role="alert" style="display: none;">
                            <strong class="text-white">Gagal!</strong>
                            <ul id="error-ajax-list" class="mb-0">
                                <!-- Error akan diisi oleh JS -->
                            </ul>
                        </div>

                        {{-- 1. FORM UNTUK MENAMBAH ITEM BARU --}}
                        {{-- Hanya tampilkan jika status masih 'Draft' --}}
                        @if ($transaksiForm->status == 'Draft')
                            {{-- Form ini di-handle oleh AJAX --}}
                            <form id="form-tambah-detail"
                                action="{{ route('permohonan.detail.store', $transaksiForm) }}" method="POST">
                                @csrf
                                <p class="text-uppercase text-sm">Tambah Item Rincian</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="pengakuan_transaksi" class="form-control-label">Pengakuan Transaksi</label>
                                            <input class="form-control" type="text" id="pengakuan_transaksi"
                                                name="pengakuan_transaksi" placeholder="Cth: Biaya Akomodasi" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="input-nominal-rincian" class="form-control-label">Nominal</label>
                                            {{-- Input ini akan diformat oleh AutoNumeric --}}
                                            <input class="form-control" type="text" id="input-nominal-rincian">
                                            {{-- Input tersembunyi untuk angka mentah --}}
                                            <input type="hidden" id="input-nominal-rincian-raw" name="nominal">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="keterangan_detail" class="form-control-label">Keterangan Item</label>
                                            <input class="form-control" type="text" id="keterangan_detail"
                                                name="keterangan_detail" placeholder="Cth: Hotel 5 malam">
                                        </div>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-sm" id="btn-tambah-detail">Tambah</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <hr class="horizontal dark">
                        @endif

                        {{-- 2. TABEL DAFTAR ITEM YANG SUDAH ADA --}}
                        <p class="text-uppercase text-sm">Daftar Item</p>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Pengakuan Transaksi</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Keterangan</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Nominal</th>
                                        @if ($transaksiForm->status == 'Draft')
                                            <th class="text-secondary opacity-7"></th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody id="rincian-table-body">
                                    @forelse ($transaksiForm->details as $detail)
                                        <tr data-id="{{ $detail->id }}">
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 px-3">{{ $detail->pengakuan_transaksi }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $detail->keterangan_detail ?? '-' }}</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-sm font-weight-bold" data-nominal="{{ $detail->nominal }}">
                                                    Rp {{ number_format($detail->nominal, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            @if ($transaksiForm->status == 'Draft')
                                                <td class="align-middle">
                                                    {{-- Tombol Hapus AJAX --}}
                                                    <button type="button" class="btn btn-link text-danger font-weight-bold text-xs btn-hapus-detail"
                                                            data-id="{{ $detail->id }}"
                                                            data-url="{{ route('permohonan.detail.destroy', $detail) }}"
                                                            data-toggle="tooltip" data-original-title="Hapus item">
                                                        Hapus
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr id="row-empty">
                                            <td colspan="4" class="text-center py-3">
                                                <p class="mb-0 text-secondary">Belum ada rincian pengakuan transaksi.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                {{-- Footer Total --}}
                                <tfoot>
                                    <tr class="bg-gray-100">
                                        <th colspan="2"
                                            class="text-uppercase text-sm font-weight-bolder opacity-7 ps-3">Total
                                            Rincian</th>
                                        <th id="total-rincian-cell"
                                            class="text-center text-uppercase text-sm font-weight-bolder opacity-7">
                                            Rp {{ number_format($transaksiForm->details->sum('nominal'), 0, ',', '.') }}
                                        </th>
                                        @if ($transaksiForm->status == 'Draft')
                                            <th></th>
                                        @endif
                                    </tr>
                                    <tr class="bg-gray-200">
                                        <th colspan="2"
                                            class="text-uppercase text-sm font-weight-bolder opacity-7 ps-3">Total Form
                                        </th>
                                        <th id="total-form-cell"
                                            class="text-center text-uppercase text-sm font-weight-bolder opacity-7">
                                            Rp {{ number_format($transaksiForm->total_nominal, 0, ',', '.') }}
                                        </th>
                                        @if ($transaksiForm->status == 'Draft')
                                            <th></th>
                                        @endif
                                    </tr>
                                    {{-- Validasi Total --}}
                                    <tr id="row-peringatan-total"
                                        class="{{ $transaksiForm->details->sum('nominal') != $transaksiForm->total_nominal ? 'bg-danger' : '' }}"
                                        style="{{ $transaksiForm->details->sum('nominal') == $transaksiForm->total_nominal ? 'display: none;' : '' }}">
                                        <th id="text-peringatan-total" colspan="4"
                                            class="text-center text-uppercase text-sm font-weight-bolder text-white">
                                            PERINGATAN: Total rincian (Rp {{ number_format($transaksiForm->details->sum('nominal'), 0, ',', '.') }}) tidak sama dengan Total Form (Rp {{ number_format($transaksiForm->total_nominal, 0, ',', '.') }})!
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Lampiran Transaksi --}}
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Lampiran Transaksi</h6>
                    </div>
                    <div class="card-body">
                        {{-- Notifikasi Error AJAX untuk Lampiran --}}
                        <div id="error-lampiran-container" class="alert alert-danger" role="alert" style="display: none;">
                            <strong class="text-white">Gagal Mengunggah!</strong>
                            <p id="error-lampiran-message" class="mb-0"></p>
                        </div>

                        {{-- Form Upload (Hanya untuk role tertentu) --}}
                        @php
                            $user = Auth::user();
                            $userRole = $user->role->role_name ?? null;
                            $canUpload = false;

                            // Pemohon bisa upload jika status belum final
                            if ($transaksiForm->pemohon_id == $user->id && !in_array($transaksiForm->status, ['Disetujui BO', 'Ditolak'])) {
                                $canUpload = true;
                            }
                            // Approver (PYB, BO) & Admin bisa upload kapan saja
                            if (in_array($userRole, ['PYB1', 'PYB2', 'BO', 'Admin'])) {
                                $canUpload = true;
                            }
                        @endphp

                        @if ($canUpload)
                        <form id="form-upload-lampiran" action="{{ route('lampiran.store', $transaksiForm) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <p class="text-uppercase text-sm">Unggah Lampiran Baru</p>

                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="lampiran-file-input" class="form-control-label">Pilih File (Max: 10MB)</label>
                                        <input class="form-control" type="file" id="lampiran-file-input" name="lampiran" required>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="lampiran-type-input" class="form-control-label">Tipe Lampiran</label>
                                        <input class="form-control" type="text" id="lampiran-type-input" name="attachment_type" placeholder="Cth: Invoice, PO, Nota Dinas">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary btn-sm" id="btn-upload-lampiran">
                                            <i class="fa fa-upload me-1"></i> Unggah
                                        </button>
                                        {{-- Indikator Loading --}}
                                        <span id="loading-lampiran-spinner" class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display: none;"></span>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <hr class="horizontal dark">
                        @endif

                        {{-- Daftar Lampiran yang sudah ada --}}
                        <p class="text-uppercase text-sm">Daftar Lampiran</p>
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama File</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipe</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pengunggah</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody id="lampiran-list-body">
                                    @forelse ($transaksiForm->attachments as $attachment)
                                        <tr data-id="lampiran-{{ $attachment->id }}">
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 px-3">{{ $attachment->file_name }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $attachment->attachment_type }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $attachment->uploader->name ?? 'N/A' }}</p>
                                            </td>
                                            <td class="align-middle text-end">
                                                <a href="{{ route('lampiran.download', $attachment) }}" class="btn btn-link text-dark font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Download">
                                                    Download
                                                </a>

                                                {{-- Otorisasi tombol hapus --}}
                                                @php
                                                    $canDelete = false;
                                                    if ($user->role->role_name == 'Admin' || $attachment->uploaded_by == $user->id) {
                                                        if ($user->role->role_name == 'Pemohon' && $transaksiForm->status != 'Draft') {
                                                            $canDelete = false; // Pemohon tdk bisa hapus jika sdh di-submit
                                                        } else {
                                                            $canDelete = true;
                                                        }
                                                    }
                                                @endphp

                                                @if($canDelete)
                                                <button type="button" class="btn btn-link text-danger font-weight-bold text-xs btn-hapus-lampiran"
                                                        data-url="{{ route('lampiran.destroy', $attachment) }}"
                                                        data-id="{{ $attachment->id }}"
                                                        data-toggle="tooltip" data-original-title="Hapus lampiran">
                                                    Hapus
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="lampiran-row-empty">
                                            <td colspan="4" class="text-center py-3">
                                                <p class="mb-0 text-secondary">Belum ada lampiran.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{--
                CARD AKSI
                Muncul bergantian tergantung status dan role
            --}}

            {{-- Card 4: Aksi Pemohon (Hanya muncul jika status 'Draft' DAN user adalah Pemohon) --}}
            @if ($transaksiForm->status == 'Draft' && $transaksiForm->pemohon_id == Auth::id())
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Aksi Pemohon</h6>
                    </div>
                    <div class="card-body d-flex justify-content-end">
                        <form action="{{ route('permohonan.submit', $transaksiForm) }}" method="POST">
                            @csrf
                            {{-- Tombol Submit ini dikontrol oleh JS --}}
                            <button type="submit" class="btn btn-success ms-2" id="btn-submit-pengajuan"
                                {{ ($transaksiForm->details->sum('nominal') != $transaksiForm->total_nominal || $transaksiForm->details->count() == 0) ? 'disabled' : '' }}
                                title="Tombol akan aktif jika Total Rincian == Total Form dan rincian tidak kosong.">
                                Submit Pengajuan
                            </button>
                            <a href="{{ route('list-permohonan') }}" class="btn btn-outline-secondary ms-2">Simpan & Tutup</a>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            {{-- Card 5: Aksi Approval (Hanya muncul jika status BUKAN 'Draft'/'Ditolak' DAN user adalah Approver yg tepat) --}}
            @php
                $showApprovalBox = false;
                if ($transaksiForm->status == 'Diajukan' && $userRole == 'PYB1') $showApprovalBox = true;
                if ($transaksiForm->status == 'Disetujui PYB1' && $userRole == 'PYB2') $showApprovalBox = true;
                if ($transaksiForm->status == 'Disetujui PYB2' && $userRole == 'BO') $showApprovalBox = true;
            @endphp

            @if ($showApprovalBox)
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Aksi Persetujuan ({{ $userRole }})</h6>
                    </div>
                    <div class="card-body d-flex justify-content-end">
                        {{-- Form Approve --}}
                        <form action="{{ route('permohonan.approve', $transaksiForm) }}" method="POST" class="ms-2">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-check me-1"></i>
                                Approve
                            </button>
                        </form>

                        {{-- Form Reject (Pakai SweetAlert) --}}
                        <form id="form-reject" action="{{ route('permohonan.reject', $transaksiForm) }}" method="POST" class="ms-2">
                            @csrf
                            <input type="hidden" name="remarks" id="reject-remarks-input">
                            <button type="button" class="btn btn-danger" id="btn-reject">
                                <i class="fa fa-times me-1"></i>
                                Reject
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif


            {{-- Card 6: Riwayat Transaksi --}}
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Riwayat Transaksi</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">User</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Aksi</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catatan (Remarks)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Perubahan Status</th>
                                    </tr>
                                </thead>
                                <tbody id="history-table-body">
                                    @forelse ($transaksiForm->history as $item)
                                        <tr data-history-id="{{ $item->id }}">
                                            <td>
                                                <p class="text-sm font-weight-bold mb-0 px-3">{{ $item->created_at->format('d M Y H:i:s') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $item->user->name ?? 'Sistem' }}</p>
                                            </td>
                                            <td>
                                                <span class="badge badge-sm
                                                    {{ $item->action == 'Disetujui' ? 'bg-gradient-success' : '' }}
                                                    {{ $item->action == 'Ditolak' ? 'bg-gradient-danger' : '' }}
                                                    {{ str_contains($item->action, 'Pengajuan') ? 'bg-gradient-info' : '' }}
                                                    {{ str_contains($item->action, 'Dibuat') ? 'bg-gradient-secondary' : '' }}
                                                ">{{ $item->action }}</span>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">{{ $item->remarks ?? '-' }}</p>
                                            </td>
                                            <td>
                                                <p class="text-sm mb-0">
                                                    @if($item->from_status)
                                                    <span class="badge badge-sm bg-gradient-secondary">{{ $item->from_status }}</span>
                                                    <i class="fa fa-arrow-right mx-1"></i>
                                                    <span class="badge badge-sm bg-gradient-primary">{{ $item->to_status }}</span>
                                                    @else
                                                    <span class="badge badge-sm bg-gradient-primary">{{ $item->to_status }}</span>
                                                    @endif
                                                </p>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr id="history-row-empty">
                                            <td colspan="5" class="text-center py-3">
                                                <p class="mb-0 text-secondary">Belum ada riwayat untuk transaksi ini.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

{{--
    SECTION SCRIPT KHUSUS UNTUK HALAMAN INI
    (AJAX, SweetAlert, AutoNumeric)
--}}
@push('js')
    {{-- 1. CDN Library --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.6.0"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // ===================================================================
            // Inisialisasi Variabel Global & Library
            // ===================================================================
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const totalFormRaw = parseFloat(document.getElementById('total-form-nominal-raw').value) || 0;
            let totalRincian = {{ $transaksiForm->details->sum('nominal') }}; // Ambil total awal dari PHP

            // Inisialisasi AutoNumeric untuk input rincian
            let anRincian = null;
            const inputRincian = document.getElementById('input-nominal-rincian');
            if(inputRincian) {
                anRincian = new AutoNumeric(inputRincian, {
                    decimalCharacter: ',',
                    digitGroupSeparator: '.',
                    // currencySymbol: 'Rp ', // Kita hilangkan agar lebih bersih
                    // currencySymbolPlacement: 'p',
                    minimumValue: '0'
                });
            }

            // ===================================================================
            // Helper Functions (Fungsi Bantuan)
            // ===================================================================

            // Fungsi untuk format angka ke Rupiah
            function formatRupiah(number) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(number);
            }

            // Fungsi untuk menampilkan error AJAX
            function showAjaxErrors(errors, title = 'Validasi Gagal!') {
                const errorContainer = document.getElementById('error-ajax-container');
                const errorList = document.getElementById('error-ajax-list');
                errorList.innerHTML = ''; // Kosongkan list

                if (typeof errors === 'object') {
                    // Jika error adalah object (dari validasi Laravel)
                    for (const key in errors) {
                        errors[key].forEach(message => {
                            const li = document.createElement('li');
                            li.textContent = message;
                            errorList.appendChild(li);
                        });
                    }
                    Swal.fire({
                        icon: 'error',
                        title: title,
                        html: errorList.innerHTML,
                        backdrop: 'rgba(0,0,0,0.4)'
                    });
                } else {
                    // Jika error adalah string
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kesalahan',
                        text: errors,
                        backdrop: 'rgba(0,0,0,0.4)'
                    });
                }
            }

            // Fungsi untuk menghitung ulang total dan validasi
            function updateTotals() {
                // Hitung ulang totalRincian dari tabel
                totalRincian = 0;
                document.querySelectorAll('#rincian-table-body tr[data-id]').forEach(row => {
                    const nominalEl = row.querySelector('span[data-nominal]');
                    if (nominalEl) {
                        totalRincian += parseFloat(nominalEl.dataset.nominal) || 0;
                    }
                });

                // Update tampilan Total Rincian
                document.getElementById('total-rincian-cell').textContent = formatRupiah(totalRincian);

                // Validasi Balance
                const rowPeringatan = document.getElementById('row-peringatan-total');
                const textPeringatan = document.getElementById('text-peringatan-total');
                const btnSubmit = document.getElementById('btn-submit-pengajuan');
                const rincianCount = document.querySelectorAll('#rincian-table-body tr[data-id]').length;

                if (totalRincian.toFixed(2) != totalFormRaw.toFixed(2)) {
                    // Jika TIDAK balance
                    textPeringatan.textContent = `PERINGATAN: Total Rincian (${formatRupiah(totalRincian)}) tidak sama dengan Total Form (${formatRupiah(totalFormRaw)})!`;
                    rowPeringatan.style.display = '';
                    rowPeringatan.classList.add('bg-danger');
                    if (btnSubmit) btnSubmit.disabled = true;
                } else if (rincianCount === 0) {
                     // Jika balance tapi KOSONG
                    textPeringatan.textContent = `PERINGATAN: Rincian transaksi tidak boleh kosong.`;
                    rowPeringatan.style.display = '';
                    rowPeringatan.classList.remove('bg-danger'); // Mungkin tidak merah, tapi tetap warning
                    rowPeringatan.classList.add('bg-warning', 'text-dark');
                    if (btnSubmit) btnSubmit.disabled = true;
                } else {
                    // Jika BALANCE dan TIDAK KOSONG
                    rowPeringatan.style.display = 'none';
                    if (btnSubmit) btnSubmit.disabled = false;
                }
            }

            // Fungsi untuk menambah baris baru ke tabel Rincian
            function addNewRow(detail) {
                // Hapus row 'empty' jika ada
                document.getElementById('row-empty')?.remove();

                const tableBody = document.getElementById('rincian-table-body');
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-id', detail.id);
                newRow.innerHTML = `
                    <td>
                        <p class="text-sm font-weight-bold mb-0 px-3">${detail.pengakuan_transaksi}</p>
                    </td>
                    <td>
                        <p class="text-sm mb-0">${detail.keterangan_detail || '-'}</p>
                    </td>
                    <td class="text-center">
                        <span class="text-sm font-weight-bold" data-nominal="${detail.nominal}">
                            ${formatRupiah(detail.nominal)}
                        </span>
                    </td>
                    <td class="align-middle">
                        <button type="button" class="btn btn-link text-danger font-weight-bold text-xs btn-hapus-detail"
                                data-id="${detail.id}"
                                data-url="/permohonan-detail/destroy/${detail.id}"
                                data-toggle="tooltip" data-original-title="Hapus item">
                            Hapus
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);

                // Tambahkan event listener ke tombol Hapus yang baru dibuat
                newRow.querySelector('.btn-hapus-detail').addEventListener('click', handleHapusRincian);
            }


            // ===================================================================
            // Event Listeners (Rincian)
            // ===================================================================

            // 1. Handle "Tambah" Item (AJAX)
            const formTambah = document.getElementById('form-tambah-detail');
            if (formTambah) {
                formTambah.addEventListener('submit', function(event) {
                    event.preventDefault(); // Mencegah reload halaman

                    // Masukkan angka mentah ke hidden input
                    document.getElementById('input-nominal-rincian-raw').value = anRincian.getNumber();

                    const formData = new FormData(this);
                    const url = this.action;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json', // Minta balasan JSON
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.detail) {
                            // Sukses!
                            addNewRow(data.detail); // Tambah baris baru
                            updateTotals(); // Hitung ulang total
                            this.reset(); // Kosongkan form
                            anRincian.set(null); // Kosongkan AutoNumeric
                            document.getElementById('error-ajax-container').style.display = 'none';
                        } else if (data.errors) {
                            // Gagal validasi
                            showAjaxErrors(data.errors);
                        } else {
                            // Gagal lainnya
                            showAjaxErrors(data.error || 'Terjadi kesalahan tidak diketahui.');
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        showAjaxErrors('Tidak dapat terhubung ke server. Silakan coba lagi.');
                    });
                });
            }

            // 2. Handle "Hapus" Item (AJAX)
            // Fungsi ini akan di-attach ke semua tombol hapus
            function handleHapusRincian(event) {
                const button = event.currentTarget;
                const url = button.dataset.url;
                const row = button.closest('tr');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Anda akan menghapus item rincian ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    backdrop: 'rgba(0,0,0,0.4)' // Backdrop gelap
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim request Hapus
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Sukses! Hapus baris dari tabel
                                row.remove();
                                updateTotals(); // Hitung ulang total
                                Swal.fire(
                                    'Dihapus!',
                                    'Item rincian telah dihapus.',
                                    'success'
                                );
                                // Cek jika tabel jadi kosong
                                if (document.querySelectorAll('#rincian-table-body tr[data-id]').length === 0) {
                                    document.getElementById('rincian-table-body').innerHTML =
                                        '<tr id="row-empty"><td colspan="4" class="text-center py-3"><p class="mb-0 text-secondary">Belum ada rincian pengakuan transaksi.</p></td></tr>';
                                    updateTotals(); // Update lagi agar tombol submit nonaktif
                                }
                            } else {
                                // Gagal
                                showAjaxErrors(data.error || 'Gagal menghapus data.');
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Error:', error);
                            showAjaxErrors('Tidak dapat terhubung ke server.');
                        });
                    }
                });
            }
            // Terapkan listener Hapus ke tombol yang sudah ada saat load
            document.querySelectorAll('.btn-hapus-detail').forEach(button => {
                button.addEventListener('click', handleHapusRincian);
            });

            // ===================================================================
            // Event Listeners (Lampiran)
            // ===================================================================
            const formUpload = document.getElementById('form-upload-lampiran');
            const btnUpload = document.getElementById('btn-upload-lampiran');
            const loadingSpinner = document.getElementById('loading-lampiran-spinner');
            const errorContainer = document.getElementById('error-lampiran-container');
            const errorMessage = document.getElementById('error-lampiran-message');

            if (formUpload) {
                formUpload.addEventListener('submit', function(event) {
                    event.preventDefault(); // Mencegah submit form standar

                    btnUpload.disabled = true;
                    loadingSpinner.style.display = 'inline-block';
                    errorContainer.style.display = 'none';

                    const formData = new FormData(this);
                    const url = this.action;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.attachment) {
                            // Sukses!
                            addNewLampiranRow(data.attachment);
                            this.reset(); // Kosongkan form upload
                            Swal.fire('Sukses!', 'Lampiran berhasil diunggah.', 'success');
                        } else if (data.errors) {
                            // Gagal validasi
                            let errorMsg = Object.values(data.errors).flat().join('<br>');
                            errorMessage.innerHTML = errorMsg;
                            errorContainer.style.display = 'block';
                        } else {
                            // Gagal lainnya
                            errorMessage.textContent = data.message || 'Terjadi kesalahan tidak diketahui.';
                            errorContainer.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Fetch Error:', error);
                        errorMessage.textContent = 'Tidak dapat terhubung ke server.';
                        errorContainer.style.display = 'block';
                    })
                    .finally(() => {
                        // Kembalikan tombol ke normal
                        btnUpload.disabled = false;
                        loadingSpinner.style.display = 'none';
                    });
                });
            }

            // Fungsi untuk menambah baris baru di tabel lampiran
            function addNewLampiranRow(attachment) {
                document.getElementById('lampiran-row-empty')?.remove();

                const tableBody = document.getElementById('lampiran-list-body');
                const newRow = document.createElement('tr');
                newRow.setAttribute('data-id', `lampiran-${attachment.id}`);
                newRow.innerHTML = `
                    <td>
                        <p class="text-sm font-weight-bold mb-0 px-3">${attachment.file_name}</p>
                    </td>
                    <td>
                        <p class="text-sm mb-0">${attachment.attachment_type}</p>
                    </td>
                    <td>
                        <p class="text-sm mb-0">${attachment.uploader}</p>
                    </td>
                    <td class="align-middle text-end">
                        <a href="${attachment.download_url}" class="btn btn-link text-dark font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Download">
                            Download
                        </a>
                        <button type="button" class="btn btn-link text-danger font-weight-bold text-xs btn-hapus-lampiran"
                                data-url="${attachment.delete_url}"
                                data-id="${attachment.id}"
                                data-toggle="tooltip" data-original-title="Hapus lampiran">
                            Hapus
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);

                // Tambahkan listener ke tombol Hapus yang baru
                newRow.querySelector('.btn-hapus-lampiran').addEventListener('click', handleHapusLampiran);
            }

            // Fungsi untuk handle hapus lampiran (AJAX)
            function handleHapusLampiran(event) {
                const button = event.currentTarget;
                const url = button.dataset.url;
                const row = button.closest('tr');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Anda akan menghapus file lampiran ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                    backdrop: 'rgba(0,0,0,0.4)'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                row.remove(); // Hapus baris dari tabel
                                Swal.fire('Dihapus!', data.message, 'success');
                                // Cek jika tabel jadi kosong
                                if (document.querySelectorAll('#lampiran-list-body tr[data-id]').length === 0) {
                                    document.getElementById('lampiran-list-body').innerHTML =
                                        '<tr id="lampiran-row-empty"><td colspan="4" class="text-center py-3"><p class="mb-0 text-secondary">Belum ada lampiran.</p></td></tr>';
                                }
                            } else {
                                Swal.fire('Gagal!', data.error || 'Gagal menghapus file.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Fetch Error:', error);
                            Swal.fire('Error!', 'Tidak dapat terhubung ke server.', 'error');
                        });
                    }
                });
            }

            // Terapkan listener Hapus ke tombol lampiran yang sudah ada
            document.querySelectorAll('.btn-hapus-lampiran').forEach(button => {
                button.addEventListener('click', handleHapusLampiran);
            });


            // 3. Handle "Reject" (SweetAlert)
            const btnReject = document.getElementById('btn-reject');
            if (btnReject) {
                btnReject.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Tolak Pengajuan',
                        text: 'Silakan masukkan alasan penolakan (wajib diisi):',
                        input: 'textarea',
                        inputPlaceholder: 'Tulis alasan Anda di sini...',
                        inputAttributes: {
                            'aria-label': 'Tulis alasan Anda di sini'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Tolak Sekarang',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33',
                        backdrop: 'rgba(0,0,0,0.4)',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Alasan penolakan wajib diisi!'
                            }
                            if (value.length < 5) {
                                return 'Alasan harus lebih dari 5 karakter.'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Isi hidden input dengan alasan
                            document.getElementById('reject-remarks-input').value = result.value;
                            // Submit form reject
                            document.getElementById('form-reject').submit();
                        }
                    });
                });
            }

            // Panggil updateTotals() saat halaman pertama kali load untuk cek balance awal
            updateTotals();

        }); // Tutup DOMContentLoaded
    </script>
@endpush

