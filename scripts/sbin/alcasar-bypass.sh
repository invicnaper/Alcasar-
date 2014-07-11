#!/bin/bash
# $Id: alcasar-bypass.sh 1062 2013-04-01 21:20:12Z richard $

# alcasar-bypass.sh
# by Franck BOUIJOUX and Richard REY
# This script is distributed under the Gnu General Public License (GPL)

# activation / désactivation du contournement de l'authentification et du filtrage WEB
# enable / disable the bypass of authenticate process and filtering

usage="Usage: alcasar-bypass.sh {--on or -on } | {--off or -off}"
SED="/bin/sed -i"
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
	--on | -on)	
		# activation du contournement
		for i in chilli dansguardian havp mysqld radiusd httpd freshclam dnsmasq squid 
		do
			if  (pgrep $i) > /dev/null ; then /etc/init.d/$i stop ; fi
		done
		echo "Configure eth1 ..."
		cp /etc/sysconfig/network-scripts/default-ifcfg-eth1 /etc/sysconfig/network-scripts/ifcfg-eth1
		ifup eth1
		sh /usr/local/bin/alcasar-iptables-bypass.sh
		echo "Configure dnsmasq ..."
		$SED "s?^conf-dir=.*?#&?g" /etc/dnsmasq-blackhole.conf
		$SED "s?^no-dhcp-interface=.*?#&?g" /etc/dnsmasq.conf /etc/dnsmasq-blackhole.conf
		/etc/init.d/dnsmasq start
		echo "Le contournement des modules d'authentification de filtrage est activé"
		echo "les journaux de connexions continuent néanmoins d'être enregistrés"
		;;
	--off | -off)
		# désactivation du contournement
		if (pgrep dnsmasq) > /dev/null ; then /etc/init.d/dnsmasq stop ; fi
		echo "Configure dnsmasq ..."
		$SED "s?^#conf-dir=.*?conf-dir=/usr/local/share/dnsmasq-bl-enabled?g" /etc/dnsmasq-blackhole.conf
		$SED "s?^#no-dhcp-interface=.*?no-dhcp-interface=eth1?g" /etc/dnsmasq.conf /etc/dnsmasq-blackhole.conf
		rm -f /etc/sysconfig/network-scripts/ifcfg-eth1
		for i in chilli dansguardian havp mysqld radiusd httpd freshclam dnsmasq squid	
		do
			if  ! (pgrep $i) > /dev/null ; then /etc/init.d/$i start ; fi
		done
		sh /usr/local/bin/alcasar-iptables.sh
		echo "L'authentification et le filtrage sont de nouveau activés"
;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac
