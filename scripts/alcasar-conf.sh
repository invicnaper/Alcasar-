#/bin/bash
# $Id: alcasar-conf.sh 1269 2013-12-16 23:13:20Z richard $

# alcasar-conf.sh
# by REXY
# This script is distributed under the Gnu General Public License (GPL)

# Ce script permet la mise à jour ALCASAR 
#	- création de l'archive des fichiers de configuration (/tmp/alcasar-conf.tar.gz)
#	- chargement d'une archive (lors de la mise à jour d'un alcasar)
#	- application des directives du fichier de conf central "/usr/local/etc/alcasar.conf" (lors d'un changement de conf à chaud) 
# This script allows ALCASAR update 
#	- create the configuration files backup (/tmp/alcasar-conf.tar.gz)
#	- load the bachup of configuration files (during the update process)
#	- apply ALCASAR central configuration file "/usr/local/etc/alcasar.conf" (when hot modification are needed)

new="$(date +%G%m%d-%Hh%M)"  			# date et heure des fichiers
fichier="alcasar-conf-$new.tar.gz"		# nom du fichier de sauvegarde
DIR_UPDATE="/tmp/conf"				# répertoire de stockage des fichier de conf pour une mise à jour
DIR_WEB="/var/www/html"				# répertoire du centre de gestion
DIR_BIN="/usr/local/bin"			# répertoire des scripts d'admin
DIR_SBIN="/usr/local/sbin"			# répertoire des scripts d'admin
DIR_ETC="/usr/local/etc"			# répertoire des fichiers de conf
DIR_SAVE="/var/Save/system_backup"		# répertoire de sauvegarde
CONF_FILE="$DIR_ETC/alcasar.conf"		# main alcasar conf file
VERSION="/var/www/html/VERSION"			# contient la version en cours
EXTIF="eth0"					# ETH0 est l'interface connectée à Internet (Box FAI)
INTIF="eth1"					# ETH1 est l'interface connectée au réseau local de consultation
HOSTNAME="alcasar"
DB_USER="radius"
radiuspwd=""
SED="/bin/sed -i"
RUNNING_VERSION=`cat $VERSION|cut -d" " -f1`
MAJ_RUNNING_VERSION=`echo $RUNNING_VERSION|cut -d"." -f1`
MIN_RUNNING_VERSION=`echo $RUNNING_VERSION|cut -d"." -f2|cut -c1`
UPD_RUNNING_VERSION=`echo $RUNNING_VERSION|cut -d"." -f3`
DOMAIN=`grep DOMAIN $CONF_FILE|cut -d"=" -f2` 2>/dev/null # Error if (Version < 2.2) (no conf file)
DOMAIN=${DOMAIN:=localdomain}
DATE=`date '+%d %B %Y - %Hh%M'`
private_network_calc ()
{
	PRIVATE_PREFIX=`/bin/ipcalc -p $PRIVATE_IP $PRIVATE_NETMASK |cut -d"=" -f2`				# prefixe du réseau (ex. 24)
	PRIVATE_NETWORK=`/bin/ipcalc -n $PRIVATE_IP $PRIVATE_NETMASK| cut -d"=" -f2`				# @ réseau de consultation (ex.: 192.168.182.0)
	PRIVATE_NETWORK_MASK=$PRIVATE_NETWORK/$PRIVATE_PREFIX							# @ + masque du réseau de consult (192.168.182.0/24)
	classe=$((PRIVATE_PREFIX/8)); classe_sup=`expr $classe + 1`; classe_sup_sup=`expr $classe + 2`		# classes de réseau (ex.: 2=classe B, 3=classe C)
	PRIVATE_NETWORK_SHORT=`echo $PRIVATE_NETWORK | cut -d"." -f1-$classe`.					# @ compatible hosts.allow et hosts.deny (ex.: 192.168.182.)
	PRIVATE_BROADCAST=`/bin/ipcalc -b $PRIVATE_NETWORK_MASK | cut -d"=" -f2`				# private network broadcast (ie.: 192.168.182.255)
	private_network_ending=`echo $PRIVATE_NETWORK | cut -d"." -f$classe_sup`				# last octet of LAN address
	private_broadcast_ending=`echo $PRIVATE_BROADCAST | cut -d"." -f$classe_sup`				# last octet of LAN broadcast
	PRIVATE_FIRST_IP=`echo $PRIVATE_NETWORK | cut -d"." -f1-3`"."`expr $private_network_ending + 1`		# First network address (ex.: 192.168.182.1)
	PRIVATE_SECOND_IP=`echo $PRIVATE_NETWORK | cut -d"." -f1-3`"."`expr $private_network_ending + 2`	# second network address (ex.: 192.168.182.2)
	PRIVATE_LAST_IP=`echo $PRIVATE_BROADCAST | cut -d"." -f1-3`"."`expr $private_broadcast_ending - 1`	# last network address (ex.: 192.168.182.254)
}

usage="Usage: alcasar-conf.sh {--create or -create} | {--load or -load} | {--apply or -apply}"
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
	--create|-create)	
		[ -d $DIR_UPDATE ] && rm -rf $DIR_UPDATE
		mkdir $DIR_UPDATE
# Sauvegarde de la base des usagers
		$DIR_SBIN/alcasar-mysql.sh -dump
		cp /var/Save/base/`ls /var/Save/base|tail -1` $DIR_UPDATE
# Sauvegarde du logo
		cp -f $DIR_WEB/images/organisme.png $DIR_UPDATE
# Sauvegarde des fichiers exploités par dansguardian 
		cp -f /etc/dansguardian/lists/exceptioniplist $DIR_UPDATE
		cp -f /etc/dansguardian/lists/urlregexplist $DIR_UPDATE
		cp -f /etc/dansguardian/lists/exceptionsitelist $DIR_UPDATE
		cp -f /etc/dansguardian/lists/bannedsitelist $DIR_UPDATE
		cp -f /etc/dansguardian/lists/exceptionurllist $DIR_UPDATE
		cp -f /etc/dansguardian/lists/bannedurllist $DIR_UPDATE
		cp -rf /etc/dansguardian/lists/blacklists/ossi $DIR_UPDATE
# sauvegarde des fichiers : de conf, de filtrage, d'exception, digest, etc.
		mkdir $DIR_UPDATE/etc/
		cp -rf $DIR_ETC/* $DIR_UPDATE/etc/
# particularité des versions
# si version <= 2.8
		if [ $MAJ_RUNNING_VERSION -lt 2 ] || ([ $MAJ_RUNNING_VERSION -eq 2 ] && [ $MIN_RUNNING_VERSION -lt 8 ])
		then
			$SED "s?alcasar?alcasar.$DOMAIN?g" $DIR_UPDATE/etc/digest/*	# add the domainname to the hostname
		else
# si version > 2.8 : sauvegarde des certificats (serveur et CA)
			cert_date=`/usr/bin/openssl x509 -noout -in /etc/pki/tls/certs/alcasar.crt -dates|grep After|cut -d"=" -f2`
			cp -f /etc/pki/tls/certs/alcasar.crt $DIR_UPDATE
			cp -f /etc/pki/tls/private/alcasar.key $DIR_UPDATE
			cp -f /etc/pki/CA/alcasar-ca.crt $DIR_UPDATE
			cp -f /etc/pki/CA/private/alcasar-ca.key $DIR_UPDATE
			if [ -e /etc/pki/tls/certs/server-chain.crt ]; then
				cp -f /etc/pki/tls/certs/server-chain.crt $DIR_UPDATE
			else
				cp -f /etc/pki/tls/certs/alcasar.crt $DIR_UPDATE/server-chain.crt
			fi
		fi
# Changes since V2.6
# SSH_ADMIN_FROM is redefined
		$SED "s?^Admin_from_IP=.*?SSH_ADMIN_FROM=0.0.0.0/0.0.0.0?" $CONF_FILE
# macallowed is replaced with macauth 
		rm -f $DIR_UPDATE/etc/alcasar-macallowed
# DHCP mode can be "off/half/full"
		DHCP_mode=`cat $CONF_FILE|grep DHCP=|cut -d"=" -f2`
		if [ $DHCP_mode = "on" ]; then
			$SED "s?^DHCP=on.*?DHCP=full?" $CONF_FILE	# DHCP option can be "off/half/full" since V2.6
		fi
# The option 'EXT_LAN_FILTERING' is deleted
		$SED "/^EXT_LAN/d" $CONF_FILE
# The category "ip" no longer exist
		$SED "/\/ip\/urls/d" $DIR_UPDATE/bannedurllist;	$SED "/\/ip\/domains/d" $DIR_UPDATE/bannedsitelist
		$SED "/blacklists\/ip/d" $DIR_UPDATE/etc/alcasar-bl-categories; $SED "/^ip/d" $DIR_UPDATE/etc/alcasar-bl-categories-enabled
# BL and WL are now dynamically built in "/usr/local/share"
		rm -rf $DIR_UPDATE/etc/alcasar-dnsfilter-enabled $DIR_UPDATE/etc/alcasar-dnsfilter-available
# Bing et Youtube are addes to the safesearching system
		Bing=`grep bing $DIR_UPDATE/urlregexplist | wc -l`
		if [ $Bing -ne "1" ]; then
			SafeSearch=`grep ^\"\(\^http\:\/\/ $DIR_UPDATE/urlregexplist | wc -l`
			if [ $SafeSearch -eq "0" ]; then
			cat <<EOF >> $DIR_UPDATE/urlregexplist
# Bing - add 'adlt=strict'
#"(^http://[0-9a-z]+\.bing\.[a-z]+[-/%.0-9a-z]*\?)(.*)"->"\1\2&adlt=strict"
# Youtube - add 'edufilter=your_ID' 
#"(^http://[0-9a-z]+\.youtube\.[a-z]+[-/%.0-9a-z]*\?)(.*)"->"\1\2&edufilter=ABCD1234567890abcdef"
EOF
			else
			cat <<EOF >> $DIR_UPDATE/urlregexplist
# Bing - add 'adlt=strict'
"(^http://[0-9a-z]+\.bing\.[a-z]+[-/%.0-9a-z]*\?)(.*)"->"\1\2&adlt=strict"
# Youtube - add 'edufilter=your_ID' 
"(^http://[0-9a-z]+\.youtube\.[a-z]+[-/%.0-9a-z]*\?)(.*)"->"\1\2&edufilter=ABCD1234567890abcdef"
EOF
			fi
		fi
# la variable YOUTUBE_ID est déclarée dans le fichier de conf
	YOUTUBE_ID=`grep ^YOUTUBE_ID $CONF_FILE | cut -d"=" -f2`
	YOUTUBE_ID=${YOUTUBE_ID:="-1"}
	if [ $YOUTUBE_ID = "-1" ]; then
		echo "YOUTUBE_ID=ABCD1234567890abcdef" >> $CONF_FILE
	fi
	 	cp /etc/sysconfig/dnsmasq $DIR_UPDATE
# copie du fichier de conf modifié
	cp $CONF_FILE $DIR_UPDATE/etc/
# le répertoire "ISO" est remplacé par "system_backup" suite à la suppression de "mondoarchive" (V2.5)
		rm -rf /var/Save/ISO
# création de l'archive et copie dans le répertoire WEB associé
		cd /tmp
		tar -cf alcasar-conf.tar conf/
		gzip -f alcasar-conf.tar
		[ -d $DIR_SAVE ] && cp alcasar-conf.tar.gz $DIR_SAVE/$fichier
		rm -rf $DIR_UPDATE
		;;
	--load|-load)
		cd /tmp
		tar -xf /tmp/alcasar-conf*.tar.gz
# Retrieve the logo
		[ -e $DIR_UPDATE/organisme.png ] && cp -f $DIR_UPDATE/organisme.png $DIR_WEB/images/
		chown apache:apache $DIR_WEB/images/organisme.png $DIR_WEB/intercept.php
# Retrieve the security certificates (CA and server)
		[ -e $DIR_UPDATE/alcasar-ca.crt ] && cp -f $DIR_UPDATE/alcasar-ca.crt /etc/pki/CA/
		[ -e $DIR_UPDATE/alcasar-ca.key ] && cp -f $DIR_UPDATE/alcasar-ca.key /etc/pki/CA/private/
		[ -e $DIR_UPDATE/alcasar.crt ] && cp -f $DIR_UPDATE/alcasar.crt /etc/pki/tls/certs/
		[ -e $DIR_UPDATE/alcasar.key ] && cp -f $DIR_UPDATE/alcasar.key /etc/pki/tls/private/
		[ -e $DIR_UPDATE/server-chain.crt ] &&	cp -f $DIR_UPDATE/server-chain.crt /etc/pki/tls/certs/
		chown -R root:apache /etc/pki
		chmod -R 750 /etc/pki
# Import of the users database
		mysql -u$DB_USER -p$radiuspwd < `ls $DIR_UPDATE/radius*`
# Retrieve lacal parameters & Remove blacklist files (now in /usr/local/share)
		[ -d $DIR_UPDATE/etc ] && rm -rf $DIR_UPDATE/etc/alcasar-dnsfilter* && cp -rf $DIR_UPDATE/etc/* $DIR_ETC/
# Retrieve Dansguardian files
		[ -e $DIR_UPDATE/exceptioniplist ] && cp -f $DIR_UPDATE/exceptioniplist /etc/dansguardian/lists/
		[ -e $DIR_UPDATE/exceptionsitelist ] && cp -f $DIR_UPDATE/exceptionsitelist /etc/dansguardian/lists/
		[ -e $DIR_UPDATE/urlregexplist ] && cp -f $DIR_UPDATE/urlregexplist /etc/dansguardian/lists/
		[ -e $DIR_UPDATE/bannedsitelist ] && cp -f $DIR_UPDATE/bannedsitelist /etc/dansguardian/lists/ 
		[ -e $DIR_UPDATE/exceptionurllist ] && cp -f $DIR_UPDATE/exceptionurllist /etc/dansguardian/lists/
		[ -e $DIR_UPDATE/bannedurllist ] && cp -f $DIR_UPDATE/bannedurllist /etc/dansguardian/lists/
		[ -d $DIR_UPDATE/ossi ] && cp -rf $DIR_UPDATE/ossi /etc/dansguardian/lists/blacklists/
		chown -R dansguardian:apache /etc/dansguardian/lists
		chmod -R g+rw /etc/dansguardian/lists
# Adapt DNS/URL filtering
		PARENT_SCRIPT=`basename $0`
		export PARENT_SCRIPT
		$DIR_SBIN/alcasar-bl.sh -adapt
		$DIR_SBIN/alcasar-bl.sh -reload
# retrieve dnsmasq general config file
		[ -e $DIR_UPDATE/dnsmasq ] && cp -f $DIR_UPDATE/dnsmasq /etc/sysconfig/dnsmasq \
		&& chown root.root /etc/sysconfig/dnsmasq \
		&& chmod 644 /etc/sysconfig/dnsmasq
# admin profile update (admin + manager + backup)
		$DIR_SBIN/alcasar-profil.sh --list
# Start / Stop SSH Daemon
		ssh_active=`grep SSH= $CONF_FILE|cut -d"=" -f2`
		if [ $ssh_active = "on" ]
		then
			/sbin/chkconfig --add sshd
		else
			/sbin/chkconfig --del sshd
		fi
# Remove the update folder
		rm -rf $DIR_UPDATE
		;;
	--apply|-apply)
		PTN="\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\/([012]?[0-9]|3[0-2])\b"
		PRIVATE_IP_MASK=`grep ^PRIVATE_IP $CONF_FILE|cut -d"=" -f2`
		check=$(echo $PRIVATE_IP_MASK | egrep $PTN)
		if [[ "$?" -ne 0 ]]
		then 
			echo "Syntax error for PRIVATE_IP_MASK ($PRIVATE_IP_MASK)"
			exit 0
		fi
		PUBLIC_IP_MASK=`grep ^PUBLIC_IP $CONF_FILE|cut -d"=" -f2`
		check=$(echo $PUBLIC_IP_MASK | egrep $PTN)
		if [[ "$?" -ne 0 ]]
		then 
			echo "Syntax error for PUBLIC_IP_MASK ($PUBLIC_IP_MASK)"
			exit 0
		fi
		PTN="\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b"
		PUBLIC_GATEWAY=`grep ^GW $CONF_FILE|cut -d"=" -f2`
		check=$(echo $PUBLIC_GATEWAY | egrep $PTN)
		if [[ "$?" -ne 0 ]]
			then 
			echo "Syntax error for the Gateway IP ($PUBLIC_GATEWAY)"
			exit 0
		fi
		DNS1=`grep ^DNS1 $CONF_FILE|cut -d"=" -f2`
		check=$(echo $DNS1 | egrep $PTN)
		if [[ "$?" -ne 0 ]]
		then 
			echo "Syntax error for the IP address of the first DNS server ($DNS1)"
			exit 0
		fi
		DNS2=`grep ^DNS2 $CONF_FILE|cut -d"=" -f2`
		check=$(echo $DNS2 | egrep $PTN)
		if [[ "$?" -ne 0 ]]
		then 
			echo "Syntax error for the IP address of the second DNS server ($DNS2)"
			exit 0
		fi
		PUBLIC_IP=`echo $PUBLIC_IP_MASK | cut -d"/" -f1`
		PUBLIC_NETMASK=`/bin/ipcalc -m $PUBLIC_IP_MASK | cut -d"=" -f2`
		PRIVATE_IP=`echo $PRIVATE_IP_MASK | cut -d"/" -f1`
		PRIVATE_NETMASK=`/bin/ipcalc -m $PRIVATE_IP_MASK | cut -d"=" -f2`
		private_network_calc
		INSTALL_DATE=`grep INSTALL_DATE $CONF_FILE|cut -d"=" -f2`
		ORGANISME=`grep ORGANISM $CONF_FILE|cut -d"=" -f2`
 		DOMAIN=`grep DOMAIN $CONF_FILE|cut -d"=" -f2`
		DHCP_mode=`grep DHCP= $CONF_FILE|cut -d"=" -f2`
		if [ "$PARENT_SCRIPT" != "alcasar.sh" ] # don't launch on install stage
		then
			if [ $DHCP_mode = "off" ]
			then
				$DIR_SBIN/alcasar-dhcp.sh --off
			fi
# Logout everybody
			$DIR_SBIN/alcasar-logout.sh all		
# Services stop
			for i in squid ntpd chilli httpd network
			do
				[ -e /etc/init.d/$i ] && /etc/init.d/$i stop && killall $i 2>/dev/null
			done
		fi

# /etc/hosts
		cat <<EOF > /etc/hosts
127.0.0.1	localhost
$PRIVATE_IP	$HOSTNAME $HOSTNAME.$DOMAIN
EOF

# Ext Network Card config
		$SED "s?^IPADDR=.*?IPADDR=$PUBLIC_IP?" /etc/sysconfig/network-scripts/ifcfg-$EXTIF
		$SED "s?^NETMASK=.*?NETMASK=$PUBLIC_NETMASK?" /etc/sysconfig/network-scripts/ifcfg-$EXTIF
		$SED "s?^GATEWAY=.*?GATEWAY=$PUBLIC_GATEWAY?" /etc/sysconfig/network-scripts/ifcfg-$EXTIF
# NTP server
		$SED "/127.0.0.1/!s?^restrict.*?restrict $PRIVATE_NETWORK mask $PRIVATE_NETMASK nomodify notrap?g" /etc/ntp.conf
# host.allow 
		cat <<EOF > /etc/hosts.allow
ALL: LOCAL, 127.0.0.1, localhost, $PRIVATE_IP
sshd: ALL
ntpd: $PRIVATE_NETWORK_SHORT
EOF
# Alcasar Control Center
		$SED "s?^Listen.*?Listen $PRIVATE_IP:80?g" /etc/httpd/conf/httpd.conf
		FIC_MOD_SSL=`find /etc/httpd/modules.d/ -type f -name *mod_ssl.conf`
		$SED "s?^Listen.*?Listen $PRIVATE_IP:443?g" $FIC_MOD_SSL
		$SED "/127.0.0.1/!s?Allow from .*?Allow from $PRIVATE_NETWORK_MASK?g" /etc/httpd/conf/webapps.d/alcasar.conf
# Dialup_Admin
		$SED "s?^nas1_name:.*?nas1_name: alcasar-$ORGANISME?g" /etc/freeradius-web/naslist.conf
		$SED "s?^nas1_ip:.*?nas1_ip: $PRIVATE_IP?g" /etc/freeradius-web/naslist.conf
# coova
		$SED "s?ifconfig.*?ifconfig \$HS_LANIF $PRIVATE_IP?g" /etc/init.d/chilli
		$SED "s?^net.*?net\t\t$PRIVATE_NETWORK_MASK?g" /etc/chilli.conf
		$SED "s?^dns1.*?dns1\t\t$PRIVATE_IP?g" /etc/chilli.conf
		$SED "s?^dns2.*?dns2\t\t$PRIVATE_IP?g" /etc/chilli.conf
		$SED "s?^uamlisten.*?uamlisten\t$PRIVATE_IP?g" /etc/chilli.conf
# dhcp (coova + dnsmasq)
		$DIR_SBIN/alcasar-dhcp.sh -$DHCP_mode
# dnsmasq
		$SED "/127.0.0.1/!s?^listen-address=.*?listen-address=$PRIVATE_IP?g" /etc/dnsmasq.conf /etc/dnsmasq-blackhole.conf
		for i in /etc/dnsmasq.conf /etc/dnsmasq-blackhole.conf
		do
			$SED "/^server=/d" $i
			echo "server=$DNS1" >> $i
			echo "server=$DNS2" >> $i
		done
		$SED "s?^dhcp-range=.*?dhcp-range=$PRIVATE_SECOND_IP,$PRIVATE_LAST_IP,$PRIVATE_NETMASK,12h?g" /etc/dnsmasq.conf
		$SED "s?^dhcp-option=option:router.*?dhcp-option=option:router,$PRIVATE_IP?g" /etc/dnsmasq.conf
# DG + BL
		$SED "s?^filterip.*?filterip = $PRIVATE_IP?g" /etc/dansguardian/dansguardian.conf
# Watchdog
		$SED "s?^PRIVATE_IP=.*?PRIVATE_IP=\"$PRIVATE_IP\"?g" $DIR_BIN/alcasar-watchdog.sh
# SSHD
		$SED "/^ListenAddress/d" /etc/ssh/sshd_config
#		$SED "s?^#ListenAddress 0\.0\.0\.0?ListenAddress $PRIVATE_IP?g" /etc/ssh/sshd_config
		$SED "/ListenAddress 0.0.0.0.*/a\ListenAddress $PUBLIC_IP" /etc/ssh/sshd_config
		$SED "/ListenAddress $PUBLIC_IP/a\ListenAddress $PRIVATE_IP" /etc/ssh/sshd_config
# Prompts
		$SED "s?^ORGANISME.*?ORGANISME=$ORGANISME?g" /etc/bashrc
# sudoers
		$SED "s?^Host_Alias.*?Host_Alias	LAN_ORG=$PRIVATE_NETWORK/$PRIVATE_NETMASK,localhost		#réseau de l'organisme?g" /etc/sudoers
		if [ "$PARENT_SCRIPT" != "alcasar.sh" ] # don't launch on install stage
		then
# Services start
			for i in network squid ntpd chilli httpd 
			do
				[ -e /etc/init.d/$i ] && /etc/init.d/$i start
			done
# Reload BL (restart DG, dnsmasq & iptables)
			$DIR_SBIN/alcasar-bl.sh -reload
		fi
# Start / Stop SSH Daemon
		ssh_active=`grep SSH= $CONF_FILE|cut -d"=" -f2`
		if [ $ssh_active = "on" ]
		then
			/bin/systemctl enable sshd.service
			if [ "$PARENT_SCRIPT" != "alcasar.sh" ] # don't launch on install stage
			then
				/bin/systemctl start sshd.service
			fi
		else
			/bin/systemctl disable sshd.service
			if [ "$PARENT_SCRIPT" != "alcasar.sh" ] # don't launch on install stage
			then
				/bin/systemctl stop sshd.service
			fi
		fi
		;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac

