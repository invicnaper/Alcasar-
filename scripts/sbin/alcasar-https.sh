#/bin/bash
# $Id: alcasar-https.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-dhcp.sh
# by Rexy
# This script is distributed under the Gnu General Public License (GPL)

# active ou désactive le chiffrement sur les flux d'authentification
# enable or disable encryption on authentication flows

SED="/bin/sed -i"
CHILLI_CONF_FILE="/etc/chilli.conf"
INTERCEPT_FILE="/var/www/html/intercept.php"

usage="Usage: alcasar-https.sh {--on | -on} | {--off | -off}"
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
	echo "$usage"
	exit 1
fi
case $args in
	-\? | -h* | --h*)
		echo "$usage"
		exit 0
		;;
	--off|-off) # disable HTTPS 
		$SED "/# If https not use/,/}/s?^?#?" $INTERCEPT_FILE
		$SED "s?uamserver.*?uamserver\thttp://alcasar/intercept.php?" $CHILLI_CONF_FILE
		/etc/init.d/chilli restart
		;;
	--on|-on) # enable HTTPS
		$SED "/## If https not use/,/#}/s?^#??" $INTERCEPT_FILE
		$SED "s?uamserver.*?uamserver\thttps://alcasar/intercept.php?" $CHILLI_CONF_FILE
		/etc/init.d/chilli restart
		;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac

