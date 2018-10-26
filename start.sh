#!/bin/bash
if [ ! -f composer.phar ]; then
    wget https://getcomposer.org/composer.phar
fi
php composer.phar update
php vendor/mindaphp/tools/requirements.php
if [[ $? != 0 ]]; then
    exit
fi
php -t web/ -S localhost:8000 vendor/mindaphp/tools/server.php
