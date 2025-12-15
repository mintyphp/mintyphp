#!/bin/bash
if [ ! -f composer.phar ]; then
    wget https://getcomposer.org/composer.phar
fi
php composer.phar install
CWD=$(pwd)
php -t web/ -S localhost:8000 $CWD/vendor/mintyphp/core/src/Tools/Server.php
