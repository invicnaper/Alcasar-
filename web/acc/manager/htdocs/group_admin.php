<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
if ($show == 1 && isset($del_members)){
        header("Location: user_admin.php?login=$del_members[0]");
        exit;
}
if ($config[general_lib_type] != 'sql'){
	echo <<<EOM
<title>Admin_groups</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="style.css">
</head>
<body>
<center>
<b>This page is only available if you are using sql as general library type</b>
</body>
</html>
EOM;
	exit();
}

unset($group_members);
if (is_file("../lib/$config[general_lib_type]/group_info.php")){
	include("../lib/$config[general_lib_type]/group_info.php");
	if ($group_exists == 'no'){
		echo <<<EOM
<title>Admin_groups</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="style.css">
</head>
<body>
<center>
<form action="group_admin.php" method=get>
<b>Le groupe &nbsp;&nbsp;</b>
<input type="text" size=10 name="login" value="$login">
<b>&nbsp;&nbsp;n'existe pas</b><br>
<input type=submit class=button value="Show Group">
</body>
</html>
EOM;
                exit();
        }
}
?>

<html>
<head>
<title>Admin_groups</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config[general_charset]?>">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><?php echo "$l_groups_managment"; ?></th></tr>
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
include("../html/group_toolbar.html.php");
?>
</table>
<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=340></td>
<td bgcolor="black" width=200>
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white"><?php echo "$l_group : $login";?></font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>

<?php
if ($do_changes == 1){
	if (is_file("../lib/$config[general_lib_type]/group_admin.php"))
		include("../lib/$config[general_lib_type]/group_admin.php");
	if (is_file("../lib/$config[general_lib_type]/group_info.php"))
		include("../lib/$config[general_lib_type]/group_info.php");
}
?>
   
   <form method=post>
      <input type=hidden name=login value=<?php echo $login ?>>
      <input type=hidden name=do_changes value=0>
      <input type=hidden name=show value=0>
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<tr>
<td align=right bgcolor="#d0ddb0">
<?php echo "$l_group_members_to_remove";?>
</td>
<td>
<select name=del_members[] multiple size=5> 
<?php
foreach ($group_members as $member){
	echo "<option value=\"$member\">$member\n";
}
?>
</select>
</td>
</tr>
<tr>
<td align=right bgcolor="#d0ddb0">
<?php echo "$l_group_members_to_add";?>
</td>
<td>
<textarea name=new_members cols="15" wrap="PHYSICAL" rows=5></textarea>
</td>
</tr>
	</table>
<br>
<input type=submit class=button value="<?php echo "$l_change";?>" OnClick="this.form.do_changes.value=1">
<br><br>
<input type=submit class=button value="<?php echo "$l_manage_selected_user";?>" OnClick="this.form.show.value=1">
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
