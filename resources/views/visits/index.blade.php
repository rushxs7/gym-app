@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Visites') }}</div>

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
                            <form action="{{ route('visits.index') }}" method="GET">
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
                                        <a href="{{ route('visits.index') }}" class="btn btn-danger"><i class="bi-arrow-counterclockwise"></i></a>
                                    @endif
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                        <div class="col-2">
                            {{-- <div class="d-flex justify-content-end">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#additionModal"><i class="bi-plus"></i> Aanmelden</button>
                            </div> --}}
                        </div>
                    </div>

                    <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Visite Nr.</th>
                            <th>Member</th>
                            <th>Aangekomen</th>
                            <th>Vertrokken</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                            @forelse ($visits as $visit)
                            <tr>
                                <td>#{{ $visit->id }}</td>
                                <td>{{ $visit->members->name }}</td>
                                <td>{{ $visit->time_of_arrival }}</td>
                                <td>{{ $visit->time_of_departure }}</td>
                                <td align="right">
                                    {!! !$visit->time_of_departure ? '<button data-tooltip="Uitklokken" onclick="recordVisitation(' . $visit->member_id . ')" class="btn btn-sm btn-danger ms-1"><i class="bi-box-arrow-in-right"></i> Uitklokken</button>' : '' !!}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td>Geen visites</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {!! $visits->appends($_GET)->links() !!}
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

    async function recordVisitation(memberId) {
        await axios.get('/api/members/' + memberId)
        .then((response) => {
            if (response.data.data.ends_in_days < 0) {
                // return error message
                Swal.fire({
                    title: 'Lidmaatschap vervallen',
                    text: 'Vervallen op ' + response.data.data.end_of_membership + '. Graag eerst prolongeren.',
                    icon: 'error',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                })

                return Promise.reject('expired')
            }
            return axios({
                method: 'post',
                url: '/api/members/' + memberId + '/actions/visit'
            })
        })
        .then((response) => {
            switch (response.data.message) {
                case 'aangemeld':
                    return Swal.fire({
                        title: response.data.data.message,
                        text: response.data.data.expiryMessage,
                        icon: 'success',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    })
                    break;
                case 'afgemeld':
                    return Swal.fire({
                        title: response.data.data.message,
                        text: response.data.data.expiryMessage,
                        icon: 'success',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    })
                    break;

                default:
                    Promise.reject('unknown')
                    break;
            }
        })
        .then((swal) => {
            return fireReloadToast()
        })
        .then((swal) => {
            if(swal.isDismissed) {
                reloadAfter(0)
            }
        })
        .catch((error) => {
            console.log(error)
            if (error != 'expired') {
                Swal.fire({
                    title: error,
                    icon: 'error'
                })
            }
        })
    }

    function fireReloadToast () {
        return Swal.fire({
            title: 'Aan het reloaden...',
            icon: 'info',
            toast: true,
            timer: 2000,
            timerProgressBar: true,
            position: 'top-end',
            showConfirmButton: false,
        })
    }

    function reloadAfter (seconds = 6) {
        setTimeout(function () {
            window.location.reload()
        }, seconds * 1000)
    }
</script>
@endsection
