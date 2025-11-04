{{-- Baris ini dipanggil oleh AJAX --}}
<tr>
    <td>
        <p class="text-sm font-weight-bold mb-0 px-3">{{ $attachment->attachment_type }}</p>
    </td>
    <td>
        <p class="text-sm mb-0">{{ $attachment->file_name }}</p>
    </td>
    <td>
        <p class="text-sm mb-0">{{ $attachment->uploader->name ?? 'N/A' }}</p>
    </td>
    <td class="align-middle text-end">
        {{-- Tombol Download --}}
        <a href="{{ route('permohonan.attachment.download', $attachment) }}"
           class="btn btn-link text-dark font-weight-bold text-xs"
           data-toggle="tooltip" data-original-title="Download file">
           Download
        </a>

        {{-- Tombol Hapus (Hanya muncul untuk Pemohon saat Draft) --}}
        @if ($transaksiForm->status == 'Draft' && Auth::id() == $transaksiForm->pemohon_id)
        <button type="button" class="btn btn-link text-danger font-weight-bold text-xs btn-delete-attachment"
                data-url="{{ route('permohonan.attachment.destroy', $attachment) }}"
                data-toggle="tooltip" data-original-title="Hapus file">
            Hapus
        </button>
        @endif
    </td>
</tr>
