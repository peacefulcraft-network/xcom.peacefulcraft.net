#! /bin/sh
composer --working-dir=test/ require
composer --working-dir=test/ dump-autoload -o
php -S 127.0.0.1:8081 public/entrypoint-development.php