<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");

echo <<<EOM
<title>$l_user : $cn</title>
<meta http-equiv="Content-Type" content="text/html; charset=$config[general_charset]">
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
<link rel="stylesheet" href="/css/style.css">
EOM;
if ($logged_now)
	print <<<EOM
<script Language="JavaScript">
<!--
	var start;
	var our_time;
	
	function startcounter() 
	{
		var start_date = new Date();
		start = start_date.getTime();
		our_time = $lastlog_session_time_jvs;
		showcounter();
	}

	function showcounter ()
	{
		var now_date = new Date();
		var diff = now_date.getTime() - start + our_time;
			
		var hours = parseInt(diff / 3600000);
		if(isNaN(hours)) hours = 0;
			
		var minutes = parseInt((diff % 3600000) / 60000);
		if(isNaN(minutes)) minutes = 0;
			
		var seconds = parseInt(((diff % 3600000) % 60000) / 1000);
		if(isNaN(seconds)) seconds = 0;
			
		var timeValue = " " ;
		timeValue += ((hours < 10) ? "0" : "") + hours;
		timeValue += ((minutes < 10) ? ":0" : ":") + minutes;
		timeValue += ((seconds < 10) ? ":0" : ":") + seconds;
		
		document.online.status.value = timeValue;
		setTimeout("showcounter()", 1000);
	}
	//-->
</script>
EOM;

print <<<EOM
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
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>

EOM;
if ($logged_now){
	print <<<EOM
	<form name="online" onSubmit="return(false);">
	<tr><td align=center bgcolor="#d0ddb0">
	<b>$l_connected</b>
	</td><td>
	$lastlog_time
	</td></tr>
	<tr><td align=center bgcolor="#d0ddb0">
	<b>$l_connection_time</b>
	</td><td>	
	<input type="text" name="status" size=10 value="$lastlog_session_time">
	</form>
	</td></tr>
EOM;
	require('../html/user_admin_userinfo.html.php');

}else if ($not_known)  print <<<EOM
	<tr><td align=center bgcolor="#d0ddb0">
	$l_user_never_login
	</td><td>-
	</td></tr>
EOM;
else{
	print <<<EOM
	<tr><td align=center bgcolor="#d0ddb0">
	<b>$l_user_not_login_yet</b>
	</td><td>-
	</td></tr>
	<tr><td align=center bgcolor="#d0ddb0">
	<b>$l_last_login</b>
	</td><td>
	$lastlog_time
	</td></tr>
	<tr><td align=center bgcolor="#d0ddb0">
	<b>$l_connection_time</b>
	</td><td>
	$lastlog_session_time
	</td></tr>
EOM;
	require('../html/user_admin_userinfo.html.php');
}

print <<<EOM
	<tr><td align=center bgcolor="#d0ddb0">
	<b>$l_remain_time</b>
	</td><td>
	$msg
	</td></tr>
<!--	<tr><td align=center bgcolor="#d0ddb0">
	Complete user description
	</td><td>
	$descr
	</td></tr>  -->
	</table>
	</table>
</table>

EOM;

if (is_file("../lib/$config[general_lib_type]/password_check.php"))
	include("../lib/$config[general_lib_type]/password_check.php");

echo <<<EOM
<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white">Analyse</font>&nbsp;
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
	<tr><td align=center bgcolor="#d0ddb0">&nbsp;</td><td align=center bgcolor="#d0ddb0"><b>total</b></td><td align=center bgcolor="#d0ddb0"><b>$l_monthly</b></td><td align=center bgcolor="#d0ddb0"><b>$l_weekly</b></td><td align=center bgcolor="#d0ddb0"><b>$l_daily</b></td><td align=center bgcolor="#d0ddb0"><b>$l_by_session</b></td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_limit</b></td><td>$total_limit</td><td>$monthly_limit</td><td>$weekly_limit</td><td>$daily_limit</td><td>$session_limit</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_used_time</b></td><td>$tot_time</td><td>$monthly_used</td><td>$weekly_used</td><td>$daily_used</td><td>$lastlog_session_time</td></tr>
	</table>
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" va
lign=top>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_day</b></td><td align=center bgcolor="#d0ddb0"><b>$l_daily_limit</b></td><td align=center bgcolor="#d0ddb0"><b>$l_used_time</b></td><tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_sunday</b></td><td>$daily_limit</td><td>$used[0]</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_monday</b></td><td>$daily_limit</td><td>$used[1]</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_tuesday</b></td><td>$daily_limit</td><td>$used[2]</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_wednesday</b></td><td>$daily_limit</td><td>$used[3]</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_thursday</b></td><td>$daily_limit</td><td>$used[4]</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_friday</b></td><td>$daily_limit</td><td>$used[5]</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_saturday</b></td><td>$daily_limit</td><td>$used[6]</td></tr>
	</table></table>
</table>
<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white">$l_last7days_status</font>&nbsp;
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_connections_number</b></td><td>
	$tot_conns</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_total_connections_time</b></td><td>
	$tot_time</td></tr>
<!--	<tr><td align=center bgcolor="#d0ddb0">Identifications d&eacute;fectueuses</td><td>
	$tot_badlogins</td></tr> -->
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_download</b></td><td>
	$tot_input</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_upload</b></td><td>
	$tot_output</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_connection_time ($l_average)</b></td><td>
	$avg_time</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_download ($l_average)</b></td><td>
	$avg_input</td></tr>
	<tr><td align=center bgcolor="#d0ddb0"><b>$l_upload ($l_average)</b></td><td>
	$avg_output</td></tr>	
	</table>
	</table>
</table>
<br>
EOM;
/*
if ($user_info){
	echo <<<EOM
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=340></td>
<td bgcolor="black" width=250>
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white">Informations personnelles</font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>nom</b>
	</td>
	<td>
	$cn
	</td>
	</tr>
EOM;
	if ($config[general_prefered_lang] != 'en'){
		echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>nom ($config[general_prefered_lang_name])</b>
	</td>
	<td>
	$cn_lang
	</td>
	</tr>
EOM;
	}
	echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>service</b>
	</td>
	<td>
	$ou
	</td>
	</tr>
EOM;
	if ($config[general_prefered_lang] != 'en'){
		echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>service ($config[general_prefered_lang_name])</b>
	</td>
	<td>
	$ou_lang
	</td>
	</tr>
EOM;
	}
	echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>titre</b>
	</td>
	<td>
	$title
	</td>
	</tr>
EOM;
	if ($config[general_prefered_lang] != 'en'){
		echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>title ($config[general_prefered_lang_name])</b>
	</td>
	<td>
	$title_lang
	</td>
	</tr>
EOM;
	}
	echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>adresse</b>
	</td>
	<td>
	$address
	</td>
	</tr>
EOM;
	if ($config[general_prefered_lang] != 'en'){
		echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>adresse ($config[general_prefered_lang_name])</b>
	</td>
	<td>
	$address_lang
	</td>
	</tr>
EOM;
	}
	echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>adresse personnelle</b>
	</td>
	<td>
	$homeaddress
	</td>
	</tr>
EOM;
	if ($config[general_prefered_lang] != 'en'){
		echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>adresse personnelle ($config[general_prefered_lang_name])</b>
	</td>
	<td>
	$homeaddress_lang
	</td>
	</tr>
EOM;
	}
	echo <<<EOM
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>t&eacute;l&eacute;phone</b>
	</td>
	<td>
	$telephonenumber
	</td>
	</tr>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>t&eacute;l&eacute;phone personnel</b>
	</td>
	<td>
	$homephone
	</td>
	</tr>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>mobile</b>
	</td>
	<td>
	$mobile
	</td>
	</tr>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>fax</b>
	</td>
	<td>
	$fax
	</td>
	</tr>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>home page</b>
	</td>
	<td>
	<a href="$url" target=userpage onclick=window.open("$url","userpage","width=1000,height=550,toolbar=no,scrollbars=yes,resizable=yes") title="Aller à&agrave; la page d'accueil de l'utilisateur">$url</a>
	</td>
	</tr>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>e-mail</b>
	</td>
	<td>
	<a href="mailto: $mail" title="Envoyer un email">$mail</a>
	</td>
	</tr>
	<tr>
	<td align=center bgcolor="#d0ddb0">
	<b>e-mail alias</b>
	</td>
	<td>
	<a href="mailto: $mailalt" title="Envoyer un email">$mailalt</a>
	</td>
	</tr>
	</table>
	</table>
</table>

EOM;
}
 */
	print <<<EOM
</tr></table>
EOM;
if ($logged_now)
	print <<<EOM
<script Language="JavaScript">
	startcounter();
</script>
EOM;
?>
</body>
</html>
