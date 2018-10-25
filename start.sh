#!/bin/bash
php tools/requirements.php
if [[ $? != 0 ]] ; then
    exit
fi
php composer.phar install -q
php -t web/ -S localhost:8000 tools/server.php
