<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Coba HLS Player</title>
</head>

<body>
  <video id="video" controls width="640" height="360"></video>

  <!-- Library hls.js -->
  <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const video = document.getElementById("video");
      const videoSrc = "{{ asset('storage/hls/182b1965adf9dcd3cdae27c68de9e810cef75346/index.m3u8') }}";

      if (Hls.isSupported()) {
        const hls = new Hls({
          startPosition: 0
        });
        hls.loadSource(videoSrc);
        hls.attachMedia(video);
        hls.on(Hls.Events.MANIFEST_PARSED, function() {
          video.play();
        });
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        // untuk Safari
        video.src = videoSrc;
        video.addEventListener('loadedmetadata', function() {
          video.play();
        });
      } else {
        alert("Browser kamu tidak mendukung HLS playback");
      }
    });
  </script>
</body>

</html>
