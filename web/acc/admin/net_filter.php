<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- written by Rexy -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>Network Filter</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=0>
<?
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_title_antivir = "Antivirus de flux WEB";
  $l_antivir_on="L'antivirus de flux WEB est actuellement activ&eacute;";
  $l_antivir_off="L'antivirus de flux WEB est actuellement désactiv&eacute;";
  $l_switch_antivir_on="Activer l'antivirus";
  $l_switch_antivir_off="D&eacute;sactiver l'antivirus";
  $l_title_ip_block="Filtrage d'adresses IP";
  $l_ip_address="Adresses IP (ou addresse de réseau) bloquées";
  $l_blocked_ip_address="Adresses IP";
  $l_blocked_ip_address_comment="Commentaires";
  $l_blocked="Bloquée";
  $l_ip_block_explain="Liste des adresses IP (ou adresses IP de réseaux) bloquées";
  $l_remove="Retirer de la liste";
  $l_title_proto = "Filtrage de protocoles r&eacute;seau";
  $l_netfilter_on="Le filtrage de protocoles r&eacute;seau est actuellement activ&eacute;";
  $l_netfilter_off="Le filtrage de protocoles réseau est actuellement désactiv&eacute";
  $l_switch_on="Activer le filtrage";
  $l_switch_off="D&eacute;sactiver le filtrage";
  $l_comment_on="&Agrave; l'exclusion du WEB (port 80), les protocoles r&eacute;seaux sont interdits.<BR>Choisissez ci-dessous les protocoles que vous autorisez";
  $l_comment_off="(tous les protocoles réseau sont autoris&eacute;s)";
  $l_protocols="Protocoles autoris&eacute;s";
  $l_error_open_file="Erreur d'ouverture du fichier";
  $l_port="Numéro de port";
  $l_proto="Nom du protocole";
  $l_enabled="Autoris&eacute;";
  $l_add_to_list="Ajouter &agrave; la liste";
  $l_save="Enregistrer les modifications";
}
else {
  $l_title_antivir = "WEB antivirus";
  $l_antivir_on="Actually, the WEB antivirus is on";
  $l_antivir_off="Actually, the WEB antivirus is off";
  $l_switch_antivir_on="Switch the antivirus on";
  $l_switch_antivir_off="Switch the antivirus off";
  $l_title_ip_block="IP address filter";
  $l_ip_address="IP address (or network IP address)";
  $l_blocked_ip_address="IP addresses";
  $l_blocked_ip_address_comment="Comments";
  $l_blocked="Blocked";
  $l_ip_block_explain="List of blocked IP addresses (or network IP adresses)";
  $l_remove="Remove from list";
  $l_title_proto = "Network protocols filter";
  $l_netfilter_on="Actually, the network protocols filter is enable";
  $l_netfilter_off="Actually, the network protocols filter is disable";
  $l_switch_on="Switch the Filter on";
  $l_switch_off="Switch the Filter off";
  $l_comment_on="(choose the authorized network protocols)";
  $l_comment_on="Except for the WEB (port 80), all protocols are blocked.<BR>Choose in the list below, the protocols you want authorize";
  $l_comment_off="(all the network protocols are allowed for authenticated users)";
  $l_protocols="Authorize protocols";
  $l_error_open_file="Error opening the file";
  $l_port="Port number";
  $l_proto="protocol name";
  $l_enabled="Authorized";
  $l_add_to_list="Add to the list";
  $l_save="Save changes";
}
/********************
*  TEST CONF FILES  *
*********************/
define ("SERVICES_LIST", "/usr/local/etc/alcasar-services");
define ("CONF_FILE", "/usr/local/etc/alcasar.conf");
define ("IP_BLOCKED", "/usr/local/etc/alcasar-ip-blocked");
$conf_files=array(SERVICES_LIST,CONF_FILE,IP_BLOCKED);
foreach ($conf_files as $file){
if (!file_exists($file)){
	exit("Requested file ".$file." isn't present");}
if (!is_readable($file)){
	exit("Can't read the file ".$file);}
}
/**********************************
*	Read ALCASAR CONF_FILE    *
***********************************/
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
	fclose($ouvre);
}

if (isset($_POST['choix'])){$choix=$_POST['choix'];} else {$choix="";}
switch ($choix)
{
case 'AV_On' :
	exec ("sudo /usr/local/sbin/alcasar-havp.sh -on");
	break;
case 'AV_Off' :
	exec ("sudo /usr/local/sbin/alcasar-havp.sh -off");
	break;
case 'NF_On' :
	exec ("sudo /usr/local/sbin/alcasar-nf.sh -on");
	break;
case 'NF_Off' :
	exec ("sudo /usr/local/sbin/alcasar-nf.sh -off");
	break;
case 'new_port' :
	if ((trim($_POST['add_port']) != "80") and ($_POST['add_port'] != "") and ($_POST['add_proto'] != "") and (is_numeric($_POST['add_port'])))
		{
		$_POST['add_proto'] = str_replace (CHR(32),"-",$_POST['add_proto']);
		$tab=file(SERVICES_LIST);
		$insert = true;
		if ($tab) // file isn't empty
			{
			foreach ($tab as $line)  //test if port doesn't already exist
				{
				$proto_f=explode(" ", $line);
				if (trim($_POST['add_port']) == trim($proto_f[1])) {$insert = false;}
				}
			}
		if ($insert == true) 
			{
			$line = "\n" . "#" . trim($_POST['add_proto']) . " " . trim($_POST['add_port']);
			$pointeur=fopen(SERVICES_LIST,"a");
			fwrite ($pointeur, $line);
			fclose ($pointeur);
			exec ("sudo /usr/local/bin/alcasar-file-clean.sh");
			}
		}
	break;
case 'new_ip' :
	if (trim($_POST['add_ip']) != "") 
		{
		$_POST['add_comment'] = str_replace (CHR(32),"-",$_POST['add_comment']);
		$tab=file(IP_BLOCKED);
		$insert = true;
		if ($tab) // file isn't empty
			{
			foreach ($tab as $line) // test if IP address doesn't already exist
				{
				$IP_f=explode(" ", $line);
				if (strcmp (trim($_POST['add_ip']),trim(trim($IP_f[0],"#"))) == 0)
					{
					$insert = false;
					break;
					}
				}
			}
		if ($insert == true) 
			{
			$line ="\n" . "#".trim($_POST['add_ip']) . " " . trim($_POST['add_comment']);
			$pointeur=fopen(IP_BLOCKED,"a");
			fwrite ($pointeur, $line);
			fclose ($pointeur);
			exec ("sudo /usr/local/bin/alcasar-file-clean.sh");
			}
		}
	break;
case 'change_port' :
	$tab=file(SERVICES_LIST);
	if ($tab)
		{
// authorize/block protocols
		$pointeur=fopen(SERVICES_LIST,"w+");
		foreach ($tab as $ligne)
			{
			$proto_f=explode(" ", $ligne);
			$name_svc1=trim($proto_f[0],"#");
			$actif = False; $remove_line = false;
			foreach ($_POST as $key => $value)
				{
				if (strstr($key,'del-'))
					{
					$name_svc2 = str_replace('del-','',$key);
					if ($name_svc1 == $name_svc2)
				       		{
						$remove_line = True;
						}
					}
				if (strstr($key,'chk-'))
					{
					$name_svc2 = str_replace('chk-','',$key);
					if ($name_svc1 == $name_svc2)
				       		{
						$actif = True;
						break;
						}
					}
				}
			if (! $remove_line)
				{
				if (! $actif) {	$line="#$name_svc1 $proto_f[1]";}
				else { $line="$name_svc1 $proto_f[1]";}
				fwrite($pointeur,$line);
				}
			}
		fclose($pointeur);
		}
	exec ("sudo /usr/local/bin/alcasar-iptables.sh -on");
	break;
case 'change_ip' :
	$tab=file(IP_BLOCKED);
	if ($tab)
		{
// authorize/block IPs 
		exec ("sudo /usr/sbin/ipset flush alcasar_ip_blocked");
		$pointeur=fopen(IP_BLOCKED,"w+");
		foreach ($tab as $ligne)
			{
			$ip_f=explode(" ", $ligne);
			$ip_blocked1=trim($ip_f[0],"#");
			$actif = False; $remove_line = false;
			foreach ($_POST as $key => $value)
				{
				$key = str_replace ("_",".",$key); // dot are replace by '_' in post request
				if (strstr($key,'del-'))
					{
					$ip_blocked2 = str_replace('del-','',$key);
					if ($ip_blocked1 == $ip_blocked2)
				       		{
						$remove_line = True;
						break;
						}
					}
				if (strstr($key,'chk-'))
					{
					$ip_blocked2 = str_replace('chk-','',$key);
					if ($ip_blocked1 == $ip_blocked2)
				       		{
						$actif = True;
						break;
						}
					}
				}
			if (! $remove_line)
				{
				if (! $actif) {	$line="#$ip_blocked1 $ip_f[1]";}
				else 
					{
					$line="$ip_blocked1 $ip_f[1]";
					exec ("sudo /usr/sbin/ipset add alcasar_ip_blocked $ip_blocked1");
					}
				fwrite($pointeur,$line);
				}
			}
		fclose($pointeur);
		}
	break;
	}
# default values
if (is_file (CONF_FILE))
	{
	$tab=file(CONF_FILE);
	if ($tab)
		{
		foreach ($tab as $line)
			{
			$field=explode("=", $line);
			if ($field[0] == "PROTOCOLS_FILTERING")	{$PROTOCOLS_FILTERING=trim($field[1]);}
			if ($field[0] == "WEB_ANTIVIRUS")	{$WEB_ANTIVIRUS=trim($field[1]);}
			}
		}
	}
echo "<tr><th>$l_title_antivir</th></tr>";
?>
<tr bgcolor=#FFCC66><td><img src=/images/pix.gif width=1 height=2></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
	<tr><td valign="middle" align="left">
<?php
if ($WEB_ANTIVIRUS == "on")
	{
	echo "<CENTER><H3>$l_antivir_on</H3></CENTER>";
 	echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
	echo "<input type=hidden name='choix' value=\"AV_Off\">";
	echo "<input type=submit value=\"$l_switch_antivir_off\">";
}
else
	{
	echo "<CENTER><H3>$l_antivir_off</H3></CENTER>";
 	echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
	echo "<input type=hidden name='choix' value=\"AV_On\">";
	echo "<input type=submit value=\"$l_switch_antivir_on\">";
	}
?>
</FORM>
</td></tr>
</table>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><th><?echo "$l_title_ip_block";?></th></tr>
<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td colspan=2 align="center">
<?
echo "$l_ip_block_explain</td></tr>";
echo "<tr><td align='center' valign='middle'>";
echo "<FORM action='$_SERVER[PHP_SELF]' method='POST'>";
echo "<input type=hidden name='choix' value=\"IP_block_filter\">";
echo "<table cellspacing=2 cellpadding=2 border=1>";
echo "<tr><th>$l_blocked_ip_address<th>$l_blocked_ip_address_comment<th>$l_blocked<th>$l_remove</tr>";
// Read the "IP_block" file
$tab=file(IP_BLOCKED);
if ($tab)  # the file isn't empty
	{
	foreach ($tab as $line)
		{
		if (trim($line) != '') # the line isn't empty
			{
			$blocked_ip=explode(" ", $line);
			$ip_addr=trim($blocked_ip[0],"#");
			$comment=trim($blocked_ip[1]);
			if ($comment ==''){$comment="&nbsp;";}
			echo "<tr><td>$ip_addr<td>$comment";
			echo "<td><input type='checkbox' name='chk-$ip_addr'";
			if (preg_match('/^#/',$line, $r)) {
				echo ">";}
			else {
				echo "checked>";}
			echo "<td>";
			if (strcmp (trim($ip_addr),trim($conf["PUBLIC_IP"]))) {
				echo "<input type='checkbox' name='del-$ip_addr'>";}
			else {
				echo "&nbsp;";}
			echo "</tr>";
			}
		}
	}
?>
</table>
<input type='hidden' name='choix' value='change_ip'>
<input type='submit' value='<?echo"$l_save";?>'>
</form></td><td valign='middle' align='center'>
<form action='<?echo"$_SERVER[PHP_SELF]"?>' method='POST'>
<table cellspacing=2 cellpadding=3 border=1>
<tr><th><?echo"$l_ip_address<th>$l_blocked_ip_address_comment";?>
<td></td></tr>
<tr><td>exemple1 : 15.25.26.27 <br>exemple2 : 18.20.20.0/24</td><td>exemple1 : CERT alert<br>exemple2 : LAN of zombies</td><td></td></tr>
<tr><td><input type='text' name='add_ip' size='17'></td>
<td><input type='text' name='add_comment' size='10'></td>
<input type='hidden' name='choix' value='new_ip'>
<td><input type='submit' value='<?echo"$l_add_to_list";?>'></td>
</tr></table>
</form>
</td></tr>
</table>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><th><?echo "$l_title_proto";?></th></tr>
<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
<tr>
<?
if ($PROTOCOLS_FILTERING == "on")
	{
	echo "<td colspan=\"2\" valign=\"middle\" align=\"left\">";
	echo "<CENTER><H3>$l_netfilter_on</H3>$l_comment_on</CENTER>";
	echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
	echo "<input type=hidden name='choix' value=\"NF_Off\">";
	echo "<input type=submit value=\"$l_switch_off\">";
	echo "</FORM></td></tr>";
	require ('net_filter2.php');
	}
else
	{
	echo "<td valign=\"middle\" align=\"left\">";
	echo "<CENTER><H3>$l_netfilter_off</H3>$l_comment_off</CENTER>";
 	echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
	echo "<input type=hidden name='choix' value=\"NF_On\">";
	echo "<input type=submit value=\"$l_switch_on\">";
	echo "</FORM></td></tr>";
	echo "</table></body></html>";
	}
?>
