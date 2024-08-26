#!/bin/bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
brew tap denji/nginx
brew install nginx-full --with-rtmp-module
brew install php
brew install mysql
brew services start mysql
mysql_secure_installation
brew install phpmyadmin
brew services restart nginx-full
brew services restart php
brew services restart mysql
#sudo chmod -R 700 /usr/local/var/run/nginx/
#sudo chown -R www-data:www-data /usr/local/var/run/nginx/
#HTDOCS: /usr/local/var/www/
#CONF: /usr/local/etc/nginx/nginx.conf