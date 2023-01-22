@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Members') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <table class="table">
                        <thead>
                          <tr>
                            <th>Naam</th>
                            <th>Status</th>
                            <th>Telefoon</th>
                            {{-- <th>Adres</th> --}}
                            <th>Geldig</th>
                            <th></th>
                          </tr>
                        </thead>
                        <tbody>
                            @php
                                $today = \Carbon\Carbon::today();
                            @endphp
                            @forelse ($members as $member)
                            @php
                                $diffInDays = \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today, false)
                            @endphp
                            <tr>
                                <td>{{ $member->name }}</td>
                                <td>
                                    @if ($member->active)
                                        <span class="badge bg-primary">Actief</span>
                                    @else
                                        <span class="badge bg-danger">Inactief</span>
                                    @endif
                                </td>
                                <td>{{ $member->phone }}{{ $member->phone2 ? ' / ' . $member->phone2 : '' }}</td>
                                {{-- <td>{{ $member->address ? $member->address : '-' }}</td> --}}
                                <td>
                                    @if ($diffInDays > 5)
                                    <span class="badge bg-success">over {{ \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today) }} dagen.</span>
                                    @elseif ($diffInDays > 0 && $diffInDays <= 5)
                                    <span class="badge bg-warning">over {{ \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today) }} dagen.</span>
                                    @elseif ($diffInDays == 0)
                                    <span class="badge bg-danger">vervalt vandaag</span>
                                    @else
                                    <span class="badge bg-danger">al {{ \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today) }} dagen vervallen.</span>
                                    @endif
                                    ({{ \Carbon\Carbon::parse($member->end_of_membership)->toFormattedDateString() }})
                                </td>
                                <td></td>
                            </tr>
                            @empty
                            <tr>
                                <td>No members</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
