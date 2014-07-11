<?php

//Gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
	
require('/etc/freeradius-web/config.php');
if ($show == 1){
	header("Location: group_admin.php?login=$login");
	exit;
}

if ($config[general_lib_type] != 'sql'){
	echo <<<EOM
<title>$l_title</title>
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

require('../lib/attrshow.php');
require('../lib/defaults.php');
require("../lib/$config[general_lib_type]/group_info.php");

if ($config[general_lib_type] == 'sql' && $config[sql_use_operators] == 'true'){
	$colspan=2;
	$show_ops=1;
}else{
	$show_ops = 0;
	$colspan=1;
}
?>

<html><head><title>New group</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config[general_charset]?>">
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/epoch_styles.css" />
<script type="text/javascript" src="/js/epoch_classes.js"></script>
<script type="text/javascript" src="/js/fonctions.js"></script>
<script language="javascript" type="text/javascript">
/*Insertion du calendrier*/
	var dp_cal;      
window.onload = function () {
	dp_cal  = new Epoch('epoch_popup','popup',document.getElementById('popup_container'));
};
/*Fin calendrier*/
</script>
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><? echo "$l_groups_managment"; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
	<tr bgcolor="#666666"><td>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
		<tr><td valign="middle" align="left">
<center>
<table border=0 width=550 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=340></td>
<td bgcolor="black" width=200>
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white"><? echo "$l_group_create"; ?></font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>
   
<?php
if (is_file("../lib/$config[general_lib_type]/group_info.php"))
	include("../lib/$config[general_lib_type]/group_info.php");
if ($create == 1){
	if ($group_exists != "no"){
		echo <<<EOM
<b><i>$login</i> $l_already_exist</b>
EOM;
	}
	else{
		if (is_file("../lib/$config[general_lib_type]/create_group.php"))
			include("../lib/$config[general_lib_type]/create_group.php");
		if (is_file("../lib/$config[general_lib_type]/group_info.php"))
			include("../lib/$config[general_lib_type]/group_info.php");
	}
}
?>
   <form name="newgroup" method=post>
      <input type=hidden name=create value="0">
      <input type=hidden name=show value="0">
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<?php
	echo <<<EOM
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_created_groups
		</td><td>
EOM;
		if (!isset($existing_groups))
			echo "<b>$l_group_empty</b>\n";
		else{
			echo "<select name=\"existing_groups\">\n";	
			foreach ($existing_groups as $group => $count)
				echo "<option value=\"$group\">$group\n";
			echo "</select>\n";
		}
	echo <<<EOM
		</td>
	</tr>
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_group_name
		</td><td>
		<input type=text name="login" value="$login" size=35>
		</td>
	</tr>
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_group_members
		</td><td>
		<textarea name=members cols="15" wrap="PHYSICAL" rows=5></textarea>
		</td>
	</tr>
		
EOM;
	foreach($show_attrs as $key => $desc){
		$name = $attrmap["$key"];
		if ($name == 'none')
			continue;
		$oper_name = $name . '_op';
		$val = ($item_vals["$key"][0] != "") ? $item_vals["$key"][0] : $default_vals["$key"][0];
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
//		if ($advanced){
//			print <<<EOM
//			<tr>
//				<td class="etiquette">
//				$desc
//				</td>
//EOM;
//		}
		if ($show_ops && $advanced){
		print <<<EOM
			<tr>
			<td class="etiquette">
			<a href="$help_link" target=help onclick=window.open("$help_link","help","width=600,height=250,toolbar=no,scrollbars=no,resizable=yes") title="$l_click_for_help"><font color="blue">$desc</font></a>
			</td>
EOM;
			switch ($key){
				case 'Simultaneous-Use' : 
				case 'Max-All-Session' :
				case 'Max-Daily-Session' :
				case 'Max-Weekly-Session' :
				case 'Max-Monthly-Session' :
				case 'Login-Time' : 
				case 'Expiration' :
					echo "<td><select name=$oper_name><option $selected[$op_eq] value=\":=\">:=";
					break;
				case 'Session-Timeout' :
				case 'ChilliSpot-Max-Input-Octets' :
				case 'ChilliSpot-Max-Output-Octets' :
				case 'ChilliSpot-Max-Total-Octets' :
				case 'ChilliSpot-Bandwidth-Max-Up' :
				case 'ChilliSpot-Bandwidth-Max-Down' :
				case 'WISPr-Redirection-URL' :
					echo "<td><select name=$oper_name><option $selected[$op_eq] value=\"=\">=";
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
EOM;
					break;
			}
		}
/* 
Ajout du choix d'unité (pour les durées limites de session, journée et de mois) 
et d'un calendrier pour la date d'expiration
Sauf dans le cas de la visualisation
*/
	if ($advanced){echo "<td>";}
	if ($create==0 ){
		switch ($name){
			/*
			Choix de l'unité heures, minutes ou secondes 
			pour les durées limites de session,journée et de mois	
			*/	
			case 'Session-Timeout' :
			case 'Max-Daily-Session' :
			case 'Max-Monthly-Session' :
			case 'Max-All-Session' :
				/*valeur d'origine de durée limite */
				echo"<input id =\"$name\" type=text name=\"$name\" onfocus=\"this.value=''\" value=\"$val\" size=28>";
				/* Choix d'unité*/
				echo" <select name=\"$name"."_opt"."\" onchange=\"temps(this,'$name','newgroup')\">
						<option value=\"s\" selected>s</option>
						<option value=\"m\" >m</option>
						<option value=\"H\" >H</option>
					</select>";
				break;
			case 'Expiration' :
				/*Ajout du calendrier pour choisir la date*/
				echo"<input id=\"popup_container\" type=text name=\"$name\" value=\"$val\" size=35>";
				break;
			default :
				if ($advanced) echo"<input type=text name=\"$name\" value=\"$val\" size=35>";
				break;
			}
	}else{
		/*Pas de gestion de remplissage lors de la visualisation*/
		if ($advanced) echo"<input type=text name=\"$name\" value=\"$val\" size=35>";
	}
/*fin Ajout*/
}
echo "</table><BR>";
if ($create == 1)
	echo "<input type=submit class=button value=\"$l_show_profile\" OnClick=\"this.form.show.value=1\">";
	else
	echo "<input type=submit class=button value=\"$l_create\" OnClick=\"return formControl('newgroup');\">";
?>
<br><br>
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
