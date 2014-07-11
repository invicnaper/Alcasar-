<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
if (isset($search_IN)) $selected[$search_IN] = 'selected';
if (isset ($radius_attr)) $selected[$radius_attr] = 'selected';
if (isset ($max_results)){ $max = ($max_results) ? $max_results : 40;}
?>
<html>
<head>
<title>Find a user</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config['general_charset']?>">
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
<table border=0 width=540 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=340></td>
<td bgcolor="black" width=200>
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white"><?php echo "$l_search_filter";?></font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>

<?php
if (isset($find_user)){
if ($find_user == 1){
	unset($found_users);
	if (is_file("../lib/$config[general_lib_type]/find.php"))
		include("../lib/$config[general_lib_type]/find.php");
	if (isset($found_users)){
		$num = 0;
		$msg .= <<<EOM

        <table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
        <tr bgcolor="#d0ddb0">
        <th>#</th><th>$l_user</th><th>Actions</th><th>$l_group_member</th>
        </tr>
EOM;
		foreach ($found_users as $user){
			if ($user == '') {$user = '-';}
			else {
				$login = $user;
				if (is_file("../lib/sql/defaults.php")) //retrieve user groups
					include("../lib/sql/defaults.php");
				if (is_file("../lib/sql/user_info.php")) //retrieve user info
					include("../lib/sql/user_info.php");
			}
			$User = urlencode($user);
			$num++;
			$msg .= "<tr align=center><td>$num</td><td>$user";
		        if ($cn != '-') {$msg .= " ($cn)";}
			$msg .= <<<EOM
				</td><td><a href="user_admin.php?login=$User" title="$l_status"><img src=/images/info.gif></a>
				<a href="user_edit.php?login=$User" title="$l_attributes"><img src=/images/create.gif></a>
				<a href="user_info.php?login=$User" title="$l_personal_info"><img src=/images/tpf.gif></a>
				<a href="user_accounting.php?login=$User" title="$l_connections"><img src=/images/graph.gif></a>
				<a href="clear_opensessions.php?login=$User" title="$l_open_sessions"><img src=/images/state_ok.gif></a>
				<a href="user_delete.php?login=$User" title="$l_remove"><img src=/images/state_error.gif></a></td><td>
EOM;
			if (isset($member_groups)) foreach ($member_groups as $group) { $msg .= "$group";}
			else $msg .= "&nbsp"; 
		$msg .= "</td>";
		}
		$msg .= "</tr></table>\n";
	}
	else
		$msg = "<b>$l_no_user_found</b><br>\n";
}
}
?>
   <form method=post>
      <input type=hidden name=find_user value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<tr>
<td align=right bgcolor="#d0ddb0">
<?php
echo <<<EOM
$l_search_criteria
</td>
<td>
<select name="search_IN" editable onChange="this.form.submit();">
<option $selected[username] value="username">$l_login
<option $selected[name]  value="name">$l_name
<option $selected[department] value="department">Service
<option $selected[radius] value="radius">$l_special_attribute
EOM;
?>

</select>
</td>
</tr>
<?php
if (isset($search_IN)){
	if ($search_IN == 'radius'){
		require('../lib/attrshow.php');
		echo <<<EOM
<tr>
<td align=right bgcolor="#d0ddb0">
$l_attribute
</td>
<td>
<select name="radius_attr" editable>
EOM;
		foreach($show_attrs as $key => $desc)
		switch ($key)
		{
			case 'Simultaneous-Use' : 
				$desc=$l_simultaneous_use;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'Max-All-Session' :
				$desc=$l_max_all_session;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'Session-Timeout' : 
				$desc=$l_session_timeout;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'Max-Daily-Session' :
				$desc=$l_daily_timeout;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'Max-Monthly-Session' :
				$desc=$l_monthly_timeout;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'Login-Time' : 
				$desc=$l_login_time;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'Expiration' :
				$desc=$l_expiration;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'ChilliSpot-Max-Input-Octets' :
				$desc=$l_max_input_octets;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'ChilliSpot-Max-Output-Octets' :
				$desc=$l_max_output_octets;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'ChilliSpot-Max-Total-Octets' :
				$desc=$l_max_total_octets;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'ChilliSpot-Bandwidth-Max-Up' :
				$desc=$l_max_bandwidth_up;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'ChilliSpot-Bandwidth-Max-Down' :
				$desc=$l_max_bandwidth_down;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
			case 'WISPr-Redirection-URL' :
				$desc=$l_wispr_redirection;
				echo "<option $selected[$key] value=\"$key\">$desc\n";		
				break;
		}
		echo <<<EOM
</select>
</td>
</tr>
EOM;
	}
}
?>
<tr>
<td align=right bgcolor="#d0ddb0">
<?php echo "$l_value";?>
</td>
<td>
<input type=text name="search" value="<?php if (isset($search)) echo $search ;?>" size=25>
</td>
</tr>
<!--<tr>
<td align=right bgcolor="#d0ddb0">
Nombre de r&eacute;sultats Max.
</td>
<td>
<input type=text name="max_results" value="<?php echo $max ?>" size=25>
</td>
</tr> --> 
</table>
<br>
<input type=submit class=button value="<?php echo"$l_search";?>" OnClick="this.form.find_user.value=1">
</form>
<?php
if (isset($find_user)){
	if ($find_user == 1){ echo $msg ;}}
?>
</td></tr>
</table>
</td></tr>
</table>
</td></tr>
</TABLE>
</td></tr>
</TABLE>
</body>
</html>
