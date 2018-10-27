#!/bin/bash
if [ ! -f composer.phar ]; then
    wget https://getcomposer.org/composer.phar
fi
php composer.phar update
php vendor/mintyphp/tools/requirements.php
if [[ $? != 0 ]]; then
    exit
fi
cp -r vendor/mintyphp/debugger web/
php -t web/ -S localhost:8000 vendor/mintyphp/tools/server.php
rm -Rf web/debugger