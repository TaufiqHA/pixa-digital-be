@extends('layouts/contentNavbarLayout')

@section('title', 'User Details')

@section('content')
<h2 class="mb-4">User Details</h2>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>ID:</strong> {{ $user->id }}</p>
                <p><strong>Name:</strong> {{ $user->name }}</p>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>Created At:</strong> {{ $user->created_at->format('M d, Y H:i') }}</p>
                <p><strong>Updated At:</strong> {{ $user->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to Users</a>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit</a>
        </div>
    </div>
</div>
@endsection