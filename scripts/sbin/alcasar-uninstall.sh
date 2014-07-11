#!/bin/bash
# $Id: alcasar-uninstall.sh 1278 2014-01-04 15:13:01Z richard $

# alcasar-uninstall.sh
# by Franck BOUIJOUX, Pascal LEVANT and Richard REY
# This script is distributed under the Gnu General Public License (GPL)

# Désisntallation d'ALCASAR
# Uninstall ALCASAR

SED="/bin/sed -i"
clear
echo "-----------------------------------------------------------------------------"
echo "**                     Uninstall/Update ALCASAR                            **"
echo "-----------------------------------------------------------------------------"
echo
#services_stop
for i in squid ntpd iptables ulogd dansguardian chilli httpd radiusd freshclam havp dnsmasq mysqld named dhcpd
do
	[ -e /etc/init.d/$i ] && /sbin/chkconfig --del $i && /etc/init.d/$i stop && killall $i 2>/dev/null
done
for i in alcasar-load_balancing.service nfsen.service 
do
	[ -e /lib/systemd/system/$i ] && systemctl disable $i && systemctl stop $i 1>/dev/null
done

echo "Stop ALCASAR main functions : "

#init
echo -en "\n- init(1) : "
#les fichiers situés dans /usr/local/ seront supprimés à la fin car encore utiles ici
rm -f /root/ALCASAR* && echo -n "1"
sleep 1

# gestion
echo -en "\n- gestion(7) : "
[ -d /var/www/html ] && rm -rf /var/www/html && echo -n "1, "
[ -e /etc/httpd/conf/httpd.conf.default ] && mv /etc/httpd/conf/httpd.conf.default /etc/httpd/conf/httpd.conf && echo -n "2, "
[ -e /etc/php.ini.default ] && mv /etc/php.ini.default /etc/php.ini && echo -n "3, "
[ -e /etc/httpd/conf/vhosts-ssl.default ] && FIC_VIRTUAL_SSL=`find /etc/httpd/conf -type f -name *default_ssl_vhost.conf` && mv /etc/httpd/conf/vhosts-ssl.default $FIC_VIRTUAL_SSL && echo -n "4, "
if [ -d /usr/local/etc/digest ] # v >= 2.0
	then rm -rf /usr/local/etc/digest && echo -n "5, "
	else echo -n "5, "
fi
[ -e /etc/httpd/conf/webapps.d/alcasar.conf ] && rm -f /etc/httpd/conf/webapps.d/alcasar.conf && echo -n "6, "
[ -e /var/www/error/include/bottom.html.default ] && mv /var/www/error/include/bottom.html.default /var/www/error/include/bottom.html && echo -n "7"
sleep 1

# CA
echo -en "\n- AC(4) : "
[ -e /etc/pki/CA/alcasar-ca.crt ] && rm -f /etc/pki/CA/alcasar-ca.crt && echo -n "1, "
[ -e /etc/pki/CA/private/alcasar-ca.key ] && rm -f /etc/pki/CA/private/alcasar-ca.key && echo -n "2, "
[ -e /etc/pki/tls/certs/alcasar.crt ] && rm -f /etc/pki/tls/certs/alcasar.crt && echo -n "3, "
[ -e /etc/pki/tls/private/alcasar.key ] && rm -f /etc/pki/tls/private/alcasar.key && echo -n "4"
sleep 1

#init_db
echo -en "\n- init_db(2) : "
[ -e /etc/my.cnf.default ] && mv -f /etc/my.cnf.default /etc/my.cnf && echo -n "1, "
[ -e /etc/init.d/mysqld.default ] && mv -f /etc/init.d/mysqld.default /etc/init.d/mysqld && echo -n "2"
rm -rf /var/lib/mysql*
sleep 1

#param_radius
echo -en "\n- param_radius(8) : "
[ -e /etc/raddb/radiusd-db-vierge.sql ] && rm -f /etc/raddb/radiusd-db-vierge.sql && echo -n "1, "
[ -e /etc/raddb/radiusd.conf.default ] && mv /etc/raddb/radiusd.conf.default /etc/raddb/radiusd.conf && echo -n "2, "
[ -e /etc/raddb/sites-enabled/alcasar ] && rm /etc/raddb/sites-enabled/alcasar && echo -n "3, "
[ -e /etc/raddb/sites-available/alcasar ] && rm /etc/raddb/sites-available/alcasar && echo -n "4, "
[ -e /etc/raddb/clients.conf.default ] && mv /etc/raddb/clients.conf.default /etc/raddb/clients.conf && echo -n "5, "
[ -e /etc/raddb/sql.conf.default ] && mv /etc/raddb/sql.conf.default /etc/raddb/sql.conf && echo -n "6, "
[ -e /etc/raddb/sql/mysql/dialup.conf.default ] && mv /etc/raddb/sql/mysql/dialup.conf.default /etc/raddb/sql/mysql/dialup.conf && echo -n "7, "
[ -e /etc/raddb/sql/mysql/counter.conf.default ] && mv /etc/raddb/sql/mysql/counter.conf.default /etc/raddb/sql/mysql/counter.conf && echo -n "8"
sleep 1

#param_web_radius
echo -en "\n- param_web_radius(3) : "
[ -e /etc/freeradius-web/admin.conf.default ] && mv /etc/freeradius-web/admin.conf.default /etc/freeradius-web/admin.conf && echo -n "1, "
[ -e /etc/freeradius-web/naslist.conf ] && rm /etc/freeradius-web/naslist.conf && echo -n "2, "
[ -e /etc/freeradius-web/user_edit.attrs.default ] && mv /etc/freeradius-web/user_edit.attrs.default /etc/freeradius-web/user_edit.attrs && echo -n "3, "
[ -e /etc/freeradius-web/sql.attrmap.default ] || mv /etc/freeradius-web/sql.attrmap.default /etc/freeradius-web/sql.attrmap && echo -n "4"
sleep 1

#param_chilli
	[ -e /etc/chilli/alcasar-macallowed ] && rm /etc/chilli/alcasar-macallowed # if 2.7 and later, macallowed is replaced with macauth
if [ -e /etc/chilli.conf.default ] # >= V2.0
	then
	echo -en "\n- param_chilli(2) : "
	[ -e /etc/init.d/chilli.default ] && mv /etc/init.d/chilli.default /etc/init.d/chilli && echo -n "1, "
	[ -e /etc/chilli.conf.default ] && mv /etc/chilli.conf.default /etc/chilli.conf && echo -n "2"
	else # < V2.0
	echo -en "\n- param_chilli(5) : "
	[ -e /etc/chilli/functions.default ] && mv /etc/chilli/functions.default /etc/chilli/functions && echo -n "1, "
	[ -e /etc/chilli/config ] && rm /etc/chilli/config && echo -n "2, "
	[ -e /etc/chilli/alcasar-uamallowed ] && rm /etc/chilli/alcasar-uamallowed && echo -n "3, "
	[ -e /etc/chilli/alcasar-uamdomain ] && rm /etc/chilli/alcasar-uamdomain && echo -n "4, "
	[ -e /etc/init.d/chilli.default ] && mv /etc/init.d/chilli.default /etc/init.d/chilli && echo -n "5"
fi
sleep 1

#param_squid
echo -en "\n- param_squid(2) : "
[ -e /etc/squid/squid.conf.default ] && mv /etc/squid/squid.conf.default /etc/squid/squid.conf && echo -n "1, "
[ `ls /var/spool/squid/|wc -l` -ne "0" ] && rm -rf /var/spool/squid/* && echo -n "2"

#param_dansguardian
echo -en "\n- param_dansguardian(8) : "
[ -d /var/dansguardian ] && rm -rf /var/dansguardian && echo -n "1, "
[ -e /etc/dansguardian/dansguardian.conf.default ] && mv /etc/dansguardian/dansguardian.conf.default /etc/dansguardian/dansguardian.conf && echo -n "2, "
[ -e /etc/dansguardian/lists/bannedphraselist.default ] && mv /etc/dansguardian/lists/bannedphraselist.default /etc/dansguardian/lists/bannedphraselist && echo -n "3, "
[ -e /etc/dansguardian/dansguardianf1.conf.default ] && mv /etc/dansguardian/dansguardianf1.conf.default /etc/dansguardian/dansguardianf1.conf && echo -n "4, "
[ -e /etc/dansguardian/lists/bannedextensionlist.default ] && mv /etc/dansguardian/lists/bannedextensionlist.default /etc/dansguardian/lists/bannedextensionlist && echo -n "5, "
[ -e /etc/dansguardian/lists/bannedmimetypelist.default ] && mv /etc/dansguardian/lists/bannedmimetypelist.default /etc/dansguardian/lists/bannedmimetypelist && echo -n "6, "
[ -e /etc/dansguardian/lists/exceptioniplist.default ] && mv /etc/dansguardian/lists/exceptioniplist.default /etc/dansguardian/lists/exceptioniplist && echo -n "7, "
[ -e /etc/dansguardian/lists/bannedsitelist.default ] && mv /etc/dansguardian/lists/bannedsitelist.default /etc/dansguardian/lists/bannedsitelist && echo -n "8"
sleep 1

#antivirus
echo -en "\n- antivirus(2) : "
if [ -e /etc/init.d/havp ] 
	then
	[ -e /etc/havp/havp.config.default ] && mv /etc/havp/havp.config.default /etc/havp/havp.config && echo -n "1, "
	userdel -r havp 2>/dev/null && echo -n "2"
	[ `grep havp /etc/fstab|wc -l` -ne "0" ] && $SED "/havp/d" /etc/fstab # anciennes versions (mémoire tampon sur disque)
else	echo -n "uninstalled"
fi
sleep 1

#param_ulogd
echo -en "\n- ulogd(2) : "
if [ -e /etc/init.d/ulogd.default ]
	then
	mv -f /etc/init.d/ulogd.default /etc/init.d/ulogd && echo -n "1, "
	rm -f /etc/ulogd-* && echo -n "2"
else echo -n "nothing to do"
fi	
sleep 1

#awstats
echo -en "\n- awstats(1) : "
if [ -e /etc/awstats/awstats.conf.default ]
then
	mv /etc/awstats/awstats.conf.default /etc/awstats/awstats.conf && echo -n "1"
else echo -n "uninstalled"
fi
sleep 1

#DnsMasq
echo -en "\n- dnsmasq(4) : "
if [ -e /etc/init.d/dnsmasq ]
then
	[ -e /etc/dnsmasq.conf.default ] && mv /etc/dnsmasq.conf.default /etc/dnsmasq.conf && echo -n "1, "
	[ -e /etc/dnsmasq-blackhole.conf ] && rm -f /etc/dnsmasq-blackhole.conf && echo -n "2, "
	[ -d /etc/dnsmasq.d ] && rm -rf /etc/dnsmasq.d
	[ -e /etc/init.d/dnsmasq.default ] && mv /etc/init.d/dnsmasq.default /etc/init.d/dnsmasq && echo -n "3, "
	[ -e /etc/sysconfig/dnsmasq.default ] && mv /etc/sysconfig/dnsmasq.default /etc/sysconfig/dnsmasq && echo -n "4"
else echo -n "uninstalled"
fi
sleep 1

#Bind
echo -en "\n- bind(1) : "
if [ -e /etc/init.d/named ]
then
	/usr/sbin/urpme --auto bind --auto-orphans && echo -n "1"
else echo -n "uninstalled"
fi
sleep 1

#dhcpd
echo -en "\n- dhcp-server(1) : "
if [ -e /etc/init.d/dhcpd ]
then
	/usr/sbin/urpme --auto dhcp-server --auto-orphans && echo -n "1"
else echo -n "uninstalled"
fi
sleep 1

#cron
echo -en "\n- cron(10) : "
[ -e /etc/crontab.default ] && mv /etc/crontab.default /etc/crontab && echo -n "1, "
[ -e /etc/anacrontab.default ] && mv /etc/anacrontab.default /etc/anacrontab && echo -n "2, "
[ -e /etc/cron.d/alcasar-mysql ] && rm -f /etc/cron.d/alcasar-mysql && echo -n "3, "
[ -e /etc/cron.d/alcasar-export_log ] && rm -f /etc/cron.d/alcasar-export_log && echo -n "4, "
[ -e /etc/cron.d/alcasar-clean_log ] && rm -f /etc/cron.d/alcasar-clean_log && echo -n "5, "
[ -e /etc/cron.d/alcasar-clean_import ] && rm -f /etc/cron.d/alcasar-clean_import && echo -n "6, "
[ -e /etc/cron.d/alcasar-distrib-updates ] && rm -f /etc/cron.d/alcasar-distrib-updates && echo -n "7, "
[ -e /etc/cron.d/awstats ] && rm -f /etc/cron.d/awstats && echo -n "8, "
[ -e /etc/cron.d/freeradius-web ] && rm -f /etc/cron.d/freeradius-web && echo -n "9, "
[ -e /etc/cron.d/alcasar-watchdog ] && rm -f /etc/cron.d/alcasar-watchdog && echo -n "10"
rm -f /etc/cron.d/coova /etc/cron.d/alcasar-bl_download
sleep 1

# network
echo -en "\n- network(9) : "
hostname localhost
/sbin/ifdown eth0
[ -e /etc/sysconfig/network-scripts/default-ifcfg-eth0 ] && mv /etc/sysconfig/network-scripts/default-ifcfg-eth0 /etc/sysconfig/network-scripts/ifcfg-eth0 && echo -n "1, " 
[ -e /etc/sysconfig/network.default ] && mv /etc/sysconfig/network.default /etc/sysconfig/network && echo -n "2, "
[ -e /etc/hosts.default ] && mv /etc/hosts.default /etc/hosts && echo -n "3, "
[ -e /etc/sysconfig/network-scripts/ifcfg-eth1 ] && rm -f /etc/sysconfig/network-scripts/ifcfg-eth1 && echo -n "4, "
[ -e /etc/ntp.conf.default ] && mv /etc/ntp.conf.default /etc/ntp.conf && echo -n "5, "
[ -e /etc/hosts.allow.default ] && mv /etc/hosts.allow.default /etc/hosts.allow && echo -n "6, "
[ -e /etc/hosts.deny.default ] && mv /etc/hosts.deny.default /etc/hosts.deny && echo -n "7, "
[ -e /etc/sysconfig/iptables ] && rm -f /etc/sysconfig/iptables && echo -n "8, "
[ -e /etc/modprobe.preload.default ] && mv /etc/modprobe.preload.default /etc/modprobe.preload && echo -n "9"

echo
/sbin/ifup eth0
sleep 1

#post_install
echo -en "\n- post_install(12) : "
[ -e /etc/mageia-release.default ] && mv /etc/mageia-release.default /etc/mageia-release && echo -n "1, "
[ -e /etc/ssh/alcasar-banner-ssh ] && rm -f /etc/ssh/alcasar-banner-ssh && echo -n "2, "
[ -e /etc/ssh/sshd_config.default ] && mv /etc/ssh/sshd_config.default /etc/ssh/sshd_config && echo -n "3, "
[ -e /etc/bashrc.default ] && mv /etc/bashrc.default /etc/bashrc && echo -n "4, "
[ -e /etc/sudoers.default ] && mv /etc/sudoers.default /etc/sudoers && echo -n "5, "
[ -e /etc/logrotate.d/mysqld ] && rm -f /etc/logrotate.d/mysqld && echo -n "6, "
[ -e /etc/logrotate.d/httpd ] && rm -f /etc/logrotate.d/httpd && echo -n "7, "
[ -e /etc/logrotate.d/squid ] && rm -f /etc/logrotate.d/squid && echo -n "8, "
[ -e /etc/logrotate.d/radiusd ] && rm -f /etc/logrotate.d/radiusd && echo -n "9, "
[ -e /etc/logrotate.d/ulogd ] && rm -f /etc/logrotate.d/ulogd && echo -n "10, "
[ -e /etc/logrotate.d/dnsmasq ] && rm -f /etc/logrotate.d/dnsmasq && echo -n "11, "
[ -e /lib/systemd/system/alcasar-load_balancing.service ] && rm -f /lib/systemd/system/alcasar-load_balancing.service && echo -n "12"
sleep 1

#nettoyage (on retire les services supprimés ou remplacés dans la nouvelle version)
echo -en "\n- cleaning() : "
for rm_fic in /usr/local/bin /usr/local/sbin /usr/local/etc
	do
	rm -rf $rm_fic/alcasar*
	done
echo

# suppression des exceptions de mises à jours ( coova-chilli et freeradius)
sed -i '/coova.*/d' /etc/urpmi/skip.list
