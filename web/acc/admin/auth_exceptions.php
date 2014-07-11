<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- written by Rexy - 3abtux -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>Exceptions</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<?
/********************
*  TEST CONF FILES  *
*********************/
define ("DOMAIN_ALLOWED_LIST", "/usr/local/etc/alcasar-uamdomain");
define ("IP_ALLOWED_LIST", "/usr/local/etc/alcasar-uamallowed");
$conf_files=array(DOMAIN_ALLOWED_LIST,IP_ALLOWED_LIST);
foreach ($conf_files as $file){
if (!file_exists($file)){
	exit("Requested file ".$file." isn't present");}
if (!is_readable($file)){
	exit("Can't read the file ".$file);}
}
$domain_allowed_list="/usr/local/etc/alcasar-uamdomain";
$url_allowed_list="/usr/local/etc/alcasar-uamallowed";
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
	$l_error_open_file	= "Erreur d'ouverture du fichier";
	$l_trusted_domain	= "Noms de domaine Internet de confiance";
	$l_domain		= "Noms de domaine";
	$l_comment_explain	= "Lien affiché dans la page d'interception";
	$l_comment_explain2	= "Laissez vide si non affiché";
	$l_remove		= "Retirer de la liste";
	$l_trusted_ip		= "adresses IP de confiance";
	$l_trusted_equipments	= "Equipements de consultation de confiance";
	$l_comment		= "Commentaires";
	$l_trusted_domain_explain = "Gérez ici les noms de domaine Internet pouvant &ecirc;tre joints sans authentification";
	$l_trusted_equipments_explain	= "Pour gérer les équipements du réseau de consultation pouvant accéder à Internet sans être interceptés : lisez la documentation d'exploitation (ch.4.7c)";
	$l_trusted_ip_explain	= "Gérez ici les adresses IP de systèmes ou de réseaux pouvant être joints sans authentification";
	$l_submit		= "Enregistrer";
	$l_add_to_list		= "Ajouter à la liste";
	$l_apply		= "Appliquer les changements";
}
else {
	$l_error_open_file	= "File open error";
	$l_trusted_domain	= "Trusted Internet domain names";
	$l_domain		= "Domain names";
	$l_comment_explain	= "Link displayed in intercept page";
	$l_comment_explain2	= "Let empty to not display link";
	$l_remove		= "Remove from list";
	$l_trusted_ip		= "Trusted IP addresses";
	$l_trusted_equipments	= "Trusted consultation equipements";
	$l_comment		= "Comments";
	$l_trusted_domain_explain = "Manage Internet domain names that can be joined without authentication";
	$l_trusted_equipments_explain	= "To manage consultation equipments allowed to connect to Internet without interception, read exploitation documentation (ch.4.7c)";
	$l_trusted_ip_explain	= "Manage systems addresses or networks IP addresses that can be joined without authentication";
	$l_submit		= "Submit";
	$l_add_to_list		= "Add to list";
	$l_apply		= "Apply changes";
}
if (isset($_POST['choix'])){ 
	switch ($_POST['choix'])
	{
	case 'new_uamdomain' :
	if (trim($_POST['add_uamdomain']) != "") 
		{
		$tab=file(DOMAIN_ALLOWED_LIST);
		$insert = true;
		if ($tab) // file isn't empty
			{
			foreach ($tab as $line) // test if domain address doesn't already exist
				{
				$domain=explode("\"", $line);
				if (strcmp(trim($_POST['add_uamdomain']),$domain[1]) == 0)
			       		{
					$insert = false;
					break;
					}
				}
			}
		if ($insert == true) 
			{
			$line ="\nuamdomain=\"" . trim($_POST['add_uamdomain']) . "\" #" . trim($_POST['add_domain_comment']);
			$pointeur=fopen(DOMAIN_ALLOWED_LIST,"a");
			fwrite ($pointeur, $line);
			fclose ($pointeur);
			exec ("sudo /usr/local/bin/alcasar-file-clean.sh");
			exec ("sudo service chilli restart");
			}
		}
	break;
	case 'change_uamdomain' :
	$tab=file(DOMAIN_ALLOWED_LIST);
	if ($tab)
		{
		$pointeur=fopen(DOMAIN_ALLOWED_LIST,"w+");
		foreach ($tab as $ligne)
			{
			$uamdomain1=explode("\"", $ligne);
			$remove_line = false;
			foreach ($_POST as $key => $value)
				{
				$key = str_replace ("_",".",$key); // dot are replace by '_' in post request
				if (strstr($key,'del-'))
					{
					$uamdomain2 = str_replace('del-','',$key);
					if (strcmp($uamdomain1[1],$uamdomain2) == 0)
				       		{
						$remove_line = True;
						break;
						}
					}
				}
			if (! $remove_line)
				{
				fwrite($pointeur,$ligne);
				}
			}
		fclose($pointeur);
		}
	exec ("sudo service chilli restart");
	break;
	case 'new_ip' :
	if (trim($_POST['add_ip']) != "") 
		{
		$tab=file(IP_ALLOWED_LIST);
		$insert = true;
		if ($tab) // file isn't empty
			{
			foreach ($tab as $line) // test if domain address doesn't already exist
				{
				$ip=explode("\"", $line);
				if (strcmp(trim($_POST['add_ip']),$ip[1]) == 0)
			       		{
					$insert = false;
					break;
					}
				}
			}
		if ($insert == true) 
			{
			$line ="\nuamallowed=\"" . trim($_POST['add_ip']) ."\" #" . trim($_POST['add_ip_comment']);
			$pointeur=fopen(IP_ALLOWED_LIST,"a");
			fwrite ($pointeur, $line);
			fclose ($pointeur);
			exec ("sudo /usr/local/bin/alcasar-file-clean.sh");
			exec ("sudo service chilli restart");
			}
		}
	break;
	case 'change_ip' :
	$tab=file(IP_ALLOWED_LIST);
	if ($tab)
		{
		$pointeur=fopen(IP_ALLOWED_LIST,"w+");
		foreach ($tab as $ligne)
			{
			$ip1=explode("\"", $ligne);
			$remove_line = false;
			foreach ($_POST as $key => $value)
				{
				$key = str_replace ("_",".",$key); // dot are replace by '_' in post request
				if (strstr($key,'del-'))
					{
					$ip2 = str_replace('del-','',$key);
					if (strcmp($ip1[1],$ip2) == 0)
				       		{
						$remove_line = True;
						break;
						}
					}
				}
			if (! $remove_line)
				{
				fwrite($pointeur,$ligne);
				}
			}
		fclose($pointeur);
		}
	exec ("sudo service chilli restart");
	break;
	}
}
?>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><th><?echo "$l_trusted_domain";?></th></tr>
<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td colspan=2 align="center">
<?
echo "$l_trusted_domain_explain</td></tr>";
echo "<tr><td align='center' valign='middle'>";
echo "<table cellspacing=2 cellpadding=2 border=1>";
echo "<FORM action='$_SERVER[PHP_SELF]' method='POST'>";
echo "<tr align='center' bgcolor='#d0ddb0'><td>$l_domain<td>$l_comment_explain<td>$l_remove</tr>";
// Read the "Domain alowed" file
$tab=file(DOMAIN_ALLOWED_LIST);
if ($tab)  # the file isn't empty
	{
	foreach ($tab as $line)
		{
		if (trim($line) != '') # the line isn't empty
			{
			$domain_allowed=explode("#", $line);
			$uamdomain=trim($domain_allowed[0],"#");
			$domain=explode("\"", $uamdomain);
			echo "<tr><td>$domain[1]";
			echo "<td>";
			if (isset ($domain_allowed[1])) {
				echo trim($domain_allowed[1]);}
			else echo "&nbsp";
			echo "<td>";
			echo "<input type='checkbox' name='del-$domain[1]'>";
			echo "</tr>";
			}
		}
	}
echo "</table>";
if ($tab)
	{
	echo "<input type='hidden' name='choix' value='change_uamdomain'>";
	echo "<input type='submit' value='$l_apply'>";
	}
?>
</form>
</td><td valign='middle' align='center'>
<form action='<?echo"$_SERVER[PHP_SELF]"?>' method='POST'>
<table cellspacing=2 cellpadding=3 border=1>
<tr align='center'><td bgcolor='#d0ddb0'><?echo "$l_domain<td bgcolor='#d0ddb0'>$l_comment_explain";?>
<td></tr>
<tr><td>exemple1 : www.mydomain.com <br>exemple2 : .yourdomain.net
<td>exemple1 : mydomain<br><?echo "$l_comment_explain2";?><td></tr>
<tr><td><input type='text' name='add_uamdomain' size='20'>
<td><input type='text' name='add_domain_comment' size='15'>
<input type='hidden' name='choix' value='new_uamdomain'>
<td><input type='submit' value='<?echo "$l_add_to_list";?>'>
</tr></table>
</form>
</tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo "$l_trusted_ip" ;?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td colspan=2 align="center">
<?
echo "$l_trusted_ip_explain</td></tr>";
echo "<tr><td align='center' valign='middle'>";
echo "<table cellspacing=2 cellpadding=2 border=1>";
echo "<FORM action='$_SERVER[PHP_SELF]' method='POST'>";
echo "<tr align='center' bgcolor='#d0ddb0'><td>$l_trusted_ip<td>$l_comment<td>$l_remove</tr>";
// Read the "ip alowed" file
$tab=file(IP_ALLOWED_LIST);
if ($tab)  # the file isn't empty
	{
	foreach ($tab as $line)
		{
		if (trim($line) != '') # the line isn't empty
			{
			$ip_allowed=explode("#", $line);
			$ip_a=trim($ip_allowed[0],"#");
			$ip=explode("\"", $ip_a);
			echo "<tr><td>$ip[1]";
			echo "<td>";
			if (isset($ip_allowed[1]))
				echo trim($ip_allowed[1]);
			else echo "&nbsp;";
			echo "<td><input type='checkbox' name='del-$ip[1]'>";
			echo "</tr>";
			}
		}
	}
echo "</table>";
if ($tab)
	{
	echo "<input type='hidden' name='choix' value='change_ip'>";
	echo "<input type='submit' value='$l_apply'>";
	}
?>
</form>
</td><td valign='middle' align='center'>
<form action='<?echo "$_SERVER[PHP_SELF]"?>' method='POST'>
<table cellspacing=2 cellpadding=3 border=1>
<tr align='center'><td bgcolor='#d0ddb0'><?echo "$l_trusted_ip<td bgcolor='#d0ddb0'>$l_comment";?>
<td></tr>
<tr><td>exemple1 : 170.25.23.10 <br>exemple2 : 15.20.20.0/16</td>
<td>my_web_server <br>my_dmz<td></tr>
<tr><td><input type='text' name='add_ip' size='20'></td>
<td><input type='text' name='add_ip_comment' size='15'></td>
<input type='hidden' name='choix' value='new_ip'>
<td><input type='submit' value='<?echo "$l_add_to_list";?>'></td>
</tr></table>
</td></tr>
</table>
</form>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo "$l_trusted_equipments";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td colspan=2 align="center">
<?echo "$l_trusted_equipments_explain";?>
</td></tr>
</table>
</BODY>
</HTML>
