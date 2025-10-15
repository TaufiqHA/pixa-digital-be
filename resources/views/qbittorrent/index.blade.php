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
                    <form action="{{ route('qbittorrent.pause') }}" method="POST" class="action-form">
                      @csrf
                      <input type="hidden" name="hash" value="{{ $torrent['hash'] }}">
                      <button type="submit" class="dropdown-item">
                        <i class="icon-base bx bx-pause me-1"></i>Pause
                      </button>
                    </form>
                    <form action="{{ route('qbittorrent.resume') }}" method="POST" class="action-form">
                      @csrf
                      <input type="hidden" name="hash" value="{{ $torrent['hash'] }}">
                      <button type="submit" class="dropdown-item">
                        <i class="icon-base bx bx-play me-1"></i>Resume
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

  <script>
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

      // Handle form submissions for pause/resume
      // document.querySelectorAll('.action-form').forEach(form => {
      //   form.addEventListener('submit', function(e) {
      //     e.preventDefault();

      //     const formData = new FormData(this);
      //     const action = this.getAttribute('action');

      //     fetch(action, {
      //       method: 'POST',
      //       body: formData,
      //       headers: {
      //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      //       }
      //     })
      //     .then(response => response.json())
      //     .then(data => {
      //       if (data.success) {
      //         showAlert('success', data.message || 'Operation completed successfully');
      //         // Refresh torrent list after successful action
      //         updateTorrents();
      //       } else {
      //         showAlert('error', data.message || 'An error occurred');
      //       }
      //     })
      //     .catch(error => {
      //       console.error('Error:', error);
      //       showAlert('error', 'An error occurred while processing your request');
      //     });
      //   });
      // });

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
  </script>
@endsection
