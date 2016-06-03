#!/bin/sh

PATH=/app/.heroku/php/bin:/app/.heroku/php/sbin:/app/.heroku/php/bin:/app/.heroku/php/sbin:/app/.heroku/php/bin:/app/.heroku/php/sbin:/app/.heroku/php/bin:/usr/local/bin:/usr/bin:/bin:/app/vendor/bin
php worker.php &
php -S 0:$PORT index.php
