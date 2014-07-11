<?php
require_once('/var/www/html/acc/manager/lib/alcasar/ticketspdf.class.php');
// ticket d'impression (thank's to Geoffroy MUSITELLI)
//--recupération des variables provenant du formulaire 
$langue_imp=utf8_decode($_POST["langue_imp"]);
$log_imp=utf8_decode($_POST["log_imp"]);
$passwd_imp=utf8_decode($_POST["passwd_imp"]);
$exp_imp=utf8_decode($_POST["exp_imp"]);
$sto_imp=utf8_decode($_POST["sto_imp"]);
$mas_imp=utf8_decode($_POST["mas_imp"]);
$mds_imp=utf8_decode($_POST["mds_imp"]);
$mms_imp=utf8_decode($_POST["mms_imp"]);
//	Langue du Ticket d'impression en fonction de la liste déroulante
if (isset($_POST["langue_imp"])) { $langue_imp = $_POST["langue_imp"]; } else { $langue_imp = "en"; };
if (is_file("../lib/langues_imp.php")) include("../lib/langues_imp.php") ;
// Si les valeurs de durée ne sont pas définies, on les remplace par la valeur 'Illimitée'
	if (($sto_imp=='') or ($sto_imp=='-')){ $sto_imp=$l_unlimited;}
	if (($mas_imp=='') or ($mas_imp=='-')){ $mas_imp=$l_unlimited;}
	if (($mds_imp=='') or ($mds_imp=='-')){ $mds_imp=$l_unlimited;}
	if (($mms_imp=='') or ($mms_imp=='-')){ $mms_imp=$l_unlimited;}
//création de la classe PDF pour faire l'entête et pieds de page
$pdf = new ticketsPDF(2,3);
$pdf->setTicketsTitle($l_title_imp);
$pdf->setTicketsFooter($l_footer_imp);
$pdf->newTickets();
$pdf->Ln(5);
$pdf->addInfos($l_login_imp, $log_imp);
$pdf->addInfos($l_password_imp, $passwd_imp);
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
$pdf->addInfos($l_login_imp, $log_imp);
$pdf->addInfos($l_password_imp, $passwd_imp);
$pdf->Ln(5);
$pdf->addInfos($l_max_all_session_imp, $mas_imp);
$pdf->addInfos($l_session_timeout_imp, $sto_imp);
$pdf->addInfos($l_max_daily_session_imp, $mds_imp);
$pdf->addInfos($l_expiration_imp, $exp_imp);
$pdf->Ln(10);
$pdf->addComment($l_duplicate,'C');//à mettre en rouge

// envoie du document au navigateur 
$ticket_name="ticket_".$log_imp.".pdf";
$pdf->Output("ticket.pdf");
header ('Location: ticket.pdf');
?>
