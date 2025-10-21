@extends('layouts/contentNavbarLayout')

@section('title', 'Sonarr Search')

@section('content')
  <div class="col-xl">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Search TV Shows</h5>
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

        <form action="{{ route('sonarr.search') }}" method="GET">
          <div class="mb-6">
            <label class="form-label" for="basic-icon-default-fullname">TV Show Title</label>
            <div class="input-group input-group-merge">
              <span id="basic-icon-default-fullname2" class="input-group-text"><i
                  class="icon-base bx bx-search-alt"></i></span>
              <input type="text" name="search" class="form-control" id="basic-icon-default-fullname"
                placeholder="Stranger Things" aria-label="Stranger Things" aria-describedby="basic-icon-default-fullname2"
                value="{{ request('search') }}" />
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Search</button>
        </form>
      </div>
    </div>
  </div>

  <div class="card mt-5">
    <h5 class="card-header">TV Show Results</h5>
    @if (isset($results) && count($results) > 0)
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Poster</th>
              <th>Title</th>
              <th>Year</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody class="table-border-bottom-0">
            @foreach ($results as $result)
              <tr>
                <td>
                  @if (isset($result['images']) && is_array($result['images']))
                    @php
                      $poster = collect($result['images'])->firstWhere('coverType', 'poster');
                    @endphp
                    @if ($poster)
                      <img src="{{ $poster['remoteUrl'] ?? $poster['url'] }}"
                        alt="{{ $result['title'] ?? ($result['Title'] ?? 'Poster') }}" class="img-thumbnail"
                        style="width: 60px; height: 90px; object-fit: cover;"
                        onerror="this.onerror=null; this.src='/storage/images/no-poster.svg';">
                    @else
                      <img src="/storage/images/no-poster.svg" alt="No Poster" class="img-thumbnail"
                        style="width: 60px; height: 90px; object-fit: cover;">
                    @endif
                  @elseif (isset($result['remotePoster']) && $result['remotePoster'] !== 'N/A' && $result['remotePoster'])
                    <img src="{{ $result['remotePoster'] }}" alt="{{ $result['title'] ?? 'Poster' }}"
                      class="img-thumbnail" style="width: 60px; height: 90px; object-fit: cover;"
                      onerror="this.onerror=null; this.src='/storage/images/no-poster.svg';">
                  @else
                    <img src="/storage/images/no-poster.svg" alt="No Poster" class="img-thumbnail"
                      style="width: 60px; height: 90px; object-fit: cover;">
                  @endif
                </td>
                <td>
                  <i class="icon-base bx bx-tv icon-md text-primary me-4"></i>
                  <strong>{{ $result['title'] ?? ($result['Title'] ?? 'N/A') }}</strong>
                </td>
                <td>{{ $result['year'] ?? ($result['Year'] ?? 'N/A') }}</td>
                <td>
                  <form action="{{ route('sonarr.addTvShow') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="result" value="{{ json_encode($result) }}">
                    <button type="submit" class="btn btn-success btn-sm">Download</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @else
      <div class="card-body">
        <div class="text-center py-4">
          <i class="bx bx-tv bx-lg text-muted"></i>
          <p class="text-muted">No TV shows found. Try searching for a TV series.</p>
        </div>
      </div>
    @endif
  </div>
@endsection
