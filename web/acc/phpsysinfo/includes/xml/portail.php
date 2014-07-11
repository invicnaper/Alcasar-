<?php 
/***************************************************************************
 *   Copyright (C) 2006 by phpSysInfo - A PHP System Information Script    *
 *   http://phpsysinfo.sourceforge.net/                                    *
 *   addons by 3abtux & Rexy for ALCASAR                                   *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/

// $Id: portail.php 971 2012-08-13 17:16:03Z richard $

// xml_utilisateur()

function request ($texte) {
		$strResult = 0;
		// Déclaration des paramètres de connexion
		$host = "localhost";
		$DB_USER = "radius";
		$DB_RADIUS = "radius";
		$radiuspwd = "hxoGX9sw";
		// Connexion au serveur
		mysql_connect($host, $DB_USER,$radiuspwd) or die("erreur de connexion au serveur");
		mysql_select_db($DB_RADIUS) or die("erreur de connexion a la base de donnees");
		// Creation et envoi de la requete
		if ($texte == 'user') {$query = "SELECT UserName FROM userinfo";}
		else { $query = "SELECT GroupName FROM radusergroup GROUP BY GroupName";}
		$result = mysql_query($query);
		// Recuperation des resultats
		$strResult = mysql_num_rows($result);
		// Deconnexion de la base de donnees
		 mysql_close();
		return $strResult;
  }
function xml_portail () {
	global $sysinfo;
	
	$_text = "  <Portail>\n"
//		. "    <Utilisateur>" . htmlspecialchars( request('user'), ENT_QUOTES ) . "</Utilisateur>\n"
		. "    <Utilisateur>" . "</Utilisateur>\n"
		. "    <Groupe>" . "</Groupe>\n";
//		. "    <Groupe>" . htmlspecialchars( trim( request('group') ), ENT_QUOTES ) . "</Groupe>\n";
	$_text .= "  </Portail>\n";
	
	return $_text;
} 
// Fonction de test de connectivité internet
function internetTest($INSTALLEDVERSION){
        $host = "www.google.com";  # Google Test
        $host2 = "svn.alcasar.net";
        $port = "80";
        //var $num;     //non utilisé
        //var $error;   //non utilisé
	$sock = fsockopen($host, $port, $num, $error, 2);
	if (!$sock){
		return false; # Internet access is down
		}
	else 	{   
		fclose($sock);
		$sock = fsockopen($host2, $port, $num, $error, 2);
		if ($sock){
                	fputs($sock,"GET http://$host2/images/M_images/weblink-$INSTALLEDVERSION.png HTTP/1.0\n\n");
			fclose($sock); }
		return true;
		}
}
// Fonction de test du filtrage
function filtrageTest($file, $search_regex){
	$pointeur = fopen($file,"r");
	$result = false;
	if ($pointeur)
		{
		while (!feof($pointeur))
			{
				$ligne = fgets($pointeur);
				if (preg_match($search_regex, $ligne, $r))
				{
				$result = true;
				break;
				}
			}
		}
	fclose($pointeur);
	return $result;
}
// html_portail()
function html_portail () {
	global $webpath;
	global $XPath;
	global $text;
	exec ("sudo /usr/local/bin/alcasar-watchdog.sh -lt");
	$file_version = "/var/www/html/VERSION";
	$handle = fopen ($file_version, "r");
	$INSTALLEDVERSION = fread ($handle, filesize ($file_version));
	fclose ($handle);
	$VERSIONBL = date ("F d Y", filemtime ('/etc/dansguardian/lists/blacklists/README'));
	$nbr_user = request ('user');
	$nbr_grp  = request ('group');
	$nbr_user_online = exec ("sudo /usr/sbin/chilli_query list | cut -d\" \" -f5 | grep \"1\" | wc -l");
	if (filtrageTest("/usr/local/etc/alcasar.conf", "/^PROTOCOLS_FILTERING=on/")){
		$network_filter_status = $text['enable'];}
	else {	$network_filter_status = $text['disable'];}
	if (filtrageTest("/usr/local/etc/alcasar.conf","/^DNS_FILTERING=on/")){
		$domain_filter_status = $text['enable'];}
	else {	$domain_filter_status = $text['disable'];}
	if (filtrageTest("/usr/local/etc/alcasar.conf","/^WEB_ANTIVIRUS=on/")){
		$web_antivir_status = $text['enable'];}
	else {	$web_antivir_status = $text['disable'];}
	if ((filtrageTest("/var/www/html/index.php","/network_pb = False/")) && (internetTest($INSTALLEDVERSION))){
		$internet_status =  "<img src='/images/state_ok.gif'>".$text['enable'];
		$version = dns_get_record("version.alcasar.net",DNS_TXT);
		$AVAILABLEDVERSION = $version[0]['txt'];
	} else {
		$internet_status =  "<img src='/images/state_error.gif'>".$text['disable'];
		$AVAILABLEDVERSION = "-";
	}
	$_text = "<table border=\"0\" width=\"100%\" align=\"center\">\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['internet_link'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $internet_status . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['portail-version'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $INSTALLEDVERSION . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['portail-disp'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $AVAILABLEDVERSION . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['utilisateur'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $nbr_user_online . " / " . $nbr_user . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['groupe'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $nbr_grp . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['net_filter'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $network_filter_status . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['web_antivirus'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $web_antivir_status . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['domain_filter'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $domain_filter_status . "</font></td>\n"
		. "  </tr>\n"
		. "  <tr>\n"
		. "    <td valign=\"top\"><font size=\"-1\">" . $text['bl-version'] . "</font></td>\n"
		. "    <td><font size=\"-1\">" . $VERSIONBL . "</font></td>\n"
		. "  </tr>\n"
		. "</table>\n";
	return $_text;
} 

function wml_portail () {
	global $XPath;
	global $text;
	
	$_text = "<card id=\"vitals\" title=\"" . $text['vitals']  . "\">\n"
		. "<p>" . $text['hostname'] . ":<br/>\n"
		. "-&nbsp;" . $XPath->getData( "/phpsysinfo/Vitals/Hostname" ) . "</p>\n"
		. "<p>" . $text['ip'] . ":<br/>\n"
		. "-&nbsp;" . $XPath->getData( "/phpsysinfo/Vitals/IPAddr" ) . "</p>\n"
		. "<p>" . $text['kversion'] . ":<br/>\n"
		. "-&nbsp;" . $XPath->getData( "/phpsysinfo/Vitals/Kernel" ) . "</p>\n"
		. "<p>" . $text['uptime'] . ":<br/>\n"
		. "-&nbsp;" . uptime( $XPath->getData( "/phpsysinfo/Vitals/Uptime" ) ) . "</p>\n"
		. "<p>" . $text['users'] . ":<br/>"
		. "-&nbsp;" . $XPath->getData( "/phpsysinfo/Vitals/Users" ) . "</p>\n"
		. "<p>" . $text['loadavg'] . ":<br/>"
		. "-&nbsp;" . $XPath->getData( "/phpsysinfo/Vitals/LoadAvg" ) . "</p>\n"
		. "</card>\n";
	
	return $_text;
}
?>
