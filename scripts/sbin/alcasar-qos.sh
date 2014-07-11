#/bin/bash
# $Id: alcasar-qos.sh 1258 2013-12-04 21:51:29Z franck $

# alcasar-CA.sh
# by Franck BOUIJOUX
# This script is distributed under the Gnu General Public License (GPL)

# Active / désactive la qualite de service réseau
# Enable / disable QOS

SED="/bin/sed -i"
FIC_QOS="/usr/local/etc/alcasar-iptables-qos.sh"

usage="Usage: alcasar-qos.sh {--on or -on} | {--off | -off} "
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
	nb_args=1
	args="-h"
fi
case $args in
	-\? | -h* | --h*)
		echo "$usage"
		exit 0
		;;
	--on|-on)	
		# activation du filtrage réseau
		if [ -e $FIC_QOS ] 
		then 
			$SED "s?^QOS.*?QOS=\"on\"?g" /usr/local/bin/alcasar-iptables.sh
			/usr/local/bin/alcasar-iptables.sh
		else	
			exit 2
		fi
		;;
	--off|-off)
		# désactivation du filtrage réseau
		$SED "s?^QOS.*?QOS=\"off\"?g" /usr/local/bin/alcasar-iptables.sh
		/usr/local/bin/alcasar-iptables.sh
		;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac

