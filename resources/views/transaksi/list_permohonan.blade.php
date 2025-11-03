@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Daftar Permohonan Transaksi'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                {{--
                    PERBAIKAN:
                    Tambahkan class 'min-vh-75' (Bootstrap/Argon utility)
                    Ini akan mengatur 'min-height: 75vh' pada card,
                    sehingga card akan selalu memanjang ke bawah mengisi layar.
                --}}
                <div class="card mb-4 min-vh-75">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6>Daftar Permohonan</h6>

                            {{-- Tombol ini hanya muncul jika user adalah Pemohon --}}
                            @if(Auth::check() && Auth::user()->role && Auth::user()->role->role_name == 'Pemohon')
                                <a class="btn btn-primary btn-sm ms-auto" href="{{ route('form-permohonan') }}">Buat Permohonan Baru</a>
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
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Loop data transaksi di sini --}}
                                    @forelse ($transaksiForms as $transaksi)
                                        @php
                                            $progress = 0;
                                            $statusClass = 'bg-gradient-secondary'; // Default

                                            switch ($transaksi->status) {
                                                case 'Draft': $progress = 10; $statusClass = 'bg-gradient-secondary'; break;
                                                case 'Diajukan': $progress = 25; $statusClass = 'bg-gradient-warning'; break;
                                                case 'Disetujui PYB1': $progress = 50; $statusClass = 'bg-gradient-info'; break;
                                                case 'Disetujui PYB2': $progress = 75; $statusClass = 'bg-gradient-primary'; break;
                                                case 'Disetujui BO': $progress = 100; $statusClass = 'bg-gradient-success'; break;
                                                case 'Ditolak': $progress = 100; $statusClass = 'bg-gradient-danger'; break;
                                            }
                                        @endphp

                                        <tr>
                                            {{-- Uraian Transaksi --}}
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ Str::limit($transaksi->uraian_transaksi, 50) }}</h6>
                                                        <p class="text-xs text-secondary mb-0">Tgl. Dibuat: {{ $transaksi->tanggal_pengajuan->format('d/m/Y') }}</p>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Pemohon --}}
                                            <td>
                                                <div class="d-flex">
                                                    {{-- Asumsi ada relasi 'pemohon' di model TransaksiForm --}}
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $transaksi->pemohon->name ?? 'N/A' }}</h6>
                                                        {{-- Asumsi ada relasi 'perusahaan' di model TransaksiForm --}}
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
                                                            <div class="progress-bar {{ $statusClass }}" role="progressbar"
                                                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"
                                                                style="width: {{ $progress }}%;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="align-middle">
                                                {{-- PERBAIKAN: Link href ke route 'permohonan.detail' --}}
                                                <a href="{{ route('permohonan.detail', ['transaksiForm' => $transaksi->id]) }}" class="text-secondary font-weight-bold text-xs"
                                                    data-toggle="tooltip" data-original-title="Lihat Detail">
                                                    Detail
                                                </a>
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

                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

