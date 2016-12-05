#!/bin/bash
PHP7=/usr/local/php7/bin/php

OPT=$1

case ${OPT} in
    "start")
        if [ `pgrep -f 'php' | wc -l` -eq 0 ]; then
            ${PHP7} /vagrant/setup; ${PHP7} -S 0.0.0.0:3939 /vagrant/htdocs/index.php &
        fi
        ;;
    "kill")
        pkill -f 'php'; echo "killed";;
esac
