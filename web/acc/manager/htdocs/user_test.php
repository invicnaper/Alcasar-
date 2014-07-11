<?php
require('/etc/freeradius-web/config.php');

if ($login == 'da_server_test'){
	$login = $config[general_test_account_login];
	$test_login=1;
}

echo <<<EOM
<html>
<head>
<title>Test de l'utilisateur $login</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="style.css">
</head>
<body>
<center>
<table border=0 width=550 cellpadding=0 cellspacing=0>
<tr valign=top>
<!--<td align=center><img src="images/title2.gif"></td>-->
</tr>
</table>

<table border=0 width=400 cellpadding=0 cellspacing=2>
EOM;

if (!$test_login)
	include("../html/user_toolbar.html.php");

print <<<EOM
</table>

<br>
<table border=0 width=540 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=340></td>
<td bgcolor="black" width=200>
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
EOM;

if ($test_login){
	print <<<EOM
	<font color="white">Page de Test du serveur Radius</font>&nbsp;
EOM;
}else{
	print <<<EOM
	<font color="white">Page de Test de l'utilisateur $login</font>&nbsp;
EOM;
}
?>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>

<?php
if ($server == '' || !preg_match('/^[\w\.]+$/',$server))
	$server = $config[general_radius_server];
if ($port == 0 || !is_numeric($port))
	$port = $config[general_radius_server_port];
if ($auth_proto == '')
	$auth_proto = $config[general_radius_server_auth_proto];
$selected[$auth_proto] = 'selected';

if ($test_user == 1){
	$tmp_file = tempnam("$config[general_tmp_dir]",'DA');
	$req=file($config[general_auth_request_file]);
	if ($config[general_ld_library_path] != '')
		putenv("LD_LIBRARY_PATH=$config[general_ld_library_path]");
	$comm = $config[general_radclient_bin] . " $server:$port" . ' auth ' . $config[general_radius_server_secret] 
		. ' >' . $tmp_file;
	$fp = popen("$comm","w");
	if ($fp){
		foreach ($req as $val){
			// Ignore comments
			if (ereg('^[[:space:]]*#',$val) || ereg('^[[:space:]]*$',$val))
				continue;
			fwrite($fp,$val);
		}
		if ($test_login){
			$test=1;
			fwrite($fp, "User-Name = \"$config[general_test_account_login]\"\n");
			fwrite($fp, "User-Password = \"$config[general_test_account_password]\"\n");
			pclose($fp);
		}
		else{
			fwrite($fp, "User-Name = \"$login\"\n");
			if ($auth_proto == 'chap')
				fwrite($fp, "CHAP-Password = \"$passwd\"\n");
			else
				fwrite($fp, "User-Password = \"$passwd\"\n");
			if (strlen($extra))
				fwrite($fp,$extra);
			pclose($fp);
		}
		$reply = file($tmp_file);
		unlink($tmp_file);
		$msg = "<b>" . strftime('%A, %e %B %Y, %T %Z') . "</b><br>\n";
		$msg .= "<b>Server: </b><i>$server:$port</i><br><br>\n";
		if (ereg('code 2', $reply[0]))
			$msg .= "<b>L'authentification a <font color=green>r&eacute;ussie</font>";
		else if (ereg('code 3',$reply[0]))
			$msg .= "<b>L'authentification a <font color=red>&eacute;chou&eacute;e</font>";
		else if (ereg('no response from server', $reply[0]))
			$msg .= "<b><font color=red>Pas de r&eacute;ponse du serveur</font>";
		else if (ereg('Connection refused',$reply[0]))
			$msg .= "<b><font color=red>La connection a &eacute;t&eacute; refus&eacute;e</font>";
		if ($test_login)
			$msg .= "</b><i> (test de l'utilisateur $login)</i><br>\n";
		else
			$msg .= "</b><br>\n";
		array_shift($reply);
		if (count($reply)){
			$msg .= "<br><b>R&eacute;ponse du serveur :</b><br>\n";
			foreach ($reply as $val){
				$msg .= "<i>$val</i><br>\n";
			}
		}
		if ($test_login){
			print <<<EOM
$msg
<br>
</td></tr>
</table>
</tr>
</table>
</body>
</html>
EOM;
			exit();
		}

	}
}
?>
   <form method=post>
      <input type=hidden name=login value=<?php print $login ?>>
      <input type=hidden name=test_user value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<tr>
<td align=right bgcolor="#d0ddb0">
Mot de passe utilisateur
</td>
<td>
<input type=password name=passwd value="<?php print $passwd ?>" size=25>
</td>
</tr>
<tr>
<td align=right bgcolor="#d0ddb0">
Serveur Radius
</td>
<td>
<input type=text name=server value="<?php print $server ?>" size=25>
</td>
</tr>
<tr>
<td align=right bgcolor="#d0ddb0">
Port du serveur Radius
</td>
<td>
<input type=text name=port value="<?php print $port ?>" size=25>
</td>
</tr>
<tr>
<td align=right bgcolor="#d0ddb0">
Attributs suppl&eacute;mentaires
</td>
<td>
<textarea name="extra" cols="35" wrap="PHYSICAL" rows="4"><?php print $extra ?></textarea>
</td>
</tr>
<tr>
<td align=right bgcolor="#d0ddb0">
Protocole d'authentification
</td>
<td>
<?php
echo <<<EOM
<select name="auth_proto" editable>
<option $selected[pap] value="pap">PAP
<option $selected[chap] value="chap">CHAP
EOM
?>
</select>
</td>
</tr>

	</table>
<br>
<input type=submit class=button value="Lancement du Test" OnClick="this.form.test_user.value=1">
</form>
<?php
if ($test_user == 1){
	echo <<<EOM
<br>
$msg
EOM;
}
?>
	</td></tr>
</table>
</tr>
</table>
</body>
</html>
