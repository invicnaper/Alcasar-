<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
require('../lib/attrshow.php');
require('../lib/defaults.php');
if ($user_type != 'group'){
	if (is_file("../lib/$config[general_lib_type]/user_info.php"))
		include("../lib/$config[general_lib_type]/user_info.php");
	if ($config[general_lib_type] == 'sql' && $config[sql_show_all_groups] == 'true'){
		$saved_login = $login;
		$login = '';
		if (is_file("../lib/sql/group_info.php"))
			include("../lib/sql/group_info.php");
		$login = $saved_login;
	}
}
else{
	if (is_file("../lib/$config[general_lib_type]/group_info.php"))
		include("../lib/$config[general_lib_type]/group_info.php");
}
if ($config[general_lib_type] == 'sql' && $config[sql_use_operators] == 'true'){
	$colspan=2;
	$show_ops = 1;
	include("../lib/operators.php");
}
else{
	$show_ops = 0;
	$colspan=1;
}
?>
<html>
<head>
<title>Users & groups edition</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config[general_charset]?>">
<link rel="stylesheet" href="/css/style.css">
<script language="javascript" type="text/javascript">
var chars='0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ'
function password(size)
  {
  var pass=''
  while(pass.length < size)
  {
    pass+=chars.charAt(Math.round(Math.random() * (chars.length)))
  }
  document.edituser.passwd.value=pass
  document.edituser.pwdgene.value=pass
}
</script>
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th>
<?php
if ($user_type != 'group'){ echo "$l_users_managment";} else{ echo "$l_groups_managment";}
?>
	</th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
	<tr><td valign="middle" align="left">
	<center>
	<table border=0 width=640 cellpadding=0 cellspacing=2>
<?php
if ($user_type != 'group')
	{
	include("../html/user_toolbar.html.php");
	$titre=$l_user;
	}
else
	{
	include("../html/group_toolbar.html.php");
	$titre=$l_group;
	}
print <<<EOM
</table>
<br>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=center valign=top><th>
	<font color="white">$titre : $login ($cn)</font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
EOM;
   
if ($change == 1){
	if (is_file("../lib/$config[general_lib_type]/change_attrs.php"))
		include("../lib/$config[general_lib_type]/change_attrs.php");
	if ($user_type != 'group'){
		if ($config[general_show_user_password] != 'no' && $passwd != '' 
			&& is_file("../lib/$config[general_lib_type]/change_passwd.php"))
			include("../lib/$config[general_lib_type]/change_passwd.php");
		if (is_file("../lib/$config[general_lib_type]/user_info.php"))
			include("../lib/$config[general_lib_type]/user_info.php");
		if ($group_change && $config[general_lib_type] == 'sql' && $config[sql_show_all_groups] == 'true'){
			include("../lib/sql/group_change.php");
			include("../lib/defaults.php");
		}
	}
	else{
		if (is_file("../lib/$config[general_lib_type]/group_info.php"))
			include("../lib/$config[general_lib_type]/group_info.php");
	}
}
else if ($badusers == 1){
	if (is_file("../lib/add_badusers.php"))
		include("../lib/add_badusers.php");
}
	
?>
   <form name="edituser" method=post>
      <input type=hidden name=login value=<?php print $login ?>>
      <input type=hidden name=user_type value=<?php print $user_type ?>>
      <input type=hidden name=change value="0">
      <input type=hidden name=add value="0">
      <input type=hidden name=badusers value="0">
      <input type=hidden name=group_change value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<?php
if ($user_type != 'group' && $config[general_show_user_password] != 'no'){
	echo <<<EOM
<tr>
<td align=right colspan=$colspan bgcolor="#d0ddb0">
$l_new_password<br>
EOM;
	echo <<<EOM
</td>
<td>
<input type=password name=passwd value="" size=20>
<br /><input type="button" value="$l_passwd_gen" onclick="password(8)">
<input type="text" value="" name="pwdgene" size=10 readonly>
</td>
</tr>
EOM;
}
	foreach($show_attrs as $key => $desc){
		$name = $attrmap["$key"];
		$generic = $attrmap[generic]["$key"];
		if ($name == 'none')
			continue;
		unset($vals);
		unset($selected);
		unset($ops);
		$def_added = 0;
		if ($item_vals["$key"][count]){
			for($i=0;$i<$item_vals["$key"][count];$i++){
				$vals[] = $item_vals["$key"][$i];
				$ops[] = $item_vals["$key"][operator][$i];
			}
		}
		else{
			if ($default_vals["$key"][count]){
				for($i=0;$i<$default_vals["$key"][count];$i++){
					$vals[] = $default_vals["$key"][$i];
					$ops[] = $default_vals["$key"][operator][$i];
				}
			}
			else{
				$vals[] = '';
				$ops[] = '=';
			}
			$def_added = 1;
		}
		if ($generic == 'generic' && $def_added == 0){
			for($i=0;$i<$default_vals["$key"][count];$i++){
				$vals[] = $default_vals["$key"][$i];
				$ops[] = $default_vals["$key"][operator][$i];
			}
		}	
		if ($add && $name == $add_attr){
			$vals[] = $default_vals["$key"][0];
			$ops[] = ($default_vals["$key"][operator][0] != '') ? $default_vals["$key"][operator][0] : '=';
		}

		$i = 0;
		foreach($vals as $val){
			$name1 = $name . $i;
			$val = ereg_replace('"','&quot;',$val);
			$oper_name = $name1 . '_op';
			$oper = $ops[$i];
			$selected[$oper] = 'selected';
			$i++;
		switch ($key)
		{
				// $advanced = 1 : champs de saisie amélioré (calendrier, convertisseur, etc.) 
			case 'Simultaneous-Use' : 
				$advanced=1;
				$help_link="help/simultaneous_use_help.html";
				$desc=$l_simultaneous_use;
				break;
			case 'Max-All-Session' :
				$advanced=1;
				$help_link="help/max_all_session_help.html";
				$desc=$l_max_all_session;
				break;
			case 'Session-Timeout' : 
				$advanced=1;
				$help_link="help/session_timeout_help.html";
				$desc=$l_session_timeout;
				break;
			case 'Max-Daily-Session' :
				$advanced=1;
				$help_link="help/session_timeout_help.html";
				$desc=$l_daily_timeout;
				break;
			case 'Max-Monthly-Session' :
				$advanced=1;
				$help_link="help/session_timeout_help.html";
				$desc=$l_monthly_timeout;
				break;
			case 'Login-Time' : 
				$advanced=1;
				$help_link="help/login_time_help.html";
				$desc=$l_login_time;
				break;
			case 'Expiration' :
				$advanced=1;
				$help_link="help/expiration_help.html";
				$desc=$l_expiration;
				break;
			case 'ChilliSpot-Max-Input-Octets' :
				$advanced=1;
				$help_link="help/chillispot_max_input_octets_help.html";
				$desc=$l_max_input_octets;
				break;
			case 'ChilliSpot-Max-Output-Octets' :
				$advanced=1;
				$help_link="help/chillispot_max_output_octets_help.html";
				$desc=$l_max_output_octets;
				break;
			case 'ChilliSpot-Max-Total-Octets' :
				$advanced=1;
				$help_link="help/chillispot_max_total_octets_help.html";
				$desc=$l_max_total_octets;
				break;
			case 'ChilliSpot-Bandwidth-Max-Up' :
				$advanced=1;
				$help_link="help/chillispot_bandwidth_max_up_help.html";
				$desc=$l_max_bandwidth_up;
				break;
			case 'ChilliSpot-Bandwidth-Max-Down' :
				$advanced=1;
				$help_link="help/chillispot_bandwidth_max_down_help.html";
				$desc=$l_max_bandwidth_down;
				break;
			case 'WISPr-Redirection-URL' :
				$advanced=1;
				$help_link="help/wispr_redirection_url_help.html";
				$desc=$l_wispr_redirection;
				break;
			default:
				$advanced=1;
				break;
		}
			print <<<EOM
			<tr>
			<td class="etiquette">
			<a href="$help_link" target=help onclick=window.open("$help_link","help","width=600,height=250,toolbar=no,scrollbars=no,resizable=yes") title="$l_click_for_help"><font color="blue">$desc</font></a>
EOM;
			if ($show_ops){
				switch ($key)
					{
					case 'Simultaneous-Use' : 
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Login-Time' : 
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Expiration' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Max-All-Session' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Session-Timeout' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Max-Daily-Session' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Max-Weekly-Session' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'Max-Monthly-Session' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'ChilliSpot-Max-Input-Octets' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'ChilliSpot-Max-Output-Octets' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'ChilliSpot-Max-Total-Octets' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'ChilliSpot-Bandwidth-Max-Up' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'ChilliSpot-Bandwidth-Max-Down' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					case 'WISPr-Redirection-URL' :
						echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=</td><td><input type=text name=\"$name1\" value=\"$val\" size=20></td>";
						break;
					default :
						print <<<EOM
<td>
<select name=$oper_name>
<option $selected[$op_eq] value="=">=
<option $selected[$op_set] value=":=">:=
<option $selected[$op_add] value="+=">+=
<option $selected[$op_eq2] value="==">==
<option $selected[$op_ne] value="!=">!=
<option $selected[$op_gt] value=">">&gt;
<option $selected[$op_ge] value=">=">&gt;=
<option $selected[$op_lt] value="<">&lt;
<option $selected[$op_le] value="<=">&lt;=
<option $selected[$op_regeq] value="=~">=~
<option $selected[$op_regne] value="!~">!~
<option $selected[$op_exst] value="=*">=*
<option $selected[$op_nexst] value="!*">!*
</select>
</td>
<td><input type=text name="$name1" value="$val" size=20></td>
EOM;
						break;
					}
				}
			print <<<EOM
</tr>
EOM;
		}
	}
if ($user_type != 'group'){
	echo <<<EOM
<tr>
<td align=right colspan=$colspan bgcolor="#d0ddb0">
$l_group_member<br><font size=-2><i>($l_main_group)</i></font>
</td>
<td>
EOM;
if (isset($member_groups)){
	echo "<select size=5 name=\"edited_groups[]\" multiple OnChange=\"this.form.group_change.value=1\">";
	if ($config[sql_show_all_groups] == 'true'){
		foreach ($existing_groups as $group => $count){
			if ($member_groups[$group] == $group)
				echo "<option selected value=\"$group\">$group\n";
			else
				echo "<option value=\"$group\">$group\n";
			}
		}else{
		foreach ($member_groups as $group)
			echo "<option value=\"$group\">$group\n";
		}
		echo "</select></td></tr>";
	}
	else{
		echo "aucun group</td></tr>";
	}
} 
echo "</table><br>";
echo "<input type=submit class=button value=$l_change OnClick=\"this.form.change.value=1\">";
//if ($user_type != 'group'){
//	echo <<<EOM
//<br><br>
//<input type=submit class=button value="Add to Badusers" OnClick="this.form.badusers.value=1">
//<a href="help/badusers_help.html" target=bu_help onclick=window.open("help/badusers_help.html","bu_help","width=600,height=210,toolbar=no,scrollbars=no,resizable=yes") title="BADUSERS Help Page"><font color="blue">&lt;--Help</font></a>
//EOM;
//}
?>
</form>
</td></tr>
</table>
</tr>
</table>
</td></tr>
</TABLE>
</body>
</html>
