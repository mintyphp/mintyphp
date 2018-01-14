#!/bin/bash
cd "$(dirname "$(readlink -f "$0")")"
php tools/requirements.php
if [[ $? != 0 ]] ; then
    exit
fi
php -t ../../../web/ -S localhost:8000 tools/server.php
