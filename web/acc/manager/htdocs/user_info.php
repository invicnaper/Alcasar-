<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config[general_charset]?>">
<title>User personal information</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><?php echo "$l_users_managment";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
	<tr bgcolor="#666666"><td>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
		<tr><td valign="middle" align="left">
<center>
<table border=0 width=640 cellpadding=0 cellspacing=2>
<?php
include("../html/user_toolbar.html.php");
?>
</table>

<?php
if ($change == 1){
	if (is_file("../lib/$config[general_lib_type]/user_info.php"))
		include("../lib/$config[general_lib_type]/user_info.php");
	if (is_file("../lib/$config[general_lib_type]/change_info.php"))
		include("../lib/$config[general_lib_type]/change_info.php");
}

if (is_file("../lib/$config[general_lib_type]/user_info.php"))
	include("../lib/$config[general_lib_type]/user_info.php");
?>

<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white"><?php echo "$l_user : $login ($cn)"?></font>&nbsp;
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
   
   <form method=post>
      <input type=hidden name=login value="<?php echo $login?>">
      <input type=hidden name=change value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<?php
	echo <<<EOM
	<tr>
		<td align=right bgcolor="#d0ddb0">
		$l_name
		</td><td>
		<input type=text name="Fcn" value="$cn" size=35>
		</td>
	</tr>
	<tr>
		<td align=right bgcolor="#d0ddb0">
		$l_email
		</td><td>
		<input type=text name="Fmail" value="$mail" size=35>
		</td>
	</tr>
	<tr>
		<td align=right bgcolor="#d0ddb0">
		Service
		</td><td>
		<input type=text name="Fou" value="$ou" size=35>
		</td>
	</tr>
	<tr>
		<td align=right bgcolor="#d0ddb0">
		$l_homephone
		</td><td>
		<input type=text name="Fhomephone" value="$homephone" size=35>
		</td>
	</tr>
	<tr>
		<td align=right bgcolor="#d0ddb0">
		$l_telephonenumber
		</td><td>
		<input type=text name="Ftelephonenumber" value="$telephonenumber" size=35>
		</td>
	</tr>
	<tr>
		<td align=right bgcolor="#d0ddb0">
		$l_mobile
		</td><td>
		<input type=text name="Fmobile" value="$mobile" size=35>
		</td>
	</tr>
EOM;
?>
	</table>
<br>
<input type=submit class=button value="<?php echo "$l_change";?>" OnClick="this.form.change.value=1">
</form>
	</td></tr>
</table>
</tr>
</table>
</TD></TR>
</TABLE>
</td></tr>
</TABLE>
</body>
</html>
