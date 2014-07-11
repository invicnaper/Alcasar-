<?php
# $Id: index.php 1306 2014-01-16 10:37:45Z richard $
#
# index.php for ALCASAR captive portal
# by REXY
# UI & css style by stephane ERARD
# The contents of this file may be used under the terms of the GNU
# General Public License Version 2, provided that the above copyright
# notice and this permission notice is included in all copies or
# substantial portions of the software.
/****************************************************************
*			GLOBAL FILE PATHS			*
*****************************************************************/
define ("CONF_FILE", "/usr/local/etc/alcasar.conf");

/****************************************************************
*			FILE reading test			*
*****************************************************************/
$conf_files=array(CONF_FILE);
foreach ($conf_files as $file){
	if (!file_exists($file)){
		exit("File ".$file." unknown");
	}
	if (!is_readable($file)){
		exit("You don't have read rights on the file ".$file);
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
	exit("Error opening the file ".CONF_FILE);
}
fclose($ouvre);
$hostname = alcasar.".".$conf["DOMAIN"];
$network_pb = False;
$cert_add = "http://$hostname/certs";
$direct_access = False;
$diagnostic = "can't contact the default router";
$remote_ip = ($_SERVER['REMOTE_ADDR']);
$tab = array();$user = array();
$connection_history =  "";
$nb_connection_history = 3;

# on discrimine les accès directs sur Alcasar par rapport aux redirections (blacklist ou pannes rso)
if (($_SERVER['HTTP_HOST'] == $_SERVER['SERVER_ADDR']) || preg_match ("/^alcasar/", $_SERVER['HTTP_HOST']) || preg_match ("/^$hostname/", $_SERVER['HTTP_HOST']))
	{
	$direct_access=True;
	exec ("sudo /usr/sbin/chilli_query list|grep $remote_ip" , $tab);
	$user = explode (" ", $tab[0]);
	}
#### Affichage des 3 dernières connexions de $user[5]
function secondsToDuration($seconds = null){
	if ($seconds == null) return "";

	$temp = $seconds % 3600;
	$time[0] = ( $seconds - $temp ) / 3600 ;	// hours
	$time[2] = $temp % 60 ;				// seconds
	$time[1] = ( $temp - $time[2] ) / 60;		// minutes
	
	return $time[0]." h ".$time[1]." m ".$time[2]." s";
}

$l_connected = "connected"; // a traduire (choix de la langue ci-dessous mais nécessitant de $connection_history)
// si on a pas d'accès à la bdd, la page s'affiche quand même correctement
if ((isset ($user[4])) && ($user[4] != "0")){
	if ((is_file("./acc/manager/lib/sql/drivers/mysql/functions.php"))&&(is_file("/etc/freeradius-web/config.php"))){
		include_once("/etc/freeradius-web/config.php");
		include_once("./acc/manager/lib/sql/drivers/mysql/functions.php");
		
		$sql = "SELECT UserName, AcctStartTime, AcctStopTime, acctsessiontime FROM radacct WHERE UserName='$user[5]' ORDER BY AcctStartTime DESC LIMIT 0 , $nb_connection_history";
		$link = @da_sql_pconnect($config); // on affiche pas les erreurs
		
		if ($link){
			$res = @da_sql_query($link,$config,$sql); // on affiche pas les erreurs
			
			if ($res){
				$connection_history.= "<ul>";
				while(($row = @da_sql_fetch_array($res,$config))){
					$connected = "";
					if ($row[acctstoptime] == "") $connected = " ($l_connected)";
					$connection_history.="<li title='$row[username] $row[acctstarttime] $row[acctstoptime] (".secondsToDuration($row[acctsessiontime]).")'>$row[acctstarttime] (".secondsToDuration($row[acctsessiontime]).") $connected</li>";
				}
				$connection_history.="</ul>";
			}
		}
	}
}
####

# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_access_denied = "ACC&Egrave;S REFUS&Eacute;";
  $l_access_welcome = "Bienvenue sur ALCASAR";
  $l_access_unavailable = "ACC&Egrave;S INDISPONIBLE";
  $l_required_domain = "Site WEB demand&eacute;";
  $l_explain_acc_access = "Le centre de gestion permet d'administrer le portail. Vous devez poss&eacute;der un compte d'administration ou de gestion pour y acc&eacute;der.";
  $l_explain_access_deny = "Vous tentez d'acc&eacute;der &agrave; une ressource dont le contenu est r&eacute;put&eacute; contenir des informations inappropri&eacute;es.";
  $l_explain_net_pb = "Votre portail d&eacute;tecte que l'acc&egrave;s &agrave; Internet est indisponible.";
  $l_contact_access_deny = "Contactez le responsable de la s&eacute;curit&eacute; (OSSI/RSSI) si vous pensez que ce filtrage est abusif.";
  $l_contact_net_pb = "Contactez votre responsable informatique ou votre prestataire Internet pour plus d'information.";
  $l_welcome = "Page principale de votre portail captif";
  $l_acc_access = "<a href=\"https://$hostname/acc\">Acc&egrave;s au centre de gestion</a>";
  $l_install_certif = "<a href=\"$cert_add/certificat_alcasar_ca.crt\">Installer le certificat racine</a>";
  $l_install_certif_more = "<a href=\"$cert_add/certificat_alcasar_ca.crt\">Installation du certificat de l'autorit&eacute; racine d'ALCASAR</a>";
  $l_certif_explain = "Permet l'&eacute;change de donn&eacute;es s&eacute;curis&eacute;es entre votre station de consultation et le portail captif ALCASAR.<BR>Si ce certificat n'est pas enregistr&eacute; sur votre station de consultation, il est possible que des alertes de s&eacute;curit&eacute;s soient &eacute;mises par votre navigateur.<br><br>";
  $l_certif_explain_help = "<a href=\"alcasar-certificat.pdf\" target=\"_blank\">Aide complémentaire</a>";
  $l_category = "catégorie :";
if ((isset ($user[4])) && ($user[4] == "0")) {
	  $l_logout_explain = "Aucune session de consultation Internet n'est actuellement ouverte sur votre syst&egrave;me.";
	  $l_logout = "<a href=\"http://www.qwant.com/lang/fr_FR/\">Ouvrir une session Internet</a>";}
  else {
	  if ($user[5] != $user[0]) // authentication exception or not
	  {
	  	$l_logout_explain = "Ferme la session de l'usager actuellement connect&eacute;. <br><br>Utilisateur connect&eacute; : <a href=\"http://$hostname:3990/logoff\" title=\"Deconnecter l'utilisateur $user[5]\"><b>$user[5]</b></a><br><br>$nb_connection_history derni&egrave;res connexions :$connection_history";
		$l_logout = "<a href=\"http://$hostname:3990/logoff\">Se d&eacute;connecter d'internet</a>";
	  }
	  else
	  {
		  $l_logout_explain = "Votre système ($user[5]) est en exception d'authentication.<br><br>$nb_connection_history last connections :$connection_history";
		  $l_logout = "Information des connexions";
	  }
	}
  $l_password_change = "<a href=\"https://$hostname/pass\">Changer votre mot de passe</a>";
  $l_password_change_explain = "Vous redirige sur la page de changement du mot de passe de votre compte d'acc&egrave;s &agrave; internet.<br><br>Vous devez avoir un compte internet valide.";
  $l_back_page = "<a href=\"javascript:history.back()\">Page pr&eacute;c&eacute;dente</a>";
}
else if($Language == 'pt'){
  $l_access_denied = "Acesso negado";
  $l_access_welcome = "Bem-vindo ao Alcasar";
  $l_access_unavailable = "ACESSO INDISPONÍVEL";
  $l_required_domain = "Site WEB Obrigatório";
  $l_explain_acc_access = "Este é o centro de controle do portal para acessar você deve ter uma conta administrativa valida.";
  $l_explain_access_deny = "Você tenta se conectar a um recurso cujo conteúdo é considerado inadequado no conteúdo de informações.";
  $l_explain_net_pb = "O sistema detectou que o acesso é de risco, não será permitido o acesso";
  $l_contact_access_deny = "Entre em contato com o administrador do sistema de segurança se acha que essa filtragem é abusiva.";
  $l_contact_net_pb = "Entre em contato com a empresa fornecedora de Internet para mais informações";
  $l_welcome = "Página do portal";
  $l_acc_access = "<a href=\"https://$hostname/acc\">ALCASAR Controle Center</a>";
  $l_install_certif = "<a href=\"$cert_add/certificat_alcasar_ca.crt\">Instalar Certificado Alcasar AC</a>";
  $l_install_certif_more = "<a href=\"$cert_add/certificat_alcasar_ca.cert\">Instalar Certificado Alcasar AC</a>";
  $l_certif_explain = "O certificado Permiti a troca de dados seguro entre seu computador e o portal Alcasar.<BR>Se este certificado não estiver incorporado no seu computador, alguns alertas de segurança deverá aparecer no navegador.<br><br>";
  $l_certif_explain_help = "<a href=\"alcasar-certificat.pdf\" target=\"_blank\">Essa foi uma ajuda complementar</a>";
  $l_category = "categoria :";
if ((isset ($user[4])) && ($user[4] == "0")) {
	  $l_logout_explain = "Não há conexão de Internet aberta em seu computador, deseja conectar?";
	  $l_logout = "<a href=\"http://www.qwant.com/lang/pt_BR/\">Abrir uma conexão de Internet</a>";}
  else {
	  if ($user[5] != $user[0]) // authentication exception or not
	  {
		  $l_logout_explain = "Se desejar, feche a conexão do usuário atual conectado.<br> Usuário conectado : <a href=\"http://$hostname:3990/logoff\" title=\"Disconnect user $user[5]\"><b>$user[5]</b></a><br><br>$nb_connection_history last connections :$connection_history";
		  $l_logout = "<a href=\"http://$hostname:3990/logoff\">Sair da Internet</a>";
	  }
	  else
	  {
		  $l_logout_explain = "O sistema ($user[5]) detctou exesso de autenticação.<br><br>$nb_connection_history logins últimos :$connection_history";
		  $l_logout = "Informações de conexões";
	  }
  	}
  $l_password_change = "<a href=\"https://$hostname/pass\">Mudar sua senha</a>";
  $l_password_change_explain = "Você será redirecionado à página de alteração de senha.<br><br> e deverá ter uma conta de usuário valido para efetuar a troca e acessar à Internet.";
  $l_back_page = "<a href=\"javascript:history.back()\">Página anterior</a>";
}
else {
  $l_access_denied = "ACCESS DENIED";
  $l_access_welcome = "Welcome on ALCASAR";
  $l_access_unavailable = "ACCESS UNAVAILABLE";
  $l_required_domain = "Required WEB site";
  $l_explain_acc_access = "This center control the portal. You must have an administrative account.";
  $l_explain_access_deny = "You try to connect to a resource whose content is deemed to contain inappropriate information.";
  $l_explain_net_pb = "Your portal has just detected that the Internet access is down";
  $l_contact_access_deny = "Contact your security system manager if you think this filtering is abusive.";
  $l_contact_net_pb = "Contact your network responsive or your Internet provider for more information";
  $l_welcome = "Your captive portal main page";
  $l_acc_access = "<a href=\"https://$hostname/acc\">ALCASAR Control Center</a>";
  $l_install_certif = "<a href=\"$cert_add/certificat_alcasar_ca.crt\">Install ALCASAR AC Certificate</a>";
  $l_install_certif_more = "<a href=\"$cert_add/certificat_alcasar_ca.cert\">Install ALCASAR AC Certificate</a>";
  $l_certif_explain = "Allow secure data exchange between your computer and ALCASAR portal.<BR>If this certificate isn't incorporated in your computer, some security alerts should appear in your browser.<br><br>";
  $l_certif_explain_help = "<a href=\"alcasar-certificat.pdf\" target=\"_blank\">Complementary help</a>";
  $l_category = "category :";
if ((isset ($user[4])) && ($user[4] == "0")) {
	  $l_logout_explain = "No Internet consultation session is actualy open on your system";
	  $l_logout = "<a href=\"http://www.qwant.com/lang/en_GB/\">Open an Internet session</a>";}
  else {
	  if ($user[5] != $user[0]) // authentication exception or not
	  {
		  $l_logout_explain = "Close the session of the user currently connected.<br> User logged-on : <a href=\"http://$hostname:3990/logoff\" title=\"Disconnect user $user[5]\"><b>$user[5]</b></a><br><br>$nb_connection_history last connections :$connection_history";
		  $l_logout = "<a href=\"http://$hostname:3990/logoff\">Logoff from internet</a>";
	  }
	  else
	  {
		  $l_logout_explain = "Your system ($user[5]) is in exception of authentication.<br><br>$nb_connection_history Last logins :$connection_history";
		  $l_logout = "Connections information";
	  }
  	}
  $l_password_change = "<a href=\"https://$hostname/pass\">Change your password</a>";
  $l_password_change_explain = "Redirect you on password change page.<br><br> You should already have an Internet access account.";
  $l_back_page = "<a href=\"javascript:history.back()\">Previous page</a>";
}
$l_title = ($direct_access ? $l_access_welcome : ($network_pb ? $l_access_unavailable : $l_access_denied));
$l_explain = ($direct_access ? $l_explain_acc_access : ($network_pb ? $l_explain_net_pb : $l_explain_access_deny));

# Attribution des icones / images
$img_rep = "images/";
$img_organisme = "organisme.png";
$img_access = "globe_acces_70.png";
$img_connect = "globe_70.png";
$img_warning = "globe_warning_70.png";
$img_pwd = "cle_ombre.png";
$img_certificate = "certificat.png";
$img_acc = "logo-alcasar_70.png";
$img_false = "interdit.png";
$img_internet = $img_connect;

if ((isset ($user[4])) && ($user[4] == "0")) {
	if (! $network_pb) {
		$img_internet = $img_access;
		}
		else {
		$img_internet = $img_warning;
	}
}
else {
	$img_internet = $img_connect;
}

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>ALCASAR - <?php echo $l_title; ?></title>
	<meta http-equiv="Cache-control" content="no-cache">
	<meta http-equiv="Pragma" content="no-cache">
	<link rel="stylesheet" type="text/css" href="css/style_intercept.css">
	<script type="text/javascript">
		function valoriserDiv5(param){
			document.getElementById("box_info").innerHTML = param.innerHTML;
		}
	</script>
</head>
<body onload="valoriserDiv5(text_conn);">
<?php
if ($direct_access){
	echo "
		<div id=\"cadre_titre\" class=\"titre_controle\">
			<p id=\"acces_controle\" class=\"titre_controle\">$l_title</p>";
	if ($network_pb) {
		echo "	<span>$l_explain_net_pb</span>";
		}
	}
	else {
		echo"
			<div id=\"cadre_titre\" class=\"titre_refus\">
				<p id=\"acces_controle\" class=\"titre_refus\">$l_title</p>";
	}
?>
			<div id="boite_logo">
				<img src="images/organisme.png">
			</div>
		</div>
		<div id="contenu_acces">
			<div id="box_url">
<?php 
//search in the blacklist categories
if ((! $direct_access) && (! $network_pb)){
	$pattern = preg_replace('/www./','',$_SERVER['HTTP_HOST']);
	exec("grep -Re ^$pattern$ /etc/dansguardian/lists/blacklists/*/domains|cut -d'/' -f6", $output);
	unset ($line);
	foreach ($output as $row) {
		$line=$line.(trim($row)).", ";
	} 
	echo "$l_required_domain : $_SERVER[HTTP_HOST]";
	if ($line != "") { echo "<BR>".rtrim ("$l_category $line", ", ");}
}
?>
			</div>

<?php
if ($direct_access){
	echo "	<div id=\"box_bienvenue\">
				$l_welcome
			</div>
			<div class=\"box_menu\" id=\"box_conn\" onmouseover=\"valoriserDiv5(text_conn);\">
				<span>$l_logout</span>
				<img src=\"$img_rep$img_internet\">
			</div>
			<div class=\"box_menu\" id=\"box_certif\" onmouseover=\"valoriserDiv5(text_certif);\">
				<span>$l_install_certif</span>
				<img src=\"$img_rep$img_certificate\">
			</div>
			<div class=\"box_menu\" id=\"box_mdp\" onmouseover=\"valoriserDiv5(text_mdp);\">
				<img src=\"$img_rep$img_pwd\">
				<span>$l_password_change</span>
			</div>		
			<div class=\"box_menu\" id=\"box_acc\" onmouseover=\"valoriserDiv5(text_acc);\">
				<span>$l_acc_access</span>
				<img src=\"$img_rep$img_acc\">
			</div>
			<div class=\"div-cache\" id=\"text_conn\">
				<h2>$l_logout</h2>
				<p>$l_logout_explain</p>
				<img src=\"$img_rep$img_internet\">
			</div>
			<div class=\"div-cache\" id=\"text_certif\">
				<h2>$l_install_certif_more</h2>
				<p>$l_certif_explain $l_certif_explain_help</p>
				<img src=\"$img_rep$img_certificate\">				
			</div>
			<div class=\"div-cache\" id=\"text_mdp\">
				<h2>$l_password_change</h2>
				<p>$l_password_change_explain</p>
				<img src=\"$img_rep$img_pwd\">
			</div>
			<div class=\"div-cache\" id=\"text_acc\">
				<h2>$l_acc_access</h2>
				<p>$l_explain</p>
				<img src=\"$img_rep$img_acc\">
			</div>
			<div id=\"box_info\">
			</div>";
	}
	else {
		echo "
			<div id=\"box_refuse\">
				<img src=\"$img_rep$img_false\">
				<p>$l_explain</p>
			</div>
			<div id=\"liens_redir\">
				<p>$l_back_page</p>
			</div>";
		}
	if (($network_pb)&&(! $direct_access)) {
	echo "	<span>Diagnostic : $diagnostic</span>";
	}
?>
		</div>
	</body>
</html>
