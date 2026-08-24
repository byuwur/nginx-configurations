<?php
declare(strict_types=1);

function respond(int $status): void
{
    http_response_code($status);
    exit;
}

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    header("Allow: POST");
    respond(405);
}

$expected = getenv("RTMP_STREAM_KEY");
if (!is_string($expected) || $expected === "") {
    respond(503);
}

$name = $_POST["name"] ?? null;
$token = $_POST["token"] ?? null;
if (
    ($_POST["call"] ?? null) !== "publish"
    || !is_string($name)
    || preg_match('/\A[a-zA-Z0-9_-]{1,64}\z/', $name) !== 1
    || !is_string($token)
) {
    respond(400);
}

if (!hash_equals($expected, $token)) {
    respond(403);
}

respond(204);
