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
CWD=$(pwd)
php -t web/ -S localhost:8000 $CWD/vendor/mintyphp/tools/server.php
rm -Rf web/debugger