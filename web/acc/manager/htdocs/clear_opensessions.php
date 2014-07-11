<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
if (is_file("../lib/$config[general_lib_type]/user_info.php"))
	include("../lib/$config[general_lib_type]/user_info.php");
if (is_file("../lib/sql/drivers/$config[sql_type]/functions.php"))
	include_once("../lib/sql/drivers/$config[sql_type]/functions.php");
else{
	echo <<<EOM
<title>Clear opensession</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="style.css">
</head>
<body>
<center>
<b>Could not include SQL library functions. Aborting</b>
</body>
</html>
EOM;
        exit();
}

echo <<<EOM
<html>
<head>
<title>Clear opensession</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th>$l_users_managment</th></tr>
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

include("../html/user_toolbar.html.php");

print <<<EOM
</table>

<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white">$l_user : $login ($cn)</font>&nbsp;
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
EOM;
 
if ($clear_sessions == 1)
	{
# close active sessions
	exec ("sudo /usr/local/sbin/alcasar-logout.sh $login");
# delete open accounting sessions
	$now = time();
	$today_now = date("Y-m-d H:i:s",$now);
	$link = @da_sql_pconnect($config);
	if ($link)
		{
		$res = @da_sql_query($link,$config,
		"UPDATE $config[sql_accounting_table] SET acctstoptime = '$today_now', acctterminatecause='Admin-Reset'
		WHERE username='$login' AND acctstoptime IS NULL;");
		if (! $res)
			echo "<b>Error deleting open sessions for user" . da_sql_error($link,$config) . "</b><br>\n";
		}
	else
		echo "<b>Could not connect to SQL database</b><br>\n";
	}
# Count of accounting open sessions (in database)
$open_accnt_sessions = 0;
$link = @da_sql_pconnect($config);
if ($link){
	$search = @da_sql_query($link,$config,
	"SELECT COUNT(*) AS counter FROM $config[sql_accounting_table]
	WHERE username = '$login' AND acctstoptime IS NULL;");
	if ($search){
		if ($row = @da_sql_fetch_array($search,$config))
			$open_accnt_sessions = $row['counter'];
	}
	else
		echo "<b>Database query failed: " . da_sql_error($link,$config) . "</b><br>\n";
}
else
	echo "<b>Could not connect to SQL database</b><br>\n";

# Count of chilli open sessions (for coova-chilli)
$open_chilli_sessions = 0;
exec ("sudo /usr/sbin/chilli_query list|cut -d\" \" -f5,6|grep $login|grep ^1|wc -l" , $open_chilli_sessions);

?>
   <form method=post>
      <input type=hidden name=login value=<?php print $login ?>>
      <input type=hidden name=clear_sessions value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<tr>
<td align=center>
<?
if (($open_accnt_sessions == 0) && ($open_chilli_sessions[0] == 0))
	{
	echo "$l_no_open_session";
	}
else	{
	echo "<b>$open_chilli_sessions[0]</b> $l_opened_sessions<br><b>$open_accnt_sessions</b> $l_active_accounting<br>";
	echo "$l_want_to_close ";
	echo "<input type=submit class=button value=\"$l_yes_close\" OnClick=\"this.form.clear_sessions.value=1\">";
	}
?>
</form>
</td></tr></table>
</td></tr></table>
</TD></TR></TABLE>
</body>
</html>
