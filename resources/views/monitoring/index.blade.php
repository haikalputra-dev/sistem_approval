@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Monitoring Transaksi'])
    <div class="container-fluid py-4">

        {{-- CARD 1: FILTER --}}
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <h6>Filter Monitoring</h6>
                    </div>
                    <div class="card-body">
                        {{-- Form Filter --}}
                        <form action="{{ route('monitoring.index') }}" method="GET" id="form-filter">
                            <div class="row">
                                {{-- Filter Status --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status" class="form-control-label">Status</label>
                                        <select class="form-control" name="status" id="status">
                                            <option value="">Semua Status</option>
                                            @foreach ($statusList as $status)
                                                <option value="{{ $status }}"
                                                    {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Filter Tanggal Dari --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tanggal_dari" class="form-control-label">Tanggal Dari</label>
                                        <input class="form-control" type="date" name="tanggal_dari" id="tanggal_dari"
                                               value="{{ $filters['tanggal_dari'] ?? '' }}">
                                    </div>
                                </div>

                                {{-- Filter Tanggal Sampai --}}
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tanggal_sampai" class="form-control-label">Tanggal Sampai</label>
                                        <input class="form-control" type="date" name="tanggal_sampai" id="tanggal_sampai"
                                               value="{{ $filters['tanggal_sampai'] ?? '' }}">
                                    </div>
                                </div>

                                {{-- Filter Pemohon (Hanya untuk Admin) --}}
                                @if(Auth::user()->role->role_name == 'Admin')
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="pemohon_id" class="form-control-label">Pemohon</Tabel>
                                        <select class="form-control" name="pemohon_id" id="pemohon_id">
                                            <option value="">Semua Pemohon</option>
                                            @foreach ($pemohonList as $pemohon)
                                                <option value="{{ $pemohon->id }}"
                                                    {{ ($filters['pemohon_id'] ?? '') == $pemohon->id ? 'selected' : '' }}>
                                                    {{ $pemohon->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Tombol Filter --}}
                            <div class="row">
                                <div class="col-md-12 d-flex justify-content-end">
                                    <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary me-2">Reset</a>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARD 2: TABEL DATA --}}
        <div class="row">
            <div class="col-12">
                <div class="card mb-4 min-vh-75">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6>Daftar Riwayat Permohonan</h6>
                            {{-- (Opsional: Tombol Export) --}}
                            {{-- <a class="btn btn-success btn-sm ms-auto" href="#">Export Excel</a> --}}
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
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
                                                            <div class="progress-bar {{ $statusClass }}" role="progressbar"
                                                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"
                                                                style="width: {{ $progress }}%;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="align-middle">
                                                <a href="{{ route('permohonan.detail', $transaksi) }}" class="text-secondary font-weight-bold text-xs"
                                                    data-toggle="tooltip" data-original-title="Lihat Detail">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <p class="mb-0 text-secondary">Tidak ada data transaksi yang sesuai dengan filter.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination Links --}}
                        <div class="card-footer py-2">
                            {{ $transaksiForms->links() }}
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

