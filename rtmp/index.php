<?php
declare(strict_types=1);

$requestedStream = $_GET["stream"] ?? "live";
$stream = is_string($requestedStream) && preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/', $requestedStream) === 1
    ? $requestedStream
    : "live";
$source = "/tv/" . rawurlencode($stream) . "/index.m3u8";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RTMP/HLS demo</title>
    <link rel="stylesheet" href="https://vjs.zencdn.net/8.23.9/video-js.css">
    <style>
        body { margin: 0; background: #111; color: #fff; font-family: sans-serif; }
        main { width: min(960px, 94vw); margin: 4vh auto; }
        .video-js { width: 100%; aspect-ratio: 16 / 9; }
        code { overflow-wrap: anywhere; }
    </style>
</head>
<body>
    <main>
        <h1>RTMP/HLS demo</h1>
        <video class="video-js vjs-big-play-centered" controls preload="auto" data-setup="{}">
            <source src="<?= htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") ?>" type="application/x-mpegURL">
        </video>
        <p>Playing <code><?= htmlspecialchars($source, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") ?></code></p>
    </main>
    <script src="https://vjs.zencdn.net/8.23.9/video.min.js"></script>
</body>
</html>
