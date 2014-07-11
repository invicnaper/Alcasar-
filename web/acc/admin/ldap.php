<?php
require_once("lib/alcasar/freeradius/siteconfig.php");
require_once("lib/alcasar/freeradius/ldapconfig.php");

/* written by steweb57 */
/****************************************************************
*	CONSTANTES AVEC CHEMINS DES FICHIERS DE CONFIGURATION		*
*****************************************************************/

define ("ALCASAR_RADIUS_SITE", "/etc/raddb/sites-available/alcasar");
define ("ALCASAR_RADIUS_MODULE_LDAP", "/etc/raddb/modules/ldap");

/****************************************************************
*						Choice of language						*
*****************************************************************/

$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
	$Langue	= explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$Language	= strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
	$l_file						= "Fichier ";
	$l_not_found				= " non présent";
	$l_no_writing_right_on_file	= "Vous n'avez pas les droits d'écriture sur le fichier ";
	$l_ldap_update_sucess		= "Mise à jour des paramètres LDAP réalisée avec succès";
	$l_ldap_title				= "Authentification externe : LDAP";
	$l_ldap_legend				= "Authentification LDAP";
	$l_ldap_auth_enable_label	= "Activer l'authentification LDAP:";
	$l_ldap_YES					= "OUI";
	$l_ldap_NO					= "NON";
	$l_ldap_server_label		= "Nom du serveur LDAP:";
	$l_ldap_server_text			= "Nom ou IP du serveur LDAP éventuel.";
	$l_ldap_base_dn_label		= "DN de la base LDAP:";
	$l_ldap_base_dn_text		= "DN est le 'Distinguished Name', il situe les informations utilisateurs, exemple: 'o=Mon entreprise, c=FR'.";
	$l_ldap_filter_label		= "Identifiant LDAP:";
	$l_ldap_filter_text			= "Clé utilisée pour la recherche d'un identifiant de connexion, exemple: 'uid', 'sn', etc. Pour un AD mettre 'sAMAccountName'.";
	$l_ldap_base_filter_label	= "Filtre de l'utilisateur LDAP:";
	$l_ldap_base_filter_text	= "Sur option, vous pouvez en plus limiter les objets recherchés avec des filtres additionnels. Par exemple 'objectClass=posixGroup' aurait comme conséquence l'utilisation de '(&amp;(uid=username)(objectClass=posixGroup))'";
	$l_ldap_user_label			= "Utilisateur LDAP:";
	$l_ldap_user_text			= "Laissez vide pour utiliser un accès invité. Si renseigné, ALCASAR se connectera au serveur LDAP en tant qu'un utilisateur spécifié, exemple: 'uid=Utilisateur,ou=MonUnité,o=MaCompagnie,c=FR'. Requis pour les serveurs possédant un Active Directory.";
	$l_ldap_password_label		= "Mot de passe LDAP:";
	$l_ldap_password_text		= "Laissez vide pour un accès invité. Sinon, indiquez le mot de passe de connexion. Requis pour les serveurs possédant un Active Directory.";
	$l_ldap_submit				= "Enregistrer";
	$l_ldap_reset				= "Annuler";
	$l_ldap_test_network_failed	= "Pas de connectivité réseau avec le serveur LDAP.";
	$l_ldap_test_connection_failed	= "Impossible de se connecter au serveur LDAP.";
	$l_ldap_test_bind_ok		= "Connexion LDAP réussie...";
	$l_ldap_test_bind_failed	= "Echec d'authentification sur le serveur LDAP...Vérifiez votre configuration ldap...";
} else {
	$l_file						= "File ";
	$l_not_found				= " not found";
	$l_no_writing_right_on_file	= "You have no writting permission on the file ";
	$l_ldap_update_sucess		= "Successfull LDAP settings update";
	$l_ldap_title				= "External authentication : LDAP";
	$l_ldap_legend				= "LDAP authentication";
	$l_ldap_auth_enable_label	= "Use LDAP authentication :";
	$l_ldap_YES					= "YES";
	$l_ldap_NO					= "NO";
	$l_ldap_server_label		= "LDAP server name:";
	$l_ldap_server_text			= "This is the hostname or IP address of the LDAP server.";
	$l_ldap_base_dn_label		= "LDAP base dn:";
	$l_ldap_base_dn_text		= "This is the 'Distinguished Name', locating the user information, e.g. 'o=My Company,c=US'.";
	$l_ldap_filter_label		= "LDAP uid:";
	$l_ldap_filter_text			= "This is the key under which to search for a given login identity, e.g. 'uid', 'sn', etc.. For AD use 'sAMAccountName'.";
	$l_ldap_base_filter_label	= "LDAP user filter:";
	$l_ldap_base_filter_text	= "Optionally you can further limit the searched objects with additional filters. For example 'objectClass=posixGroup' would result in the use of '(&amp;(uid=username)(objectClass=posixGroup))'";
	$l_ldap_user_label			= "LDAP user dn:";
	$l_ldap_user_text			= "Leave blank to use anonymous binding. If filled, ALCASAR uses the specified distinguished name on login attempts to find the correct user, e.g. 'uid=Username,ou=MyUnit,o=MyCompany,c=US'. Required for Active Directory Servers.";
	$l_ldap_password_label		= "LDAP password:";
	$l_ldap_password_text		= "Leave blank to use anonymous binding. Else fill in the password for the above user. Required for Active Directory Servers.";
	$l_ldap_submit				= "Save";
	$l_ldap_reset				= "Reset";
	$l_ldap_test_network_failed	= "LDAP server is not reachable.";
	$l_ldap_test_connection_failed	= "LDAP connexion failed...";
	$l_ldap_test_bind_ok		= "LDAP connexion success...";
	$l_ldap_test_bind_failed	= "LDAP authentication failed...Check your ldap setup...";
}
/********************************************************
*		TEST DES FICHIERS DE CONFIGURATION	*
*********************************************************/

//Test de présence et des droits en lecture des fichiers de configuration.
if (!file_exists(ALCASAR_RADIUS_SITE)){
	exit($l_file.ALCASAR_RADIUS_SITE.$l_not_found);
}
if (!file_exists(ALCASAR_RADIUS_MODULE_LDAP)){
	exit($l_file.ALCASAR_RADIUS_MODULE_LDAP.$l_not_found);
}
if (!is_readable(ALCASAR_RADIUS_SITE)){
	exit($l_no_writing_right_on_file.ALCASAR_RADIUS_SITE);
}
if (!is_readable(ALCASAR_RADIUS_MODULE_LDAP)){
	exit($l_no_writing_right_on_file.ALCASAR_RADIUS_MODULE_LDAP);
}

/********************************************************
*		VARIABLES DE FORMULAIRE			*
*********************************************************/

if (isset($_GET['erreur'])&&(!($_GET['erreur']==""))) $erreur = $_GET['erreur']; else $erreur = false;//valeur de $erreur non controlée car ne sert qu'un afficher un msg.
if (isset($_GET['update'])&&($_GET['update']=="ok")) $update = true; else $update = false;

$message = "";
if ((bool)$erreur){ 
	$message = "<div align=\"center\"><br>";
	$message.="<strong><font color=\"red\">".$erreur."</font></strong><br>";
	$message.="<br></div>";
}else{
	if ($update){
		$message = "<div align=\"center\"><br>";
		$message.="<strong><font color=\"green\">$l_ldap_update_sucess</font><br></strong>";
		$message.="<br></div>";
	}
}

/****************************************************************
*			VARIABLES RESULTATS			*
*****************************************************************/
//Création des variables nécessaires
//variables ldap
$ldap_on		= "";
$ldap_server	= ""; 	//IP ou nom DNS du seveur LDAP (ou AD)
						//par défaut : server = "ldap.your.domain"
$ldap_identity	= "";	//nom d'utilisateur qui intérroge le ldap (vide = anonyme)
						//par défaut : # identity = "cn=admin,o=My Org,c=UA"
$ldap_password	= "";	//mot de passe de l'utilisateur intérrogeant le ldap
						//par défaut : # password = mypass
$ldap_basedn	= "";	//DN de base ou l'on recherchera les utilisateurs 
						//par défaut : basedn = "o=My Org,c=UA"
$ldap_filter	= "";	//permet entre autre de déterminer l'attribut utilisé pour la recherche d'un utilisateur dans LDAP
						//attribut uid pour un ldap standard, samaccountname pour AD
						//par défaut : filter = "(uid=%{Stripped-User-Name:-%{User-Name}})"
$ldap_base_filter = "";	//
						//par défaut : # base_filter = "(objectclass=radiusprofile)"

/********************************************************
*		Fichier ALCASAR_RADIUS_SITE						*
*********************************************************/
$site = new siteConfig();
$site->load(ALCASAR_RADIUS_SITE);
$ldap_on = $site->authorize->ldap;

/********************************************************
*		Fichier ALCASAR_RADIUS_MODULE_LDAP				*
*********************************************************/
//Lecture du fichier /etc/raddb/modules/ldap
$ldap = new ldapConfig();
$ldap->load(ALCASAR_RADIUS_MODULE_LDAP);
$ldap_server		= $ldap->host;		// others options only in alcasar 3.x ($ldap->server)
$ldap_identity		= $ldap->identity;
$ldap_password		= $ldap->password;
$ldap_basedn		= $ldap->basedn;
$ldap_filter		= $ldap->uid;		// others options only in alcasar 3.x ($ldap->filter)
$ldap_base_filter	= $ldap->base_filter;

function ldap_test($f_ldap_server, $f_ldap_identity, $f_ldap_password, $f_ldap_port = "389"){
	// Test du serveur
	if (!$sock = @fsockopen($f_ldap_server, $f_ldap_port, $num, $error, 2)) {
		// no network connection
		return -1;
	} else {
		fclose($sock);
		// Connexion au serveur LDAP
		$ldapconn = ldap_connect($f_ldap_server, $f_ldap_port);
		ldap_set_option($ldapconn, LDAP_OPT_TIMELIMIT, 2);
		if ($ldapconn) {
			$ldapbind = ldap_bind($ldapconn, $f_ldap_identity, $f_ldap_password);
			if ($ldapbind) {
				// LDAP Bind success
				ldap_unbind($ldapconn);
				return 1;
			} else {
				// LDAP Bind failed
				return 0;
			}
		} else {
			// LDAP connection failed
			return -2;
		}
	}
}

/********************************
*		TO DO		*
*********************************/
//internationnalisation à mettre en haut du fichier pour internationnaliser les erreurs de script!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><!-- written by steweb57 -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $l_ldap_title; ?></title>
<link rel="stylesheet" href="/css/style.css" type="text/css">
<link rel="stylesheet" href="/css/ldap.css" type="text/css">
<script language="javascript">
function testLdapActif(){
	//List des ID des éléments à désactiver
	var listToDisables = new Array("ldap_server","ldap_dn","ldap_filter","ldap_base_filter","ldap_user","ldap_password");

	if (document.getElementById("auth_enable").value == "1"){
		for (var i=0;i<listToDisables.length;i++){
			document.getElementById(listToDisables[i]).style.backgroundColor ="#ffffff";
			document.getElementById(listToDisables[i]).disabled = false;
		}
	} else {
		for (var i=0;i<listToDisables.length;i++){
			document.getElementById(listToDisables[i]).style.backgroundColor ="#c0c0c0";
			document.getElementById(listToDisables[i]).disabled = true;
		}
	}
}
</script>
</head>
<body onLoad="testLdapActif();">
<table width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><th><?php echo $l_ldap_legend; ?></th></tr>
<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width=1 height=2></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td valign="middle" align="left">
<form name="config_ldap" method="post" action="update_ldap.php">
<fieldset>
<legend>
<?php
echo $message;
$pos = strpos($ldap_server, "//");
if ($pos!==false){
	$new_ldap_server = explode("//",$ldap_server); //pour discriminer le host et le protocole dans la notation "ldap://192.168.182.10" ou "ldaps://monldap.monentreperise.com"
} else {
	$new_ldap_server = $ldap_server;
}
if (($ldap_on == "ldap") && (function_exists('ldap_connect'))){
	echo "<div align='center'><br>";	
	switch(ldap_test($new_ldap_server, $ldap_identity, $ldap_password)){
		case -2:
			echo "<font color='red'>".$l_ldap_test_connection_failed."</font>";
			break;
		case -1:
			echo "<font color='red'>".$l_ldap_test_network_failed."</font>";
			break;
		case 0:
			echo "<font color='red'>".$l_ldap_test_bind_failed."</font>";
			break;
		case 1:
			echo "<font color='green'>".$l_ldap_test_bind_ok."</font>";
		break;
		default:
			echo "LDAP error";
	}
	echo "<br><br></div>"; 
}
?>
</legend>
<dl>
  <dt>
    <label for="auth_enable"><?php echo $l_ldap_auth_enable_label; ?></label>
  </dt>
  <dd>
    <select id="auth_enable" name="auth_enable" onchange="testLdapActif();">
	<?php if ($ldap_on == "ldap") { 
      echo "<option value=\"1\" selected=\"selected\">$l_ldap_YES</option>";
      echo "<option value=\"0\">$l_ldap_NO</option>";	
	}else{
      echo "<option value=\"1\">$l_ldap_YES</option>";
      echo "<option value=\"0\" selected=\"selected\">$l_ldap_NO</option>";
	}?>
    </select>
  </dd>
</dl>
<dl>
  <dt>
    <label for="ldap_server"><?php echo $l_ldap_server_label; ?></label>
    <br>
    <?php echo $l_ldap_server_text; ?></dt>
  <dd>
    <input id="ldap_server" size="40" name="ldap_server" value="<?php echo htmlspecialchars($ldap_server); ?>">
  </dd>
</dl>
<dl>
  <dt>
    <label for="ldap_dn"><?php echo $l_ldap_base_dn_label; ?></label>
    <br>
    <?php echo $l_ldap_base_dn_text; ?></dt>
  <dd>
    <input id="ldap_dn" size="40" name="ldap_base_dn" value="<?php echo htmlspecialchars($ldap_basedn); ?>">
  </dd>
</dl>
<dl>
  <dt>
    <label for="ldap_filter"><?php echo $l_ldap_filter_label; ?></label>
    <br>
    <?php echo $l_ldap_filter_text; ?></dt>
  <dd>
    <input id="ldap_filter" size="40" name="ldap_filter" value="<?php echo htmlspecialchars($ldap_filter); ?>">
  </dd>
</dl>
<dl>
  <dt>
    <label for="ldap_base_filter"><?php echo $l_ldap_base_filter_label; ?></label>
    <br>
    <?php echo $l_ldap_base_filter_text; ?></dt>
  <dd>
    <input id="ldap_base_filter" size="40" name="ldap_base_filter" value="<?php echo htmlspecialchars($ldap_base_filter); ?>">
  </dd>
</dl>
<dl>
  <dt>
    <label for="ldap_user"><?php echo $l_ldap_user_label; ?></label>
    <br>
    <?php echo $l_ldap_user_text; ?></dt>
  <dd>
    <input id="ldap_user" size="40" name="ldap_user" value="<?php echo htmlspecialchars($ldap_identity); ?>">
  </dd>
</dl>
<dl>
  <dt>
    <label for="ldap_password"><?php echo $l_ldap_password_label; ?></label>
    <br>
    <?php echo $l_ldap_password_text; ?></dt>
  <dd>
    <input id="ldap_password" type="password" size="40" name="ldap_password" value="<?php echo htmlspecialchars($ldap_password);?>">
  </dd>
</dl>
<p>
  <input id="submit" type="submit" value="<?php echo $l_ldap_submit; ?>" name="submit">

  <input id="reset" type="reset" value="<?php echo $l_ldap_reset; ?>" name="reset">
</p>

</fieldset>
</form>
<br>
</td></tr>
</table>
</body>
</html>
