@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Daftar Permohonan Transaksi'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                {{-- PERBAIKAN: Tambahkan class 'min-vh-75' --}}
                <div class="card mb-4 min-vh-75">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            @php
                                $userRole = Auth::user()->role->role_name ?? '';
                                $pageTitle = 'Daftar Permohonan';
                                if ($userRole == 'Pemohon') $pageTitle = 'Daftar Permohonan Saya';
                                if (in_array($userRole, ['Direksi', 'PYB1', 'PYB2', 'BO'])) $pageTitle = 'Daftar Tugas Persetujuan';
                                if ($userRole == 'Admin') $pageTitle = 'Daftar Tugas (Kosong)';
                            @endphp
                            <h6>{{ $pageTitle }}</h6>

                            {{-- Tombol ini hanya muncul jika user adalah Pemohon --}}
                            @if(Auth::check() && $userRole == 'Pemohon')
                                <a class="btn btn-primary btn-sm ms-auto" href="{{ route('permohonan.create') }}">Buat Permohonan Baru</a>
                            @endif

                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">

                        {{-- Tampilkan pesan sukses --}}
                        @if (session('success'))
                            <div class="alert alert-success mx-4" role="alert">
                                <strong class="text-white">{{ session('success') }}</strong>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger mx-4" role="alert">
                                <strong class="text-white">{{ session('error') }}</strong>
                            </div>
                        @endif

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Uraian Transaksi</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Pemohon</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Nominal</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Progress</th>
                                        <th class="text-secondary opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop data transaksi di sini --}}
                                    @forelse ($transaksiForms as $transaksi)
                                        @php
                                            // LOGIKA PROGRESS BAR BARU (5-Step Approval: Direksi -> PYB1 -> PYB2 -> BO)
                                            $progress = 0;
                                            $progressClass = 'bg-gradient-secondary';
                                            $statusClass = 'bg-gradient-secondary';

                                            switch ($transaksi->status) {
                                                case 'Draft':
                                                    $progress = 10;
                                                    $statusClass = 'bg-gradient-secondary';
                                                    $progressClass = 'bg-gradient-secondary';
                                                    break;
                                                case 'Diajukan': // Menunggu Direksi
                                                    $progress = 25;
                                                    $statusClass = 'bg-gradient-warning';
                                                    $progressClass = 'bg-gradient-warning';
                                                    break;
                                                case 'Disetujui Direksi': // Menunggu PYB1
                                                    $progress = 50;
                                                    $statusClass = 'bg-gradient-info';
                                                    $progressClass = 'bg-gradient-info';
                                                    break;
                                                case 'Disetujui PYB1': // Menunggu PYB2
                                                    $progress = 70;
                                                    $statusClass = 'bg-gradient-info';
                                                    $progressClass = 'bg-gradient-info';
                                                    break;
                                                case 'Disetujui PYB2': // Menunggu BO
                                                    $progress = 90;
                                                    $statusClass = 'bg-gradient-primary';
                                                    $progressClass = 'bg-gradient-primary';
                                                    break;
                                                case 'Disetujui BO': // Selesai
                                                    $progress = 100;
                                                    $statusClass = 'bg-gradient-success';
                                                    $progressClass = 'bg-gradient-success';
                                                    break;
                                                case 'Ditolak':
                                                    $progress = 100;
                                                    $statusClass = 'bg-gradient-danger';
                                                    $progressClass = 'bg-gradient-danger';
                                                    break;
                                            }
                                        @endphp

                                        <tr>
                                            {{-- Uraian Transaksi --}}
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ Str::limit($transaksi->uraian_transaksi, 50) }}</h6>
                                                        <p class="text-xs text-secondary mb-0">ID: {{ $transaksi->id }} | Tgl: {{ $transaksi->tanggal_pengajuan->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Pemohon --}}
                                            <td>
                                                <div class="d-flex">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $transaksi->pemohon->name ?? 'N/A' }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $transaksi->perusahaan->nama_perusahaan ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Nominal --}}
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-xs font-weight-bold mb-0">Rp {{ number_format($transaksi->total_nominal, 0, ',', '.') }}</span>
                                            </td>

                                            {{-- Status --}}
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm {{ $statusClass }}">{{ $transaksi->status }}</span>
                                            </td>

                                            {{-- Progress Bar --}}
                                            <td class="align-middle text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="me-2 text-xs font-weight-bold">{{ $progress }}%</span>
                                                    <div>
                                                        <div class="progress">
                                                            <div class="progress-bar {{ $progressClass }}" role="progressbar"
                                                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"
                                                                style="width: {{ $progress }}%;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="align-middle">
                                                <a href="{{ route('permohonan.detail', $transaksi) }}" class="text-primary font-weight-bold text-xs me-2"
                                                    data-toggle="tooltip" data-original-title="Lihat Detail">
                                                    Detail
                                                </a>

                                                {{-- Tombol Aksi untuk Pemohon --}}
                                                @if(Auth::id() == $transaksi->pemohon_id)
                                                    {{-- Hanya bisa edit/hapus jika status 'Draft' --}}
                                                    @if ($transaksi->status == 'Draft')
                                                        <a href="{{ route('permohonan.edit', $transaksi) }}" class="text-secondary font-weight-bold text-xs me-2"
                                                            data-toggle="tooltip" data-original-title="Edit Draft">
                                                            Edit
                                                        </a>
                                                        <form action="{{ route('permohonan.destroy', $transaksi) }}" method="POST" class="d-inline delete-form">
                                                            @csrf
                                                            @method('DELETE')
                                                            <a href="javascript:;" class="text-danger font-weight-bold text-xs delete-btn"
                                                                data-toggle="tooltip" data-original-title="Hapus Draft">
                                                                Hapus
                                                            </a>
                                                        </form>
                                                    @endif
                                                    {{-- Hanya bisa edit (ajukan ulang) jika 'Ditolak' --}}
                                                    @if ($transaksi->status == 'Ditolak')
                                                         <a href="{{ route('permohonan.edit', $transaksi) }}" class="text-warning font-weight-bold text-xs me-2"
                                                            data-toggle="tooltip" data-original-title="Revisi & Ajukan Ulang">
                                                            Revisi
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <p class="mb-0 text-secondary">Belum ada data permohonan.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        <div class="d-flex justify-content-center mt-4">
                            {{ $transaksiForms->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tambahkan event listener ke semua tombol hapus
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Mencegah link default

                // Cari form terdekat
                const form = e.target.closest('form.delete-form');

                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Anda akan menghapus Draft permohonan ini secara permanen!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f5365c', // Argon danger color
                    cancelButtonColor: '#adb5bd', // Argon secondary
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit form jika dikonfirmasi
                    }
                });
            });
        });
    });
</script>
@endpush

