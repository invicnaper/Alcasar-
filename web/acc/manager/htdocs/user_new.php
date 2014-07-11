<?php
// ticket d'impression (thank's to Geoffroy MUSITELLI)
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
require('/etc/freeradius-web/config.php');
if ($show == 1){
	header("Location: user_admin.php?login=$login");
	exit;
}
require('../lib/attrshow.php');
require('../lib/defaults.php');

if ($config[general_lib_type] == 'sql' && $config[sql_use_operators] == 'true'){
	$colspan=2;
	$show_ops=1;
}else{
	$show_ops = 0;
	$colspan=1;
}
?>

<html><head><title>User creation</title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $config[general_charset]?>">
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" type="text/css" href="/css/epoch_styles.css" />
<script type="text/javascript" src="/js/epoch_classes.js"></script>
<script type="text/javascript" src="/js/fonctions.js"></script>
<script language="javascript" type="text/javascript">

/*Insertion du calendrier */
	var dp_cal;      
window.onload = function () {
	dp_cal  = new Epoch('epoch_popup','popup',document.getElementById('popup_container'));
};
/*Fin calendrier*/

</script>
</head>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo "$l_users_managment"; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
	<tr bgcolor="#666666"><td>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
		<tr><td valign="middle" align="left">
<center>
<table border=0 width=620 cellpadding=1 cellspacing=1>
<tr valign=top>
<td width=400></td>
<td bgcolor="black">
	<table border=0 width=100% cellpadding=2 cellspacing=0>
	<tr bgcolor="#907030" align=right valign=top><th>
	<font color="white"><? echo "$l_user_create"; ?></font>
	</th></tr>
	</table>
</td></tr>
<tr bgcolor="black" valign=top><td colspan=2>
	<table border=0 width=100% cellpadding=12 cellspacing=0 bgcolor="#ffffd0" valign=top>
	<tr><td>   
<?php
function sec_imp($time)
	/*Formatage des secondes avant l'impression */
    {
	$jour = 0;$heure = 0;$minute = 0;$seconde = 0;
	$jour = floor($time/86400);
	$reste = $time%86400;
    	if ($jour!=0) $result = $jour.' J ';
	$heure = floor($reste/3600);
    	$reste = $reste%3600;
    	if ($heure!=0) $result = $result.$heure.' H ';
	$minute = floor($reste/60);
    	if ($minute!=0) $result = $result.$minute.' min ';
	$seconde = $reste%60;
    	if ($seconde!=0) $result = $result.$seconde.' s ';
   	return $result;
    }

if ($create == 1){
	if (is_file("../lib/$config[general_lib_type]/user_info.php"))
		include("../lib/$config[general_lib_type]/user_info.php");
	if ($user_exists != "no"){
		echo <<<EOM
<b><i>$login</i> $l_already_exist</b>
EOM;
	}
	else{
		if (is_file("../lib/$config[general_lib_type]/create_user.php"))
			include("../lib/$config[general_lib_type]/create_user.php");
		/*  Petit traitement pré-impression pour la lisibilité */
		/*  Récupération des attributs du groupe le cas échéant */
		if ($group!=''){
			$saved_login = $login;
			$login = $group;
			if (is_file("../lib/sql/group_info.php"))
				include("../lib/sql/group_info.php");
			$login = $saved_login;}
		/*  Si les valeurs de durée sont vide remplissage avec la valeur 'Illimitée'*/
		/*  et formatage des secondes sous le format Heure min ses*/
		if ($sto_imp==''){ $sto_imp=$v_illimit;}
			else { $sto_imp=sec_imp($sto_imp);}
		if ($mas_imp==''){ $mas_imp=$v_illimit;}
			else { $mas_imp=sec_imp($mas_imp);}
		if ($mds_imp==''){ $mds_imp=$v_illimit;}
			else { $mds_imp=sec_imp($mds_imp);}
		if ($mms_imp==''){ $mms_imp=$v_illimit;}
			else { $mms_imp=sec_imp($mms_imp);}
		/*Formatage de la date afin d'être lisible dans toute les langues 'jj mm yyyy'*/
		if ($Expiration!=''){ $Expiration=date("d - m - Y",strtotime($Expiration));}
			else { $Expiration=$v_without;}
		//Appel du ticket d'impression , passage en paramètres des valeurs à afficher
		echo'	<form name="impression" method="post" action="ticket_user.php" target=_blank>
					<input type="hidden" name="langue_imp" value="'.$langue_imp.'">
					<input type="hidden" name="log_imp" value="'.$login.'">
					<input type="hidden" name="passwd_imp" value="'.$passwd_imp.'">
					<input type="hidden" name="sto_imp" value="'.$sto_imp.'">
					<input type="hidden" name="mas_imp" value="'.$mas_imp.'">
					<input type="hidden" name="mds_imp" value="'.$mds_imp.'">
					<input type="hidden" name="mms_imp" value="'.$mms_imp.'">
					<input type="hidden" name="exp_imp" value="'.$Expiration.'">
				</form>';
		echo'	<script type="text/javascript"> document.forms["impression"].submit();</script>';
		//fin ticket impression
		require("../lib/defaults.php");
		if (is_file("../lib/$config[general_lib_type]/user_info.php"))
			include("../lib/$config[general_lib_type]/user_info.php");
	}
}
?>
   <form name="newuser" method=post>
      <input type=hidden name=create value="0">
      <input type=hidden name=show value="0">
	  <input type=hidden name=langue_imp value='fr'>
	<table border=1 bordercolordark=#ffffe0 bordercolorlight=#000000 width=100% cellpadding=2 cellspacing=0 bgcolor="#ffffe0" valign=top>
<?php
	echo <<<EOM
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_login
		</td><td>
		<input type=text name="login" value="$login" size=20>
		</td>
	</tr>
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_password
		</td><td>
		<input type=password name="passwd" size=20>
		<br><input type="button" value="$l_passwd_gen" onclick="password(8,'newuser')">
		<input type="text" value="" name="pwdgene" size=10 readonly>
		</td>
	</tr>
EOM;
	if ($config[general_lib_type] == 'sql'){
		if (isset($member_groups))
			$selected[$member_groups[0]] = 'selected';
		echo <<<EOM
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_group
		</td><td>
EOM;
		include_once("../lib/$config[general_lib_type]/group_info.php");
		if (isset($existing_groups)){
			echo "<select name=\"Fgroup\">";
			echo "<option value=\"\" selected>";
			foreach ($member_groups as $group)
				echo "<option value=\"$group\">$group\n";
			echo " </select>";
			}
		else echo "$l_group_empty";
	echo "</td></tr>";
	}
	if ($config[general_lib_type] == 'ldap' ||
	($config[general_lib_type] == 'sql' && $config[sql_use_user_info_table] == 'true')){
		echo <<<EOM
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_name
		</td><td>
		<input type=text name="Fcn" value="$cn" size=20>
		</td>
	</tr>
	<tr>
		<td class="etiquette" colspan=$colspan>
		$l_email
		</td><td>
		<input type=text name="Fmail" value="$mail" size=20>
		</td>
	</tr>
EOM;
	}
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
Ajout du choix d'unité (pour les durées limites de session,journée et de mois) 
et d'un calendrier pour la date d'expiration
Sauf dans le cas de la visualisation
*/
		if ($advanced){echo "<td>";}
		if ($create==0 ){
			switch ($name){
				/*
				Choix de l'unité jour, heures, minutes ou secondes 
				pour les durées limites max, de session,de journée et de mois	
				*/	
				case 'Session-Timeout' :
				case 'Max-Daily-Session' :
					/*valeur d'origine de durée limite */
					echo"<input type=text name=\"$name\" onfocus=\"this.value=''\" value=\"$val\" size=10>";
					/* Choix d'unité*/
					echo" <select name=\"$name"."_opt"."\" onchange=\"temps(this,'$name','newuser')\">
							<option value=\"s\" selected>s</option>
							<option value=\"m\" >m</option>
							<option value=\"H\" >H</option>
						</select>";
					break;
				case 'Max-Monthly-Session' :
				case 'Max-All-Session' :
					/*valeur d'origine de durée limite */
					echo"<input type=text name=\"$name\" onfocus=\"this.value=''\" value=\"$val\" size=10>";
					/* Choix d'unité*/
					echo" <select name=\"$name"."_opt"."\" onchange=\"temps(this,'$name','newuser')\">
							<option value=\"s\" selected>s</option>
							<option value=\"m\" >m</option>
							<option value=\"H\" >H</option>
							<option value=\"J\" >J</option>
						</select>";
					break;
				case 'Expiration' :
					/*Ajout du calendrier pour choisir la date*/
					echo"<input id=\"popup_container\" type=text name=\"$name\" value=\"$val\" size=20>";
					break;
				default :
					if ($advanced) echo"<input type=text name=\"$name\" value=\"$val\" size=20>";
					break;
			}
		}else{
		/*Pas de gestion de remplissage lors de la visualisation*/
			if ($advanced) echo"<input type=text name=\"$name\" value=\"$val\" size=20>";
		
/*fin Ajout*/
		}
	}
if (create==0){
	print <<<EOM
	<tr>
		<td class="etiquette" colspan=$colspan>
			$l_lang_ticket
		</td>
		<td width=20>
EOM;
/*Choix de la langue du ticket d'impression*/
	echo" <select name=\"$langue_imp\" onchange=\"lang_imp(this,'newuser')\">
			<option value=\"fr\" selected>Fran&ccedil;ais</option>
			<option value=\"en\" >English</option>
			<option value=\"nl\" >Nederlandse</option>
			<option value=\"de\" >Deutsch</option>
			<option value=\"es\" >Espa&ntilde;ol</option>
			<option value=\"it\" >Italiano</option>
			<option value=\"pt\" >Portugês</option>
		</select></td></tr>";	
	}
echo "</table><BR>";
if ($create == 1){
	echo "<a href=\"ticket.pdf\">Ticket</a><br>";
	echo "<input type=submit class=button value=\"$l_show_profile\" OnClick=\"this.form.show.value=1\">";}
	else{
	echo "<input type=submit class=button value=\"$l_create\" OnClick=\"return formControl('newuser');\">";
	echo "<input type='hidden' name='nbtickets' value=''>";
	echo "<br>$l_or :<br>";
	echo "<input type=button class=button value=\"$l_create_multiple\" OnClick=\"return createTickets(this.form, '$l_createTicketsMSG');\">";
	echo $l_create_multiple_comment;
	}
?>
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
