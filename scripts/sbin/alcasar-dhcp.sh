#/bin/bash
# $Id: alcasar-dhcp.sh 1157 2013-07-16 10:48:11Z stephane $

# alcasar-dhcp.sh
# by Rexy
# This script is distributed under the Gnu General Public License (GPL)

# active ou desactive le service DHCP sur le réseau de consultation
# enable or disable the DHCP service on consultation LAN

SED="/bin/sed -i"
CHILLI_CONF_FILE="/etc/chilli.conf"
ALCASAR_CONF_FILE="/usr/local/etc/alcasar.conf"
DNSMASQ_CONF_FILE="/etc/dnsmasq.conf"

# define DHCP parameters (LAN side)
PRIVATE_IP_MASK=`grep PRIVATE_IP $ALCASAR_CONF_FILE|cut -d"=" -f2`
PRIVATE_IP=`echo $PRIVATE_IP_MASK | cut -d"/" -f1`
PRIVATE_PREFIX=`/bin/ipcalc -p $PRIVATE_IP_MASK |cut -d"=" -f2`				# network prefix (ie. 24)
PRIVATE_NETMASK=`/bin/ipcalc -m $PRIVATE_IP_MASK | cut -d"=" -f2`
PRIVATE_NETWORK=`/bin/ipcalc -n $PRIVATE_IP $PRIVATE_NETMASK| cut -d"=" -f2`
PRIVATE_PREFIX=`/bin/ipcalc -p $PRIVATE_IP $PRIVATE_NETMASK |cut -d"=" -f2`
PRIVATE_NETWORK_MASK=$PRIVATE_NETWORK/$PRIVATE_PREFIX					# ie.: 192.168.182.0/24
classe=$((PRIVATE_PREFIX/8)); classe_sup=`expr $classe + 1`; classe_sup_sup=`expr $classe + 2`		# ie.: 2=classe B, 3=classe C
PRIVATE_BROADCAST=`/bin/ipcalc -b $PRIVATE_NETWORK_MASK | cut -d"=" -f2`		# private network broadcast (ie.: 192.168.182.255)
private_network_ending=`echo $PRIVATE_NETWORK | cut -d"." -f$classe_sup`		# last octet of LAN address
private_broadcast_ending=`echo $PRIVATE_BROADCAST | cut -d"." -f$classe_sup`		# last octet of LAN broadcast
PRIVATE_FIRST_IP=`echo $PRIVATE_NETWORK | cut -d"." -f1-3`"."`expr $private_network_ending + 1`		# First network address (ex.: 192.168.182.1)
PRIVATE_LAST_IP=`echo $PRIVATE_BROADCAST | cut -d"." -f1-3`"."`expr $private_broadcast_ending - 1`	# last network address (ex.: 192.168.182.254)
PRIVATE_NETWORK_MASK=$PRIVATE_NETWORK/$PRIVATE_PREFIX
tmp_mask=`echo $PRIVATE_NETWORK_MASK|cut -d"/" -f2`; half_mask=`expr $tmp_mask + 1`	# masque du 1/2 réseau de consultation (ex.: 25)
PRIVATE_STAT_IP=$PRIVATE_NETWORK/$half_mask						# plage des adresses statiques (ex.: 192.168.182.0/25)
private_network_ending=`echo $PRIVATE_NETWORK | cut -d"." -f$classe_sup`		# dernier octet de l'@ de réseau
private_broadcast_ending=`echo $PRIVATE_BROADCAST | cut -d"." -f$classe_sup`		# dernier octet de l'@ de broadcast
private_plage=`expr $private_broadcast_ending - $private_network_ending + 1`
private_half_plage=`expr $private_plage / 2`
private_dyn=`expr $private_half_plage + $private_network_ending`
private_dyn_ip_network=`echo $PRIVATE_NETWORK | cut -d"." -f1-$classe`"."$private_dyn"."`echo $PRIVATE_NETWORK | cut -d"." -f$classe_sup_sup-5`
PRIVATE_DYN_IP=`echo $private_dyn_ip_network | cut -d"." -f1-4`/$half_mask					# @ réseau (CIDR) de la plage des adresses dynamiques (ex.: 192.168.182.128/25)
private_dyn_ip_ending=`echo $private_dyn_ip_network | cut -d"." -f4`
PRIVATE_DYN_FIRST_IP=`echo $private_dyn_ip_network | cut -d"." -f1-3`"."`expr $private_dyn_ip_ending + 1`	# 1ère adresse de la plage dynamique (ex.: 192.168.182.129)
PRIVATE_DYN_LAST_IP=`echo $PRIVATE_BROADCAST | cut -d"." -f1-3`"."`expr $private_broadcast_ending - 1`		# dernière adresse de la plage dynamique (ex.: 192.168.182.254)
EXT_DHCP_IP=`grep EXT_DHCP_IP $ALCASAR_CONF_FILE|cut -d"=" -f2`				# Adresse du serveur DHCP externe
RELAY_DHCP_IP=`grep RELAY_DHCP_IP $ALCASAR_CONF_FILE|cut -d"=" -f2`			# Adresse de l'agent Relay : IP interne (défaut 192.168.182.1) dans le cas de DHCP dans le LAN de consultation
RELAY_DHCP_IP=${RELAY_DHCP_IP:=$PRIVATE_IP}						# 			IP externe (défaut x.y.z.t) dans le cas de DHCP du côté eth0 ( WAN)
RELAY_DHCP_PORT=`grep RELAY_DHCP_PORT $ALCASAR_CONF_FILE|cut -d"=" -f2`			# Port de redirection vers le relay DHCP :  67 par défaut
RELAY_DHCP_PORT=${RELAY_DHCP_PORT:=67}

usage="Usage: alcasar-dhcp.sh {--full | -full} | {--off | -off} | {--half | -half}"
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
	echo "$usage"
	exit 1
fi
case $args in
	-\? | -h | --h)
		echo "$usage"
		exit 0
		;;
	--off|-off) # disable DHCP service
		$SED "s?.*statip.*?statip\t\t$PRIVATE_NETWORK_MASK?g" $CHILLI_CONF_FILE
		$SED "s?^#nodynip.*?nodynip?g" $CHILLI_CONF_FILE
		$SED "s?^dynip.*?#dynip?g" $CHILLI_CONF_FILE
		$SED "s?^#dynip.*?#dynip?g" $CHILLI_CONF_FILE
		$SED "s?^DHCP.*?DHCP=off?g" $ALCASAR_CONF_FILE
		if [ "$EXT_DHCP_IP" != "none" ] 
		then
		      $SED "s?.*dhcpgateway\t.*?dhcpgateway\t\t $EXT_DHCP_IP?g" $CHILLI_CONF_FILE
		      $SED "s?.*dhcprelayagent.*?dhcprelayagent\t\t$RELAY_DHCP_IP?g" $CHILLI_CONF_FILE
		      $SED "s?.*dhcpgatewayport.*?dhcpgatewayport\t\t$RELAY_DHCP_PORT?g" $CHILLI_CONF_FILE
		else
		      $SED "s?.*dhcpgateway.*?#dhcpgateway\t\t$EXT_DHCP_IP?g" $CHILLI_CONF_FILE
		      $SED "s?.*dhcprelayagent.*?#dhcprelayagent\t\t$RELAY_DHCP_IP?g" $CHILLI_CONF_FILE
		      $SED "s?.*dhcpgatewayport.*?#dhcpgatewayport\t\t$RELAY_DHCP_PORT?g" $CHILLI_CONF_FILE
		fi
		/etc/init.d/chilli restart
		;;
	--full|-full) # enable DHCP service on all range of IP addresses
		$SED "s?^.*statip.*?#statip?g" $CHILLI_CONF_FILE
		$SED "s?^nodynip.*?#nodynip?g" $CHILLI_CONF_FILE
		$SED "s?^DHCP.*?DHCP=full?g" $ALCASAR_CONF_FILE
		$SED "s?^dynip.*?dynip\t\t$PRIVATE_NETWORK_MASK?g" $CHILLI_CONF_FILE
		$SED "s?^#dynip.*?dynip\t\t$PRIVATE_NETWORK_MASK?g" $CHILLI_CONF_FILE
		$SED "s?^dhcp_range.*?dhcp-range=$PRIVATE_FIRST_IP,$PRIVATE_LAST_IP,$PRIVATE_NETMASK,12h?g" $DNSMASQ_CONF_FILE
		$SED "s?^dhcpgateway\t.*?#dhcpgateway\t\t $EXT_DHCP_IP?g" $CHILLI_CONF_FILE
		$SED "s?^dhcprelayagent.*?#dhcprelayagent\t\t$RELAY_DHCP_IP?g" $CHILLI_CONF_FILE
		$SED "s?^dhcpgatewayport.*?#dhcpgatewayport\t\t$RELAY_DHCP_PORT?g" $CHILLI_CONF_FILE
		$SED "s?^EXT_DHCP_IP.*?EXT_DHCP_IP=none?g" $ALCASAR_CONF_FILE
		$SED "s?^RELAY_DHCP_IP.*?RELAY_DHCP_IP=none?g" $ALCASAR_CONF_FILE
		$SED "s?^RELAY_DHCP_PORT.*?RELAY_DHCP_PORT=none?g" $ALCASAR_CONF_FILE
		/etc/init.d/chilli restart
		;;
	--half|-half) # enable DHCP service on half (upper) range of IP addresses
		$SED "s?.*statip.*?statip\t\t$PRIVATE_STAT_IP?g" $CHILLI_CONF_FILE
		$SED "s?^nodynip.*?#nodynip?g" $CHILLI_CONF_FILE
		$SED "s?^DHCP.*?DHCP=half?g" $ALCASAR_CONF_FILE
		$SED "s?^dynip.*?dynip\t\t$PRIVATE_DYN_IP?g" $CHILLI_CONF_FILE
		$SED "s?^#dynip.*?dynip\t\t$PRIVATE_DYN_IP?g" $CHILLI_CONF_FILE
		$SED "s?^dhcp_range.*?dhcp-range=$PRIVATE_DYN_FIRST_IP,$PRIVATE_DYN_LAST_IP,$PRIVATE_NETMASK,12h?g" $DNSMASQ_CONF_FILE
		$SED "s?^dhcpgateway\t.*?#dhcpgateway\t\t $EXT_DHCP_IP?g" $CHILLI_CONF_FILE
		$SED "s?^dhcprelayagent.*?#dhcprelayagent\t\t$RELAY_DHCP_IP?g" $CHILLI_CONF_FILE
		$SED "s?^dhcpgatewayport.*?#dhcpgatewayport\t\t$RELAY_DHCP_PORT?g" $CHILLI_CONF_FILE
		$SED "s?^EXT_DHCP_IP.*?EXT_DHCP_IP=none?g" $ALCASAR_CONF_FILE
		$SED "s?^RELAY_DHCP_IP.*?RELAY_DHCP_IP=none?g" $ALCASAR_CONF_FILE
		$SED "s?^RELAY_DHCP_PORT.*?RELAY_DHCP_PORT=none?g" $ALCASAR_CONF_FILE
		/etc/init.d/chilli restart
		;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac

