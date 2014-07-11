<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- written by Rexy -->
<HEAD>
<TITLE>Sauvegarde</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<?
# choice of language
$Language = "en";
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
 $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
 $Language = strtolower(substr(chop($Langue[0]),0,2));}
if ($Language == 'fr'){
 $l_backups = "Sauvegarde";
 $l_create_user_db_backup = "Sauvegarder la base active des usagers";
 $l_tracability_backup = "Créer le fichier actif des traces";
 $l_create_system_backup = "Créer un fichier de configuration";
 $l_execute = "Ex&eacute;cuter";
 $l_backup_files = "Fichiers disponibles pour archivage";
 $l_firewall_log = "Journaux de traçabilité";
 $l_users_db_backups = "Base des usagers";
 $l_system_backup = "Fichiers de configuration";
 $l_empty = "vide";
}
else {
 $l_backups = "Backups";
 $l_create_user_db_backup = "Save the active users database";
 $l_tracability_backup = "Create the active traceability file";
 $l_create_system_backup = "Create the configuration file";
 $l_execute = "Execute";
 $l_backup_files = "Archive backup files";
 $l_firewall_log = "Traceability log files";
 $l_users_db_backups = "Users database";
 $l_system_backup = "Configuration files";
 $l_empty = "empty";
}
function taille_fichier($fichier)
{
	$taille_fichier = filesize($fichier);
	if ($taille_fichier >= 1073741824){
		$taille_fichier = round($taille_fichier / 1073741824 * 100) / 100 . " Go";}
	elseif ($taille_fichier >= 1048576){
		$taille_fichier = round($taille_fichier / 1048576 * 100) / 100 . " Mo";}
	elseif ($taille_fichier >= 1024){
		$taille_fichier = round($taille_fichier / 1024 * 100) / 100 . " Ko";}
	else {$taille_fichier = $taille_fichier . " o";} 
	return $taille_fichier;
}
?>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><? echo $l_backups;?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
	<tr><td valign="middle" align="left">
	<FORM action="sauvegarde.php" method=POST><b>
		<select name='choix'></b>
			<option value="tracability_backup"><?echo "$l_tracability_backup";?>
			<option value="user_DB_backup"><?echo "$l_create_user_db_backup";?>
			<option value="system_backup"><?echo "$l_create_system_backup";?>
		</select>
		<input type=submit value="<?echo "$l_execute";?>">
	</FORM>
	</td></tr>
</TABLE>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?echo "$l_backup_files";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
	<TR align="center">
	<TD><b><?echo "$l_firewall_log";?></b></TD>
	<TD><b><?echo "$l_users_db_backups";?></b></TD>
	<TD><b><?echo "$l_system_backup";?></b></TD>
	</TR><TR align="center">
<?
if (isset($_POST['choix'])){
	switch ($_POST['choix']){
		case 'user_DB_backup' :
			exec ("sudo /usr/local/sbin/alcasar-mysql.sh --dump");
		break;
		case 'tracability_backup' :
			exec ("sudo /usr/local/bin/alcasar-archive.sh --live");
		break;
		case 'system_backup' :
			exec ("sudo /usr/local/bin/alcasar-conf.sh --create");
		break;
	}
}
$dir[0]="archive";
$dir[1]="base";
$dir[2]="system_backup";
$j=0;
$nb=count($dir);
while ($j != $nb)
{
	echo "<TD valign='top'>";
	$rep = opendir("/var/Save/".$dir[$j]);
	$i=0; unset ($liste_f);
	while ( $file = readdir($rep) )
	{
		if ($file != '.' && $file != '..')
		{
			$liste_f[$i] = $file;
			$i++;
		}
	}
	closedir($rep);
	if ($i == 0)
	{
		echo "$l_empty";
	}
	else
	{
		sort($liste_f);
		while ( $i > 0)
		{
			$i--;
			echo "<a href=\"/save/$dir[$j]/$liste_f[$i]\">$liste_f[$i]</A> (";echo taille_fichier("/var/Save/".$dir[$j]."/".$liste_f[$i]);echo ")<BR>";
		}
	}
	echo "</TD>";
	$j++;
}
?>
	</tr>
</TABLE>
</BODY>
</HTML>
