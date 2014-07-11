#!/bin/bash
# $Id: alcasar-iptables.sh 1299 2014-01-13 22:26:55Z richard $
# Script de mise en place des regles du parefeu d'Alcasar (mode normal)
# This script write the netfilter rules for ALCASAR
# Rexy - 3abtux - CPN
#
# Reminders
# There are four channels for log :
#	1 tracability of the consultation equipment with The 'Netflow' kernel module (iptables target = NETFLOW);
#	2 protection of ALCASAR with the Ulog group 1 (default group) 
#	3 SSH on ALCASAR with the Ulog group 2;
#	4 extern access attempts on ALCASAR with the Ulog group 3.
# The bootps/dhcp (67) port is always open on tun0/eth1 by coova 
conf_file="/usr/local/etc/alcasar.conf"
private_ip_mask=`grep PRIVATE_IP= $conf_file|cut -d"=" -f2`
private_ip_mask=${private_ip_mask:=192.168.182.1/24}
PRIVATE_IP=`echo $private_ip_mask | cut -d"/" -f1`			# ALCASAR LAN IP address
private_network=`/bin/ipcalc -n $private_ip_mask|cut -d"=" -f2`		# LAN IP address (ie.: 192.168.182.0)
private_prefix=`/bin/ipcalc -p $private_ip_mask|cut -d"=" -f2`		# LAN prefix (ie. 24)
PRIVATE_NETWORK_MASK=$private_network/$private_prefix			# Lan IP address + prefix (192.168.182.0/24)
public_ip_mask=`grep PUBLIC_IP= $conf_file|cut -d"=" -f2`		# ALCASAR WAN IP address
PUBLIC_IP=`echo $public_ip_mask | cut -d"/" -f1`
dns1=`grep DNS1= $conf_file|cut -d"=" -f2`				# first public DNS server
dns1=${dns1:=208.67.220.220}
dns2=`grep DNS2= $conf_file|cut -d"=" -f2`				# second public DNS server
dns2=${dns2:=208.67.222.222}
DNSSERVERS="$dns1,$dns2"						# first and second DNS IP servers addresses
PROTOCOLS_FILTERING=`grep PROTOCOLS_FILTERING= $conf_file|cut -d"=" -f2`	# Network protocols filter (on/off)
PROTOCOLS_FILTERING=${PROTOCOLS_FILTERING:=off}
DNS_FILTERING=`grep DNS_FILTERING= $conf_file|cut -d"=" -f2`		# DNS and URLs filter (on/off)
DNS_FILTERING=${DNS_FILTERING:=off}
BL_IP_CAT="/usr/local/share/iptables-bl"				# categories files of the BlackListed IP
QOS=`grep QOS= $conf_file|cut -d"=" -f2`				# QOS (on/off)
QOS=${QOS:=off}
SSH=`grep SSH= $conf_file|cut -d"=" -f2`				# sshd active (on/off)
SSH=${SSH:=off}
SSH_ADMIN_FROM=`grep SSH_ADMIN_FROM= $conf_file|cut -d"=" -f2`
SSH_ADMIN_FROM=${SSH_ADMIN_FROM:="0.0.0.0/0.0.0.0"}			# WAN IP address to reduce ssh access (all ip allowed on LAN side)
LDAP=`grep LDAP= $conf_file|cut -d"=" -f2`				# LDAP external server active (on/off)
LDAP=${LDAP:=off}
LDAP_IP=`grep LDAP_IP= $conf_file|cut -d"=" -f2`			# WAN IP address to reduce LDAP WAN access (all ip allowed on LAN side)
LDAP_IP=${LDAP_IP:="0.0.0.0/0.0.0.0"}
EXTIF="eth0"
INTIF="eth1"
TUNIF="tun0"								# listen device for chilli daemon
IPTABLES="/sbin/iptables"


# loading of NetFlow probe (ipt_NETFLOW kernel module)
modprobe ipt_NETFLOW destination=127.0.0.1:2055

# Effacement des règles existantes
# Flush all existing rules
$IPTABLES -F
$IPTABLES -t nat -F
$IPTABLES -t mangle -F
$IPTABLES -F INPUT
$IPTABLES -F FORWARD
$IPTABLES -F OUTPUT

# Suppression des chaines utilisateurs sur les tables filter et nat
# Flush non default rules on filter and nat tables
$IPTABLES -X
$IPTABLES -t nat -X

# Stratégies par défaut
# Default policies
$IPTABLES -P INPUT DROP
$IPTABLES -P FORWARD DROP
$IPTABLES -P OUTPUT DROP
$IPTABLES -t nat -P PREROUTING ACCEPT
$IPTABLES -t nat -P POSTROUTING ACCEPT
$IPTABLES -t nat -P OUTPUT ACCEPT

# destruction de tous les SET
# destroy all the SET
ipset destroy

# Création et peuplement du SET alcasar_ip_blocked
# creation and first populating of alcasar_ip_blocked SET
ipset create alcasar_ip_blocked hash:net hashsize 1024
if [ -s /usr/local/etc/alcasar-ip-blocked ]; then 
	while read ip_line
	do
		ip_on=`echo $ip_line|cut -b1`
		if [ $ip_on != "#" ]
		then	
			ip_blocked=`echo $ip_line|cut -d" " -f1`
			ipset add alcasar_ip_blocked $ip_blocked
		fi
	done < /usr/local/etc/alcasar-ip-blocked
fi

# Création et initialisation du SET authenticated_ip (dynamiquement peuplé par les scripts conup/condown)
# creation and initialization of authenticated_ip_ SET (populated dynamicly by conup/condown scripts)
ipset create authenticated_ip hash:net hashsize 1024
OLDIFS=$IFS
IFS=$'\n'
for equipment in `/usr/sbin/chilli_query list |grep -v "\.0\.0\.0"`
do
	active_ip=`echo $equipment |cut -d" " -f2`
	active_session=`echo $equipment |cut -d" " -f5`
	if [[ $(expr $active_session) -eq 1 ]]
	then
		ipset add authenticated_ip $active_ip
	fi
done
IFS=$OLDIFS

# Création et peuplement du SET blacklist_ip_blocked
# creation and first populating of blacklist_ip_blocked SET
# It take a lot of time (try to do this during the blacklist import process)
#ipset create blacklist_ip_blocked hash:net hashsize 1024
#cd $BL_IP_CAT
#for category in `ls -1 | cut -d"@" -f1`
#do
#	while read ip_blocked
#	do
#		ipset add blacklist_ip_blocked $ip_blocked
#	done < $BL_IP_CAT/$category
#done

#############################
#       PREROUTING          #
#############################
# Marquage (et journalisation) des paquets qui tentent d'accéder directement à DansGuardian pour pouvoir les rejeter en INPUT
# mark (and log) the dansguardian bypass attempts in order to DROP them in INPUT rules
$IPTABLES -A PREROUTING -t nat -i $TUNIF -p tcp -d $PRIVATE_IP -m tcp --dport 8080 -j ULOG --ulog-prefix "RULE direct-proxy -- DENY "
$IPTABLES -A PREROUTING -t mangle -i $TUNIF -d $PRIVATE_IP -p tcp -m tcp --dport 8080 -j MARK --set-mark 1

# Marquage (et journalisation) des paquets qui tentent d'accéder directement au port udp 54 pour pouvoir les rejeter en INPUT
# Mark (and log) the udp 54 direct attempts to REJECT them in INPUT rules
# Remarque : Ce port n'est ouvert que lorsque le filtrage est activé
# Remark : this port is only open when filtering is on
# $IPTABLES -A PREROUTING -t nat -i $TUNIF -p udp -d $PRIVATE_IP -m udp --dport 54 -j ULOG --ulog-prefix "RULE DNS-proxy -- DENY "
$IPTABLES -A PREROUTING -t mangle -i $TUNIF -d $PRIVATE_IP -p tcp --dport 54 -j MARK --set-mark 2

# Si le filtrage DNS est activé, redirection des flux DNS vers le port 54 (dns+blackhole) sauf pour les IP en exceptions 
# If DNS filter is on, redirect DNS request to udp 54 (dns+blackhole) except for exception IP addresses
if [ $DNS_FILTERING = on ]; then
	# Compute exception IP
	nb_exceptions=`wc -l /usr/local/etc/alcasar-filter-exceptions | cut -d" " -f1`
	if [ $nb_exceptions != "0" ]
	then
		while read ip_exception 
		do
			$IPTABLES -A PREROUTING -t nat -i $TUNIF -p udp -s $ip_exception -d $PRIVATE_IP --dport domain -j ACCEPT
		done < /usr/local/etc/alcasar-filter-exceptions
	fi
		$IPTABLES -A PREROUTING -t nat -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p udp --dport domain -j REDIRECT --to-port 54
fi
# Redirection des requêtes HTTP des IP admin bannies vers ALCASAR (page 'accès interdit')
# Redirect HTTP requests of admin banned ip to ALCASAR (access deny window)
$IPTABLES -A PREROUTING -t nat -i $TUNIF -s $PRIVATE_NETWORK_MASK -m set --match-set alcasar_ip_blocked dst -p tcp --dport http -j REDIRECT --to-port http

# Redirection des requêtes HTTP des IP de la blacklist vers ALCASAR (page 'accès interdit')
# Redirect HTTP requests of blacklist ip to ALCASAR (access deny window)
#if [ $DNS_FILTERING = on ]; then
#$IPTABLES -A PREROUTING -t nat -i $TUNIF -s $PRIVATE_NETWORK_MASK -m set --match-set blacklist_ip_blocked dst -p tcp --dport http -j REDIRECT --to-port 80
#fi

# Journalisation des requètes HTTP vers Internet (seulement les paquets SYN) - Les autres protocoles sont journalisés en FORWARD par netflow
## Log HTTP requests to Internet (only syn packets) - Other protocols are log in FORWARD by netflow
$IPTABLES -A PREROUTING -t nat -i $TUNIF -s $PRIVATE_NETWORK_MASK ! -d $PRIVATE_IP -p tcp --dport http -m state --state NEW -j ULOG --ulog-prefix "RULE F_http -- ACCEPT "

# Redirection des requêtes HTTP sortantes vers DansGuardian (proxy transparent)
# Redirect outbound HTTP requests to DansGuardian (transparent proxy)
$IPTABLES -A PREROUTING -t nat -i $TUNIF -s $PRIVATE_NETWORK_MASK ! -d $PRIVATE_IP -p tcp --dport http -j REDIRECT --to-port 8080

# Redirection des requêtes NTP vers le serveur NTP local
# Redirect NTP request in local NTP server
$IPTABLES -A PREROUTING -t nat -i $TUNIF -s $PRIVATE_NETWORK_MASK ! -d $PRIVATE_IP -p udp --dport ntp -j REDIRECT --to-port 123

#############################
#         INPUT             #
#############################

# Tout passe sur loopback
# accept all on loopback
$IPTABLES -A INPUT -i lo -j ACCEPT
$IPTABLES -A OUTPUT -o lo -j ACCEPT

# Rejet des demandes de connexions non conformes (FIN-URG-PUSH, XMAS, NullScan, SYN-RST et NEW not SYN)
# Drop non standard connexions (FIN-URG-PUSH, XMAS, NullScan, SYN-RST et NEW not SYN)
$IPTABLES -A INPUT -p tcp --tcp-flags FIN,URG,PSH FIN,URG,PSH -j DROP
$IPTABLES -A INPUT -p tcp --tcp-flags ALL ALL -j DROP
$IPTABLES -A INPUT -p tcp --tcp-flags ALL NONE -j DROP
$IPTABLES -A INPUT -p tcp --tcp-flags SYN,RST SYN,RST -j DROP
$IPTABLES -A INPUT -p tcp -m tcp ! --syn -m state --state NEW -j DROP

# On rejette les trame en broadcast et en multicast sur EXTIF (évite leur journalisation)
# Drop broadcast & multicast on EXTIF to avoid log 
$IPTABLES -A INPUT -i $EXTIF -m addrtype --dst-type BROADCAST,MULTICAST -j DROP

# On autorise les retours de connexions légitimes par INPUT
# Conntrack on INPUT
$IPTABLES -A INPUT -m state --state RELATED,ESTABLISHED -j ACCEPT

# On interdit les connexions directes au port utilisé par DansGuardian (8080). Les packets concernés ont été marqués et loggués dans la table mangle (PREROUTING)
# Deny direct connections on DansGuardian port (8080). The concerned paquets are marked and logged in mangle table (PREROUTING)
$IPTABLES -A INPUT -i $TUNIF -p tcp --dport 8080 -m mark --mark 1 -j REJECT --reject-with tcp-reset

# Autorisation des connexions légitimes à DansGuardian 
# Allow connections for DansGuardian
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -p tcp --dport 8080 -m state --state NEW --syn -j ACCEPT

# On interdit les connexions directes au port UDP 54. Les packets concernés ont été marqués dans la table mangle (PREROUTING)
# Deny direct connections on UDP 54. The concerned paquets are marked in mangle table (PREROUTING)
$IPTABLES -A INPUT -i $TUNIF -p udp --dport 54 -m mark --mark 2 -j REJECT --reject-with icmp-port-unreachable

# autorisation des connexion légitime à DNSMASQ (avec blackhole)
# Allow connections for DNSMASQ (with blackhole)
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p udp --dport 54 -j ACCEPT

# Accès direct aux services internes
# Internal services access
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p udp --dport domain -j ACCEPT	# DNS non filtré # DNS without blackhole
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p icmp --icmp-type 8 -j ACCEPT	# Réponse ping # ping responce
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p icmp --icmp-type 0 -j ACCEPT	# Requête  ping # ping request
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p tcp --dport https -j ACCEPT	# Pages d'authentification et MCC # authentication pages and MCC
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p tcp --dport http -j ACCEPT	# Page d'avertissement filtrage # Filtering warning pages
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p tcp --dport 3990 -j ACCEPT	# Requêtes de deconnexion usagers # Users logout requests
$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p udp --dport ntp -j ACCEPT	# Serveur local de temps # local time server

# SSHD rules if activate 
if [ $SSH = on ]
	then
	$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p tcp --dport ssh -m state --state NEW -j ULOG --ulog-nlgroup 2 --ulog-prefix "RULE ssh-from-LAN -- ACCEPT"
	$IPTABLES -A INPUT -i $TUNIF -s $PRIVATE_NETWORK_MASK -d $PRIVATE_IP -p tcp --dport ssh -j ACCEPT
	$IPTABLES -A INPUT -i $EXTIF -s $SSH_ADMIN_FROM -d $PUBLIC_IP -p tcp --dport ssh -m state --state NEW --syn -j ULOG --ulog-nlgroup 2 --ulog-prefix "RULE ssh-from-WAN -- ACCEPT"
	$IPTABLES -A INPUT -i $EXTIF -s $SSH_ADMIN_FROM -d $PUBLIC_IP -p tcp --dport ssh -m state --state NEW -j ACCEPT
fi

# Insertion de règles locales
# Here, we add local rules (i.e. VPN from Internet)
if [ -f /usr/local/etc/alcasar-iptables-local.sh ]; then
        . /usr/local/etc/alcasar-iptables-local.sh
fi

# Journalisation et rejet des connexions (autres que celles autorisées) effectuées depuis le LAN
# Deny and log on INPUT from the LAN
$IPTABLES -A INPUT -i $TUNIF -m state --state NEW -j ULOG --ulog-prefix "RULE rej-int -- REJECT "
$IPTABLES -A INPUT -i $TUNIF -p tcp -j REJECT --reject-with tcp-reset
$IPTABLES -A INPUT -i $TUNIF -p udp -j REJECT --reject-with icmp-port-unreachable

# interdiction d'accès à INTIF (n'est utile que lorsque chilli est arrêté).
# Reject INTIF access (only when chilli is down)
$IPTABLES -A INPUT -i $INTIF -j ULOG --ulog-prefix "RULE Protect1 -- REJECT "
$IPTABLES -A INPUT -i $INTIF -j REJECT

# Journalisation et rejet des connexions initiées depuis le réseau extérieur (test des effets du paramètre --limit en cours)
# On EXTIF, the access attempts are log in channel 2 (we should test --limit option to avoid deny of service)
$IPTABLES -A INPUT -i $EXTIF -m state --state NEW -j ULOG --ulog-nlgroup 3 --ulog-qthreshold 10 --ulog-prefix "RULE rej-ext -- DROP"


#############################
#        FORWARD            #
#############################

# Rejet des requêtes DNS vers Internet
# Deny forward DNS
$IPTABLES -A FORWARD -i $TUNIF -p udp --dport domain -j REJECT --reject-with icmp-port-unreachable
$IPTABLES -A FORWARD -i $TUNIF -p tcp --dport domain -j REJECT --reject-with tcp-reset

# Blocage des IPs du SET alcasar_ip_blocked
# Deny IPs of the SET alcasar_ip_blocked 
$IPTABLES -A FORWARD -i $TUNIF -m set --match-set alcasar_ip_blocked dst -p icmp -j REJECT --reject-with icmp-port-unreachable
$IPTABLES -A FORWARD -i $TUNIF -m set --match-set alcasar_ip_blocked dst -p udp -j REJECT --reject-with icmp-port-unreachable
$IPTABLES -A FORWARD -i $TUNIF -m set --match-set alcasar_ip_blocked dst -p tcp -j REJECT --reject-with tcp-reset

# Blocage des IPs du SET blacklist_ip_blocked
# Deny IPs of the SET blacklist_ip_blocked 
#if [ $DNS_FILTERING = on ]; then
#	$IPTABLES -A FORWARD -i $TUNIF -m set --match-set blacklist_ip_blocked -p icmp -j REJECT --reject-with icmp-port-unreachable
#	$IPTABLES -A FORWARD -i $TUNIF -m set --match-set blacklist_ip_blocked dst -p udp -j REJECT --reject-with icmp-port-unreachable
#	$IPTABLES -A FORWARD -i $TUNIF -m set --match-set blacklist_ip_blocked -p tcp -j REJECT --reject-with tcp-reset
#fi

# Autorisation des retours de connexions légitimes
$IPTABLES -A FORWARD -m state --state ESTABLISHED,RELATED -j ACCEPT

#  If protocols filter is activate 
if [ $PROTOCOLS_FILTERING = on ]; then
	# Compute exception IP (IP addresses that shouldn't be filtered)
	nb_exceptions=`wc -l /usr/local/etc/alcasar-filter-exceptions | cut -d" " -f1`
	if [ $nb_exceptions != "0" ]
	then
		while read ip_exception 
		do
#			$IPTABLES -A FORWARD -i $TUNIF -s $ip_exception -m state --state NEW -j ULOG --ulog-prefix "RULE IP-exception -- ACCEPT "
			$IPTABLES -A FORWARD -i $TUNIF -s $ip_exception -m state --state NEW -j NETFLOW
			$IPTABLES -A FORWARD -i $TUNIF -s $ip_exception -m state --state NEW -j ACCEPT
		done < /usr/local/etc/alcasar-filter-exceptions
	fi
	# Compute uamallowed IP (IP address of equipments connected between ALCASAR and Internet (DMZ, own servers, ...) 
	nb_uamallowed=`wc -l /usr/local/etc/alcasar-uamallowed | cut -d" "  -f1`
	if [ $nb_uamallowed != "0" ]
	then
		while read ip_allowed_line 
		do
			ip_allowed=`echo $ip_allowed_line|cut -d"\"" -f2`
#			$IPTABLES -A FORWARD -i $TUNIF -d $ip_allowed -m state --state NEW -j ULOG --ulog-prefix "RULE IP-allowed -- ACCEPT "
			$IPTABLES -A FORWARD -i $TUNIF -d $ip_allowed -m state --state NEW -j NETFLOW
			$IPTABLES -A FORWARD -i $TUNIF -d $ip_allowed -m state --state NEW -j ACCEPT
		done < /usr/local/etc/alcasar-uamallowed
	fi
	# Autorisation des protocoles non commentés
	# Allow non comment protocols
	while read svc_line
	do
		svc_on=`echo $svc_line|cut -b1`
		if [ $svc_on != "#" ]
		then	
			svc_name=`echo $svc_line|cut -d" " -f1`
			svc_port=`echo $svc_line|cut -d" " -f2`
			if [ $svc_name = "icmp" ]
			then
				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p icmp -j NETFLOW
				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p icmp -j ACCEPT 
			else

#				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p tcp --dport $svc_port -m state --state NEW -j ULOG --ulog-prefix "RULE F_TCP-$svc_name -- ACCEPT "
				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p tcp --dport $svc_port -m state --state NEW -j NETFLOW
				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p tcp --dport $svc_port -m state --state NEW -j ACCEPT
#				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p udp --dport $svc_port -m state --state NEW -j ULOG --ulog-prefix "RULE F_UDP-$svc_name -- ACCEPT "
				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p udp --dport $svc_port -m state --state NEW -j NETFLOW
				$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -p udp --dport $svc_port -m state --state NEW -j ACCEPT
			fi
		fi
	done < /usr/local/etc/alcasar-services
	# Rejet explicite des autres protocoles
	# reject the others protocols
#	$IPTABLES -A FORWARD -i $TUNIF -j ULOG --ulog-prefix "RULE F_filter -- REJECT "
	$IPTABLES -A FORWARD -i $TUNIF -p tcp -j REJECT --reject-with tcp-reset
	$IPTABLES -A FORWARD -i $TUNIF -p udp -j REJECT --reject-with icmp-port-unreachable
	$IPTABLES -A FORWARD -i $TUNIF -p icmp -j REJECT 
fi

#  If QOS is activate  #
if [ $QOS = on ] && [ -e /usr/local/etc/alcasar-iptables-qos.sh ]; then
	. /usr/local/etc/alcasar-iptables-qos.sh 	
fi

# Autorisation des connections sortant du LAN  
# Allow forward connections with log
#$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -m state --state NEW -j ULOG --ulog-prefix "RULE F_all -- ACCEPT "
$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -m state --state NEW -j NETFLOW
$IPTABLES -A FORWARD -i $TUNIF -s $PRIVATE_NETWORK_MASK -m state --state NEW -j ACCEPT

#############################
#         OUTPUT            #
#############################
# On laisse tout sortir sur toutes les cartes sauf celle qui est connectée sur l'extérieur
# Everything is allowed but traffic through outside network interface
$IPTABLES -A OUTPUT ! -o $EXTIF -j ACCEPT

# On autorise les requêtes DNS vers les serveurs DNS identifiés 
# Allow DNS requests to identified DNS servers
$IPTABLES -A OUTPUT -o $EXTIF -d $DNSSERVERS -p udp --dport domain -m state --state NEW -j ACCEPT

# On autorise les requêtes HTTP sortantes
# HTTP requests are allowed
$IPTABLES -A OUTPUT -o $EXTIF -p tcp --dport http -j NETFLOW
$IPTABLES -A OUTPUT -o $EXTIF -p tcp --dport http -j ACCEPT

# On autorise les requêtes FTP 
# FTP requests are allowed
modprobe ip_conntrack_ftp
$IPTABLES -A OUTPUT -o $EXTIF -p tcp --dport ftp -j ACCEPT
$IPTABLES -A OUTPUT -o $EXTIF -m state --state ESTABLISHED,RELATED -j ACCEPT

# On autorise les requêtes NTP 
# NTP requests are allowed
$IPTABLES -A OUTPUT -o $EXTIF -p udp --dport ntp -j ACCEPT

# On autorise les requêtes ICMP (ping) 
# ICMP (ping) requests are allowed
$IPTABLES -A OUTPUT -o $EXTIF -p icmp --icmp-type 8 -j ACCEPT

# On autorise les requêtes LDAP si un serveur externe est configué
# LDAP requests are allowed if an external server is declared
if [ $LDAP = on ]
	then
	$IPTABLES -A OUTPUT -p tcp -d $LDAP_IP -m multiport --dports ldap,ldaps -m state --state NEW,ESTABLISHED -j ACCEPT
	$IPTABLES -A OUTPUT -p udp -d $LDAP_IP -m multiport --dports ldap,ldaps -m state --state NEW,ESTABLISHED -j ACCEPT
fi


#############################
#       POSTROUTING         #
#############################
# Traduction dynamique d'adresse en sortie
# Dynamic NAT on EXTIF
$IPTABLES -A POSTROUTING -t nat -o $EXTIF -j MASQUERADE

# Save all rules
/etc/init.d/iptables save

# End of script

