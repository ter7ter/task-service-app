@extends('layouts.app')

@section('title', 'Dispatcher Dashboard')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">Dispatcher Dashboard</div>
                <div class="card-body">
                    Welcome, Dispatcher {{ Auth::user()->name }}!
                    <hr>
                    <h3>Repair Requests</h3>

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="mb-3">
                        <form action="{{ route('dispatcher.dashboard') }}" method="GET" class="d-flex">
                            <label for="statusFilter" class="form-label me-2 mb-0 align-self-center">Filter by Status:</label>
                            <select class="form-select me-2" id="statusFilter" name="status" onchange="this.form.submit()">
                                <option value="">All</option>
                                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>New</option>
                                <option value="assigned" {{ request('status') == 'assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                                <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </form>
                    </div>

                    @if ($requests->isEmpty())
                        <p>No requests found.</p>
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
                                    <th>Assigned To</th>
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
                                        <td>{{ $request->assignedTo->name ?? 'N/A' }}</td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap">
                                                <a href="{{ route('dispatcher.requests.history', $request->id) }}" class="btn btn-sm btn-info me-1 mb-1">History</a>
                                                @if ($request->status == 'new')
                                                    <form action="{{ route('dispatcher.requests.assign', $request->id) }}" method="POST" class="d-inline mb-1">
                                                        @csrf
                                                        <div class="input-group input-group-sm">
                                                            <select name="master_id" class="form-select form-select-sm">
                                                                @foreach ($masters as $master)
                                                                    <option value="{{ $master->id }}">{{ $master->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="submit" class="btn btn-sm btn-success">Assign</button>
                                                        </div>
                                                    </form>
                                                    <form action="{{ route('dispatcher.requests.cancel', $request->id) }}" method="POST" class="d-inline ms-1 mb-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                    </form>
                                                @elseif ($request->status == 'assigned' || $request->status == 'in_progress')
                                                    <form action="{{ route('dispatcher.requests.cancel', $request->id) }}" method="POST" class="d-inline mb-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                                    </form>
                                                @endif
                                            </div>
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
