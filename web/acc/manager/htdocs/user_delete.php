<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
if ($type != 'group'){
	if (is_file("../lib/$config[general_lib_type]/user_info.php"))
		include("../lib/$config[general_lib_type]/user_info.php");
}
else {
	if (is_file("../lib/$config[general_lib_type]/group_info.php"))
		include("../lib/$config[general_lib_type]/group_info.php");
}
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");

echo <<<EOM
<html>
<head>
EOM;

if ($user_type != 'group'){
	$util = $l_user;
	$title = $l_users_managment;}
else{
	$util = $l_group;
	$title = $l_groups_managment;}

echo <<<EOM
<title>delete users and groups</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th>$title</th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
	<tr bgcolor="#666666"><td>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
		<tr><td valign="middle" align="left">
<center>
<table border=0 width=640 cellpadding=0 cellspacing=2>
EOM;

if ($user_type != 'group')
	include("../html/user_toolbar.html.php");
else
	include("../html/group_toolbar.html.php");

print <<<EOM
</table>

<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white">$util : $login ($cn)</font>&nbsp;
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
EOM;
   
if ($delete_user == 1){
	if ($user_type != 'group'){
		if (is_file("../lib/$config[general_lib_type]/delete_user.php"))
			include("../lib/$config[general_lib_type]/delete_user.php");
	}
	else{
		if ($delete_users_of_group == 1){
			unset($group_members);
			$tmp_group_name=$login;
			if (is_file("../lib/$config[general_lib_type]/group_info.php")){
				include("../lib/$config[general_lib_type]/group_info.php");
			}
			foreach ($group_members as $member){
				$login=$member;
				if (is_file("../lib/$config[general_lib_type]/delete_user.php"))
					include("../lib/$config[general_lib_type]/delete_user.php");
			}
			$login=$tmp_group_name;
		}
		if (is_file("../lib/$config[general_lib_type]/delete_group.php"))
			include("../lib/$config[general_lib_type]/delete_group.php");
	}
	echo <<<EOM
</td></tr>
</table>
</tr>
</table>
</body>
</html>
EOM;
	exit();
}
?>
   <form method=post>
      <input type=hidden name=login value=<?php print $login ?>>
      <input type=hidden name=delete_user value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<tr>
<td align=center>
<?php
if ($user_type == 'group'){
  echo "$l_group_members_remove : ";
  echo "<input type=checkbox name=delete_users_of_group value=\"1\">";
}
echo "<br>";
echo "$l_are_you_sure <b>$login</b> ? ";
?>
	<input type=submit class=button value="<?php echo"$l_yes_remove";?>" OnClick="this.form.delete_user.value=1">
</form>
</td></tr></table></td></tr>
</table>
</tr>
</table>
</TD></TR>
</TABLE>
</td></tr>
</TABLE>
</body>
</html>
