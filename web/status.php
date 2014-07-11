<?php
# $Id: status.php 1030 2013-02-13 00:30:15Z stephane $
#
# status.php for Alcasar captive portal
# by steweb57 & Rexy
# 
/****************************************************************
*			GLOBAL FILE PATHS			*
*****************************************************************/
define ("CONF_FILE", "/usr/local/etc/alcasar.conf");

/****************************************************************
*				FILE TEST			*
*****************************************************************/
//Test de présence et des droits en lecture des fichiers de configuration.
if (!file_exists(CONF_FILE)){
	exit("Fichier de configuration ".CONF_FILE." non présent");
}
if (!is_readable(CONF_FILE)){
	exit("Vous n'avez pas les droits de lecture sur le fichier ".CONF_FILE);
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

$organisme = $conf["ORGANISM"];

$remote_ip = ($_SERVER['REMOTE_ADDR']);
$connection_history =  "";
$nb_connection_history = 3;

//On récupère le nom de connexion de la session active. on attend que chilli ait mis à jour ses tables
sleep (1);
exec ("sudo /usr/sbin/chilli_query list | grep 'pass' | grep -Ew '($remote_ip)'" , $tab);
$user = explode (" ", $tab[0]);

#### Affichage des 3 dernières connexions de $user[5]
function secondsToDuration($seconds = null){
	if ($seconds == null) return "";

	$temp = $seconds % 3600;
	$time[0] = ( $seconds - $temp ) / 3600 ;	// hours
	$time[2] = $temp % 60 ;				// seconds
	$time[1] = ( $temp - $time[2] ) / 60;		// minutes
	
	return $time[0]." h ".$time[1]." m ".$time[2]." s";
}



# Choice of language
//reste quelques traductions à faire
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'es'){
	$l_login1			= "El éxito de la autenticación";
	$l_logout			= "Conexión de cierre";
	$l_logout_question		= "Are you sure you want to disconnect now?";	//à traduire
	$l_loggedout			= "Su sesión se cierra";
	$l_wait				= "Por favor, espere un momento ...";
	$l_state_label			= "State";		//à traduire
	$l_session_id_label		= "Session ID";	//à traduire
	$l_max_session_time_label	= "Max Session Time";	//à traduire
	$l_max_idle_time_label		= "Max Idle Time";		//à traduire
	$l_start_time_label		= "Start Time";	//à traduire
	$l_session_time_label		= "Tiempo de conexión";
	$l_idle_time_label		= "Idle Time";	//à traduire
	$l_downloaded_label		= "Downloaded";	//à traduire
	$l_uploaded_label		= "Uploaded";	//à traduire
	$l_original_url_label		= "Original URL";	//à traduire
	$l_not_available		= "Not available";	//à traduire
	$l_na				= "N/A";		//à traduire
	$l_error			= "error";		//à traduire
	$l_welcome			= "Welcome";	//à traduire
	$l_conn_history			= "Your last $nb_connection_history connections";	//à traduire
	$l_connected 			= "logged"; //à traduire
	$l_a_connection			= "Active connection detected on LAN"; //à traduire
	$l_a_connection_time		= "time(s)"; //à traduire
}
else if ($Language == 'pt'){
	$l_login1			= "Autenticação bem sucedida.";
	$l_logout			= "Fechando a conexão";
	$l_logout_question		= "Tem certeza de que deseja desconectar agora?";
	$l_loggedout			= "Sua conexão será fechada";
	$l_wait				= "Por favor, aguarde um momento ...";
	$l_state_label			= "Estado da conexão";
	$l_session_id_label		= "Sessão ID";
	$l_max_session_time_label	= "Restante em horas da conexão";
	$l_max_idle_time_label		= "Restante máximo liberado por dia";
	$l_start_time_label		= "Dia, mês, ano e hora da conexão";
	$l_session_time_label		= "Duração da conexão";
	$l_idle_time_label		= "Tempo de Espera";
	$l_downloaded_label		= "Recebidos";
	$l_uploaded_label		= "Enviados";
	$l_original_url_label		= "URL Original";
	$l_not_available		= "Não disponível";
	$l_na				= "N/A";
	$l_error			= "Erro";
	$l_welcome			= "Bem-vindo(a)";
	$l_conn_history			= "Suas últimos conexões : $nb_connection_history";
	$l_connected 			= "Conectado"; 
	$l_a_connection			= "Conexão ativa já detectada para essa LAN";
	$l_a_connection_time		= "Tempo (s)";
}
else if($Language == 'de'){
	$l_login1			= "Erfolgreiche Authentifizierung";
	$l_logout			= "Beenden der Verbindung";
	$l_logout_question	= "Are you sure you want to disconnect now?";	//à traduire
	$l_loggedout		= "Ihre Sitzung ist geschlossen";
	$l_wait				= "Bitte warten Sie einen Moment ...";
	$l_state_label				= "State";		//à traduire
	$l_session_id_label			= "Session ID";	//à traduire
	$l_max_session_time_label	= "Max Session Time";	//à traduire
	$l_max_idle_time_label		= "Max Idle Time";		//à traduire
	$l_start_time_label			= "Start Time";	//à traduire
	$l_session_time_label		= "Online-zeit";
	$l_idle_time_label			= "Idle Time";	//à traduire
	$l_downloaded_label			= "Downloaded";	//à traduire
	$l_uploaded_label			= "Uploaded";	//à traduire
	$l_original_url_label		= "Original URL";	//à traduire
	$l_not_available			= "Not available";	//à traduire
	$l_na						= "N/A";		//à traduire
	$l_error					= "error";		//à traduire
	$l_welcome					= "Welcome"; 	//à traduire
	$l_conn_history				= "Your last $nb_connection_history connections";	//à traduire
	$l_connected 			= "logged"; //à traduire 
	$l_a_connection			= "Active connection detected on LAN"; //à traduire
	$l_a_connection_time		= "time(s)"; //à traduire
}
else if($Language == 'nl'){
	$l_login1			= "Succesvolle authenticatie";
	$l_logout			= "Slotkoers verbinding";
	$l_logout_question	= "Are you sure you want to disconnect now?";	//à traduire
	$l_loggedout		= "Uw sessie is gesloten";
	$l_wait				= "Wacht een moment ...";
	$l_state_label				= "State";		//à traduire
	$l_session_id_label			= "Session ID";	//à traduire
	$l_max_session_time_label	= "Max Session Time";	//à traduire
	$l_max_idle_time_label		= "Max Idle Time";		//à traduire
	$l_start_time_label			= "Start Time";	//à traduire
	$l_session_time_label		= "Online tijd";
	$l_idle_time_label			= "Idle Time";	//à traduire
	$l_downloaded_label			= "Downloaded";	//à traduire
	$l_uploaded_label			= "Uploaded";	//à traduire
	$l_original_url_label		= "Original URL";	//à traduire
	$l_not_available			= "Not available";	//à traduire
	$l_na						= "N/A";		//à traduire
	$l_error					= "error";		//à traduire
	$l_welcome					= "Welcome";	//à traduire
	$l_conn_history				= "Your last $nb_connection_history connections";	//à traduire
	$l_connected 			= "logged"; //à traduire 
	$l_a_connection			= "Active connection detected on LAN"; //à traduire
	$l_a_connection_time		= "time(s)"; //à traduire
}
else if($Language == 'fr'){
	$l_login1			= "Authentification r&eacute;ussie";
	$l_logout			= "Fermeture de la session";
	$l_logout_question	= "Etes vous sûr de vouloir vous déconnecter?";
	$l_loggedout		= "Votre session est fermée";
	$l_wait				= "Patientez un instant ....";
	$l_state_label				= "Etat";
	$l_session_id_label			= "Session ID";
	$l_max_session_time_label	= "Temps de connexion autoris&eacute";
	$l_max_idle_time_label		= "Inactivit&eacute; max. autoris&eacute;e";
	$l_start_time_label			= "D&eacute;but de connexion";
	$l_session_time_label		= "Dur&eacute;e de connexion";
	$l_idle_time_label			= "Inactivit&eacute;";
	$l_downloaded_label			= "Donn&eacute;es t&eacute;l&eacute;charg&eacute;es";
	$l_uploaded_label			= "Donn&eacute;es envoy&eacute;es";
	$l_original_url_label		= "URL demand&eacute;e";
	$l_not_available			= "Non disponible";
	$l_na						= "N/D";	//à traduire
	$l_error					= "erreur";
	$l_welcome					= "Bienvenue";
	$l_conn_history				= "Vos $nb_connection_history derni&egrave;res connexions";
	$l_connected 			= "session active";  
	$l_a_connection			= "Vous &ecirc;tes d&eacute;j&agrave; connect&eacute; sur le r&eacute;seau";
	$l_a_connection_time		= "fois";
}
else {
	$l_login1			= "Successful authentication.";
	$l_logout			= "Closing connection";
	$l_logout_question	= "Are you sure you want to disconnect now?";
	$l_loggedout		= "Your session is closed";
	$l_wait				= "Please wait a moment ...";
	$l_state_label				= "State";
	$l_session_id_label			= "Session ID";
	$l_max_session_time_label	= "Max Session Time";
	$l_max_idle_time_label		= "Max Idle Time";
	$l_start_time_label			= "Start Time";
	$l_session_time_label		= "Session Time";
	$l_idle_time_label			= "Idle Time";
	$l_downloaded_label			= "Downloaded";
	$l_uploaded_label			= "Uploaded";
	$l_original_url_label		= "Original URL";
	$l_not_available			= "Not available";
	$l_na						= "N/A";
	$l_error					= "error";
	$l_welcome					= "Welcome";
	$l_conn_history				= "Your last $nb_connection_history connections";
	$l_connected 			= "logged"; 
	$l_a_connection			= "Active connection detected on LAN";
	$l_a_connection_time		= "time(s)";
}

// si on a pas d'accès à la bdd, la page s'affiche quand même correctement
if (isset($user[5])){
	if ((is_file("./acc/manager/lib/sql/drivers/mysql/functions.php"))&&(is_file("/etc/freeradius-web/config.php"))){
		include_once("/etc/freeradius-web/config.php");
		include_once("./acc/manager/lib/sql/drivers/mysql/functions.php");
		
		$sql = "SELECT UserName, AcctStartTime, AcctStopTime, acctsessiontime FROM radacct WHERE UserName='$user[5]' ORDER BY AcctStartTime DESC LIMIT 0 , $nb_connection_history";
		$link = @da_sql_pconnect($config); // on affiche pas les erreurs
		
		if ($link){
			$res = @da_sql_query($link,$config,$sql); // on affiche pas les erreurs
			
			if ($res){
				$a_connection = ""; $a_connected=0; $connection_history.= "<ul>";
				while(($row = @da_sql_fetch_array($res,$config))){
					$connected = "";
					$start_conn = date_create($row['acctstarttime']);
					$connection_history.="<li>".date_format($start_conn, 'd M Y - H:i:s')." - (";
					if ($row['acctstoptime'] == "") {
						$connected = $l_connected;
						$a_connected = $a_connected +1;
					}else{
						$connected = secondsToDuration($row['acctsessiontime']);
					}
					$connection_history.= "$connected)</li>";
				}
				$connection_history.="</ul>";
				if ($a_connected > 1){
					$a_connection = $l_a_connection." ".$a_connected." ".$l_a_connection_time; }
			}
		}
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html lang="fr">
<!-- written by steweb57 -->
	<head>
		<title>Alcasar - <?php echo $organisme; ?></title>
		<meta http-equiv="Cache-control" content="no-cache">
		<meta http-equiv="Pragma" content="no-cache">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<script type="text/javascript" src="./js/ChilliLibrary.js"></script>
		<script type="text/javascript" src="./js/statusControler.js"></script>
		<link type="text/css" href="./css/status.css" rel="stylesheet">
	</head>
	<body>
		<div id="Chilli">
		<div id="locationName"></div>
		<div id="chilliPage">
		<div id="loggedOutPage" class="c1">
			<table id="disconnectTable">
				<tr>
					<td><img height="150" src="./images/logo-alcasar.png" alt="logo"></td>
					<td><p class="text_auth"><?php echo $l_loggedout; ?></p></td>
				</tr>
			</table>
		</div>
		<div id="statusPage" class="c1">
			<table border="0" id="statusTable">
				<tr>
					<td colspan="2">
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr>
								<td valign="top" rowspan="4">
									<img height="150" src="./images/logo-alcasar.png" alt="logo">
								</td>
								<td class="text_auth_welcom">
									<?php echo $l_login1; ?>
								</td>
							</tr>
							<tr>
								<td class="text_auth">
									<?php echo $l_welcome; ?>
									<br><span id="userName"></span>
								</td>
							</tr>
							<tr>
								<td class="alert">
									<?php echo $a_connection; ?>
								</td>
							</tr>
							<tr>
								<td colspan="2" align="center" class="link_logout">
									<a href="#" onclick="return logoutWithConfirmation('<?php echo $l_logout_question;?>');" class="lien_deco"><?php echo $l_logout; ?></a>
								</td>
							</tr>
						</table>
					</td>
				</tr>
<!--tr id="connectRow">
<td id="statusMessageLabel" class="chilliLabel"><strong><?php echo $l_state_label; ?></strong></td>
<td id="statusMessage" class="chilliValue">Connected</td>
</tr-->
<!--tr id="sessionIdRow">
<td id="sessionIdLabel" class="chilliLabel"><strong><?php echo $l_session_id_label; ?></strong></td>
<td id="sessionId" class="chilliValue"><?php echo $l_not_available; ?></td>
</tr-->
				<tr id="sessionTimeoutRow">
					<td id="sessionTimeoutLabel" class="chilliLabel"><?php echo $l_max_session_time_label; ?></td>
					<td id="sessionTimeout" class="chilliValue"><?php echo $l_not_available; ?></td>
				</tr>
				<tr id="idleTimeoutRow">
					<td id="idleTimeoutLabel" class="chilliLabel"><?php echo $l_max_idle_time_label; ?></td>
					<td id="idleTimeout" class="chilliValue"><?php echo $l_not_available; ?></td>
				</tr>
				<tr id="startTimeRow">
					<td id="startTimeLabel" class="chilliLabel"><?php echo $l_start_time_label; ?></td>
					<td id="startTime" class="chilliValue"><?php echo $l_not_available; ?></td>
				</tr>
				<tr id="sessionTimeRow">
					<td id="sessionTimeLabel" class="chilliLabel"><?php echo $l_session_time_label; ?></td>
					<td id="sessionTime" class="chilliValue"><?php echo $l_not_available; ?></td>
				</tr>
				<tr id="idleTimeRow">
					<td id="idleTimeLabel" class="chilliLabel"><?php echo $l_idle_time_label; ?></td>
					<td id="idleTime" class="chilliValue"><?php echo $l_not_available; ?></td>
				</tr>
				<tr id="inputOctetsRow">
					<td id="inputOctetsLabel" class="chilliLabel"><?php echo $l_downloaded_label; ?></td>
					<td id="inputOctets" class="chilliValue"><?php echo $l_na; ?></td>
				</tr>
				<tr id="outputOctetsRow">
					<td id="outputOctetsLabel" class="chilliLabel"><?php echo $l_uploaded_label; ?></td>
					<td id="outputOctets" class="chilliValue"><?php echo $l_na; ?></td>
				</tr>
<!--tr id="originalURLRow">
<td id="originalURLLabel" class="chilliLabel"><?php echo $l_original_url_label; ?></td>
<td id="originalURL" class="chilliValue"><?php echo $l_na; ?></td>
</tr-->
				<tr>
					<td colspan=2 id="conHistoryLabel" class="chilliLabel"><?php echo $l_conn_history; ?></td>
				</tr>
				<tr id="conHistoryRow">
					<td colspan=2 id="conHistory" class="chilliValue"><?php echo $connection_history; ?></td>
				</tr>
			</table>
		</div>
		<div id="waitPage">
			<table id="waitTable">
				<tr>
					<td><img height="150" src="./images/logo-alcasar.png" alt="logo"></td>
					<td><p class="text_auth"><img src="./images/wait.gif" width="16" height="16" class="wait" alt="<?php echo $l_wait; ?>"><?php echo $l_wait; ?></p></td>
				</tr>
			</table>
		</div>
		<div id="errorPage">
			<table id="errorTable">
				<tr>
					<td><img height="150" src="./images/logo-alcasar.png" alt="logo"></td>
					<td><span id="errorMessage"><?php echo $l_error; ?></span></td>
				</tr>
			</table>
		</div>
		</div>
<!--div id="debugPage" style="display:inline;">
<textarea id="debugarea" rows="20" cols="60">
</textarea>
</div-->
		</div>
	</body>
</html>
