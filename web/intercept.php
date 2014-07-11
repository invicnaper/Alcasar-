<?php
# $Id: intercept.php 1269 2013-12-16 23:13:20Z richard $
#
# intercept.php for ALCASAR captive portal
# Copyright (C) 2003, 2004 Mondru AB.
# Modify by REXY & steweb57
# UI & css style by stephane ERARD
# Help for language translation by B. AUBARD (thanks)

# The contents of this file may be used under the terms of the GNU
# General Public License Version 2, provided that the above copyright
# notice and this permission notice is included in all copies or
# substantial portions of the software.

# Redirects from CoovaChilli (chilli daemon) :
# Response to login:
  # success :	if login successful
  # failed :	if login failed
  # logoff :	if logout successful
  # already :	if tried to login while already logged in
  # notyet :	if not logged in yet
  # Default :	it was not a form request -> client go to login form

/****************************************************************
*			GLOBAL FILE PATHS			*
*****************************************************************/
define ("CONF_FILE", "/usr/local/etc/alcasar.conf");
define ("DOMAIN_ALLOWED_LIST", "/usr/local/etc/alcasar-uamdomain");

/****************************************************************
*			FILE reading test			*
*****************************************************************/
$conf_files=array(CONF_FILE,DOMAIN_ALLOWED_LIST);
foreach ($conf_files as $file){
	if (!file_exists($file)){
		exit("Fichier ".$file." non présent");
	}
	if (!is_readable($file)){
		exit("Vous n'avez pas les droits de lecture sur le fichier ".$file);
	}
}
/****************************************************************
*			Read CONF_FILE				*
*****************************************************************/
$ouvre=fopen(CONF_FILE,"r");
if ($ouvre){
	while (!feof ($ouvre))
	{
		$tampon = fgets($ouvre, 4096);
		if (strpos($tampon,"=")!==false){
			$tmp = explode("=",$tampon);
			$conf[$tmp[0]] = $tmp[1];
		}
	}
}else{
	exit("Erreur d'ouverture du fichier ".CONF_FILE);
}
fclose($ouvre);
$organisme = trim($conf["ORGANISM"]);

# Shared secret used to encrypt challenge with radius.
$uamsecret = "";

# URL loaded after success authenticates (let blank for browser defaults)
$adminurl = "";

# Our own path
$loginpath	= $_SERVER['PHP_SELF'];
$alcasarpath = "http://alcasar.".trim($conf["DOMAIN"]);
$statuspath = $alcasarpath."/status.php";
$debug		= false;

# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'es'){
  $l_ChilliError	= "La autenticación debe ser un éxito a través del servicio de portal cautivo.";
  $l_login		= "El éxito de la autenticación.<HR>Cierre esta ventana interrumpte la sesion.";
  $l_logout		= "Conexión de cierre";
  $l_loginfailed	= "Error de autenticación";
  $l_loggingin		= "Identificación en el portal cautivo";
  $l_loggedcont		= "Control de Acceso";
  $l_loggedout		= "Su sesión se cierra";
  $l_user		= "Usuario";
  $l_password		= "Contraseña";
  $l_wait		= "Por favor, espere un momento ...";
  $l_onlinetime		= "Tiempo de conexión:";
  $l_remainingtime	= "Desconexión en:";
  $l_encrypted		= "La apertura debe usar conexión cifrada";
  $l_boutonO		= "Autenticación";
  $l_boutonF		= "Cerrar";
  $l_loggedin_stringl1	= "Information System Security";
  $l_loggedin_stringl2	= "El portal fue creado reglamentos para garantizar la trazabilidad, la rendición de cuentas y el no repudio de las conexiones.";
  $l_loggedin_stringl3	= "Su actividad en la red es registrada, de conformidad con la privacidad.";
  $l_loggedin_stringl4	= "Los datos registrados pueden ser capaces de ser operado por una autoridad judicial en el curso de una investigación.";
  $l_loggedin_stringl5	= "Estos datos se eliminan automáticamente después de un año.";
  $l_loggedin_stringl6	= "Click <a href='$alcasarpath'>here</a> to change your password or to integrate the security certificate in your browser";
  $l_loggedout_string	= "Cerrar sesión hizo portal cautivo!";
  $l_reply_1		= "Your daily connexion time has been reached";
  $l_reply_2		= "Your monthly connexion time has been reached";
  $l_reply_3		= "You try to connect outside of your allowed timespan";
  $l_reply_4		= "your account expired";
  $l_reply_5		= "You have reached the maximum number of simultaneous logins";
  $l_reply_6		= "Your authorized connexion time has been reached";
  $l_online_time	= "Tiempo en linea";
  $l_remaining_time	= "Tiempo restante";
  $l_uam_domain		= "Sitios web autorizados : ";}
else if ($Language == 'pt'){
  $l_ChilliError	= "A autenticação precisa ser bem sucedida através do portal.";
  $l_login		= "Sucesso na autenticação.<HR>Matenha esse pop-up apenas minimizado para não interromper a conexão";
  $l_logout		= "Encerrar conexão";
  $l_loginfailed	= "Falha na autenticação";
  $l_loggingin		= "Identificação do portal cativo";
  $l_loggedcont		= "Controle de acesso";
  $l_loggedout		= "Sua sessão foi fechada";
  $l_user		= "Usuário";
  $l_password		= "Senha";
  $l_wait		= "Por favor, aguarde um momento ...";
  $l_onlinetime		= "Tempo de conexão:";
  $l_remainingtime	= "Desconectado em:";
  $l_encrypted		= "A conexão com o portal deve ser criptografada";
  $l_boutonO		= "Autenticação";
  $l_boutonF		= "Fechar";
  $l_loggedin_stringl1	= "Sistema de Informação e segurança";
  $l_loggedin_stringl2	= "Este controle foi criado para garantir acesso seguro.";
  $l_loggedin_stringl3	= "A autenticação será criptografada em 256 bits, impedindo captura por escâner de rede.";
  $l_loggedin_stringl4	= "Sua atividade na Internet será resguardada de acordo com os regulamentos da lei.";
  $l_loggedin_stringl5	= "Mantenha o popup da conexão minimizado para não interromper a cessão.";
  $l_loggedin_stringl6	= "Clique <a href='$alcasarpath'>aqui</a> para alterar sua senha, instalar certificado ou sair do portal.";
  $l_loggedout_string	= "desconexão do portal cativo";
  $l_reply_1		= "Seu tempo de conexão diária foi finalizado";
  $l_reply_2		= "Seu tempo de conexão mensal foi finalizado";
  $l_reply_3		= "Você tenta conectar-se fora do seu período de tempo permitido";
  $l_reply_4		= "Sua conta expirou";
  $l_reply_5		= "Você atingiu o número máximo de logins simultâneos";
  $l_reply_6		= "Seu tempo de conexão autorizada finalizou";
  $l_online_time	= "Tempo Online";
  $l_remaining_time	= "Tempo restante";
  $l_uam_domain		= "Sites autorizados : ";}
else if($Language == 'de'){
  $l_ChilliError	= "Die Authentifizierung ist erfolgreich durch die Nutzung des Portals erfolgt.";
  $l_login		= "Erfolgreiche Authentifizierung.<HR>Schlißen dieses fensters unterbricht die sitzung";
  $l_logout		= "Beenden der Verbindung";
  $l_loginfailed	= "Authentifizierungsfehler Eigenverbrauch";
  $l_loggingin		= "Kennzeichnung auf dem Eigenverbrauch";
  $l_loggedcont		= "Zutrittskontrolle";
  $l_loggedout		= "Ihre Sitzung ist geschlossen";
  $l_user		= "Benutzer";
  $l_password		= "Passwort";
  $l_wait		= "Bitte warten Sie einen Moment ...";
  $l_onlinetime		= "Online-Zeit:";
  $l_remainingtime	= "Abmelden:";
  $l_encrypted		= "Die Öffnung muß der Anschluß Zahlen";
  $l_boutonO		= "Authentifizierung";
  $l_boutonF		= "Schließen";
  $l_loggedin_stringl1	= "Information System Security";
  $l_loggedin_stringl2	= "Dieses Portal wurde eingerichtet, um ordnungsgemäß die Rückverfolgbarkeit, der Zurechenbarkeit und der Nicht-Anerkennung der Verbindungen.";
  $l_loggedin_stringl3	= "Ihre Tätigkeit im Netzwerk registriert ist nach Schutz der Privatsphäre.";
  $l_loggedin_stringl4	= "Die gespeicherten Daten nicht pouront genutzt werden, dass von einer Justizbehörde im Rahmen einer Untersuchung.";
  $l_loggedin_stringl5	= "Diese Daten werden automatisch gelöscht nach einem Jahr.";
  $l_loggedin_stringl6	= "Click <a href='$alcasarpath'>here</a> to change your password or to integrate the security certificate in your browser";
  $l_loggedout_string	= "Trennung des Portals erfolgt Gefangener!";
  $l_reply_1		= "Your daily connexion time has been reached";
  $l_reply_2		= "Your monthly connexion time has been reached";
  $l_reply_3		= "You try to connect outside of your allowed timespan";
  $l_reply_4		= "your account expired";
  $l_reply_5		= "You have reached the maximum number of simultaneous logins";
  $l_reply_6		= "Your authorized connexion time has been reached";
  $l_online_time	= "Online-zeit";
  $l_remaining_time	= "Restzeit";
  $l_uam_domain		= "Autorisierten websites : ";}
else if($Language == 'nl'){
  $l_ChilliError	= "De authenticatie moet een succes worden via de captive portal dienst.";
  $l_login		= "Succesvolle authenticatie.<HR>Dit venster te sluiten onderbreekt uw sessie.";
  $l_logout		= "Slotkoers verbinding";
  $l_loginfailed	= "Authenticatie mislukt";
  $l_loggingin		= "Identificatie van de captive-portaal";
  $l_loggedcont		= "toegangscontrole";
  $l_loggedout		= "Uw sessie is gesloten";
  $l_user		= "Gebruiker";
  $l_password		= "Wachtwoord";
  $l_wait		= "Wacht een moment ...";
  $l_onlinetime		= "Sluit tijd:";
  $l_remainingtime	= "Verbreking in:";
  $l_encrypted		= "De opening moet gebruiken gecodeerde verbinding";
  $l_boutonO		= "Authenticatie";
  $l_boutonF		= "Sluiten";
  $l_loggedin_stringl1	= "Information System Security";
  $l_loggedin_stringl2	= "Het portaal werd opgericht verordeningen om de traceerbaarheid, verantwoordelijkheid en onloochenbaarheid van de verbindingen.";
  $l_loggedin_stringl3	= "Uw activiteit op het netwerk is geregistreerd in overeenstemming met de persoonlijke levenssfeer.";
  $l_loggedin_stringl4	= "De geregistreerde gegevens kunnen worden kunnen worden bediend door een rechterlijke instantie in de loop van een onderzoek.";
  $l_loggedin_stringl5	= "Deze gegevens worden automatisch verwijderd na een jaar.";
  $l_loggedin_stringl6	= "Click <a href='$alcasarpath'>here</a> to change your password or to integrate the security certificate in your browser";
  $l_loggedout_string	= "Logout gemaakt intern portaal!";
  $l_reply_1 		= "Your daily connexion time has been reached";
  $l_reply_2		= "Your monthly connexion time has been reached";
  $l_reply_3		= "You try to connect outside of your allowed timespan";
  $l_reply_4		= "your account expired";
  $l_reply_5		= "You have reached the maximum number of simultaneous logins";
  $l_reply_6		= "Your authorized connexion time has been reached";
  $l_online_time	= "Online tijd";
  $l_remaining_time	= "Reterende tijd";
  $l_uam_domain		= "Geautoriseerde website : ";}
else if($Language == 'fr'){
  $l_ChilliError	= "L'authentification doit être réussie sur le portail captif.";
  $l_login		= "Authentification réussie.<HR>La fermeture de cette fenêtre interrompt votre session.";
  $l_logout		= "Fermeture de la session";
  $l_loginfailed	= "Echec d'authentification";
  $l_loggingin		= "Identification sur le portail captif";
  $l_loggedcont		= "Contrôle d'accès";
  $l_loggedout		= "Votre session est fermée";
  $l_user		= "Identifiant";
  $l_password		= "Mot de passe";
  $l_wait		= "Patientez un instant ...";
  $l_onlinetime		= "Temps de connexion:";
  $l_remainingtime	= "Deconnexion dans :";
  $l_encrypted		= "La connexion avec le portail doit être chiffrée";
  $l_boutonO		= "Authentification";
  $l_boutonF		= "Fermer";
  $l_loggedin_stringl1	= "Sécurité des Systèmes d'Information";
  $l_loggedin_stringl2	= "Ce contrôle a été mis en place pour assurer réglementairement la traçabilité, l'imputabilité et la non-répudiation des connexions.";
  $l_loggedin_stringl3	= "Votre activité sur le réseau est enregistrée conformément au respect de la vie privée.";
  $l_loggedin_stringl4	= "Les données enregistrées ne pourront être exploitées que par une autorité judiciaire dans le cadre d'une enquête.";
  $l_loggedin_stringl5	= "Ces données seront automatiquement supprimées au bout d'un an.";
  $l_loggedin_stringl6	= "Cliquez <a href='$alcasarpath'>ici</a> pour changer votre mot de passe ou pour intégrer le certificat de sécurité à votre navigateur";
  $l_loggedout_string	= "Déconnexion du portail captif effectuée !";
  $l_reply_1		= "Votre durée de connexion journaliè a été atteinte";
  $l_reply_2		= "Votre durée de connexion mensuelle a été atteinte";
  $l_reply_3		= "Vous tentez de vous connecter en dehors de votre période autorisée";
  $l_reply_4		= "Votre compte a expiré";
  $l_reply_5		= "Vous avez atteint le nombre maximum de connexions simultanées";
  $l_reply_6		= "Votre durée de connexion autorisée a été atteinte";
  $l_online_time	= "Temps de connexion";
  $l_remaining_time	= "Temps restant";
  $l_uam_domain		= "Sites autorisés : ";}
else{
  $l_ChilliError	= "The authentication must be successful through the captive portal service.";
  $l_login		= "Successful authentication.<HR>Closing this window interrupts your session";
  $l_logout		= "Closing connection";
  $l_loginfailed	= "Authentication Failed";
  $l_loggingin		= "Identification on the captive portal";
  $l_loggedcont		= "Access Control";
  $l_loggedout		= "Your session is closed";
  $l_user		= "User";
  $l_password		= "Password";
  $l_wait		= "Please wait a moment ...";
  $l_onlinetime		= "Connect time:";
  $l_remainingtime	= "Disconnection in:";
  $l_encrypted		= "The connection with the portal must be encrypted";
  $l_boutonO		= "Authentication";
  $l_boutonF		= "Close";
  $l_loggedin_stringl1	= "Information System Security";
  $l_loggedin_stringl2	= "That control was set up regulations to ensure traceability, accountability and non-repudiation of connections.";
  $l_loggedin_stringl3	= "Your activity on the network is registered in accordance with privacy.";
  $l_loggedin_stringl4	= "The recorded data can be able to be operated by a judicial authority in the course of an investigation.";
  $l_loggedin_stringl5	= "These data will be automatically deleted after one year.";
  $l_loggedin_stringl6	= "Click <a href='$alcasarpath'>here</a> to change your password or to integrate the security certificate in your browser";
  $l_loggedout_string	= "Disconnection of the captive portal made";
  $l_reply_1		= "Your daily connexion time has been reached";
  $l_reply_2		= "Your monthly connexion time has been reached";
  $l_reply_3		= "You try to connect outside of your allowed timespan";
  $l_reply_4		= "your account expired";
  $l_reply_5		= "You have reached the maximum number of simultaneous logins";
  $l_reply_6		= "Your authorized connexion time has been reached";
  $l_online_time	= "Online time";
  $l_remaining_time	= "Remaining time";
  $l_uam_domain		= "Authorized websites : ";}

# If https not use, tell it's wrong
if (!(isset($_SERVER['HTTPS'])&&($_SERVER['HTTPS'] == 'on'))) {
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
  <title>$l_loggedcont</title>
  <meta http-equiv=\"Cache-control\" content=\"no-cache\">
  <meta http-equiv=\"Pragma\" content=\"no-cache\">
</head>
<body bgColor = 'white'>
  <h1 style=\"text-align: center;\">$l_loginfailed</h1>
  <center>$l_encrypted</center>
</body>
</html>";
    exit(0);
}

# Read form parameters which we care about
if (isset($_POST['UserName'])){	$username	= $_POST['UserName'];} else {$username="";}
if (isset($_POST['Password'])){	$password	= $_POST['Password'];} else {$password="";}
if (isset($_POST['challenge'])){$challenge	= $_POST['challenge'];} else {$challenge="";}
if (isset($_POST['button'])){	$button		= $_POST['button'];} else { $button="";}
//if (isset($_POST['logout'])){	$logout		= $_POST['logout'];} else {$logout="";}
//if (isset($_POST['prelogin'])){	$prelogin	= $_POST['prelogin'];} else {$prelogin="";}
if (isset($_POST['res'])){		$res		= $_POST['res'];} else {$res="";}
if (isset($_POST['uamip'])){	$uamip		= $_POST['uamip'];} else {$uamip="";}
if (isset($_POST['uamport'])){	$uamport	= $_POST['uamport'];} else {$uamport="";}
if (isset($_POST['userurl'])){	$userurl	= $_POST['userurl'];} else {$userurl="";}
if (isset($_POST['timeleft'])){	$timeleft	= $_POST['timeleft'];} else {$timeleft="";}
if (isset($_POST['redirurl'])){	$redirurl	= $_POST['redirurl'];} else {$redirurl="";}

# Read query parameters which we care about
if (isset($_GET['res']))		$res		= $_GET['res'];
if (isset($_GET['challenge']))	$challenge	= $_GET['challenge'];
if (isset($_GET['uamip']))		$uamip		= $_GET['uamip'];
if (isset($_GET['uamport']))	$uamport	= $_GET['uamport'];
if (isset($_GET['reply'])){		$reply		= $_GET['reply'];} else {$reply="";}
if (isset($_GET['userurl']))	$userurl	= $_GET['userurl'];
if (isset($_GET['timeleft']))	$timeleft	= $_GET['timeleft'];
if (isset($_GET['redirurl']))	$redirurl	= $_GET['redirurl'];

# translation of radius replies
if (isset($reply)){
	switch(trim ($reply)) {
  case 'Your maximum daily usage time has been reached' : $reply = $l_reply_1 ; break;
  case 'Your maximum monthly usage time has been reached' : $reply = $l_reply_2 ; break;
  case 'You are calling outside your allowed timespan' : $reply = $l_reply_3 ; break;
  case 'Password Has Expired' : $reply =  $l_reply_4 ; break;
  case 'You are already logged in - access denied' : $reply = $l_reply_5 ; break;
  case 'Your maximum never usage time has been reached' : $reply = $l_reply_6 ; break;
  }}

# If attempt to login
if ("$button" == "$l_boutonO") {
  $hexchal = pack ("H32", $challenge);
  $newchal = pack ("H*", md5($hexchal . $uamsecret));
  $response = md5("\0" . $password . $newchal);
  $newpwd = pack("a32", $password);
  $pappassword = implode ("", unpack("H32", ($newpwd ^ $newchal)));
  echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
  <title>$l_loggingin</title>
  <meta http-equiv=\"Cache-control\" content=\"no-cache\">
  <meta http-equiv=\"Pragma\" content=\"no-cache\">
  <meta http-equiv=\"refresh\" content=\"0;url=http://$uamip:$uamport/logon?username=$username&password=$pappassword&userurl=$userurl\">
  </head>
<body bgColor = 'white'>
<h1 style=\"text-align: center;\">$l_loggingin</h1>
  <center>
    $l_wait
  </center>
</body>
</html>";
exit(0);
}

switch($res) {
  case 'success':     $result =  1; break; // If login successful
  case 'failed':      $result =  2; break; // If login failed
  case 'logoff':      $result =  3; break; // If logout successful
  case 'already':     $result =  4; break; // If tried to login while already logged in
  case 'notyet':      $result =  5; break; // If not logged in yet
  default: $result = 0; // Default: It was not a form request -> client go to login form
}

# Otherwise it was not a form request
# Send out an error message
if ($result == 0) {	//erreur
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
  <title>$l_loggingin</title>
  <meta http-equiv=\"Cache-control\" content=\"no-cache\">
  <meta http-equiv=\"Pragma\" content=\"no-cache\">
  <meta http-equiv=\"refresh\" content=\"0;url=http://$uamip:$uamport/prelogin\">
  </head>
<body bgColor = 'white'>
<h1 style=\"text-align: center;\">$l_loggingin</h1>
  <center>
    $l_wait
  </center>
</body>
</html>";
    exit(0);
}
# Generate the output
echo "<!DOCTYPE html>
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
  <title>$l_loggingin</title>
  <meta http-equiv=\"Cache-control\" content=\"no-cache\">
  <meta http-equiv=\"Pragma\" content=\"no-cache\">
  <script type=\"text/javascript\" language=\"JavaScript\">
    var blur = 0; // not un use
	var mytimeleft = 0; // not un use
	alcasar_popup = null;

    function popUp(URL) {
      if (self.name != \"alcasar_popup\") {
        alcasar_popup = window.open(URL, 'alcasar_popup', 'width=500,height=460,directories=no,resizable=no,scrollbars=yes,location=no,toolbar=no,statusbar=no,menubar=no');
      }
    }

    function doOnLoad(result, userurl, redirurl, adminurl, timeleft) {
	    if (timeleft) { // not in use
        mytimeleft = timeleft;
      }
      if ((result == 1)||(result == 4)) {	//success or already
	      //window.location = userurl;
		  if (alcasar_popup != null) alcasar_popup.focus();
		  
		  if (adminurl != ''){
			  window.location = adminurl;
		  } else if (redirurl != '') {
			  window.location = redirurl;
		  } else if (userurl != '') {
			  window.location = userurl;
		  } else {
			  window.home();
		  }
      }
      if ((result == 2) || (result == 3) || result == 5) { //failed or logoff or notyet
		if (alcasar_popup != null) alcasar_popup.close();
			document.form1.UserName.focus();
      }
    }
  </script>
<link rel=\"stylesheet\" href=\"/css/style_intercept.css\" type=\"text/css\">
</head>
<body onLoad=\"javascript:doOnLoad($result,'$userurl','$redirurl','$adminurl','$timeleft')\">
  <center>";
if ($result == 2 || $result == 3 || $result == 5) { //failed or logoff or notyet
  echo "
	<div id=\"logon\">
	<h1>$organisme</h1>
	<h2>$l_loggedcont</h2>";
	if ($result == 2) { //failed
		echo "	
		<h3>$l_loginfailed</h3>";
		if ($reply) {
		#traitement du reply ...
		echo "<center> $reply <br /><br /></center>";
		}
	}
	echo "
	<img id=\"logo-alcasar\" src=\"/images/logo-alcasar.png\">
	<form name=\"form1\" method=\"post\" action=\"$loginpath\">
	<input type=\"hidden\" name=\"challenge\" value=\"$challenge\">
	<input type=\"hidden\" name=\"uamip\" value=\"$uamip\">
	<input type=\"hidden\" name=\"uamport\" value=\"$uamport\">
	<input type=\"hidden\" name=\"userurl\" value=\"$userurl\">
	<table id=\"boite-logon\">
		<tr>
			<td width=\"20%\" rowspan=\"3\"><img id=\"logo-organ\" src=\"/images/organisme.png\"></td>
			<td width=\"30%\" align=\"right\">$l_user</td>
			<td width=\"50%\" align=\"left\"><INPUT type=\"text\" maxLength=\"32\" name=\"UserName\" autocomplete=\"off\"></td>
		</tr>
		<tr>
			<td align=\"right\">$l_password</td>
			<td align=\"left\"><INPUT maxLength=\"32\" type=\"password\" name=\"Password\" autocomplete=\"off\"></td>
		</tr>
		<tr>
			<td height=\"23\" colSpan=\"2\" align=\"center\"><INPUT value=\"$l_boutonO\" type=\"submit\" name=\"button\" onclick=\"javascript:popUp('$statuspath')\"></td>
		</tr>
	</table>
	</form>
	<table id=\"boite-info\" cellSpacing=\"0\" cellPadding=\"0\" width=\"100%\">
		<tr>
			<td align=\"center\"><FONT color=\"red\"><B>$l_loggedin_stringl1</B></FONT></td>
		</tr>
		<tr>
			<td align=\"left\">
				<ul>
					<LI>$l_loggedin_stringl2</LI>
					<LI>$l_loggedin_stringl4</LI>
					<LI>$l_loggedin_stringl3</LI>
					<LI>$l_loggedin_stringl5</LI>
					<LI>$l_loggedin_stringl6</LI>
				</ul>
			</td>
		</tr>
	</table>";

// Read the "Domain allowed" file
$tab=file(DOMAIN_ALLOWED_LIST);
if ($tab)  # the file isn't empty
	{
	echo "<div id=\"authorized_domain\">$l_uam_domain";
	foreach ($tab as $line)
		{
		if (trim($line) != '') # the line isn't empty
			{
			$domain_allowed=explode("#", $line);
			if (trim($domain_allowed[1]) != ''){
				$domain=explode("\"", $domain_allowed[0]);
				echo "<a href=\"http://".trim($domain[1])."\">".trim($domain_allowed[1])."</a> ";}
			}	
		}
	}
echo "	
</div>
</center>
</body>
</html>";
}
exit(0);
?>
