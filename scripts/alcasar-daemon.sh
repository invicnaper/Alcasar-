#!/bin/sh
# $Id: alcasar-daemon.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-daemon.sh
# by Franck BOUIJOUX
# This script is distributed under the Gnu General Public License (GPL)
# Watchdog of Services
# See /etc/cron.d/alcasar-daemon-watchdog for config the time

conf_file="/usr/local/etc/alcasar.conf"
SSH=`grep SSH= $conf_file|cut -d"=" -f2`				# sshd active (on/off)
SSH=${SSH:=off}
SERVICE="sshd dnsmasq httpd chilli radiusd mysqld dansguardian dnsmasq havp freshclam ntpd squid master squid"

function ServiceTest () {
	 CMD=`pidof $s`
	 if [ -z "$CMD" ]
	 then
	    service $s restart
#	 else
#	    echo "Service $s is On on PID : $CMD"
	 fi
}


for s in $SERVICE
do
	if [ $s != "sshd" ] 
	then
	    ServiceTest
	else
	{
	    if [ $SSH == "ON" ] | [ $SSH == "on" ] | [ $SSH == "On" ]
	    then
		  ServiceTest
	    fi
	}
	fi
done
