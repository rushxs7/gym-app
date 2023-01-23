@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Members') }}</div>

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
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="zoeken"><i class="bi-search"></i></span>
                                        <input type="text" name="searchquery" {!! request('searchquery') ? 'value="' . request('searchquery') . '"' : '' !!} class="form-control" placeholder="Zoeken" aria-label="Zoeken" aria-describedby="search">
                                    </div>
                                </div>
                                <div class="col-2">
                                    <select name="timespan" class="form-select mb-3" aria-label="timespan">
                                        <option disabled {{ request('timespan') ? '' : 'selected' }}>Datum</option>
                                        <option value="today" {{ request('timespan') == 'today' ? 'selected' : '' }}>Vandaag</option>
                                        <option value="yesterday" {{ request('timespan') == 'yesterday' ? 'selected' : '' }}>Gisteren</option>
                                        <option value="thisweek" {{ request('timespan') == 'thisweek' ? 'selected' : '' }}>Deze week</option>
                                        <option value="thismonth" {{ request('timespan') == 'thismonth' ? 'selected' : '' }}>Deze maand</option>
                                        {{-- <option value="custom" {{ request('timespan') == 'custom' ? 'selected' : '' }}>Aangepast</option> --}}
                                    </select>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-primary" type="submit"><i class="bi-funnel"></i> Filteren</button>
                                    @if (request('searchquery') || request('timespan'))
                                        <a href="{{ route('visits.index') }}" class="btn btn-danger"><i class="bi-arrow-counterclockwise"></i></a>
                                    @endif
                                </div>
                            </div>
                            </form>
                        </div>
                        <div class="col-2">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#additionModal"><i class="bi-plus"></i> Aanmelden</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>Visite Nr.</th>
                            <th>Member</th>
                            <th>Aangekomen</th>
                            <th>Vertrokken</th>
                          </tr>
                        </thead>
                        <tbody>
                            @forelse ($visits as $visit)
                            <tr>
                                <td>#{{ $visit->id }}</td>
                                <td>{{ $visit->members->name }}</td>
                                <td>{{ $visit->time_of_arrival }}</td>
                                <td>{{ $visit->time_of_departure }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td>No visits</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {!! $visits->appends($_GET)->links() !!}

                    <div class="modal fade" id="additionModal" tabindex="-1" aria-labelledby="additionModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Register New Visit</h1>
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

</script>
@endsection
