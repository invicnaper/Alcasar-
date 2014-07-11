<?php
CONST ROOT = '/';
require_once(ROOT.'/var/www/html/acc/manager/lib/alcasar/ticketspdf.class.php');

/*
TODO :
- refonte de GenRandUsersName()
- traiter si $nbfailuser (nombre de ticket non créé pour cause de doublon)
*/

// POUR LES BESOINS DU DEVELOPPEMENT
// BUFFERISATION DES DONNEES ENVOYEES AU CLIENT (compatibilité avec les fichiers existants)
ob_start();
//Common Functions
function sec_imp($time)
/* Formatage des secondes avant l'impression */
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
	
function GenPassword($nb_car="8")
	{
 /* generation aléatoire du mot de passe */
	$password = "";
	$chaine  = "aAzZeErRtTyYuUIopP152346897mMLkK";
	$chaine .= "jJhHgGfFdDsSqQwWxXcCvVbBnN152346897";
	while($nb_car != 0)
		{
		$i = rand(0,71);
		$password = $password.$chaine[$i];
		$nb_car --;
		}
	return $password ;
	}
function GenRandUsersName()
{
	$nb_car= 12;
	$chaine = "AZE489RTYU2PML5KJ35HGF9DSQWXCV3BN267";
	//$i = rand(0,25);
	//$j = rand(0,25);
	//$k = rand(0,25);
	$userName = "";
	while($nb_car != 0)
		{
		$i = rand(0,35);
		$userName .= $chaine[$i];
		$nb_car --;
		}
	//return "T".$chaine[$i].substr(time(),4).$chaine[$j].$chaine[$k];
	return $userName;
}

if (isset($_POST['nbtickets'])&& is_numeric($_POST['nbtickets'])){
	$nbtickets = (int)$_POST['nbtickets'];
} else {
	header("Location: voucher_new.php");
	exit;
}

//	Langue du Ticket d'impression en fonction de la liste déroulante
if (isset($_POST["langue_imp"])) { $langue_imp = $_POST["langue_imp"]; } else { $langue_imp = "en"; };
if (is_file("../lib/langues_imp.php")) include("../lib/langues_imp.php") ;

require(ROOT.'etc/freeradius-web/config.php');
require('../lib/attrshow.php');
require('../lib/defaults.php');
if ($config[general_lib_type] == 'sql' && $config[sql_use_operators] == 'true'){
	$colspan=2;
	$show_ops=1;
}else{
	$show_ops = 0;
	$colspan=1;
}

$LIBpath = "../lib/";
require(ROOT.'etc/freeradius-web/config.php');
if (is_file($LIBpath."sql/drivers/$config[sql_type]/functions.php"))
	{
	include_once($LIBpath."sql/drivers/$config[sql_type]/functions.php");
	}
else
	{
	echo "<b>Could not include SQL library</b><br>\n";
	exit();
	}
include_once($LIBpath.'functions.php');
if ($config['sql_use_operators'] == 'true')
	{
	include_once($LIBpath."operators.php");
	$text = ',op';
	$passwd_op = ",':='";
	}
$link = @da_sql_pconnect($config);

$nbfailuser = 0;

// Préparation de la fiche PDF
$pdf = new ticketsPDF(2,3);
$pdf->setTicketsTitle($l_title_imp);
$pdf->setTicketsFooter($l_footer_imp);

if ($link)
{
	if (is_file($LIBpath."crypt/$config[general_encryption_method].php"))
	{
		include($LIBpath."crypt/$config[general_encryption_method].php");
		// ajout des comptes (mêmes attributs pour tous sauf login + mdp)

		for ($i = 1; $i <= $nbtickets; $i++) 
		{
			// effacement des variables
			$login = "";
			$passwd = "";
			// création des données uniques
			$login = GenRandUsersName();
			$passwd = GenPassword();
			$login = da_sql_escape_string($login);
			$passwd = da_sql_escape_string($passwd);
			// création des variables d'impression
			$login_imp = $login;
			$passwd1_imp = $passwd;
			// encryption du mot de passe (pas besoins, déjà présent dans le fichier create_user.php)
			//$passwd = da_encrypt($passwd);
			
			// test si l'usager existe
			if (is_file("../lib/$config[general_lib_type]/user_info.php"))
				include("../lib/$config[general_lib_type]/user_info.php");
				
			if ($user_exists == "no"){
				// Création de l'usager
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
				if ($sto_imp==''){ $sto_imp=$l_unlimited;}
				 	else { $sto_imp=sec_imp($sto_imp);}
				if ($mas_imp==''){ $mas_imp=$l_unlimited;}
					else { $mas_imp=sec_imp($mas_imp);}
				if ($mds_imp==''){ $mds_imp=$l_unlimited;}
				 	else { $mds_imp=sec_imp($mds_imp);}
				if ($mms_imp==''){ $mms_imp=$l_unlimited;}
				 	else { $mms_imp=sec_imp($mms_imp);}	
				/*Formatage de la date afin d'être lisible dans toute les langues 'jj mm yyyy'*/
				$exp_imp = $Expiration;
				if ($exp_imp!=''){ $exp_imp=date("d - m - Y",strtotime($exp_imp));}
				 	else { $exp_imp=$l_without;}
				// Ajout d'un ticket sur la fiche PDF
				$pdf->newTickets();
				$pdf->Ln(5);
				$pdf->addInfos($l_login_imp, $login_imp);
				$pdf->addInfos($l_password_imp, $passwd1_imp);
				$pdf->Ln(5);
				$pdf->addInfos($l_max_all_session_imp, $mas_imp);
				$pdf->addInfos($l_session_timeout_imp, $sto_imp);
				$pdf->addInfos($l_max_daily_session_imp, $mds_imp);
				$pdf->addInfos($l_expiration_imp, $exp_imp);
				$pdf->Ln(10);
				$pdf->addComment($l_explain);

				// Création du duplicata
				$pdf->newTickets();
				$pdf->Ln(5);
				$pdf->addInfos($l_login_imp, $login_imp);
				$pdf->addInfos($l_password_imp, $passwd1_imp);
				$pdf->Ln(5);
				$pdf->addInfos($l_max_all_session_imp, $mas_imp);
				$pdf->addInfos($l_session_timeout_imp, $sto_imp);
				$pdf->addInfos($l_max_daily_session_imp, $mds_imp);
				$pdf->addInfos($l_expiration_imp, $exp_imp);
				$pdf->Ln(10);
				$pdf->addComment($l_duplicate,'C');//à mettre en rouge
			} else {
				$nbfailuser++;
			}# if user
		} # end for
	} # end if (is file)
} # end if (link)

//Affichage de la fiche de tickets
ob_end_clean();
$pdf->Output();
?>
