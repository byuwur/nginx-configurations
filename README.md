# Reusable Nginx configuration

A small set of reusable Nginx configurations for PHP front-controller applications, with optional RTMP ingest and HLS playback.

## Files and configuration contexts

```text
nginx.conf/
├── nginx.conf       # main, events, and http contexts
├── site.conf        # included inside an HTTP server block
├── rtmp.conf        # optional main-context RTMP block
├── rtmp/
│   ├── auth.php     # publish authorization callback
│   ├── index.php    # small HLS player demo
│   └── site.conf    # optional HTTP server locations for auth and HLS
├── _install.php.macos.sh  # optional Homebrew setup helper
├── _restart.php.macos.sh  # optional Homebrew restart helper
├── LICENSE.md
└── README.md
```

Nginx configuration has explicit levels:

- The system file, commonly `/etc/nginx/nginx.conf`, contains workers, `events {}`, `http {}`, optional `rtmp {}`, and global includes.
- A virtual host, commonly `/etc/nginx/sites-available/example.com`, contains `listen`, `server_name`, `root`, TLS, and includes reusable server-level configuration.
- Application configuration such as `site.conf` contains routing, PHP handling, access restrictions, headers, and error handling.

Unlike Apache `.htaccess`, Nginx does not discover configuration in a project's web root. Every file must be included explicitly from active Nginx configuration.

Paths in this repository use common Debian/Ubuntu locations. Nginx package layout, service user, and PHP-FPM endpoint vary on other distributions and macOS.

The macOS scripts preserve the original Homebrew workflow as optional helpers. Review their package names and options against the current Homebrew formulae before running them.

## System configuration

Use `nginx.conf` as a concise reference or install it as the system configuration after checking these values:

- `user www-data` must name the account used by your Nginx package.
- `/etc/nginx/mime.types`, `/etc/nginx/conf.d`, and `/etc/nginx/sites-enabled` must match the package layout.
- The system-provided MIME and FastCGI parameter files remain owned and updated by the Nginx package.

RTMP is disabled by default, so the base configuration starts without the RTMP module. Leave the final `include /etc/nginx/rtmp.conf;` commented unless RTMP support is installed and configured.

## Virtual host and PHP SPA

Install `site.conf` somewhere such as `/etc/nginx/snippets/php-spa-site.conf`, then include it inside each applicable site:

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/example/public;

    include /etc/nginx/snippets/php-spa-site.conf;
}
```

The reusable file deliberately does not set a domain, document root, TLS certificate, or PHP version. It provides:

- `home.php` as the preferred index and front controller.
- `try_files $uri $uri/ /home.php?uri=$uri&$args`, preserving the request URI and query string.
- PHP execution only for scripts that exist.
- Shared handling for 400, 401, 403, 404, 500, 502, 503, and 504 through `/_error.php?e=$status`.
- Re-entry protection if the error handler itself fails.
- Safe `nosniff`, referrer, and framing headers on all response statuses.
- Blocking for TRACE/TRACK, hidden path segments, sensitive/development files, editor backups, and archives outside approved download directories.

Exact public exceptions are limited to `/robots.txt`, `/ads.txt`, `/app-ads.txt`, `/humans.txt`, `/.well-known/security.txt`, `/manifest.json`, `/.well-known/assetlinks.json`, `/sitemap.xml`, `/sitemap_index.xml`, and `/browserconfig.xml`. `/.well-known/acme-challenge/` is also available for HTTP-01 validation. Arbitrary TXT, JSON, XML, YAML, and Markdown files remain blocked.

Archives are public only below `/downloads/` and `/releases/`, including nested paths. Edit the first archive location in `site.conf` to change that allowlist.

Change the PHP-FPM endpoint in `site.conf` for the target system. The committed TCP example is:

```nginx
fastcgi_pass 127.0.0.1:9000;
```

A typical socket alternative is:

```nginx
fastcgi_pass unix:/run/php/php8.x-fpm.sock;
```

The socket name is deployment-specific. The configuration uses the package's `/etc/nginx/fastcgi_params` and sets `SCRIPT_FILENAME` to `$document_root$fastcgi_script_name`.

If the error handler is stored somewhere other than `/_error.php`, change the `error_page` URI. For example, a project that exposes SPA.php as a subdirectory may use `/spa.php/_error.php?e=$status`.

## Optional RTMP and HLS

RTMP requires an Nginx build or package containing `nginx-rtmp-module`. Copy `rtmp.conf` to the configured Nginx directory, make these two paths identical, and give the Nginx worker write access:

```nginx
# rtmp.conf
hls_path /var/lib/nginx/hls;

# rtmp/site.conf
alias /var/lib/nginx/hls/;
```

Enable RTMP from the main context, alongside `http {}` rather than inside it:

```nginx
include /etc/nginx/rtmp.conf;
```

Copy the repository's `rtmp/` application files under the streaming virtual host's document root, or adjust `$document_root/rtmp/auth.php` in `rtmp/site.conf`. Include both reusable server files in that host:

```nginx
server {
    listen 80;
    server_name stream.example.com;
    root /var/www/stream.example.com/public;

    include /etc/nginx/snippets/php-spa-site.conf;
    include /etc/nginx/snippets/rtmp-site.conf;
}
```

`rtmp/site.conf` adds a loopback-only listener on port 8080 for the authorization callback and repeats the PHP-FPM endpoint used by `site.conf`. If that port or endpoint changes, keep `rtmp.conf`, `rtmp/site.conf`, and `site.conf` synchronized. The HLS location serves `.m3u8` and `.ts` with live-stream MIME types and no-cache headers. It does not enable CORS; add `Access-Control-Allow-Origin` only when the player is intentionally hosted on another origin.

Set a long random `RTMP_STREAM_KEY` in the PHP-FPM service or pool environment. PHP-FPM commonly clears its environment, so expose it explicitly in the pool configuration without committing the real value to this repository:

```ini
env[RTMP_STREAM_KEY] = your-deployment-secret
```

Restart PHP-FPM after changing its environment. If the variable is absent, the callback fails closed with 503.

Configure an encoder with:

```text
Server:     rtmp://stream.example.com/tv
Stream key: live?token=your-deployment-secret
```

The public stream name is `live`; the token is used only for authorization and is not placed in the HLS path. With `hls_nested on`, viewers use:

```text
https://stream.example.com/tv/live/index.m3u8
```

The demo player is available at `/rtmp/?stream=live`. It uses the same-origin HLS URL and a pinned HTTPS Video.js release. Self-host the Video.js assets if the deployment must work without a third-party CDN.

Direct RTMP playback is denied because HLS is the viewer transport. Publishing accepts only POST callbacks, validates the callback fields and stream name, and compares the token with `hash_equals`.

## Optional phpMyAdmin

phpMyAdmin is intentionally not exposed by `site.conf`. A separate loopback-only virtual host avoids silently adding `/phpmyadmin` to every application and keeps its installation path independent:

```nginx
server {
    listen 127.0.0.1:8081;
    server_name _;
    root /path/to/phpmyadmin;
    index index.php;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        try_files $uri =404;
        include /etc/nginx/fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass 127.0.0.1:9000;
    }
}
```

Replace the root and PHP-FPM endpoint. Use a private network allowlist, VPN, SSH tunnel, or additional authentication if access must extend beyond the local machine.

## Deployment checks

After installing or changing configuration, run:

```sh
sudo nginx -t
sudo nginx -T
```

The first command validates syntax and contexts. The expanded output from the second confirms which files are active and helps detect duplicate `location` blocks. Reload Nginx only after validation succeeds.

Content Security Policy is intentionally omitted because it requires an application-specific audit of scripts, APIs, reCAPTCHA, media, and fonts. HSTS is also deployment-specific and should be enabled only on an HTTPS-only host after its scope and lifetime are understood.

## License

MIT (c) Andres Trujillo [Mateus] byUwUr
