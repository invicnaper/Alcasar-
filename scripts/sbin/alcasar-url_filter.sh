#/bin/bash

# alcasar-url_filter.sh
# by REXY
# This script is distributed under the Gnu General Public License (GPL)

# Active / désactive : safesearch des moteurs de recherche ainsi que le filtrage Youtube
# Enable / disable : search engines safesearch and Youtube filtering 
# Active / désactive : le filtrage des url contenant une adresse ip à la place d'un nom de domaine
# Enable / disable : filter of urls containing ip address instead of domain name 

DIR_DG="/etc/dansguardian/lists"
DNSMASQ_BL_CONF="/etc/dnsmasq-blackhole.conf"
CONF_FILE="/usr/local/etc/alcasar.conf"
SED="/bin/sed -i"
safesearch="Off"
pureip="Off"
usage="Usage: alcasar-url_filter.sh { -safesearch_on or -safesearch_off } & { -pureip_on or --pureip_off }"
nb_args=$#
if [ $nb_args -le 1 ]
then
	echo "$usage"
	nb_args=0
else
	while [ $nb_args -ge 1 ]
	do
		arg=${!nb_args}
		case $arg in
		-\? | -h* | --h*)
			echo "$usage"
			exit 0
			;;
		# Safe search activation
		-safesearch_on | --safesearch_on)	
			safesearch="On"
			;;
		# Safe search desactivation
		-safesearch_off | --safesearch_off)	
			safesearch="Off"
			;;
		# pure_ip activation
		-pureip_on | --pureip_on)
			pureip="On"
			;;
		# pureip desactivation
		-pureip_off | --pureip_off)
			pureip="Off"
			;;
		*)
			echo "Argument inconnu :$arg";
			echo "$usage"
			exit 1
			;;
		esac
	nb_args=$(expr $nb_args - 1)
	done
	if [ $safesearch == "On" ]
	then
		$SED "s?^#\"?\"?g" $DIR_DG/urlregexplist # on décommente les lignes de regles
		youtube_id=`grep YOUTUBE_ID $CONF_FILE|cut -d"=" -f2`
		$SED "s?\&edufilter=.*?\&edufilter=$youtube_id\"?g" $DIR_DG/urlregexplist
# add 'nosslsearch' redirection for google searching
		$SED "/google/d" $DNSMASQ_BL_CONF # remove old google declaration
		nossl_server=`host -ta nosslsearch.google.com|cut -d" " -f4`	# retrieve google nosslsearch ip
		echo "# nosslsearch redirect server for google" >> $DNSMASQ_BL_CONF
		echo "address=/www.google.com/$nossl_server" >> $DNSMASQ_BL_CONF
		echo "address=/www.google.fr/$nossl_server" >> $DNSMASQ_BL_CONF
	else
		$SED "s?^[^#]?#&?g" $DIR_DG/urlregexplist
		$SED "/google/d" $DNSMASQ_BL_CONF
	fi
	if [ $pureip == "On" ]
	then
		$SED "s/^\#\*ip$/*ip/g" $DIR_DG/bannedsitelist
	else
		$SED "s/^\*ip$/#*ip/g" $DIR_DG/bannedsitelist
	fi
service dansguardian restart
service dnsmasq restart
fi

