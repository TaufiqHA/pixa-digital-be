@extends('layouts/contentNavbarLayout')

@section('title', 'Users - Management')

@section('content')
  <div class="d-flex justify-content-between">
    <h2 class="fs-3 mb-4">Users</h2>
    <a href="{{ route('users.create') }}" type="button" class="btn btn-primary mb-5">Create New User</a>
  </div>

  @if (session('success'))
    <div class="alert alert-success">
      {{ session('success') }}
    </div>
  @endif

  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($users as $user)
            <tr>
              <td><span>{{ $user->id }}</span></td>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td><span>{{ $user->created_at }}</span></td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i
                      class="icon-base bx bx-dots-vertical-rounded"></i></button>
                  <div class="dropdown-menu">
                    <a href="{{ route('users.show', $user) }}" class="dropdown-item"><i
                        class="icon-base bx bx-user me-1"></i>
                      View</a>
                    <a href="{{ route('users.edit', $user) }}" class="dropdown-item"><i
                        class="icon-base bx bx-edit-alt me-1"></i>
                      Edit</a>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure?')"><i
                          class="icon-base bx bx-trash-alt me-1"></i> Delete</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
