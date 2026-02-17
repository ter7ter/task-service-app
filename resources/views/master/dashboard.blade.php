@extends('layouts.app')

@section('title', 'Master Dashboard')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Master Dashboard</div>
                <div class="card-body">
                    Welcome, Master {{ Auth::user()->name }}!
                    <hr>
                    <h3>Assigned Requests</h3>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if ($requests->isEmpty())
                        <p>No assigned requests found.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Problem</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>{{ $request->clientName }}</td>
                                        <td>{{ $request->phone }}</td>
                                        <td>{{ $request->address }}</td>
                                        <td>{{ Str::limit($request->problemText, 30) }}</td>
                                        <td>{{ $request->status }}</td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            @if ($request->status == 'assigned')
                                                <form action="{{ route('master.requests.take_in_work', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-info">Take In Work</button>
                                                </form>
                                            @elseif ($request->status == 'in_progress')
                                                <form action="{{ route('master.requests.complete', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
