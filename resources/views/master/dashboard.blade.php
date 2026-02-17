@extends('layouts.app')

@section('title', 'Master Dashboard')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Master Dashboard</div>
                <div class="card-body">
                    Welcome, Master {{ Auth::user()->name }}!
                    <hr>
                    <h3>Assigned Requests</h3>
                    {{-- Assigned request listing will go here --}}
                </div>
            </div>
        </div>
    </div>
@endsection
