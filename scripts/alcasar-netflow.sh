#!/bin/bash

NOW=$(date +%G%m%d-%Hh%M)
DIR_LOG="/var/log/nfsen/profiles-data/live/ipt_netflow"
DIR_SAVE="/var/Save/logs/firewall"
EXPIRE_DELAY=7

cd $DIR_LOG
find . -mtime 0 -mtime -$EXPIRE_DELAY -name 'nfcapd.[0-9]*' | xargs tar -czf $DIR_SAVE/tracability.log-$NOW.tar.gz;

exit 0
