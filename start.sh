#!/bin/bash
php tools/requirements.php
if [[ $? != 0 ]] ; then
    echo "requirements failed" && exit
fi
php -t web/ -S localhost:8000 tools/server.php
