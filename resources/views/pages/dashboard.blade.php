@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Dashboard'])
    <div class="container-fluid py-4">
        <div class="row">
            {{-- Card 1 (Dinamis) --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <a href="{{ $stats['card1_link'] ?? '#' }}">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ $stats['card1_title'] ?? 'Statistik 1' }}</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $stats['card1_value'] ?? 0 }}
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-secondary text-sm font-weight-bolder">Lihat Detail</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape {{ $stats['card1_color'] ?? 'bg-gradient-primary' }} shadow-primary text-center rounded-circle">
                                        <i class="ni {{ $stats['card1_icon'] ?? 'ni-money-coins' }} text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Card 2 (Dinamis) --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                 <a href="{{ $stats['card2_link'] ?? '#' }}">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ $stats['card2_title'] ?? 'Statistik 2' }}</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $stats['card2_value'] ?? 0 }}
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-secondary text-sm font-weight-bolder">Lihat Detail</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape {{ $stats['card2_color'] ?? 'bg-gradient-danger' }} shadow-danger text-center rounded-circle">
                                        <i class="ni {{ $stats['card2_icon'] ?? 'ni-world' }} text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Card 3 (Dinamis) --}}
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                 <a href="{{ $stats['card3_link'] ?? '#' }}">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ $stats['card3_title'] ?? 'Statistik 3' }}</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $stats['card3_value'] ?? 0 }}
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-secondary text-sm font-weight-bolder">Lihat Detail</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape {{ $stats['card3_color'] ?? 'bg-gradient-success' }} shadow-success text-center rounded-circle">
                                        <i class="ni {{ $stats['card3_icon'] ?? 'ni-paper-diploma' }} text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Card 4 (Dinamis) --}}
            <div class="col-xl-3 col-sm-6">
                 <a href="{{ $stats['card4_link'] ?? '#' }}">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">{{ $stats['card4_title'] ?? 'Statistik 4' }}</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $stats['card4_value'] ?? 0 }}
                                        </h5>
                                        <p class="mb-0">
                                            <span class="text-secondary text-sm font-weight-bolder">Lihat Detail</span>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape {{ $stats['card4_color'] ?? 'bg-gradient-warning' }} shadow-warning text-center rounded-circle">
                                        <i class="ni {{ $stats['card4_icon'] ?? 'ni-cart' }} text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection


