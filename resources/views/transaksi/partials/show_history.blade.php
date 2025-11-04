{{-- =================================================================================== --}}
{{-- CARD: RIWAYAT TRANSAKSI --}}
{{-- =================================================================================== --}}
<div class="col-12 mt-4">
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h6 class="mb-0">Riwayat Transaksi</h6>
        </div>
        <div class="card-body pt-4 p-3">
            <ul class="timeline timeline-one-side" data-timeline-axis-style="dotted">

                @forelse ($transaksiForm->history->sortBy('created_at') as $history)
                    <li class="timeline-block">
                        <span class="timeline-step">
                            @if($history->action == 'Mengajukan')
                                <i class="ni ni-cloud-upload-96 text-primary"></i>
                            @elseif(str_contains($history->action, 'Disetujui'))
                                <i class="ni ni-check-bold text-success"></i>
                            @elseif(str_contains($history->action, 'Ditolak'))
                                <i class="ni ni-fat-remove text-danger"></i>
                            @else
                                <i class="ni ni-bell-55 text-secondary"></i>
                            @endif
                        </span>
                        <div class="timeline-content">
                            <h6 class="text-dark text-sm font-weight-bold mb-0">
                                {{ $history->action }}
                                ({{ $history->user->role->role_name ?? 'N/A' }})
                            </h6>
                            <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                {{ $history->created_at->format('d M Y, H:i') }}
                            </p>
                            @if($history->remarks)
                            <p class="text-sm mt-2 mb-0 fst-italic">
                                Catatan: "{{ $history->remarks }}"
                            </p>
                            @endif
                            <p class="text-xs text-info mt-1 mb-0">
                                Status diubah dari '{{ $history->from_status }}' ke '{{ $history->to_status }}'
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="timeline-block">
                        <span class="timeline-step">
                            <i class="ni ni-bell-55 text-secondary"></i>
                        </span>
                        <div class="timeline-content">
                             <h6 class="text-dark text-sm font-weight-bold mb-0">
                                Belum ada riwayat untuk transaksi ini.
                            </h6>
                        </div>
                    </li>
                @endforelse

            </ul>
        </div>
    </div>
</div>
