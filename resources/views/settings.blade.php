@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Instellingen') }}</div>

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

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="home-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#payments-tab-pane"
                                type="button"
                                role="tab"
                                aria-controls="payments-tab-pane"
                                aria-selected="true">
                                Prijzen <i class="bi bi-currency-dollar"></i>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="profile-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#automation-tab-pane"
                                type="button"
                                role="tab"
                                aria-controls="automation-tab-pane"
                                aria-selected="false">
                                Automation <i class="bi bi-lightning-charge"></i>
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="payments-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                            <form action="{{ route('settings.update', ['group' => 'payments']) }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-4 my-2">
                                        <div class="mb-3">
                                            <label for="signup_fee" class="form-label">Inschrijvingskosten</label>
                                            <div class="input-group">
                                                <span class="input-group-text">SRD</span>
                                                <input type="number" id="signup_fee" name="signup_fee" value="{{ $settings['payments.signup_fee'] }}" min="0" step="0.01" required class="form-control">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prolongation_fee" class="form-label">Prolongatiekosten</label>
                                            <div class="input-group">
                                                <span class="input-group-text">SRD</span>
                                                <input type="number" id="prolongation_fee" name="prolongation_fee" value="{{ $settings['payments.prolongation_fee'] }}" min="0" step="0.01" required class="form-control">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="penalty_fee" class="form-label">Boete bij late prolongatie</label>
                                            <div class="input-group">
                                                <span class="input-group-text">SRD</span>
                                                <input type="number" id="penalty_fee" name="penalty_fee" value="{{ $settings['payments.penalty_fee'] }}" min="0" step="0.01" required class="form-control">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="daytraining_fee" class="form-label">Dagtraining</label>
                                            <div class="input-group">
                                                <span class="input-group-text">SRD</span>
                                                <input type="number" id="daytraining_fee" name="daytraining_fee" value="{{ $settings['payments.daytraining_fee'] }}" min="0" step="0.01" required class="form-control">
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" type="submit">Opslaan</button>
                                    </div>
                                    <div class="col-8"></div>
                                </div>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="automation-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                            <form action="{{ route('settings.update', ['group' => 'automation']) }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-4 my-2">
                                        <div class="mb-3">
                                            <label for="days_after_expiration_before_fine" class="form-label">Boete grace period (dagen)</label>
                                            <div class="input-group">
                                                <input type="number" id="days_after_expiration_before_fine" name="days_after_expiration_before_fine" value="{{ $settings['automation.days_after_expiration_before_fine'] }}" required class="form-control" min="0" max="30" step="1">
                                                <span class="input-group-text">dagen</span>
                                            </div>
                                        </div>
                                        {{-- <div class="mb-3">
                                            <div class="input-group mb-3">
                                            <label class="input-group-text" for="opening_time">Openingstijd</label>
                                            <select class="form-select" id="opening_time" name="opening_time" required>
                                                @for ($i=0; $i < 24; $i++)
                                                    <option value="{{ $i }}" {{ $i == $settings["automation.opening_time"] ? 'selected' : '' }}>{{ $i <= 12 ? ($i) : ($i - 12) }}{{ $i < 12 ? 'am' : 'pm' }}</option>
                                                @endfor
                                            </select>
                                            </div>
                                        </div> --}}
                                        <div class="mb-3">
                                            <div class="input-group mb-3">
                                            <label class="input-group-text" for="closing_time">Sluitingstijd</label>
                                            <select class="form-select" id="closing_time" name="closing_time" required>
                                                @for ($j=0; $j < 24; $j++)
                                                    <option value="{{ $j }}" {{ $j == $settings["automation.closing_time"] ? 'selected' : '' }}>{{ $j <= 12 ? ($j) : ($j - 12) }}{{ $j < 12 ? 'am' : 'pm' }}</option>
                                                @endfor
                                            </select>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" type="submit">Opslaan</button>
                                    </div>
                                    <div class="col-8"></div>
                                </div>
                            </form>
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
