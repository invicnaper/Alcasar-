<?php
# change user password on Alcasar captive Portal
# Copyright (C) 2003, 2004 Mondru AB.
# Copyright (C) 2008-2009 ANGEL95 & REXY



require('/etc/freeradius-web/config.php');

$current_page = $_SERVER['PHP_SELF'];

# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'es'){
$R_title = "User password change";
$R_form_l1 = "User";
$R_form_l2 = "Old password";
$R_form_l3 = "New password";
$R_form_l4 = "New password (confirmation)";
$R_eval_pass = "Score :";
$R_passwordmeter = "Powered by 'Shibbo Password Analyser'</a>";
$R_form_button_valid = "Modify";
$R_form_button_retour = "Cancel";
$R_form_result1 = "Your password has been successfuly changed";
$R_form_result2 = "Error when trying to change password";
$R_retour = "ALCASAR home page";}
else if ($Language == 'pt'){
$R_title = "Alteração de senha do usuário";
$R_form_l1 = "Usuário";
$R_form_l2 = "Senha antiga";
$R_form_l3 = "Nova senha";
$R_form_l4 = "Nova senha (confirmação)";
$R_eval_pass = "Resultado:";
$R_passwordmeter = "Powered by 'Shibbo Password Analyser'</a>";
$R_form_button_valid = "Modificar";
$R_form_button_retour = "Cancelar";
$R_form_result1 = "Sua senha foi alterada com sucesso";
$R_form_result2 = "Erro ao tentar alterar a senha";
$R_retour = "Home page Alcasar";}
else if($Language == 'de'){
$R_title = "User password change";
$R_form_l1 = "User";
$R_form_l2 = "Old password";
$R_form_l3 = "New password";
$R_form_l4 = "New password (confirmation)";
$R_eval_pass = "Score :";
$R_passwordmeter = "Powered by 'Shibbo Password Analyser'</a>";
$R_form_button_valid = "Modify";
$R_form_button_retour = "Cancel";
$R_form_result1 = "Your password has been successfuly changed";
$R_form_result2 = "Error when trying to change password";
$R_retour = "ALCASAR home page";}
else if($Language == 'nl'){
$R_title = "User password change";
$R_form_l1 = "User";
$R_form_l2 = "Old password";
$R_form_l3 = "New password";
$R_form_l4 = "New password (confirmation)";
$R_eval_pass = "Score :";
$R_passwordmeter = "Powered by 'Shibbo Password Analyser'</a>";
$R_form_button_valid = "Modify";
$R_form_button_retour = "Cancel";
$R_form_result1 = "Your password has been successfuly changed";
$R_form_result2 = "Error when trying to change password";
$R_retour = "ALCASAR home page";}
else if($Language == 'fr'){
$R_title = "Changement de mot de passe utilisateur";
$R_form_l1 = "Utilisateur :";
$R_form_l2 = "Ancien mot de passe :";
$R_form_l3 = "Nouveau mot de passe :";
$R_form_l4 = "Nouveau mot de passe (confirmation) :";
$R_eval_pass = "";
$R_passwordmeter = "Propulsé par 'Shibbo Password Analyser'</a>";
$R_form_button_valid = "Modifier";
$R_form_button_retour = "Annuler";
$R_form_result1 = "Votre mot de passe a &eacute;t&eacute; modifi&eacute; avec succ&egrave;s";
$R_form_result2 = "Erreur de changement de mot de passe";
$R_retour = "Retour &agrave; la page d'accueil ALCASAR";}
else {
$R_title = "User password change";
$R_form_l1 = "User";
$R_form_l2 = "Old password";
$R_form_l3 = "New password";
$R_form_l4 = "New password (confirmation)";
$R_eval_pass = "Score :";
$R_passwordmeter = "Powered by 'Shibbo Password Analyser'</a>";
$R_form_button_valid = "Modify";
$R_form_button_retour = "Cancel";
$R_form_result1 = "Your password has been successfuly changed";
$R_form_result2 = "Error when trying to change password";
$R_retour = "ALCASAR home page";
}
echo "
<html>
	<head>
		<title>$R_title</title>
		<meta http-equiv=\"Cache-control\" content=\"no-cache\">
		<meta http-equiv=\"Pragma\" content=\"no-cache\">
		<link rel=\"stylesheet\" href=\"../css/pass.css\" type=\"text/css\">
		<link type=\"text/css\" href=\"../css/pwdmeter.css\" media=\"screen\" rel=\"stylesheet\" />
		<!--[if lt IE 7]>
			<link type=\"text/css\" href=\"../css/ie.css\" media=\"screen\" rel=\"stylesheet\" />
		<![endif]-->
		<script type=\"text/javascript\" src=\"js/pwdmeter.js\" language=\"javascript\"></script>	
	</head>
	<body>
		<div id=\"page\">
			<div id=\"block_pass\">
				<div id=\"pass_chg\">	
					<img src=\"../images/organisme.png\" />
					<h1 id=\"titre_pass\">$R_title</h1>
				</div>
				<div id=\"pass_chg_content\">
					<form name=\"master\" action=\"$current_page\" method=\"post\">
					<input type=hidden name=action value=checkpass>
						<table id=\"champs_pass\">
							<tr>
								<td class=\"first_item\">$R_form_l1</td>
								<td><input type=\"text\" name=\"login\" value=\"\" label=\"test\"></td>
							</tr>	
							<tr>
								<td class=\"first_item\">$R_form_l2</td>
								<td><input type=\"password\" name=\"passwd\" value=\"\"></td>
							</tr>
							<tr>
								<td class=\"first_item\">$R_form_l3</td>
								<td>
									<input type=\"password\" name=\"newpasswd\" id=\"passwordPwd\" value=\"\" autocomplete=\"off\" onkeyup=\"chkPass(this.value);\" />
									<input type=\"text\" id=\"passwordTxt\" name=\"passwordTxt\" autocomplete=\"off\" onkeyup=\"chkPass(this.value);\" class=\"hide\" />
								</td>
							</tr>
							<tr>
								<td class=\"first_item\">$R_eval_pass</td>
								<td>
									<div id=\"scorebarBorder\">
										<div id=\"score\">0%</div>
										<div id=\"scorebar\">&nbsp;</div>
									</div>
									<div id=\"complexity\"></div>
								</td>
							</tr>
							<tr>
								<td colspan=\"2\" id=\"lien_pass\">$R_passwordmeter</td>
							</tr>
							<tr>
								<td class=\"first_item\">$R_form_l4</td>
								<td><input type=\"password\" name=\"newpasswd2\" value=\"\"></td>
							</tr>
						</table>
					<input type=\"submit\" class=\"btn_form\" id=\"btn_pass\" value=\"$R_form_button_valid\">
					<input type=\"button\" class=\"btn_form\" id=\"btn_retour\" value=\"$R_form_button_retour\" onclick=\"location.replace('http://alcasar');\">
				</div>
			</div>
			<div id=\"info_pass\">
						<table id=\"tablePwdStatus\" cellpadding=\"5\" cellspacing=\"1\" border=\"0\">
					<tr>
						<th colspan=\"2\">Additions</th>
						<th class=\"txtCenter\">Type</th>
						<th class=\"txtCenter\">Rate</th>
						<th class=\"txtCenter\">Count</th>
						<th class=\"txtCenter\">Bonus</th>
					</tr>
					<tr>
						<td width=\"1%\"><div id=\"div_nLength\" class=\"fail\">&nbsp;</div></td>
						<td width=\"94%\">Number of Characters</td>
						<td width=\"1%\" class=\"txtCenter\">Flat</td>
						<td width=\"1%\" class=\"txtCenter italic\">+(n*4)</td>
						<td width=\"1%\"><div id=\"nLength\" class=\"box\">&nbsp;</div></td>
						<td width=\"1%\"><div id=\"nLengthBonus\" class=\"boxPlus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nAlphaUC\" class=\"fail\">&nbsp;</div></td>
						<td>Uppercase Letters</td>
						<td class=\"txtCenter\">Cond/Incr</td>
						<td nowrap=\"nowrap\" class=\"txtCenter italic\">+((len-n)*2)</td>
					   <td><div id=\"nAlphaUC\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nAlphaUCBonus\" class=\"boxPlus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nAlphaLC\" class=\"fail\">&nbsp;</div></td>
						<td>Lowercase Letters</td>
						<td class=\"txtCenter\">Cond/Incr</td>
						<td class=\"txtCenter italic\">+((len-n)*2)</td>
						<td><div id=\"nAlphaLC\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nAlphaLCBonus\" class=\"boxPlus\">&nbsp;</div></td>
					</tr>
					<tr>
						<td><div id=\"div_nNumber\" class=\"fail\">&nbsp;</div></td>
						<td>Numbers</td>
						<td class=\"txtCenter\">Cond</td>
						<td class=\"txtCenter italic\">+(n*4)</td>
						<td><div id=\"nNumber\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nNumberBonus\" class=\"boxPlus\">&nbsp;</div></td>
				   </tr>
					<tr>
						<td><div id=\"div_nSymbol\" class=\"fail\">&nbsp;</div></td>
						<td>Symbols</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">+(n*6)</td>
						<td><div id=\"nSymbol\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nSymbolBonus\" class=\"boxPlus\">&nbsp;</div></td>
				   </tr>
					<tr>
						<td><div id=\"div_nMidChar\" class=\"fail\">&nbsp;</div></td>
						<td>Middle Numbers or Symbols</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">+(n*2)</td>
						<td><div id=\"nMidChar\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nMidCharBonus\" class=\"boxPlus\">&nbsp;</div></td>
				   </tr>
					<tr>
						<td><div id=\"div_nRequirements\" class=\"fail\">&nbsp;</div></td>
						<td>Requirements</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">+(n*2)</td>
						<td><div id=\"nRequirements\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nRequirementsBonus\" class=\"boxPlus\">&nbsp;</div></td>
				   </tr>
					<tr>
						<th colspan=\"6\">Deductions</th>
					</tr>
					<tr>
						<td width=\"1%\"><div id=\"div_nAlphasOnly\" class=\"pass\">&nbsp;</div></td>
						<td width=\"94%\">Letters Only</td>
						<td width=\"1%\" class=\"txtCenter\">Flat</td>
						<td width=\"1%\" class=\"txtCenter italic\">-n</td>
						<td width=\"1%\"><div id=\"nAlphasOnly\" class=\"box\">&nbsp;</div></td>
						<td width=\"1%\"><div id=\"nAlphasOnlyBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nNumbersOnly\" class=\"pass\">&nbsp;</div></td>
						<td>Numbers Only</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-n</td>
						<td><div id=\"nNumbersOnly\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nNumbersOnlyBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nRepChar\" class=\"pass\">&nbsp;</div></td>
						<td>Repeat Characters (Case Insensitive)</td>
						<td class=\"txtCenter\">Comp</td>
						<td nowrap=\"nowrap\" class=\"txtCenter italic\"> - </td>
						<td><div id=\"nRepChar\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nRepCharBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nConsecAlphaUC\" class=\"pass\">&nbsp;</div></td>
						<td>Consecutive Uppercase Letters</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-(n*2)</td>
						<td><div id=\"nConsecAlphaUC\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nConsecAlphaUCBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nConsecAlphaLC\" class=\"pass\">&nbsp;</div></td>
						<td>Consecutive Lowercase Letters</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-(n*2)</td>
						<td><div id=\"nConsecAlphaLC\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nConsecAlphaLCBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nConsecNumber\" class=\"pass\">&nbsp;</div></td>
						<td>Consecutive Numbers</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-(n*2)</td>
						<td><div id=\"nConsecNumber\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nConsecNumberBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nSeqAlpha\" class=\"pass\">&nbsp;</div></td>
						<td>Sequential Letters (3+)</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-(n*3)</td>
						<td><div id=\"nSeqAlpha\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nSeqAlphaBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nSeqNumber\" class=\"pass\">&nbsp;</div></td>
						<td>Sequential Numbers (3+)</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-(n*3)</td>
						<td><div id=\"nSeqNumber\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nSeqNumberBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<td><div id=\"div_nSeqSymbol\" class=\"pass\">&nbsp;</div></td>
						<td>Sequential Symbols (3+)</td>
						<td class=\"txtCenter\">Flat</td>
						<td class=\"txtCenter italic\">-(n*3)</td>
						<td><div id=\"nSeqSymbol\" class=\"box\">&nbsp;</div></td>
						<td><div id=\"nSeqSymbolBonus\" class=\"boxMinus\">&nbsp;</div></td>
					</tr>	
					<tr>
						<th colspan=\"6\">Legend</th>
					</tr>
					<tr>
						<td colspan=\"6\">
							<ul id=\"listLegend\">
								<li><div class=\"exceed imgLegend\">&nbsp;</div> <span class=\"bold\">Exceptional:</span> Exceeds minimum standards. Additional bonuses are applied.</li>
								<li><div class=\"pass imgLegend\">&nbsp;</div> <span class=\"bold\">Sufficient:</span> Meets minimum standards. Additional bonuses are applied.</li>
								<li><div class=\"warn imgLegend\">&nbsp;</div> <span class=\"bold\">Warning:</span> Advisory against employing bad practices. Overall score is reduced.</li>
								<li><div class=\"fail imgLegend\">&nbsp;</div> <span class=\"bold\">Failure:</span> Does not meet the minimum standards. Overall score is reduced.</li>
							</ul>
						</td>
					</tr>
				</table>
			   <table id=\"tablePwdNotes\" cellpadding=\"5\" cellspacing=\"1\" border=\"0\">
					<tr>
						<th>Quick Footnotes</th>
					</tr>
					<tr>
						<td>
							&bull; <strong>Flat:</strong> Rates that add/remove in non-changing increments.<br />
							&bull; <strong>Incr:</strong> Rates that add/remove in adjusting increments.<br />
							&bull; <strong>Cond:</strong> Rates that add/remove depending on additional factors.<br />
							&bull; <strong>Comp:</strong> Rates that are too complex to summarize. See source code for details.<br />
							&bull; <strong>n:</strong> Refers to the total number of occurrences.<br />
							&bull; <strong>len:</strong> Refers to the total password length.<br />
							&bull; Additional bonus scores are given for increased character variety.<br />
							&bull; Final score is a cumulative result of all bonuses minus deductions.<br />
							&bull; Final score is capped with a minimum of 0 and a maximum of 100.<br />
							&bull; Score and Complexity ratings are not conditional on meeting minimum requirements.<br />
						</td>
					</tr>
					<tr>
						<th>DISCLAIMER</th>
					</tr>
					<tr>
						<td>
							<p>This application is designed to assess the strength of password strings.  The instantaneous visual feedback provides the user a means to improve the strength of their passwords, with a hard focus on breaking the typical bad habits of faulty password formulation.  Since no official weighting system exists, we created our own formulas to assess the overall strength of a given password.  Please note, that this application does not utilize the typical \"days-to-crack\" approach for strength determination.  We have found that particular system to be severely lacking and unreliable for real-world scenarios.  This application is neither perfect nor foolproof, and should only be utilized as a loose guide in determining methods for improving the password creation process. </p>
						</td>
					</tr>
				</table>
			</div>
		</div>
";

if (is_file("sql/drivers/$config[sql_type]/functions.php"))
	include_once("sql/drivers/$config[sql_type]/functions.php");
else{
	echo "<b>Could not include SQL library</b><br>\n";
	exit();
}
if (isset($action)){
	if ($action == 'checkpass'){
	$link = @da_sql_pconnect($config);
		if ($link){
			$res = @da_sql_query($link,$config,
				"SELECT attribute,value FROM $config[sql_check_table] WHERE username = '$login'
				AND attribute = '$config[sql_password_attribute]';");
			if ($res){
				$row = @da_sql_fetch_array($res,$config);
				if (is_file("crypt/$config[general_encryption_method].php")){
					include("crypt/$config[general_encryption_method].php");
					$enc_passwd = $row['value'];
					$passwd = da_encrypt($passwd,$enc_passwd);
					$newpasswd = da_encrypt($newpasswd,$enc_passwd);
					$newpasswd2 = da_encrypt($newpasswd2,$enc_passwd);
					if (($passwd == $enc_passwd) and ($newpasswd == $newpasswd2)){
						$msg = '<font color=blue><b>'.$R_form_result1.'</b></font>';
						$res2 = @da_sql_query($link,$config,
							"UPDATE $config[sql_check_table] set value='$newpasswd' WHERE username = '$login'
							AND attribute = '$config[sql_password_attribute]';");}
					else
						$msg = '<font color=red><b>'.$R_form_result2.'</b></font>';
				}
				else
					echo "<b>Could not open encryption library file</b><br>\n";
			}
		}
		echo "<span align=center>$msg</span>\n";
	}
}
?>
</body>
</html>
