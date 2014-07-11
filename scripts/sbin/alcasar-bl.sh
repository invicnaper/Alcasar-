#/bin/bash

# $Id: alcasar-bl.sh 1293 2014-01-12 21:08:59Z richard $

# alcasar-bl.sh
# by Franck BOUIJOUX and Richard REY
# This script is distributed under the Gnu General Public License (GPL)

# Gestion de la BL pour le filtrage de domaine (via dnsmasq) et d'URL (via Dansguardian)
# Manage the BL for DnsBlackHole (dnsmasq) and URL filtering (Dansguardian)

DIR_CONF="/usr/local/etc"
CONF_FILE="$DIR_CONF/alcasar.conf"
private_ip_mask=`grep PRIVATE_IP= $CONF_FILE|cut -d"=" -f2`
private_ip_mask=${private_ip_mask:=192.168.182.1/24}
PRIVATE_IP=`echo $private_ip_mask | cut -d"/" -f1`			# ALCASAR LAN IP address
DIR_tmp="/tmp/blacklists"
FILE_tmp="/tmp/filesfilter.txt"
FILE_ip_tmp="/tmp/filesipfilter.txt"
DIR_DG="/etc/dansguardian/lists"
DIR_DG_BL="$DIR_DG/blacklists"
BL_CATEGORIES="$DIR_CONF/alcasar-bl-categories"				# list of names of the 	BL categories
WL_CATEGORIES="$DIR_CONF/alcasar-wl-categories"				#'	'		WL	'
BL_CATEGORIES_ENABLED="$DIR_CONF/alcasar-bl-categories-enabled"		#	'	'	BL enabled categories
WL_CATEGORIES_ENABLED="$DIR_CONF/alcasar-wl-categories-enabled"		#	'	'	WL enabled categories
DIR_SHARE="/usr/local/share"
DIR_DNS_BL="$DIR_SHARE/dnsmasq-bl"					# all the BL in the DNSMASQ format
DIR_DNS_WL="$DIR_SHARE/dnsmasq-wl"					# all the WL	'	'	'
DIR_IP_BL="$DIR_SHARE/iptables-bl"					# all the IP addresses of the BL 
DIR_DNS_BL_ENABLED="$DIR_SHARE/dnsmasq-bl-enabled"			# symbolic link to the dnsmasq	BL (only enabled categories)
DIR_DNS_WL_ENABLED="$DIR_SHARE/dnsmasq-wl-enabled"			#	'	'	'	WL	'	'	'
DIR_IP_BL_ENABLED="$DIR_SHARE/iptables-bl-enabled"			#	'	'	ip BL (only enabled categories)	
DNSMASQ_BL_CONF="/etc/dnsmasq-blackhole.conf"				# conf file of dnsmasq-blackhole
BL_SERVER="dsi.ut-capitole.fr"
SED="/bin/sed -i"

# enable/disable the BL & WL categories
function cat_choice (){
	rm -rf $DIR_DNS_BL_ENABLED $DIR_DNS_WL_ENABLED $DIR_IP_BL_ENABLED # cleaning for dnsmasq and iptables
	$SED "/\.Include/d" $DIR_DG/bannedsitelist $DIR_DG/bannedurllist # cleaning for DG
	$SED "s?^[^#]?#&?g" $BL_CATEGORIES $WL_CATEGORIES # cleaning BL & WL categories file (comment all lines)
	mkdir $DIR_DNS_BL_ENABLED $DIR_DNS_WL_ENABLED $DIR_IP_BL_ENABLED 
	# process the file $BL_CATEGORIES with the choice of categories 
	for ENABLE_CATEGORIE in `cat $BL_CATEGORIES_ENABLED` 
	do
		$SED "/\/$ENABLE_CATEGORIE$/d" $BL_CATEGORIES 
		$SED "1i\/etc\/dansguardian\/lists\/blacklists\/$ENABLE_CATEGORIE" $BL_CATEGORIES
		ln -s $DIR_DNS_BL/$ENABLE_CATEGORIE.conf $DIR_DNS_BL_ENABLED/$ENABLE_CATEGORIE
		ln -s $DIR_IP_BL/$ENABLE_CATEGORIE $DIR_IP_BL_ENABLED/$ENABLE_CATEGORIE
		# echo ".Include<$DIR_DG_BL/$ENABLE_CATEGORIE/domains>" >> $DIR_DG/bannedsitelist  # Blacklisted domains are managed by dnsmasq
		echo ".Include<$DIR_DG_BL/$ENABLE_CATEGORIE/urls>" >> $DIR_DG/bannedurllist
	done
	sort +0.0 -0.2 $BL_CATEGORIES -o $FILE_tmp
	mv $FILE_tmp $BL_CATEGORIES
	# process the file $WL_CATEGORIES with the choice of categories 
	for ENABLE_CATEGORIE in `cat $WL_CATEGORIES_ENABLED` 
	do
		$SED "/\/$ENABLE_CATEGORIE$/d" $WL_CATEGORIES 
		$SED "1i\/etc\/dansguardian\/lists\/blacklists\/$ENABLE_CATEGORIE" $WL_CATEGORIES
		ln -s $DIR_DNS_WL/$ENABLE_CATEGORIE.conf $DIR_DNS_WL_ENABLED/$ENABLE_CATEGORIE
	done
	sort +0.0 -0.2 $WL_CATEGORIES -o $FILE_tmp
	mv $FILE_tmp $WL_CATEGORIES
}
function bl_enable (){
	$SED "s/^reportinglevel =.*/reportinglevel = 3/g" /etc/dansguardian/dansguardian.conf
	if [ "$PARENT_SCRIPT" != "alcasar-conf.sh" ] # don't launch on install stage
	then
		service dansguardian restart
		service dnsmasq restart
		/usr/local/bin/alcasar-iptables.sh
	fi
}
function bl_disable (){
	rm -rf $DIR_DNS_BL_ENABLED/*
	$SED "s/^reportinglevel =.*/reportinglevel = -1/g" /etc/dansguardian/dansguardian.conf
	$SED "/google/d" $DNSMASQ_BL_CONF # remove nosslsearch server
	$SED "s?^[^#]?#&?g" $DIR_DG/urlregexplist  # remove safe searching
	$SED "s/^\*ip$/#*ip/g" $DIR_DG/bannedsitelist # remove pureip browsing
	if [ "$PARENT_SCRIPT" != "alcasar-conf.sh" ] # don't launch on install stage
	then
		service dansguardian restart
		service dnsmasq restart
		/usr/local/bin/alcasar-iptables.sh
	fi
}
usage="Usage: alcasar-bl.sh { -on or --on } | { -off or --off } | { -download or --download } | { -adapt or --adapt } | { -reload or --reload }"
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
	DNS_FILTERING=`grep DNS_FILTERING $CONF_FILE|cut -d"=" -f2`		# DNS and URLs filter (on/off)
	DNS_FILTERING=${DNS_FILTERING:=off}
	echo "Set BlackList Filtering to $DNS_FILTERING"
	if [ $DNS_FILTERING = on ]; then
		cat_choice
		bl_enable
	else
		bl_disable
	fi
	exit 0
fi
case $args in
	-\? | -h* | --h*)
		echo "$usage"
		exit 0
		;;
	# enable the filtering
	-on | --on)	
		cat_choice
		$SED "s?^DNS_FILTERING.*?DNS_FILTERING=on?g" $CONF_FILE
		bl_enable
		;;
	# disable the filtering
	-off | --off)
		$SED "s?^DNS_FILTERING.*?DNS_FILTERING=off?g" $CONF_FILE
		bl_disable
		;;
	# Retrieve Toulouse BL
	-download | --download)
		rm -rf /tmp/con_ok.html
		`/usr/bin/curl $BL_SERVER -# -o /tmp/con_ok.html`
		if [ ! -e /tmp/con_ok.html ]
		then
			echo "Erreur : le serveur de blacklist ($BL_SERVER) n'est pas joignable"
		else 
			rm -rf /tmp/con_ok.html $DIR_tmp
			mkdir $DIR_tmp
			wget -P $DIR_tmp http://$BL_SERVER/blacklists/download/blacklists.tar.gz
			md5sum $DIR_tmp/blacklists.tar.gz | cut -d" " -f1 > $DIR_tmp/md5sum
			chown -R apache:apache $DIR_tmp
		fi
		;;		
	# Adapt Toulouse BL to ALCASAR architecture (dnsmasq + DG + iptables)
	-adapt | --adapt)
		echo -n "Toulouse BlackList migration process. Please wait : "
		if [ -f $DIR_tmp/blacklists.tar.gz ]
		then
			[ -d $DIR_DG_BL/ossi ] && mv -f $DIR_DG_BL/ossi $DIR_tmp
			rm -rf $DIR_DG_BL
			mkdir $DIR_DG_BL
			tar zxf $DIR_tmp/blacklists.tar.gz --directory=$DIR_DG/
			[ -d $DIR_tmp/ossi ] && mv -f $DIR_tmp/ossi $DIR_DG_BL/
			rm -rf $DIR_tmp
		fi
		rm -f $BL_CATEGORIES $WL_CATEGORIES $WL_CATEGORIES_ENABLED
		rm -rf $DIR_DNS_BL $DIR_DNS_WL $DIR_IP_BL
		touch $BL_CATEGORIES $WL_CATEGORIES $WL_CATEGORIES_ENABLED
		mkdir $DIR_DNS_BL $DIR_DNS_WL $DIR_IP_BL
		chown -R dansguardian:apache $DIR_DG $BL_CATEGORIES $WL_CATEGORIES $BL_CATEGORIES_ENABLED $WL_CATEGORIES_ENABLED
		chmod -R g+w $DIR_DG $BL_CATEGORIES $WL_CATEGORIES $BL_CATEGORIES_ENABLED $WL_CATEGORIES_ENABLED
		find $DIR_DG_BL/ -type f -name domains > $FILE_tmp # retrieve directory name where a domain file exist
		$SED "s?\/domains??g" $FILE_tmp # remove "/domains" suffix
		for dir_categorie in `cat $FILE_tmp` # create the blacklist and the whitelist files
		do
			categorie=`echo $dir_categorie|cut -d "/" -f6`
			categorie_type=`grep -A1 ^NAME:[$' '$'\t']*$categorie $DIR_DG_BL/global_usage | grep ^DEFAULT_TYPE | cut -d":" -f2 | tr -d " \t"`
			if [ "$categorie_type" == "white" ]
			then
				echo "$dir_categorie" >> $WL_CATEGORIES 
				echo `basename $dir_categorie` >> $WL_CATEGORIES_ENABLED  # by default all WL are enabled 
			else
				echo "$dir_categorie" >> $BL_CATEGORIES
			fi
		done
		rm -f $FILE_tmp
		# Verify that the enabled categories are effectively in the BL (need after an update of the BL)
		for ENABLE_CATEGORIE in `cat $BL_CATEGORIES_ENABLED` 
		do
			ok=`grep /$ENABLE_CATEGORIE$ $BL_CATEGORIES|wc -l`
			if [ $ok != "1" ] 
			then
				$SED "/^$ENABLE_CATEGORIE$/d" $BL_CATEGORIES_ENABLED
			fi
		done
		# Creation of DNSMASQ and Iptables BL and WL
		for LIST in $BL_CATEGORIES $WL_CATEGORIES	# for each list (bl and wl)
		do
			for PATH_FILE in `cat $LIST` # for each category
			do
				DOMAINE=`basename $PATH_FILE`
				echo -n "$DOMAINE, "
		  		if [ ! -f $PATH_FILE/urls ] # create 'urls' file if it doesn't exist
				then
					touch $PATH_FILE/urls
					chown dansguardian:apache $PATH_FILE/urls
				fi
				$SED "s/\.\{2,10\}/\./g" $PATH_FILE/domains $PATH_FILE/urls # correct some syntax errors
				# retrieve the ip addresses for iptables
				egrep  "^([0-9]{1,3}\.){3}[0-9]{1,3}$" $PATH_FILE/domains > $FILE_ip_tmp
				# for dnsmask, remove IP addesses, accented characters and commented lines.
				egrep  -v "^([0-9]{1,3}\.){3}[0-9]{1,3}$" $PATH_FILE/domains > $FILE_tmp
				$SED "/[äâëêïîöôüû]/d" $FILE_tmp
				$SED "/^#.*/d" $FILE_tmp
				# adapt to the dnsmasq syntax
				$SED "s?.*?address=/&/$PRIVATE_IP?g" $FILE_tmp 
				if [ "$LIST" == "$BL_CATEGORIES" ]
				then
					mv $FILE_tmp $DIR_DNS_BL/$DOMAINE.conf
					mv $FILE_ip_tmp $DIR_IP_BL/$DOMAINE
				else
					mv $FILE_tmp $DIR_DNS_WL/$DOMAINE.conf
				fi
			done
		done
		rm -f $FILE_tmp $FILE_ip_tmp
		echo
		;;
	# reload when categories are changed 
	-reload | --reload)
		# for DG
		chown -R dansguardian:apache $DIR_DG_BL/ossi
		chmod -R g+w $DIR_DG_BL/ossi
		cat_choice
		#  for dnsmasq (rehabited domain names)
		if [ `wc -w $DIR_DG/exceptionsitelist|cut -d " " -f1` != "0" ]
		then
			for i in `cat $DIR_DG/exceptionsitelist`
			do
				$SED "/$i/d" $DIR_DNS_BL/*
			done
		fi
		cp -f $DIR_DG_BL/ossi/domains $DIR_DNS_BL/ossi.conf
		$SED "s?.*?address=/&/$PRIVATE_IP?g" $DIR_DNS_BL/ossi.conf
		cp -f $DIR_DG_BL/ossi/domains_wl $DIR_DNS_WL/ossi.conf
		DNS_FILTERING=`grep DNS_FILTERING $CONF_FILE|cut -d"=" -f2`		# DNS and URLs filter (on/off)
		DNS_FILTERING=${DNS_FILTERING:=off}
		if [ $DNS_FILTERING = on ]; then
			bl_enable
		else
			bl_disable
		fi
		;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac

