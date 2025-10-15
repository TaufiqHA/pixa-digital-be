@extends('layouts/contentNavbarLayout')

@section('title', 'Users - Management')

@section('content')
  <div class="card">
    <h5 class="card-header">Download Progress</h5>
    <div class="table-responsive text-nowrap">
      <table class="table" id="torrentTable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Progress</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0" id="torrentBody">
          @foreach ($torrents as $torrent)
            <tr data-hash="{{ $torrent['hash'] }}">
              <td>{{ $torrent['name'] }}</td>
              <td>
                <div class="progress" style="height: 16px;">
                  <div class="progress-bar" role="progressbar"
                    style="width: {{ number_format($torrent['progress'] * 100, 1) }}%;"
                    aria-valuenow="{{ number_format($torrent['progress'] * 100, 1) }}" aria-valuemin="0"
                    aria-valuemax="100">
                    {{ number_format($torrent['progress'] * 100, 1) }}%
                  </div>
                </div>
              </td>
              <td>
                <span class="badge bg-label-primary me-1">{{ $torrent['state'] }}</span>
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="icon-base bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);">
                      <i class="icon-base bx bx-edit-alt me-1"></i>Edit
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);">
                      <i class="icon-base bx bx-trash me-1"></i>Delete
                    </a>
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      function updateTorrents() {
        fetch("{{ route('qbittorrent.refresh') }}")
          .then(response => response.json())
          .then(data => {
            data.forEach(torrent => {
              // cari baris berdasarkan hash
              const row = document.querySelector(`tr[data-hash="${torrent.hash}"]`);
              if (!row) return; // skip kalau baris tidak ditemukan

              // update progress
              const progressBar = row.querySelector('.progress-bar');
              const percent = (torrent.progress * 100).toFixed(1);
              progressBar.style.width = `${percent}%`;
              progressBar.setAttribute('aria-valuenow', percent);
              progressBar.textContent = `${percent}%`;

              // update status
              const statusBadge = row.querySelector('.badge');
              statusBadge.textContent = torrent.state;

              // ubah warna badge sesuai status (opsional)
              statusBadge.className = 'badge me-1 ' + (
                torrent.state === 'downloading' ? 'bg-label-info' :
                torrent.state === 'completed' ? 'bg-label-success' :
                'bg-label-secondary'
              );
            });
          })
          .catch(error => console.error("Error updating torrents:", error));
      }

      updateTorrents();
      setInterval(updateTorrents, 3000);
    });
  </script>
@endsection
