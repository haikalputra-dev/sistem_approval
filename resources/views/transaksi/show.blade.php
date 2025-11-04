@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Detail Permohonan Transaksi'])

    <div class="container-fluid py-4">
        <div class="row">
            {{-- =================================================================================== --}}
            {{-- CARD 1: FORM UTAMA (DATA TRANSAKSI) --}}
            {{-- =================================================================================== --}}
            <div class="col-12">
                <div class="card mb-4">
                    {{-- Card Header --}}
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Detail Transaksi (ID: {{ $transaksiForm->id }})</h6>
                            <a href="{{ route('permohonan.list') }}" class="btn btn-dark btn-sm ms-auto">
                                <i class="fa fa-arrow-left me-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="card-body">
                        {{-- BARIS 1: INFO PEMOHON --}}
                        <p class="text-uppercase text-sm">Informasi Pemohon</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Nama Pemohon</label>
                                    <input class="form-control" type="text"
                                        value="{{ $transaksiForm->pemohon->name ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Nama Perusahaan</label>
                                    <input class="form-control" type="text"
                                        value="{{ $transaksiForm->perusahaan->nama_perusahaan ?? 'N/A' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Tanggal Pengajuan</label>
                                    <input class="form-control" type="text"
                                        value="{{ $transaksiForm->tanggal_pengajuan->format('d M Y') }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Status Saat Ini</label>
                                    <input class="form-control" type="text"
                                        value="{{ $transaksiForm->status }}" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- BARIS 2: KATEGORI & URAIAN --}}
                        <p class="text-uppercase text-sm">Detail Uraian</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Kategori Uraian</label>
                                    <input class="form-control" type="text" value="{{ $transaksiForm->kategori_uraian ?? '-' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-control-label">Uraian Transaksi</label>
                                    <input class="form-control" type="text" value="{{ $transaksiForm->uraian_transaksi }}" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- BARIS 3: KATEGORI PENGAKUAN & NOMINAL --}}
                        <p class="text-uppercase text-sm">Detail Nominal</p>
                        <div class="row">
                             <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Kategori Pengakuan Transaksi</label>
                                    <input class="form-control" type="text" value="{{ $transaksiForm->kategori_pengakuan ?? '-' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Total Nominal</label>
                                    <input class="form-control" type="text"
                                        value="Rp {{ number_format($transaksiForm->total_nominal, 0, ',', '.') }}" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- BARIS 4: INFO PEMBAYARAN --}}
                        <p class="text-uppercase text-sm">Detail Pembayaran</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Lawan Transaksi</label>
                                    <input class="form-control" type="text" value="{{ $transaksiForm->lawan_transaksi ?? '-' }}" disabled>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Rekening Tujuan</label>
                                    <input class="form-control" type="text" value="{{ $transaksiForm->rekening_transaksi ?? '-' }}" disabled>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">

                        {{-- BARIS 5: DASAR TRANSAKSI (Logika Kustom) --}}
                        <p class="text-uppercase text-sm">Detail Dasar Transaksi</p>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-control-label">Tipe Dasar Transaksi</label>
                                    <input class="form-control" type="text" value="{{ Str::title(str_replace('_', ' ', $transaksiForm->tipe_dasar_transaksi ?? '-')) }}" disabled>
                                </div>
                            </div>

                            {{-- Hanya tampilkan Keterangan jika tipenya Pernyataan Direksi --}}
                            @if($transaksiForm->tipe_dasar_transaksi == 'pernyataan_direksi')
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="form-control-label">Keterangan Tambahan (Pernyataan Direksi)</label>
                                    <input class="form-control" type="text" value="{{ $transaksiForm->keterangan_dasar_transaksi ?? '-' }}" disabled>
                                </div>
                            </div>
                            @endif
                        </div>

                        <hr class="horizontal dark">

                        {{-- BARIS 6: TANGGAL & KETERANGAN LAIN --}}
                        <p class="text-uppercase text-sm">Keterangan Lainnya</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Rencana Tgl. Eksekusi Transaksi</label>
                                    <input class="form-control" type="text"
                                        value="{{ $transaksiForm->rencana_tanggal_transaksi ? \Carbon\Carbon::parse($transaksiForm->rencana_tanggal_transaksi)->format('d M Y') : 'N/A' }}"
                                        disabled>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-control-label">Keterangan Form</label>
                                    <textarea class="form-control" rows="3"
                                        disabled>{{ $transaksiForm->keterangan_form ?? '-' }}</textarea>
                                </div>
                            </div>
                        </div>

                    </div> {{-- End Card Body --}}
                </div>
            </div>

            {{-- =================================================================================== --}}
            {{-- CARD 2: KARTU RINCIAN (DIHAPUS SETELAH REVISI) --}}
            {{-- =================================================================================== --}}
            {{-- Bagian ini sengaja dihapus sesuai revisi --}}

            {{-- =================================================================================== --}}
            {{-- CARD 3: KARTU LAMPIRAN (Tetap Ada) --}}
            {{-- =================================================================================== --}}
            @include('transaksi.partials.show_attachments')

            {{-- =================================================================================== --}}
            {{-- CARD 4: AKSI PEMOHON (Tombol Submit Draft) --}}
            {{-- =================================================================================== --}}
            @include('transaksi.partials.show_action_pemohon')

            {{-- =================================================================================== --}}
            {{-- CARD 5: AKSI PERSETUJUAN (Approve/Reject) --}}
            {{-- =================================================================================== --}}
            @include('transaksi.partials.show_action_approval')

            {{-- =================================================================================== --}}
            {{-- CARD 6: RIWAYAT TRANSAKSI (Tetap Ada) --}}
            {{-- =================================================================================== --}}
            @include('transaksi.partials.show_history')

        </div>

        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
{{-- CDN untuk SweetAlert (Konfirmasi) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Script untuk Hapus Lampiran & Aksi Approval (AJAX) --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Setup CSRF token untuk semua request AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // ===================================================================
        // HAPUS LAMPIRAN (AJAX)
        // ===================================================================
        const attachmentTableBody = document.getElementById('attachment-table-body');
        if (attachmentTableBody) {
            attachmentTableBody.addEventListener('click', function (e) {
                // Target tombol hapus
                if (e.target.classList.contains('btn-delete-attachment')) {
                    e.preventDefault();
                    const button = e.target;
                    const deleteUrl = button.getAttribute('data-url');

                    Swal.fire({
                        title: 'Anda yakin?',
                        text: "Anda tidak akan bisa mengembalikan file ini!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#f5365c',
                        backdrop: 'rgba(0,0,0,0.4)'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(deleteUrl, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Hapus baris dari tabel
                                    button.closest('tr').remove();
                                    Swal.fire('Terhapus!', data.message, 'success');
                                } else {
                                    Swal.fire('Gagal!', data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error!', 'Terjadi kesalahan.', 'error');
                            });
                        }
                    });
                }
            });
        }

        // ===================================================================
        // UPLOAD LAMPIRAN (AJAX)
        // ===================================================================
        const attachmentForm = document.getElementById('form-upload-attachment');
        if (attachmentForm) {
            attachmentForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const url = this.action;

                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json' // Minta balasan JSON
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.html) {
                        // Tambahkan baris baru ke tabel
                        attachmentTableBody.insertAdjacentHTML('beforeend', data.html);
                        // Reset form
                        this.reset();
                        Swal.fire('Berhasil!', data.message, 'success');
                    } else if (!data.success && data.message) {
                         Swal.fire('Gagal!', data.message, 'error');
                    } else {
                        // Handle error validasi
                        let errorText = 'Validasi Gagal:<br>';
                        for (const key in data.errors) {
                            errorText += `- ${data.errors[key][0]}<br>`;
                        }
                        Swal.fire('Gagal!', errorText, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                });
            });
        }

        // ===================================================================
        // AKSI REJECT (Tombol Merah)
        // ===================================================================
        const rejectButton = document.getElementById('btn-reject');
        if (rejectButton) {
            rejectButton.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('form-action-approval');

                Swal.fire({
                    title: 'Tolak Permohonan',
                    input: 'textarea',
                    inputLabel: 'Alasan Penolakan (Wajib Diisi)',
                    inputPlaceholder: 'Ketik alasan Anda di sini...',
                    inputAttributes: {
                        'aria-label': 'Ketik alasan Anda di sini'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Tolak & Kirim',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#f5365c',
                    backdrop: 'rgba(0,0,0,0.4)',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Anda harus mengisi alasan penolakan!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Set remarks dan action, lalu submit
                        document.getElementById('approval-remarks').value = result.value;
                        document.getElementById('approval-action').value = 'reject';
                        form.submit();
                    }
                });
            });
        }

        // ===================================================================
        // AKSI APPROVE (Tombol Hijau)
        // ===================================================================
        const approveButton = document.getElementById('btn-approve');
        if (approveButton) {
            approveButton.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('form-action-approval');

                Swal.fire({
                    title: 'Setujui Permohonan?',
                    text: "Pastikan Anda sudah memeriksa data dan lampiran.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Setujui!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#2dce89',
                    backdrop: 'rgba(0,0,0,0.4)'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Set action, lalu submit
                        document.getElementById('approval-action').value = 'approve';
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endpush

