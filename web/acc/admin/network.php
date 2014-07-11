<?php
/* written by steweb57 & Rexy */

/********************
* TEST CONF FILES   *
*********************/
define ("ALCASAR_CHILLI", "/etc/chilli.conf");
define ("CONF_FILE", "/usr/local/etc/alcasar.conf");
define ("ETHERS_FILE", "/usr/local/etc/alcasar-ethers");
$conf_files=array(ALCASAR_CHILLI,CONF_FILE,ETHERS_FILE);
foreach ($conf_files as $file){
if (!file_exists($file)){
	exit("Requested file ".$file." isn't present");}
if (!is_readable($file)){
	exit("Can't read the file ".$file);}
}

# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$Langue		= explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$Language	= strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
	$l_network_title	= "Configuration réseau";
	$l_eth0_legend		= "Eth0 (Interface connectée à Internet)";
	$l_eth1_legend		= "Eth1 (Réseau de consultation)";
	$l_internet_legend	= "INTERNET";
	$l_ip_adr			= "Adresse IP";
	$l_ip_mask			= "Masque";
	$l_ip_router		= "Passerelle";
	$l_ip_public		= "Adresse IP publique";
	$l_ip_dns1			= "DNS1";
	$l_ip_dns2			= "DNS2";
	$l_dhcp_title		= "Service DHCP";
	$l_dhcp_state		= "Mode actuel";
	$l_dhcp_mode		= "Les différents modes sont les suivants :";
	$l_DHCP_full		= "DHCP complet";
	$l_DHCP_half		= "Demi DHCP ";
	$l_DHCP_off			= "Sans DHCP";
	$l_DHCP_full_explain	= "Le serveur DHCP couvre la totalité des adresses du réseau. Des adresses statiques peuvent être réservées (cf. ci-dessous).";
	$l_DHCP_half_explain	= "La première moitié du réseau est réservé à l'adressage statique, l'autre moitié est en adressage dynamique (DHCP).";
	$l_DHCP_off_explain	= "Le serveur DHCP est arrêté.";
	$l_static_dhcp_title	= "Réservation d'adresses IP statiques";
	$l_mac_address		= "Adresse MAC";
	$l_ip_address		= "Adresse IP";
	$l_mac_del			= "Supprimer de la liste";
	$l_add_to_list		= "Ajouter";
	$l_apply			= "Appliquer les changements";

} else {
	$l_network_title	= "Network configuration";
	$l_eth0_legend		= "Eth0 (Internet connected interface)";
	$l_eth1_legend		= "Eth1 (Private network)";
	$l_internet_legend	= "INTERNET";
	$l_ip_adr			= "IP Address";
	$l_ip_mask			= "Mask";
	$l_ip_router		= "Gateway";
	$l_ip_public		= "Public IP address";
	$l_ip_dns1			= "DNS1 :";
	$l_ip_dns2			= "DNS2";
	$l_dhcp_title		= "DHCP service";
	$l_dhcp_state		= "Current mode";
	$l_dhcp_mode		= "The different modes are the following :";
	$l_DHCP_full		= "Full DHCP";
	$l_DHCP_half		= "Half DHCP ";
	$l_DHCP_off			= "No DHCP";
	$l_DHCP_full_explain	= "The DHCP server manage all equipments in DHCP mode. Some static addresses can be reserved (see bellow).";
	$l_DHCP_half_explain	= "The first half of LAN's equipments are in static mode, the other are in dynamic mode (DHCP).";
	$l_DHCP_off_explain	= "The DHCP server is off.";
	$l_static_dhcp_title	= "Static IP addresses reservation";
	$l_mac_address		= "MAC Address";
	$l_ip_address		= "IP Address";
	$l_mac_del			= "Delete from list";
	$l_add_to_list		= "Add";
	$l_apply			= "Apply changes";
}
if (isset($_POST['choix'])){$choix=$_POST['choix'];} else {$choix="";}
switch ($choix)
{
case 'DHCP_Full' :
	exec ("sudo /usr/local/sbin/alcasar-dhcp.sh -full");
	break;
case 'DHCP_Off' :
	exec ("sudo /usr/local/sbin/alcasar-dhcp.sh -off");
	break;
case 'DHCP_Half' :
	exec ("sudo /usr/local/sbin/alcasar-dhcp.sh -half");
	break;
case 'new_mac' :
	if ((trim($_POST['add_mac']) != "") and (trim($_POST['add_ip']) != ""))
		{
		$tab=file(ETHERS_FILE);
		$insert="True";
		if ($tab)  # le fichier n'est pas vide
			{
			foreach ($tab as $line)  # verify that MAC or IP addresses doesn't exist
				{
				$field=explode(" ", $line);
				$mac_addr=trim($field[0]);$ip_addr=trim($field[1]);
				if (strcasecmp(trim($_POST['add_mac']),trim($mac_addr)) == 0)
					{
					$insert="False";
					break;
					}
				if (strcasecmp(trim($_POST['add_ip']), trim($ip_addr)) == 0)
					{
					$insert="False";
					break;
					}
				}
			}
		if ($insert == "True") 
			{
			$line = trim($_POST['add_mac']) . " " . trim($_POST['add_ip']) . "\n";
			$pointeur=fopen(ETHERS_FILE,"a");
			fwrite ($pointeur, $line);
			fclose ($pointeur);
			exec ("sudo service chilli restart");
			}
		}
	break;
case 'del_mac' :
	$tab=file(ETHERS_FILE);
	if ($tab)
		{
		$pointeur=fopen(ETHERS_FILE,"w+");
		foreach ($tab as $line)
			{
			$field=explode(" ", $line);
			$mac_addr=trim($field[0]);
			$remove_line = False;
			foreach ($_POST as $key => $value)
				{
				if ($mac_addr == $key)
			       		{
					$remove_line = True;
					break;
					}
				}
			if (! $remove_line) {fwrite($pointeur,$line);}
			}
		fclose($pointeur);
		# exec ("sudo service chilli restart");
		}
	break;
}

// Fonction de test de connectivité internet
function internetTest(){
	$host = "www.google.fr"; # Google Test
	$port = "80";
	//var $num;	//non utilisé
	//var $error;	//non utilisé
	
	if (! $sock = @fsockopen($host, $port, $num, $error, 5)) {
		return false;
	} else {
		fclose($sock);
		return true;
	}
}
/********************************************************
*		Lecture du fichier ALCASAR_CHILLI	*
*********************************************************/
$ouvre=fopen(ALCASAR_CHILLI,"r");
if ($ouvre){
	while (!feof ($ouvre))
	{
		$tampon = fgets($ouvre, 4096);
		if (strpos($tampon,"=")!==false){
			$tmp = explode("=",$tampon);
			$chilli[$tmp[0]] = $tmp[1];
		}
	}
}else{
	exit("Erreur d'ouverture du fichier ".ALCASAR_CHILLI);
}
fclose($ouvre);

/***********************************
*	Read ALCASAR_CONF_FILE     *
************************************/
$ouvre=fopen(CONF_FILE,"r");
if ($ouvre){
	while (!feof ($ouvre))
	{
		$tampon = fgets($ouvre, 4096);
		if (strpos($tampon,"=")!==false){
			$tmp = explode("=",$tampon);
			$conf[$tmp[0]] = $tmp[1];
		}
	}
}else{
	exit("Erreur d'ouverture du fichier ".CONF_FILE);
}
fclose($ouvre);

/************************
*	TO DO		*
*************************/
//modification de la conf réseau  --> V3.0

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><!-- written by steweb57 & rexy -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $l_network_title; ?></title>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_network_title; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
	<tr><td valign="middle" align="left">
	<fieldset>
	<legend><?php echo $l_internet_legend;
 	if (InternetTest()){
		echo " <img src='/images/state_ok.gif'>";
		$IP_PUB = exec ("wget http://checkip.dyndns.org/ -O - -o /dev/null | cut -d: -f 2 | cut -d\< -f 1");}
	else 	{
		echo " <img src='/images/state_error.gif'>";
		$IP_PUB = "-.-.-.-";}
	?></legend>
	<table>
		<tr><td><?php echo $l_ip_public." : </td><td>".$IP_PUB;?></td></tr>
		<tr><td><?php echo $l_ip_dns1." : </td><td>".$conf["DNS1"];?></td></tr>
		<tr><td><?php echo $l_ip_dns2." : </td><td>".$conf["DNS2"];?></td></tr>
	</table>
	</fieldset>
	</td><td>
	<fieldset>
	<legend><?php echo $l_eth0_legend; ?></legend>
	<table>
		<tr><td><?php echo $l_ip_adr." : </td><td>".$conf["PUBLIC_IP"];?></td></tr>
		<tr><td><?php echo $l_ip_router." : </td><td>".$conf["GW"];?></td></tr>
	</table>
	</fieldset>
	</td><td>
	<fieldset>
	<legend><?php echo $l_eth1_legend; ?></legend>
	<table>
		<tr><td><?php echo $l_ip_adr." : </td><td>".$conf["PRIVATE_IP"];?></td></tr>
	</table>
	</fieldset>
	</td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_dhcp_title;?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=0>
<tr><td valign="middle" align="left">
<?
$dhcp_state=trim($conf["DHCP"]);
echo "<CENTER><H3>$l_dhcp_state : ${"l_DHCP_".$dhcp_state}</H3></CENTER>";
echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
echo "<select name='choix'>";
echo "<option value=\"DHCP_Off\" ";if (!strcmp($dhcp_state,"off")) echo "selected";echo ">$l_DHCP_off";
echo "<option value=\"DHCP_Half\" ";if (!strcmp($dhcp_state,"half")) echo "selected";echo ">$l_DHCP_half";
echo "<option value=\"DHCP_Full\" ";if (!strcmp($dhcp_state,"full")) echo "selected";echo ">$l_DHCP_full";
echo "</select>";
echo "<input type=submit value='$l_apply'>";
echo "<td valign='middle' align='left'><center><H3>$l_dhcp_mode</h3></center>";
echo "$l_DHCP_off : $l_DHCP_off_explain<br>$l_DHCP_half : $l_DHCP_half_explain<br>$l_DHCP_full : $l_DHCP_full_explain";
echo "</td>";
echo "</FORM>";
echo "</td></tr>";
if (strncmp($conf["DHCP"],"full",2) == 0) { require ('network2.php');}
else { echo "</TABLE>"; }
?>
</body>
</html>
