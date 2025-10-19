@extends('layouts/contentNavbarLayout')

@section('title', 'Jackett Search')

@section('content')
  <div class="col-xl">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Search Movie</h5>
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

        <form action="{{ route('radarr.search') }}" method="POST">
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
    @if ($results && count($results) > 0)
      <div class="table-responsive text-nowrap">
        <table class="table">
          <thead>
            <tr>
              <th>Poster</th>
              <th>Title</th>
              <th>Year</th>
              <th>Genres</th>
              <th>Rating</th>
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
                  @elseif (isset($result['Poster']) && $result['Poster'] !== 'N/A')
                    <img src="{{ $result['Poster'] }}" alt="{{ $result['title'] ?? ($result['Title'] ?? 'Poster') }}"
                      class="img-thumbnail" style="width: 60px; height: 90px; object-fit: cover;"
                      onerror="this.onerror=null; this.src='/storage/images/no-poster.svg';">
                  @else
                    <img src="/storage/images/no-poster.svg" alt="No Poster" class="img-thumbnail"
                      style="width: 60px; height: 90px; object-fit: cover;">
                  @endif
                </td>
                <td>
                  <i class="icon-base bx bx-search-alt icon-md text-primary me-4"></i>
                  <strong>{{ $result['title'] ?? ($result['Title'] ?? 'N/A') }}</strong>
                  <br>
                </td>
                <td>{{ $result['year'] ?? ($result['Year'] ?? 'N/A') }}</td>
                <td>
                  @if (isset($result['genres']))
                    {{ implode(', ', $result['genres'] ?? []) }}
                  @elseif(isset($result['Genres']))
                    {{ $result['Genres'] }}
                  @else
                    N/A
                  @endif
                </td>
                <td>
                  @if (isset($result['ratings']['imdb']['value']))
                    <span class="badge bg-label-warning">IMDB: {{ $result['ratings']['imdb']['value'] }}/10</span>
                  @elseif(isset($result['imdbRating']))
                    <span class="badge bg-label-warning">IMDB: {{ $result['imdbRating'] }}/10</span>
                  @else
                    N/A
                  @endif
                </td>
                <td>
                  <form action="{{ route('radarr.addMovie') }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="hidden" name="result" value="{{ json_encode($result) }}">
                    {{-- <input type="hidden" name="magnet_uri" value="{{ $result['link'] ?? ($result['Link'] ?? '') }}">
                    <input type="hidden" name="name" value="{{ $result['title'] ?? ($result['Title'] ?? '') }}">
                    <input type="hidden" name="size" value="{{ $result['size'] ?? '0' }}">
                    <input type="hidden" name="status" value="queued"> --}}
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
          <i class="bx bx-search-alt bx-lg text-muted"></i>
          <p class="text-muted">No results found. Try searching for a movie or TV show.</p>
        </div>
      </div>
    @endif
  </div>
@endsection
