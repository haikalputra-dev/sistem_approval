{{-- =================================================================================== --}}
{{-- CARD: KARTU LAMPIRAN --}}
{{-- =================================================================================== --}}
<div class="col-12 mt-4">
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6 class="mb-0">Lampiran Transaksi</h6>
        </div>
        <div class="card-body">

            {{-- 1. FORM UPLOAD LAMPIRAN --}}
            {{-- Hanya tampilkan jika status 'Draft' --}}
            @if ($transaksiForm->status == 'Draft' && Auth::id() == $transaksiForm->pemohon_id)
            <form id="form-upload-attachment" action="{{ route('permohonan.attachment.store', $transaksiForm) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <p class="text-uppercase text-sm">Upload Lampiran Baru</p>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="attachment_type" class="form-control-label">Tipe Lampiran *</label>
                            <select class="form-control" name="attachment_type" id="attachment_type" required>
                                <option value="Invoice">Invoice</option>
                                <option value="Nota / Kwitansi">Nota / Kwitansi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="file_upload" class="form-control-label">File *</label>
                            <input class="form-control" type="file" id="file_upload" name="file_upload" required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        </div>
                    </div>
                </div>
            </form>
            <hr class="horizontal dark">
            @endif

            {{-- 2. TABEL DAFTAR LAMPIRAN YANG SUDAH ADA --}}
            <p class="text-uppercase text-sm">Daftar Lampiran</p>
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe Lampiran</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama File</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Di-upload Oleh</th>
                            <th class="text-secondary opacity-7"></th>
                        </tr>
                    </thead>
                    <tbody id="attachment-table-body">
                        @forelse ($transaksiForm->attachments as $attachment)
                            @include('transaksi.partials.show_attachment_row', ['attachment' => $attachment])
                        @empty
                        <tr>
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
