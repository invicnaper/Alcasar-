<?php
/* written by steweb57 */
require_once("lib/alcasar/freeradius/siteconfig.php");
require_once("lib/alcasar/freeradius/ldapconfig.php");
/********************************************************************
*	CONSTANTES AVEC CHEMINS DES FICHIERS DE CONFIGURATION			*
*********************************************************************/

define ("ALCASAR_RADIUS_SITE", "/etc/raddb/sites-available/alcasar");
define ("ALCASAR_RADIUS_MODULE_LDAP", "/etc/raddb/modules/ldap");
define ("ALCASAR_CONF_FILE", "/usr/local/etc/alcasar.conf");

/********************************************************************
*						FONCTION ERREUR								*
*********************************************************************/

function erreur($er){
header('Location:ldap.php?erreur=$er');
exit();
}

/********************************************************************
*					VARIABLES DE FORMULAIRE							*
*********************************************************************/

//Récupération des variables de formulaire
if (isset($_POST['auth_enable'])) $auth_enable = $_POST['auth_enable']; else erreur('Erreur de variable auth_enable');
if ($auth_enable == "1"){	//test $auth_enable
	if (isset($_POST['ldap_server'])) $ldap_server = $_POST['ldap_server']; else erreur('Erreur de variable ldap_server');
	if (isset($_POST['ldap_base_dn'])) $ldap_base_dn = $_POST['ldap_base_dn']; else erreur('Erreur de variable ldap_base_dn');
	if (isset($_POST['ldap_filter'])) $ldap_filter = $_POST['ldap_filter']; else erreur('Erreur de variable ldap_filter');
	if (isset($_POST['ldap_base_filter'])) $ldap_base_filter = $_POST['ldap_base_filter']; else erreur('Erreur de variable ldap_base_filter');
	if (isset($_POST['ldap_user'])) $ldap_user = $_POST['ldap_user']; else erreur('Erreur de variable ldap_user');
	if (isset($_POST['ldap_password'])) $ldap_password = $_POST['ldap_password']; else erreur('Erreur de variable ldap_password');	
}	//test $auth_enable

/********************************************************************
*				TEST DES FICHIERS DE CONFIGURATION					*
*********************************************************************/

//Test de présence et des droits en modification des fichiers de configuration.
/* texte à internationaliser : pas urgent : débugage uniquement */
if (!file_exists(ALCASAR_RADIUS_SITE)){
	exit("Fichier de configuration du virtual-host 'alcasar' de freeradius non présent");
}
if (!file_exists(ALCASAR_RADIUS_MODULE_LDAP)){
	exit("Fichier de configuration du module ldap pour freeradius non présent");
}
if (!is_writable(ALCASAR_RADIUS_SITE)){
	exit("Vous n'avez pas les droits d'écriture sur le fichier /etc/raddb/sites-available/alcasar");
}
if (!is_writable(ALCASAR_RADIUS_MODULE_LDAP)){
	exit("Vous n'avez pas les droits d'écriture sur le fichier /etc/raddb/modules/ldap");
}

/********************************************************************
*					Fichier ALCASAR_RADIUS_SITE						*
*********************************************************************/
$site = new siteConfig();
$site->load(ALCASAR_RADIUS_SITE);
if ($auth_enable == "1"){	//test $auth_enable
	/*
	ON ACTIVE LE LDAP
	*/
	/*
	Configure autorize section with:
		ldap  { 
			fail=1
		}
	*/
	if ($site->authorize->ldap === false){ // always test before update
		$site->authorize->addSection('ldap');
		$site->authorize->ldap->addPair('fail','1');
	}else{
		if ($site->authorize->ldap->fail === false){ // always test before update
			$site->authorize->ldap->addPair('fail','1');
		}
	}
	/*
	Configure authenticate section with
		Auth-Type LDAP {
			ldap
		}
	*/
	if ($site->authenticate->getSectionInstance('Auth-Type','LDAP')===false){ // always test before update
		$site->authenticate->addSection('Auth-Type', 'LDAP');
		$site->authenticate->getSectionInstance('Auth-Type','LDAP')->addSection('ldap');
	}
} else {
	/*
	ON DESACTIVE LE LDAP
	*/
	if ($site->authorize->ldap !== false){ // always test before update
		$site->authorize->deleteSection("ldap");
	}
	if ($site->authenticate->getSectionInstance('Auth-Type','LDAP')!==false){ // always test before update
		$site->authenticate->deleteSection('Auth-Type','LDAP');
	}
}
//Sauvegarde du /etc/raddb/sites-available/alcasar
$site->save(ALCASAR_RADIUS_SITE);

/********************************************************************
*					Fichier ALCASAR_RADIUS_MODULE_LDAP				*
*********************************************************************/
//on ne modifie ALCASAR_RADIUS_MODULE_LDAP uniquement si l'authentification ldap est active
if ($auth_enable == "1"){	//test $auth_enable
	// chargement de la configuration courante
	$ldap = new ldapConfig();
	$ldap->load(ALCASAR_RADIUS_MODULE_LDAP);
	// mise à jours des données
	//$ldap->server = $ldap_server;
	$ldap->host = $ldap_server;
	$ldap->identity = $ldap_user;
	$ldap->password = $ldap_password;
	$ldap->basedn = $ldap_base_dn;
	//$ldap->filter = $ldap_filter;
	$ldap->uid = $ldap_filter;
	$ldap->base_filter = $ldap_base_filter;
	//sauvegarde du fichier /etc/raddb/modules/ldap
	$ldap->save(ALCASAR_RADIUS_MODULE_LDAP);
}	//test $auth_enable

/****************************************************************
*		Redémarage des service									*
*****************************************************************/

if ($auth_enable == "1"){
	file_put_contents(ALCASAR_CONF_FILE, str_replace('LDAP=off', 'LDAP=on', file_get_contents(ALCASAR_CONF_FILE)));}
else {
	file_put_contents(ALCASAR_CONF_FILE, str_replace('LDAP=on', 'LDAP=off', file_get_contents(ALCASAR_CONF_FILE)));}
exec ("sudo /usr/local/bin/alcasar-iptables.sh");
exec ("sudo service radiusd restart");

/********************************************************************
*			Redirection vers la page de configuration LDAP			*
*********************************************************************/

header('Location:ldap.php?update=ok');
exit();
?>
