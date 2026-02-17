@extends('layouts.app')

@section('title', "History for Request #{$request->id}")

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>History for Request #{{ $request->id }}</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Request Details</h5>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Client:</strong> {{ $request->clientName }}</li>
                            <li class="list-group-item"><strong>Phone:</strong> {{ $request->phone }}</li>
                            <li class="list-group-item"><strong>Address:</strong> {{ $request->address }}</li>
                            <li class="list-group-item"><strong>Problem:</strong> {{ $request->problemText }}</li>
                            <li class="list-group-item"><strong>Current Status:</strong> {{ $request->status }}</li>
                            <li class="list-group-item"><strong>Created At:</strong> {{ $request->created_at->format('Y-m-d H:i') }}</li>
                        </ul>
                    </div>

                    <h5>Audit Log</h5>
                    @if ($logs->isEmpty())
                        <p>No history found for this request.</p>
                    @else
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $log->user->name ?? 'System' }}</td>
                                        <td>{{ $log->action }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                     <a href="{{ route('dispatcher.dashboard') }}" class="btn btn-secondary mt-3">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
@endsection
