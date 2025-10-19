@extends('layouts/contentNavbarLayout')

@section('title', 'Users - Management')

@section('content')
  <!-- Alert containers -->
  <div class="alert-container mb-3">
    <div id="successAlert" class="alert alert-success alert-dismissible fade" role="alert" style="display: none;">
      <span id="successMessage"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div id="errorAlert" class="alert alert-danger alert-dismissible fade" role="alert" style="display: none;">
      <span id="errorMessage"></span>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>

  <div class="card">
    <h5 class="card-header">Downloaded Torrents</h5>
    <div class="table-responsive text-nowrap">
      <table class="table" id="torrentTable">
        <thead>
          <tr>
            <th>Name</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody class="table-border-bottom-0" id="torrentBody">
          @foreach ($torrents as $torrent)
            <tr data-hash="{{ $torrent['hash'] }}">
              <td>{{ $torrent['name'] }}</td>
              <td>
                <span class="badge bg-label-primary me-1">{{ $torrent['state'] }}</span>
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="icon-base bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <form action="{{ route('convert') }}" method="POST" class="action-form">
                      @csrf
                      <input type="hidden" name="hash" value="{{ $torrent['hash'] }}">
                      <button type="submit" class="dropdown-item">
                        <i class="icon-base bx bx-repeat me-1"></i>Convert
                      </button>
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

  {{-- <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Show alert notification
      function showAlert(type, message) {
        const alertElement = type === 'success' ? document.getElementById('successAlert') : document.getElementById(
          'errorAlert');
        const messageElement = type === 'success' ? document.getElementById('successMessage') : document
          .getElementById('errorMessage');

        messageElement.textContent = message;
        alertElement.style.display = 'block';
        alertElement.classList.add('show');

        // Hide the alert after 5 seconds
        setTimeout(() => {
          alertElement.classList.remove('show');
          alertElement.style.display = 'none';
        }, 5000);
      }

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
          .catch(error => {
            console.error("Error updating torrents:", error);
            // Optionally show error alert for refresh errors
            // showAlert('error', 'Failed to refresh torrent list');
          });
      }

      updateTorrents();
      setInterval(updateTorrents, 3000);
    });
  </script> --}}
@endsection
