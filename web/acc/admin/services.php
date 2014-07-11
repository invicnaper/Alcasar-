<?php
/* written by steweb57 & Rexy */ 
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$Langue		= explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$Language	= strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
	$l_services_title	= "Configuration des services";
	$l_main_services	= "Services principaux";
	$l_opt_services		= "Services optionnels";
	$l_service_title 	= "Nom du service";
	$l_service_start 	= "D&eacute;marrer";
	$l_service_stop 	= "Arr&ecirc;ter";
	$l_service_restart 	= "Red&eacute;marrer";
	$l_service_status 	= "Status";
	$l_service_status_img_ok= "Démarré";
	$l_service_status_img_ko= "Arrété";
	$l_service_action 	= "Actions";
	$l_radiusd		= "Serveur d'authentification et d'autorisation";
	$l_chilli		= "Passerelle d'interception";
	$l_dansguardian		= "Filtre d'URL et de contenu WEB";
	$l_mysqld		= "Serveur de la base de données usager";
	$l_squid		= "Serveur de cache WEB";
	$l_dnsmasq		= "Serveur DNS et filtre de domaine";
	$l_httpd		= "Serveur WEB (Centre de Gestion d'ALCASAR)";
	$l_havp			= "Filtre antivirus WEB";
	$l_sshd			= "Accès sécurisée à distance";
	$l_freshclam		= "Mise à jour de l'antivirus toutes les 2 heures";
	$l_ntpd			= "Service de mise à l'heure réseau";
} else {
	$l_services_title	= "Services configuration";
	$l_main_services	= "Main services";
	$l_opt_services		= "Optional services";
	$l_service_title 	= "Service name";
	$l_service_start 	= "Start";
	$l_service_stop 	= "Stop";
	$l_service_restart 	= "Restart";
	$l_service_status 	= "Status";
	$l_service_status_img_ok= "Running";
	$l_service_status_img_ko= "Stopped";
	$l_service_action 	= "Actions";
	$l_radiusd		= "Authentication and authorisation serveur";
	$l_chilli		= "Interception gateway";
	$l_dansguardian		= "URL and WEB content filter";
	$l_mysqld		= "User database server";
	$l_squid		= "Proxy Cache WEB";
	$l_dnsmasq		= "DNS and domain name filter";
	$l_httpd		= "WEB server (ALCASAR Control Center)";
	$l_havp			= "WEB antivirus filter";
	$l_sshd			= "Secure remote access";
	$l_freshclam		= "WEB antivirus update (every 2 hours)";
	$l_ntpd			= "Network time";
}

/****************************************************************
*	CONSTANTES AVEC CHEMINS DES FICHIERS DE CONFIGURATION	*
*****************************************************************/
define ("CONF_FILE", "/usr/local/etc/alcasar.conf");

/********************************************************
*	TEST DU FICHIERS DE CONFIGURATION		*
*********************************************************/
//Test de présence et des droits en lecture des fichiers de configuration.
if (!file_exists(CONF_FILE)){
	exit("Fichier de configuration ".CONF_FILE." non présent");
}
if (!is_readable(CONF_FILE)){
	exit("Vous n'avez pas les droits de lecture sur le fichier ".CONF_FILE);
}

//fonction pour faire une action (start,stop,restart) sur un service
function serviceExec($service, $action){
	if (($action == "start")||($action == "stop")||($action == "restart")){
		exec("sudo /sbin/service $service $action",$retval, $retstatus);
		if ($service == "sshd"){
			if ($action == "start"){ 
				exec("sudo /bin/systemctl enable $service.service");
				file_put_contents(CONF_FILE, str_replace('SSH=off', 'SSH=on', file_get_contents(CONF_FILE)));
				exec ("sudo /usr/local/bin/alcasar-iptables.sh");
				}
			if ($action == "stop"){
			       	exec("sudo /sbin/systemctl disable $service.service");
				file_put_contents(CONF_FILE, str_replace('SSH=on', 'SSH=off', file_get_contents(CONF_FILE)));
				exec ("sudo /usr/local/bin/alcasar-iptables.sh");
				}
			}
		return $retstatus;
	} else {
		return false;
	}
}
//fonction définissant le status d'un service 
//(en fonction de la présence d'un mot clé dans la valeur de status)
function checkServiceStatus($service){
	$response = false;
	exec("/bin/systemctl is-active $service.service",$retval);
	foreach( $retval as $val ) {
		if ($val == "active"){
			$response = true;
			break;
		}
	}
	return $response;
}

//-------------------------------
// Les actions sur un service
//-------------------------------
//sécurité sur les actions à réaliser
$autorizeService = array("radiusd","chilli","dansguardian","mysqld","squid","dnsmasq","httpd","havp","sshd","freshclam","ntpd");
$autorizeAction = array("start","stop","restart");

if (isset($_GET['service'])&&(in_array($_GET['service'], $autorizeService))) {
    if (isset($_GET['action'])&&(in_array($_GET['action'], $autorizeAction))) {
    	$execStatus = serviceExec($_GET['service'], $_GET['action']);
		// execStatus non exploité
	}
}
//-------------------------------
//recherche du status des services
//-------------------------------
$MainServiceStatus = array();
$MainServiceStatus['radiusd'] = checkServiceStatus("radiusd");
$MainServiceStatus['chilli'] = checkServiceStatus("chilli");
$MainServiceStatus['dansguardian'] = checkServiceStatus("dansguardian");
$MainServiceStatus['mysqld'] = checkServiceStatus("mysqld");
$MainServiceStatus['squid'] = checkServiceStatus("squid");
$MainServiceStatus['dnsmasq'] = checkServiceStatus("dnsmasq");
$MainServiceStatus['httpd'] = checkServiceStatus("httpd");
$MainServiceStatus['havp'] = checkServiceStatus("havp");

$OptServiceStatus = array();
$OptServiceStatus['sshd'] = checkServiceStatus("sshd");
$OptServiceStatus['freshclam'] = checkServiceStatus("freshclam");
$OptServiceStatus['ntpd'] = checkServiceStatus("ntpd");

/****************
*	MAIN	*
*****************/

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><!-- written by steweb57 / rexy -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $l_services_title; ?></title>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_main_services; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
	<tr align="center"><td><?php echo $l_service_status;?></td><td colspan="2"><?php echo $l_service_title;?></td><td colspan="3"><?php echo $l_service_action;?></td></tr>
	<TR align="center">
<?php foreach( $MainServiceStatus as $serviceName => $statusOK ) { ?>
<tr>
	<?php if ($statusOK) { ?>
    <td align="center"><img src="/images/state_ok.gif" width="15" height="15" alt="<?php echo $l_service_status_img_ok; ?>"></td>
	<td align="center"><?php $comment="l_$serviceName"; echo "<b>$serviceName</b></td><td>${$comment}" ;?> </td>
    <td width="80" align="center">---</td>
    <td width="80" align="center"><?php if ($serviceName != "chilli") { echo "<a href=".$_SERVER['PHP_SELF']."?action=stop&service=$serviceName\"> $l_service_stop</a>"; } else echo "---";?></td>
    <td width="80" align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=restart&service=$serviceName\"> $l_service_restart";?></a></td>
	<?php } else { ?>
    <td align="center"><img src="/images/state_error.gif" width="15" height="15" alt="<?php echo $l_service_status_img_ko ?>"></td>
    <td align="center"><?php $comment="l_$serviceName"; echo "<b>$serviceName</b></td><td>${$comment}" ;?> </td>
    <td width="80" align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=start&service=$serviceName\"> $l_service_start";?></a></td>
    <td width="80" align="center">---</td>
    <td width="80" align="center">---</td>
    <?php } ?>
</tr>
<?php } ?>
</td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_opt_services; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
	<tr align="center"><td><?php echo $l_service_status;?></td><td colspan="2"><?php echo $l_service_title;?></td><td colspan="3"><?php echo $l_service_action;?></td></tr>
	<TR align="center">
<?php foreach( $OptServiceStatus as $serviceName => $statusOK ) { ?>
<tr>
	<?php if ($statusOK) { ?>
    <td align="center"><img src="/images/state_ok.gif" width="15" height="15" alt="<?php echo $l_service_status_img_ok; ?>"></td>
	<td align="center"><?php $comment="l_$serviceName"; echo "<b>$serviceName</b></td><td>${$comment}" ;?> </td>
    <td width="80" align="center">---</td>
    <td width="80" align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=stop&service=$serviceName\"> $l_service_stop";?></a></td>
    <td width="80" align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=restart&service=$serviceName\"> $l_service_restart";?></a></td>
	<?php } else { ?>
    <td align="center"><img src="/images/state_error.gif" width="15" height="15" alt="<?php echo $l_service_status_img_ko ?>"></td>
    <td align="center"><?php $comment="l_$serviceName"; echo "<b>$serviceName</b></td><td>${$comment}" ;?> </td>
    <td width="80" align="center"><a href="<?php echo $_SERVER['PHP_SELF']."?action=start&service=$serviceName\"> $l_service_start";?></a></td>
    <td width="80" align="center">---</td>
    <td width="80" align="center">---</td>
    <?php } ?>
</tr>
<?php } ?>
</td></tr>
</table>
</body>
</html>
