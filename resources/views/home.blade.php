@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div id="statusCards" class="row">
                <div class="col">
                    <div class="card radius-10 bg-gradient-deepblue">
                        <div class="card-body">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 text-white">{{ $activeMembers }}</h5>
                            <div class="ms-auto">
                                <i class="bi bi-person-check fs-3 text-white"></i>
                            </div>
                        </div>
                        <div class="progress my-3 bg-light-transparent" style="height:3px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center text-white">
                            <p class="mb-0">Active Members</p>
                            {{-- <p class="mb-0 ms-auto">+4.2%<span><i class="bx bx-up-arrow-alt"></i></span></p> --}}
                        </div>
                    </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 bg-gradient-orange">
                        <div class="card-body">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 text-white">{{ $newMembersThisMonth }}</h5>
                            <div class="ms-auto">
                                <i class="bi bi-person-add fs-3 text-white"></i>
                            </div>
                        </div>
                        <div class="progress my-3 bg-light-transparent" style="height:3px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center text-white">
                            <p class="mb-0">New Members</p>
                            <p class="mb-0 ms-auto">
                                {{ Str::ucfirst(\Carbon\Carbon::today()->locale('nl')->monthName) }}
                            </p>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 bg-gradient-ohhappiness">
                        <div class="card-body">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 text-white">{{ $visitsToday }}</h5>
                            <div class="ms-auto">
                                <i class="bi bi-door-open fs-3 text-white"></i>
                            </div>
                        </div>
                        <div class="progress my-3 bg-light-transparent" style="height:3px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center text-white">
                            <p class="mb-0">Visits Today</p>
                            <p class="mb-0 ms-auto">{{ $visitorDifference }}%<span><i class="bx bx-up-arrow-alt"></i></span></p>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card radius-10 bg-gradient-ibiza">
                        <div class="card-body">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 text-white">SRD {{ $revenueThisMonth }}</h5>
                            <div class="ms-auto">
                                <i class="bi bi-currency-dollar fs-3 text-white"></i>
                            </div>
                        </div>
                        <div class="progress my-3 bg-light-transparent" style="height:3px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex align-items-center text-white">
                            <p class="mb-0">Total Revenue</p>
                            <p class="mb-0 ms-auto">{{ Str::ucfirst(\Carbon\Carbon::today()->locale('nl')->monthName) }}</p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
