#/bin/bash
# $Id: alcasar-havp.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-havp.sh
# by Rexy
# This script is distributed under the Gnu General Public License (GPL)

# Activation / désactivation antivirus de flux WEB (Havp + LibClamav)
# Enable / disable of WEB flow antivirus (HAVP + LibClamav)
 
CONF_FILE="/usr/local/etc/alcasar.conf"
SED="/bin/sed -i"
function av_disable (){
	$SED "s/^cache_peer.*/#cache_peer 127\.0\.0\.1 parent 8090 0 no-query default/g" /etc/squid/squid.conf
	$SED "s/^never_direct.*/#never_direct allow all/g" /etc/squid/squid.conf
	$SED "s/^WEB_ANTIVIRUS=.*/WEB_ANTIVIRUS=off/g" /usr/local/etc/alcasar.conf
	service squid reload
	service havp stop
}
function av_enable (){
	$SED "s/^#cache_peer.*/cache_peer 127\.0\.0\.1 parent 8090 0 no-query default/g" /etc/squid/squid.conf
	$SED "s/^#never_direct.*/never_direct allow all/g" /etc/squid/squid.conf
	$SED "s/^WEB_ANTIVIRUS=.*/WEB_ANTIVIRUS=on/g" /usr/local/etc/alcasar.conf
	service squid reload
	service havp start
}
usage="Usage: alcasar-havp.sh {--on or -on} | {--off or -off} | {--update or -update}"
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
	AV_FILTERING=`grep WEB_ANTIVIRUS $CONF_FILE|cut -d"=" -f2`		# WEB-antivir  (on/off)
	AV_FILTERING=${AV_FILTERING:=on}
	echo "Set antivirus Filtering to $AV_FILTERING"
	if [ $AV_FILTERING = on ]; then
		av_enable
	else
		av_disable
	fi
	exit 0
fi
case $args in
	-\? | -h* | --h*)
		echo "$usage"
		exit 0
		;;
	--on|-on)	
		av_enable
		;;
	--off|-off)
		av_disable
		;;
	--update|-update)
		#mise à jour de la base de signature
		freshclam
		;;		
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac

