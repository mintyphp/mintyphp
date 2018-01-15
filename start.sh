#!/bin/bash
DIR="$(dirname "$(readlink -f "$0")")"
cd "$DIR"
php tools/requirements.php
if [[ $? != 0 ]] ; then
    exit
fi
php -t ../../../webroot/ -S localhost:8000 "$DIR/tools/server.php"
