{{-- =================================================================================== --}}
{{-- CARD: AKSI PEMOHON (Submit Draft) --}}
{{-- =================================================================================== --}}
@if ($transaksiForm->status == 'Draft' && $transaksiForm->pemohon_id == Auth::id())
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-header">
             <h6 class="mb-0">Aksi Pemohon</h6>
        </div>
        <div class="card-body d-flex justify-content-end">
            <form action="{{ route('permohonan.submit', $transaksiForm) }}" method="POST">
                @csrf
                @method('PATCH')

                {{--
                    PERBAIKAN: Tombol submit sekarang tidak perlu cek balance JS
                    karena validasi rincian sudah dihapus.
                --}}
                <button type="submit" class="btn btn-success ms-2"
                    onclick="return confirm('Apakah Anda yakin ingin mengajukan (Submit) permohonan ini?')">
                    <i class="fa fa-check me-1"></i>
                    Submit Pengajuan
                </button>
            </form>
        </div>
    </div>
</div>
@endif
