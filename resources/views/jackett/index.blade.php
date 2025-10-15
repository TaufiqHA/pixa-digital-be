@extends('layouts/contentNavbarLayout')

@section('title', 'Jackett Search')

@section('content')
  <div class="col-xl">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Torrent Search</h5>
      </div>
      <div class="card-body">
        <!-- Success Message -->
        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <!-- Error Message -->
        @if (session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <!-- Error Messages for Validation -->
        @if ($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        <form action="{{ route('jackett.search') }}" method="POST">
          @csrf
          <input type="hidden" name="type" value="movie">
          <div class="mb-6">
            <label class="form-label" for="basic-icon-default-fullname">Query</label>
            <div class="input-group input-group-merge">
              <span id="basic-icon-default-fullname2" class="input-group-text"><i
                  class="icon-base bx bx-search-alt"></i></span>
              <input type="text" name="query" class="form-control" id="basic-icon-default-fullname"
                placeholder="Avengers: Endgame" aria-label="John Doe" aria-describedby="basic-icon-default-fullname2"
                value="{{ old('query') }}" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Send</button>
        </form>
      </div>
    </div>
  </div>

  <div class="card mt-5">
    <h5 class="card-header">Result</h5>
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Size</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0">
          @foreach ($results as $result)
            <tr>
              <td><i class="icon-base bx bx-search-alt icon-md text-primary me-4"></i> <span>{{ $result['title'] }}</span>
              </td>
              <td>{{ $result['size'] }}</td>
              <td>
                <form action="{{ route('jackett.add') }}" method="POST" style="display:inline;">
                  @csrf
                  <input type="hidden" name="magnet_uri" value="{{ $result['link'] ?? '' }}">
                  <input type="hidden" name="name" value="{{ $result['title'] }}">
                  <input type="hidden" name="size" value="{{ $result['size'] }}">
                  <input type="hidden" name="status" value="queued">
                  <button type="submit" class="btn btn-success">send to qbittorrent</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
