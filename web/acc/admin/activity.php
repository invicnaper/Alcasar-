<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- written by Rexy -->
<head>
<META HTTP-EQUIV="Refresh" CONTENT="30">
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<title>&Eacute;tat du r&eacute;seau</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<?
#retrieve IP_address of ALCASAR
$alcasar_conf_file="/usr/local/etc/alcasar.conf";
$ouvre=fopen("$alcasar_conf_file","r");
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
	exit("Erreur d'ouverture du fichier $alcasar_conf_file");
}
fclose($ouvre);
$tmp = explode("/",$conf["PRIVATE_IP"]);
$private_ip=$tmp[0];
require('/etc/freeradius-web/config.php');
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_activity = "Activit&eacute; sur le r&eacute;seau de consultation";
  $l_ip_adr = "Adresse IP";
  $l_mac_adr = "Adresse MAC";
  $l_user = "Usager";
  $l_mac_allowed = "@MAC autoris&eacute;e";
  $l_action = "Action";
  $l_dissociate = "Dissocier";
  $l_disconnect = "D&eacute;connecter";
  $l_refresh = "Cette page est rafraichie toutes les 30 secondes";
  $l_edit_user = "Editer l'utilisateur"; 
}
else {
  $l_activity = "Activity on the consultation LAN";
  $l_ip_adr = "IP Adress";
  $l_mac_adr = "MAC Adress";
  $l_user = "User";
  $l_mac_allowed = "@MAC allowed";
  $l_action = "Action";
  $l_dissociate = "Dissociate";
  $l_disconnect = "Disconnect";
  $l_refresh = "This frame is refreshed every 30'";
  $l_edit_user = "Edit user"; 
}
echo "
<tr><th>$l_activity</th></tr>
<tr bgcolor=\"#FFCC66\"><td><img src=\"/images/pix.gif\" width=\"1\"
height=\"2\"></td></tr>
</TABLE>";
if (isset($_POST['action'])){
	switch ($_POST['action']){
		case 'user_disconnect' :
			exec ("sudo /usr/sbin/chilli_query logout $_POST[mac_addr]");
			unset ($_POST['user']);
			unset ($_POST['mac_addr']);
			unset ($_POST['choix']);
		break;
		case 'mac_disconnect' :
			exec ("sudo /usr/sbin/chilli_query dhcp-release $_POST[mac_addr]");
			unset ($_POST['mac_addr']);
			unset ($_POST['choix']);
		break;
	}
}
?>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
	<tr><td valign="middle" align="left">
	<center>
<? echo "$l_refresh";?>
	<table border=1 width="80%" bordercolordark="#ffffe0" bordercolorlight="#000000" width="100%" cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
		<tr bgcolor="#d0ddb0">
<? echo "
		<th>#</th>
		<th>$l_ip_adr</th>
		<th>$l_mac_adr</th>
		<th>$l_user</th>
		<th>$l_action</th>
		</tr>";
		$output = array(); $output_mac = array(); $nb_ligne = 0;
		exec ('sudo /sbin/ip link show eth1 |grep ether|cut -d" " -f6', $output_mac);
		$eth1_mac_addr=strtoupper(str_replace(":","-",$output_mac[0]));
		exec ('sudo /usr/sbin/chilli_query list|sort -k5 -r', $output);
		while (list(,$ligne) = each($output)){
			$detail = explode (" ", $ligne);
			$nb_ligne ++;
			echo "<FORM action='".$_SERVER['PHP_SELF']."' method=POST>";
			echo "<TR>";
			echo "<TD>".$nb_ligne."</TD>";
			echo "<TD>".$detail[1]."</TD>";
			echo "<TD>".$detail[0]."</TD>";
			echo "<TD>";
			# authenticated equipment 
			if ($detail[4] == "1"){
			# by MAC address
				if ($detail[5] == $detail[0]){
					echo "<a href=\"/acc/manager/htdocs/user_admin.php?login=$detail[5]\" title=\"$l_edit_user\">$l_mac_allowed</a>";
					echo "</TD><TD>&nbsp;";
				}
			# by user
				else {
					echo "<a href=\"/acc/manager/htdocs/user_admin.php?login=$detail[5]\" title=\"$l_edit_user $detail[5]\">$detail[5]</a>";
					echo "</TD>";
					echo "<TD>";
					echo "<INPUT type='hidden' name='action' value='user_disconnect'>";
					echo "<INPUT type='hidden' name='user' value='$detail[5]'>";
					echo "<INPUT type='hidden' name='mac_addr' value='$detail[0]'>";
					echo "<INPUT type=submit value='$l_disconnect'>";
					}
				}
			# equipment without authenticated user
			else if (($detail[0] == $eth1_mac_addr) || ($detail[1] == $private_ip)){
				echo "ALCASAR system";
				echo "</TD>";
				echo "<TD>";
				echo "&nbsp;";
			}
			else { 
				echo "&nbsp;";
				echo "</TD>";
				echo "<TD>";
				echo "<INPUT type='hidden' name='action' value='mac_disconnect'>";
				echo "<INPUT type='hidden' name='mac_addr' value='$detail[0]'>";
				echo "<INPUT type='submit' value='$l_dissociate'>";
			}
			echo "</TD></TR></FORM>";
		}
		?>
		</td></tr>
	</table>
	</td></tr>
</table>
</html>
