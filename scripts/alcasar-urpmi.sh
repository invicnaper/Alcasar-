#!/bin/bash
# $Id: alcasar-urpmi.sh 1292 2014-01-11 11:51:08Z richard $

# alcasar-urpmi.sh
# by 3abtux and Rexy
# This script is distributed under the Gnu General Public License (GPL)

# script de mise en place des dépots RPM 
# configure the RPM repository

Lang=`echo $LANG|cut -c 1-2`
VERSION="2"
ARCH="i586" 
# The kernel version we compile netflow for
KERNEL="kernel-desktop-3.4.52-1.mga2-1-1.mga2"
ModuleTree="kernel-desktop-3.4.52-1.mga2"
# ****** Alcasar needed RPMS - paquetages nécessaires au fonctionnement d'Alcasar ******
PACKAGES="sudo freeradius freeradius-mysql freeradius-ldap freeradius-web apache-mpm-prefork apache-mod_ssl apache-mod_php iptables squid dansguardian postfix mariadb logwatch ntp bind-utils openssh-server php-xml php-ldap php-mysql pam_ccreds rng-utils dnsmasq syslinux rsync cronie-anacron clamav pm-fallback-policy php-mbstring perl-rrdtool perl-MailTools perl-Socket6 php-sockets fail2ban gnupg man ipset"

rpm_repository_sync ()
{
cat <<EOF > /etc/urpmi/urpmi.cfg
{
downloader: wget
}
EOF
echo ${!MIRRORLIST}
urpmi.addmedia core --probe-synthesis --mirrorlist ${!MIRRORLIST} /media/core/release
urpmi.addmedia core-updates --update --probe-synthesis --mirrorlist ${!MIRRORLIST} /media/core/updates
}

rpm_error ()
{
echo
if [ $Lang == "fr" ]
then	
	echo "Relancez l'installation ultérieurement."
	echo "Si vous rencontrez à nouveau ce problème, modifier les variables MIRRORLIST[1&2] du fichier 'scripts/alcasar-urpmi.sh'"
else
	echo "Try an other install later."
	echo "If this problem occurs again, change the MIRRORLIST[1&2] variables in the file 'scripts/alcasar-urpmi.sh'"
fi
}
# extract the current Mandriva/Mageia version and hardware architecture (i586 ou X64)
fic=`cat /etc/product.id`
unknown_os=0
old="$IFS"
IFS=","
set $fic
for i in $*
do
	if [ "`echo $i|grep distribution|cut -d'=' -f1`" == "distribution" ]
	then 
		DISTRIBUTION=`echo $i|cut -d"=" -f2`
		unknown_os=`expr $unknown_os + 1`
	fi
	if [ "`echo $i|grep version|cut -d'=' -f1`" == "version" ]
	then 
		CURRENT_VERSION=`echo $i|cut -d"=" -f2`
		unknown_os=`expr $unknown_os + 1`
	fi
	if [ "`echo $i|grep arch|cut -d'=' -f1`" == "arch" ]
	then 
		ARCH=`echo $i|cut -d"=" -f2`
		unknown_os=`expr $unknown_os + 1`
	fi
done
IFS="$old"
if [[ ( $unknown_os != 3 || "$DISTRIBUTION" != "Mageia" ) && ( "$CURRENT_VERSION" != "2" ) ]]
then
	if [ $Lang == "fr" ]
	then	
		echo "La migration automatique d'ALCASAR ne peut pas être réalisée."
		echo "Le système d'exploitation doit être remplacé"
	else
		echo "The automatic update of ALCASAR can't be performed."
		echo "The OS must be replaced"
	fi
	if [ -e /tmp/alcasar-conf.tar.gz ]
	then
		echo
		if [ $Lang == "fr" ]
		then	
			echo "1 - Récupérez le fichier de configuration actuel (/tmp/alcasar-conf.tar.gz)."
			echo "2 - Installez Linux-Mageia2 (cf. doc d'installation)"
			echo "3 - copiez le fichier 'alcasar-conf.tar.gz' dans le répertoire '/tmp' avant de lancer l'installation d'ALCASAR"
		else
			echo "1 - Retrieve the configuration file (/tmp/alcasar-conf.tar.gz)"
			echo "2 - Install Linux-Mageia2 (cf. installation doc)"
			echo "3 - Copy the file 'alcasar-conf.tar.gz' in the folder '/tmp' before launching the installation of ALCASAR"
		fi
	fi
	exit 1
fi
# We prefer wget than curl
urpmi --no-verify-rpm --auto conf/rpms/$ARCH/wget*.rpm
# Set the RPM repository
MIRROR_NBR=2
#                       For Europeans 
MIRRORLIST1="http://www.mirrorservice.org/sites/mageia.org/pub/mageia/distrib/$VERSION/$ARCH"
#                       For International install
MIRRORLIST2="http://mirrors.mageia.org/api/mageia.$VERSION.$ARCH.list"
try_nb="0"; nb_repository="0"
while [ "$nb_repository" != "2" ]
do
	try_nb=`expr $try_nb + 1`
	MIRRORLIST="MIRRORLIST$try_nb"
	rpm_repository_sync 
	nb_repository=`cat /etc/urpmi/urpmi.cfg|grep mirrorlist|wc -l`
	if [ "$nb_repository" != "2" ]
	then
		if [ $Lang == "fr" ]
		then	
			echo "Une erreur a été détectée lors de la synchronisation avec le dépot N°$try_nb."
		else
			echo "An error occurs when synchronising the repositories N°$try_nb"
		fi
		if [ $(expr $try_nb) -eq $MIRROR_NBR ]
		then
			rpm_error
			exit 1
		fi
		if [ $Lang == "fr" ]
		then	
			echo "Voulez-vous tenter une synchronisation avec un autre dépôt?"
		else
			echo "Do you wan't to try a synchronisation with an other repository?"
		fi
		response=0
		PTN='^[oOnNyY]$'
		until [[ $(expr $response : $PTN) -gt 0 ]]
		do
			read response
		done
		if [ "$response" = "n" ] || [ "$response" = "N" ] 
		then
			exit 1
		fi
	fi
done
# delete unused RPMs
if [ $Lang == "fr" ]
then
	echo "Cleaning the system : "
else
	echo "Nettoyage du système : "
fi
for rm_rpm in shorewall mandi radeontool avahi awstats
do
	/usr/sbin/urpme --auto $rm_rpm --auto-orphans 2>/dev/null
	echo -n "."
done
# download the kernel used by ALCASAR and fix its version
if [ $Lang == "fr" ]
then	
	echo "Récupération du noyau Linux exploité par ALCASAR. Veuillez patienter ..."
else
	echo "Download the Linux kernel used by ALCASAR. Please wait ..."
fi
urpmi --auto --quiet $KERNEL
echo "/^kernel/" > /etc/urpmi/skip.list
# download "timezone" to avoid a mageia issue  --> should be remove with mageia3
urpmi timezone
# download updated RPM in cache 
if [ $Lang == "fr" ]
then	
	echo "Récupération des paquetages de mise à jour. Veuillez patienter ..."
	echo "Il est temps d'aller prendre un café (ou une bonne bière) ;-)"
else
	echo "Updated RPM download. Please wait ..."
	echo "You should now take a coffe (or a good beer) ;-)"
fi
urpmi --auto --auto-update --quiet --test --retry 2
if [ "$?" != "0" ]
then
	echo
	if [ $Lang == "fr" ]
	then	
		echo "Une erreur a été détectée lors de la récupération des paquetages."
	else
		echo "An error occurs when downloading RPMS"
	fi
	rpm_error
	exit 1
fi
# update with cached RPM
urpmi --auto --auto-update
if [ "$?" != "0" ]
then
	echo
	if [ $Lang == "fr" ]
	then	
		echo "Une erreur a été détectée lors de la mise à jour des paquetages."
	else
		echo "An error occurs when updating packages"
	fi
	rpm_error
	exit 1
fi
# Clean the RPM cache
urpmi --clean
# Download of ALCASAR specifics RPM in cache (and test)
if [ $Lang == "fr" ]
then	
	echo "Récupération des paquetages complémentaires. Veuillez patienter ..."
else
	echo "Download of complementary packages. Please wait ..."
fi
urpmi --auto $PACKAGES --quiet --test --retry 2
if [ "$?" != "0" ]
then
	echo
	if [ $Lang == "fr" ]
	then	
		echo "Une erreur a été détectée lors de la récupération des paquetages complémentaires."
	else
		echo "An error occurs when downloading complementary packages"
	fi
	rpm_error
	exit 1
fi
# update with cached RPM
urpmi --auto $PACKAGES 
if [ "$?" != "0" ]
then
	echo
	if [ $Lang == "fr" ]
	then	
		echo "Une erreur a été détectée lors de l'installation des paquetages complémentaires."
	else
		echo "An error occurs when installing complementary packages"
	fi
	rpm_error
	exit 1
fi
# Keep only the kernel version we compil netflow with, and remove all others
kernelVersion=$(rpm -qa | grep "kernel-desktop")
for i in $kernelVersion
do
	if [ ! $i = $KERNEL ];then
		urpme --auto $i
	fi
done
# delete old alcasar RPMs and unused services
for rm_rpm in c-icap-server lib64chilli0 libchilli0 python-coova-chilli cyrus-sasl mageia-gfxboot-theme
do
	/usr/sbin/urpme --auto $rm_rpm --auto-orphans 2>/dev/null
done

# Save chilli launch script (erase with new rpm one)
[ -e /etc/chilli.conf ] && cp /etc/chilli.conf /tmp/
# Install home made RPMs
urpmi --no-verify --auto conf/rpms/$ARCH/*.rpm
# restore chilli launch script
[ -e /tmp/chilli.conf ] && mv /tmp/chilli.conf /etc/
# Clean the RPM cache
urpmi --clean

exit 0
