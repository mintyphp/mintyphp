#!/bin/bash
cd "$(dirname "$(readlink -f "$0")")"
php phpunit.phar --bootstrap src/Loader.php src/Tests
