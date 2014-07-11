<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');

if (is_file("../lib/sql/drivers/$config[sql_type]/functions.php"))
	include_once("../lib/sql/drivers/$config[sql_type]/functions.php");
else{
	echo <<<EOM
<html>
<title>Find group</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<center>
<b>Could not include SQL library functions. Aborting</b>
</body>
</html>
EOM;
	exit();
}
if ($config[general_lib_type] != 'sql'){
	echo <<<EOM
<html>
<title>find group</title>
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
?>
<head>
<title>Find group</title>
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
<table border=0 width=550 cellpadding=0 cellspacing=0>
<tr valign=top>
</tr>
</table>
<table border=0 width=540 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=55%></td>
<td bgcolor="black" width=45%>
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white"><?php echo "$l_group_select"; ?></font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
<?php
unset($login);
$num = 0;
include_once("../lib/$config[general_lib_type]/group_info.php");
if (isset($existing_groups)){
	echo "<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor=\"#ffffe0\" valign=top>";
	echo "<tr bgcolor=\"#d0ddb0\">";
	echo "<th>#</th><th>$l_group </th><th>$l_nb_users</th></tr>";
	foreach ($existing_groups as $group => $num_members){
		$num++;
		$Group = urlencode($group);
		echo <<<EOM
		<tr align=center>
			<td>$num</td>
			<td><a href="group_admin.php?login=$Group" title="Editer le groupe $group">$group</a></td>
			<td>$num_members</td>
		</tr>
EOM;
	}
}
else
	echo "<b>$l_group_empty</b>\n";
?>
	</table>
</table>
</tr>
</table>
</TD></TR>
</TABLE>
</td></tr>
</TABLE>
</body>
</html>
