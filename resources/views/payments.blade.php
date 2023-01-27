@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Betalingen') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('status') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-10">
                            <form action="{{ route('payments.index') }}" method="GET">
                            <div class="row mb-2">
                                <div class="col-3">
                                    <div class="input-group">
                                        <span class="input-group-text" id="zoeken"><i class="bi-search"></i></span>
                                        <input type="text" name="searchquery" {!! request('searchquery') ? 'value="' . request('searchquery') . '"' : '' !!} class="form-control" placeholder="Zoeken" aria-label="Zoeken" aria-describedby="search">
                                    </div>
                                </div>
                                <div class="col-2">
                                    <select id="timespan" name="timespan" class="form-select" aria-label="timespan">
                                        <option disabled {{ request('timespan') ? '' : 'selected' }}>Tijdspanne</option>
                                        <option value="today" {{ request('timespan') == 'today' ? 'selected' : '' }}>Vandaag</option>
                                        <option value="yesterday" {{ request('timespan') == 'yesterday' ? 'selected' : '' }}>Gisteren</option>
                                        <option value="thisweek" {{ request('timespan') == 'thisweek' ? 'selected' : '' }}>Deze week</option>
                                        <option value="thismonth" {{ request('timespan') == 'thismonth' ? 'selected' : '' }}>Deze maand</option>
                                        <option value="custom" {{ request('timespan') == 'custom' ? 'selected' : '' }}>Aangepast</option>
                                    </select>
                                </div>
                                @php
                                    $startFilter = \Carbon\Carbon::parse(request('startFilter'))->toDateString();
                                    $endFilter = \Carbon\Carbon::parse(request('endFilter'))->toDateString();
                                @endphp
                                <div id="specificDateFilter" class="col-5" style="display: none;">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-fill">
                                            <input type="date" required class="form-control" id="startFilter" name="startFilter" {!! request('startFilter') ? 'value="' . $startFilter . '"' : '' !!}>
                                        </div>
                                        <div class="">
                                            &nbsp;&nbsp;-&nbsp;&nbsp;
                                        </div>
                                        <div class="flex-fill">
                                            <input type="date" required class="form-control" id="endFilter" name="endFilter" {!! request('endFilter') ? 'value="' . $endFilter . '"' : '' !!}>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div class="btn-group" role="group">

                                    <button class="btn btn-primary" type="submit"><i class="bi-funnel"></i></button>
                                    @if (request('searchquery') || request('timespan') || request('memberId'))
                                        <a href="{{ route('payments.index') }}" class="btn btn-danger"><i class="bi-arrow-counterclockwise"></i></a>
                                    @endif
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                        <div class="col-2">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#additionModal"><i class="bi-plus"></i> Betaling</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Beschrijving</th>
                            <th>Inkomst</th>
                            <th>Member</th>
                            <th>Gedaan op</th>
                          </tr>
                        </thead>
                        <tbody>
                            @forelse ($payments as $payment)
                            <tr>
                                <td>#{{ $payment->id }}</td>
                                <td>{{ $payment->type }}</td>
                                <td>{{ $payment->description }}</td>
                                <td>SRD {{ number_format($payment->balance, 2) }}</td>
                                <td>
                                    @if ($payment->members)
                                        {{ $payment->members->name }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $payment->created_at }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td>Geen betalingen</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {!! $payments->appends($_GET)->links() !!}

                    <div class="modal fade" id="additionModal" tabindex="-1" aria-labelledby="additionModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Nieuwe Betaling Registreren</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('visits.store') }}" method="post" autocomplete="off">
                                    @csrf
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Naam*</label>
                                            <input type="text" required class="form-control" id="addName" name="name" placeholder="John Doe">
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Telefoon*</label>
                                            <input type="text" required class="form-control" id="addPhone" name="phone" placeholder="+597 7654321">
                                        </div>
                                        <hr class="my-3">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-block btn-primary">Opslaan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>
<script>
    computedSpecificDateFilter()

    $("#timespan").on("change", function() {
        computedSpecificDateFilter()
    })

    function computedSpecificDateFilter() {
        if($("#timespan").val() == "custom"){
            $("#specificDateFilter").show()
            $("#startFilter").prop("required", true)
            $("#endFilter").prop("required", true)
        } else {
            $("#specificDateFilter").hide()
            $("#startFilter").val("")
            $("#endFilter").val("")
            $("#startFilter").prop("required", false)
            $("#endFilter").prop("required", false)
        }
    }
</script>
@endsection
