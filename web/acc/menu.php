<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN//2.0">
<HTML>
<!-- written by Rexy ! -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>menu</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<?
$file_version = "/var/www/html/VERSION";
$handle = fopen ($file_version, "r");
$full_version = fread ($handle, filesize ($file_version));
fclose ($handle);
$tab = explode (" ", $full_version);
$installed_version = $tab[0];
# Choice of language
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_home = "ACCUEIL";
  $l_system = "SYSTÈME";
  $l_auth = "AUTHENTIFICATION";
  $l_filter = "FILTRAGE";
  $l_statistics = "STATISTIQUES";
  $l_backup = "SAUVEGARDES";
  $l_activity = "Activité";
  $l_blacklist = "Noms de domaine";
  $l_ldap = "Ldap/A.D.";
  $l_access_nb = "Accès au centre";
  $l_create_user = "Créer un usager";
  $l_create_voucher = "Créer un ticket rapide";
  $l_edit_user = "Éditer un usager";
  $l_create_group = "Créer un groupe";
  $l_edit_group = "Éditer un groupe";
  $l_import_empty = "Importer / Vider";
  $l_network = "Réseau";
  $l_stat_user_day = "usager/jour";
  $l_stat_con = "connexions";
  $l_stat_daily ="usage journalier";
  $l_stat_network="trafic réseau";
  $l_security="sécurité";
  $l_menu="Menu";
}
else {
  $Language = 'en';
  $l_home = "HOME";
  $l_system = "SYSTEM";
  $l_auth = "AUTHENTICATION";
  $l_filter = "FILTERING";
  $l_statistics = "STATISTICS";
  $l_backup = "BACKUPS";
  $l_activity = "Activity";
  $l_blacklist = "Domain names";
  $l_ldap = "Ldap/A.D.";
  $l_access_nb = "Access to center";
  $l_create_voucher = "Create a quick ticket";
  $l_create_user = "Create a user";
  $l_edit_user = "Edit a user";
  $l_create_group = "Create a group";
  $l_edit_group = "Edit a group";
  $l_import_empty = "Import / Empty";
  $l_network = "Network";
  $l_stat_user_day = "user/day";
  $l_stat_con = "connections";
  $l_stat_daily ="daily use";
  $l_stat_network="network traffic";
  $l_security="security";
  $l_menu="Main";
}
echo "
	<TABLE width=\"100%\" border=0 cellspacing=0 cellpadding=0>
	<tr><th>$l_menu</th></tr>
	<tr><td bgcolor=\"#FFCC66\"><img src=\"/images/pix.gif\" width=1 height=2></td></tr>
</TABLE>
<TABLE width=\"100%\" border=1 cellspacing=0 cellpadding=0>
	<tr bgcolor=\"#666666\"><td>
		<TABLE width=\"100%\" border=0 cellspacing=0 cellpadding=2>
			<tr><td valign=\"middle\" align=\"left\">
				<img src=\"/images/right.gif\" height=10 width=10 border=no nosave><A HREF=\"phpsysinfo/\" TARGET=\"REXY2\">$l_home</A></td></tr>";
if (isset($_GET['a'])) { $a=$_GET['a']; }
	else $a=0;
if (isset($_GET['b'])) { $b=$_GET['b']; }
	else $b=0;
$selection[0]=$l_system;
$selection[1]=$l_auth;
$selection[2]=$l_filter;
$selection[3]=$l_statistics;
$fichier[0]="system.php";
$fichier[1]="auth.php";
$fichier[2]="filtering.php";
$fichier[3]="stat.php";
$i=0;
$nb1=count($selection);
while ($i != $nb1)
  {
	if ($a==1 AND $i==$b)
		{
		echo "<tr><td valign=\"middle\" align=\"left\"><img src=\"/images/down2.gif\" height=10 width=10 border=no nosave><a href=\"menu.php?a=0&b=0\"><font color=\"black\"><b>$selection[$i]</b></font></a></td></tr>";
		include($fichier[$i]);
		}
	else
		{
		echo "<tr><td valign=\"middle\" align=\"left\"><img src=\"/images/right.gif\" height=10 width=10 border=no nosave><a href=\"menu.php?a=1&b=$i\">$selection[$i]</a></td></tr>";
		}
	$i++;
  }
echo "
			<tr><td valign=\"middle\" align=\"left\">
			<img src=\"/images/right.gif\" height=10 width=10 border=no nosave><A HREF=\"backup/sauvegarde.php\" TARGET=\"REXY2\">$l_backup</A></td></tr>";
?>
		</TABLE>
	</td></tr>
</TABLE>
<br>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th>Doc</th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1"
height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
	<tr bgcolor="#666666"><td>
		<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
			<tr><td valign="middle" align="left"><img src="/images/right.gif" height=10
width=10 border=no nosave><a href="<? echo "alcasar-$installed_version-presentation-$Language.pdf"; ?>" target="_blank">Presentation</a></td></tr>
			<tr><td valign="middle" align="left"><img src="/images/right.gif" height=10
width=10 border=no nosave><a href="<? echo "alcasar-$installed_version-installation-$Language.pdf"; ?>" target="_blank">Installation</a></td></tr>
			<tr><td valign="middle" align="left"><img src="/images/right.gif" height=10
width=10 border=no nosave><a href="<? echo "alcasar-$installed_version-exploitation-$Language.pdf"; ?>" target="_blank">Exploitation</a></td></tr>
			<tr><td valign="middle" align="left"><img src="/images/right.gif" height=10
width=10 border=no nosave><a href="<? echo "alcasar-$installed_version-technique.pdf"; ?>" target="_blank">Technique</a></td></tr>
		</TABLE>
	</td></tr>
</TABLE>
<BR>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><? echo "$l_access_nb"; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
	<tr bgcolor="#666666"><td>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=0>
		<tr><td valign="middle" align="center">
		<?				// Access counter
		$name_fic="compteur.txt";
		if (($fp=fopen($name_fic,"r")) == false) exit;
		$nb=fgets($fp,10);
		fclose($fp);
		printf("%d", $nb);
		?>
		<br>depuis le 13/08/2013<br></center></td></tr>
	</TABLE>
	</td></tr>
</TABLE>
</BODY>
</HTML>
