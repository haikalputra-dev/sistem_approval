{{-- =================================================================================== --}}
{{-- CARD: AKSI PERSETUJUAN (Approve/Reject) --}}
{{-- =================================================================================== --}}

@php
    $userRole = Auth::user()->role->role_name ?? null;
    $showApprovalCard = false;

    // ==========================================================
    // PERBAIKAN: LOGIKA TOMBOL APPROVAL (ALUR 5 LANGKAH)
    // ==========================================================
    $currentStatus = $transaksiForm->status;

    if ($userRole == 'Direksi' && $currentStatus == 'Diajukan') $showApprovalCard = true;
    if ($userRole == 'PYB1' && $currentStatus == 'Disetujui Direksi') $showApprovalCard = true;
    if ($userRole == 'PYB2' && $currentStatus == 'Disetujui PYB1') $showApprovalCard = true;
    if ($userRole == 'BO' && $currentStatus == 'Disetujui PYB2') $showApprovalCard = true;

@endphp

@if ($showApprovalCard)
<div class="col-12 mt-4">
    <div class="card">
        <div class="card-header">
             <h6 class="mb-0">Aksi Persetujuan ({{ $userRole }})</h6>
        </div>
        <div class="card-body d-flex justify-content-end">
            {{-- Form ini di-trigger oleh JS SweetAlert --}}
            <form id="form-action-approval" action="{{ route('permohonan.process', $transaksiForm) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="action" id="approval-action" value="">
                <input type="hidden" name="remarks" id="approval-remarks" value="">

                {{-- Tombol Reject (Merah) --}}
                <button type="button" id="btn-reject" class="btn btn-danger ms-2">
                    <i class="fa fa-times me-1"></i>
                    Tolak / Minta Revisi
                </button>

                {{-- Tombol Approve (Hijau) --}}
                <button type="button" id="btn-approve" class="btn btn-success ms-2">
                    <i class="fa fa-check me-1"></i>
                    Setujui
                </button>
            </form>
        </div>
    </div>
</div>
@endif

