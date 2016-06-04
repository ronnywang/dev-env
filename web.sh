#!/bin/sh

php worker.php &
php -S 0:$PORT index.php
