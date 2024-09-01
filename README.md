# NGINX Configurations

This repository contains a collection of NGINX configuration files to help you set up and manage various server environments. These configurations cover a range of use cases including fastcgi, reverse proxy, PHP handling, MySQL integration, and more.

## Table of Contents

-   [Files](#files)
-   [Usage](#usage)
-   [License](#license)

## Files

### 1. `install_MacNginxPHPmysql.sh`

A shell script to install and configure NGINX, PHP, and MySQL on macOS. It automates the setup process for a local development environment.

### 2. `start_MacNginxPHPmysql.sh`

A shell script to start NGINX, PHP, and MySQL on macOS. This script is often used after running the installation script to begin services.

### 3. `nginx.conf`

The main configuration file for NGINX. It includes global settings and can include other configuration files.

### 4. `nginx.conf.default`

A default configuration file provided by NGINX. This file serves as a starting point and can be customized as needed.

### 5. `server.common.conf`

A file containing common server configurations that can be included in other server blocks. It helps maintain consistency across multiple server configurations.

### 6. `localhost.conf`

Generic NGINX configuration for localhost. It can be used as a template for setting up other local development environments.

### 7. `localhost.byuwur.conf`

NGINX configuration for a `byuwur` local development environment, containing server block configurations for the `byuwur` application.

### 8. `location.php.conf`

This file contains location blocks to handle PHP requests. It is typically included in other server block configurations.

### 9. `location.phpmyadmin.conf`

This configuration is used to serve the `phpMyAdmin` interface securely, typically within a local or restricted environment.

### 10. `location.router.conf`

This file configures NGINX to work with client-side routers, often used in single-page applications (SPAs) where the URL paths need to be handled by the frontend application.

### 11. `location.tv.conf`

Configuration for serving a TV-related application. It includes specific location blocks and other server configurations.

### 12. `location.blacklist.conf`

This configuration file is used to deny access to certain IP addresses or ranges. Useful for blocking unwanted traffic.

### 13. `fastcgi.conf`

This file is an alternative to `fastcgi_params` and includes more detailed configuration options for FastCGI.

### 14. `fastcgi_params`

This file contains standard parameters required by FastCGI. It is typically used when setting up PHP-FPM with NGINX.

### 15. `scgi_params`

Parameters used by SCGI, another protocol similar to FastCGI. It is used in configurations where SCGI is the chosen protocol for handling requests.

### 16. `uwsgi_params`

Parameters required by uWSGI, which is often used for serving Python applications. This file is used when configuring NGINX to work with uWSGI.

### 17. `rtmp.conf`

Configuration for RTMP (Real-Time Messaging Protocol), often used for streaming media.

### 18. `mime.types`

Defines the MIME types recognized by NGINX. This file helps NGINX serve files with the correct content type.

### 19. `koi-utf`

This file defines character encoding mappings for KOI8-U and UTF-8, commonly used in Russian-speaking environments.

### 20. `koi-win`

Similar to `koi-utf`, but this file maps KOI8-WIN encoding to UTF-8, often used in legacy systems.

### 21. `win-utf`

Similar to `koi-utf`, this file maps Windows code pages to UTF-8, ensuring proper character encoding.

## Usage

To use these configurations, copy the relevant files to your NGINX configuration directory (usually `/etc/nginx/` on Linux systems or `/usr/local/etc/nginx/` on macOS). You can include or modify these configurations in your `nginx.conf` or other server-specific configuration files.

For example, to include the `fastcgi_params` in your server block:

```nginx
server {
    listen 80;
    server_name example.com;

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## License

MIT (c) Andrés Trujillo [Mateus] byUwUr
