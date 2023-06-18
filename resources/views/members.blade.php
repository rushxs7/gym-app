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
                            <form action="{{ route('members.index') }}" method="GET">
                            <div class="row">
                                <div class="col-4">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="zoeken"><i class="bi-search"></i></span>
                                        <input type="text" name="searchquery" {!! request('searchquery') ? 'value="' . request('searchquery') . '"' : '' !!} class="form-control" placeholder="Zoeken" aria-label="Zoeken" aria-describedby="search">
                                    </div>
                                </div>
                                {{-- <div class="col-2">
                                    <select name="status" class="form-select mb-3" aria-label="status">
                                        <option disabled {{ request('status') ? '' : 'selected' }}>Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actief</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactief</option>
                                    </select>
                                </div> --}}
                                <div class="col-3">
                                    <select name="expiry" class="form-select mb-3" aria-label="status">
                                        <option disabled {{ request('expiry') ? '' : 'selected' }}>Geldigheid</option>
                                        <option value="valid" {{ request('expiry') == 'valid' ? 'selected' : '' }}>Geldig</option>
                                        <option value="soon" {{ request('expiry') == 'soon' ? 'selected' : '' }}>Vervalt binnenkort</option>
                                        <option value="expired" {{ request('expiry') == 'expired' ? 'selected' : '' }}>Reeds vervallen</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-primary" type="submit"><i class="bi-funnel"></i></button>
                                        @if (request('searchquery') || request('status') || request('expiry'))
                                            <a href="{{ route('members.index') }}" class="btn btn-danger"><i class="bi-arrow-counterclockwise"></i></a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            </form>
                        </div>
                        <div class="col-2">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#additionModal"><i class="bi-plus"></i> Toevoegen</button>
                            </div>
                        </div>
                    </div>

                    <table class="table table-sm">
                        <thead>
                          <tr>
                            <th>ID</th>
                            <th>Naam</th>
                            <th>Telefoon</th>
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
                                $diffInDays = $today->diffInDays(\Carbon\Carbon::parse($member->end_of_membership), false);
                            @endphp
                            <tr>
                                <td>#{{ $member->id }}</td>
                                <td>
                                    {{ $member->name }}
                                    {!! count($member['visits']) ? '<span class="badge bg-success"><i class="bi bi-activity"></i> in de gym</span>' : '' !!}
                                </td>
                                {{-- <td>
                                    @if ($member->active)
                                        <span class="badge bg-primary">Actief</span>
                                    @else
                                        <span class="badge bg-danger">Inactief</span>
                                    @endif
                                </td> --}}
                                <td>{{ $member->phone }}</td>
                                {{-- <td></td> --}}
                                <td>
                                    ({{ \Carbon\Carbon::parse($member->end_of_membership)->toFormattedDateString() }})
                                    @if ($diffInDays > 5)
                                    <span class="badge bg-success">vervalt over {{ \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today) }} dag(en)</span>
                                    @elseif ($diffInDays > 0 && $diffInDays <= 5)
                                    <span class="badge bg-warning">vervalt over {{ \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today) }} dag(en)</span>
                                    @elseif ($diffInDays == 0)
                                    <span class="badge bg-danger">vervalt vandaag</span>
                                    @else
                                    <span class="badge bg-danger">al {{ \Carbon\Carbon::parse($member->end_of_membership)->diffInDays($today) }} dagen vervallen.</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end" data-tooltip-stickto="top" data-tooltip-color="#ddd">
                                        <button data-tooltip="Inklokken" onclick="recordVisitation({{ $member->id }})" class="btn btn-sm {{ count($member['visits']) ? 'btn-danger' : 'btn-success' }} ms-1">
                                            <i class="bi-box-arrow-in-right"></i>
                                        </button>
                                        <button data-tooltip="Prolongeren" onclick="prolongMember({{ $member->id }})" class="btn btn-sm btn-primary ms-1">
                                            <i class="bi-arrow-repeat"></i>
                                        </button>
                                        <div class="dropdown ms-1">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi-link"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="{{ route('visits.index') }}/?memberId={{ $member->id }}">Visites</a></li>
                                                <li><a class="dropdown-item" href="{{ route('payments.index') }}?memberId={{ $member->id }}">Betalingen</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item" href="#">Bewerken</a></li>
                                                <li><a class="dropdown-item" href="#">Verwijderen</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td>Geen members</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {!! $members->appends($_GET)->links() !!}

                    <div class="modal fade" id="additionModal" tabindex="-1" aria-labelledby="additionModal" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add new member</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('members.store') }}" method="post" autocomplete="off">
                                    @csrf
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Naam*</label>
                                            <input type="text" required class="form-control" id="addName" name="name" placeholder="John Doe">
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Telefoon*</label>
                                            <input type="text" required class="form-control" id="addPhone" name="phone" placeholder="+597 7654321">
                                        </div>
                                        <div class="mb-3">
                                            <label for="birthday" class="form-label">Geboortedatum*</label>
                                            <input type="date" class="form-control" id="birthday" name="birthday">
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="addEmail" name="email" placeholder="johndoe@contoso.com">
                                        </div>
                                        <div class="mb-3">
                                            <label for="address" class="form-label">Adres</label>
                                            <input type="text" class="form-control" id="addAddress" name="address" placeholder="Hamburgstraat 12">
                                        </div>
                                        <div class="mb-3">
                                            <label for="expiry" class="form-label">Vervaldatum*</label>
                                            <input type="date" required class="form-control" id="addExpiry" name="expiry">
                                        </div>
                                        <div class="mb-3">
                                            <select name="gender" class="form-select" aria-label="gender">
                                                <option disabled selected >Geslacht</option>
                                                <option value="male">Mannelijk</option>
                                                <option value="female">Vrouwelijk</option>
                                            </select>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="registration" value="true" id="addRegistration">
                                            <label class="form-check-label" for="addRegistration">
                                                Inschrijving
                                            </label>
                                        </div>
                                        <div id="registrationFields" class="mt-2" style="display: none;">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="form-check" style="padding-left: 0em;">
                                                        <label for="paid" class="form-label">Sign up fee</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="paid">SRD</span>
                                                            <input type="number" id="addPaid" name="balance" class="form-control" placeholder="0.00" aria-label="" aria-describedby="paid">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    $("#addRegistration").on("change", function() {
        if ($("#addRegistration").prop('checked')) {
            $("#registrationFields").show()
            $("#addPaid").prop("required", true)
            $("#addRetour").prop("required", true)
        } else {
            $("#registrationFields").hide()
            $("#addPaid").prop("required", false)
            $("#addRetour").prop("required", false)
        }
    })

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

    async function prolongMember (memberId) {
        console.log("Prolong Member #" + memberId)
        await axios({
            method: 'get',
            url: '/api/members/' + memberId + '/actions/promptprolongation'
        })
        .then((response) => {
            let boeteString = ''
            if (response.data.data.penalty != 0) {
                boeteString = ' (incl. boete van SRD' + response.data.data.penalty + ')'
            }
            return Swal.fire({
                title: 'Prolongatie bevestigen?',
                text: 'Lidmaatschap verlengen tot en met ' + response.data.data.proposed_date + ' voor SRD' + response.data.data.price + ' ' + boeteString + '?',
                confirmButtonText: 'Verlengen',
                showDenyButton: true,
                denyButtonText: 'Niet verlengen',
                icon: 'question',
                allowOutsideClick: false,
                allowEscapeKey: false
            })
        })
        .then((swal) => {
            if (!swal.isConfirmed) {
                return Promise.reject('Prolongatie niet bevestigd.')
            }

            return axios({
                method: 'post',
                url: '/api/members/' + memberId + '/actions/prolong',
            })
        })
        .then((response) => {
            switch (response.data.message) {
                case 'geprolongeerd':
                    return Swal.fire({
                        title: response.data.data.message,
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
