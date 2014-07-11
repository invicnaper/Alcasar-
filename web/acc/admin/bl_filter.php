<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- written by Rexy -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>ALCASAR DNS filtering</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<?
function form_filter ($form_content)
{
// réencodage iso + format unix + rc fin de ligne (ouf...)
	$list = str_replace("\r\n", "\n", utf8_decode($form_content));
	if (strlen($list) != 0){
		if ($list[strlen($list)-1] != "\n") { $list[strlen($list)]="\n";} ;} ;
	return $list;
}
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_title1="Filtrage de noms de domaine et d'URL";
  $l_dnsfilter_on="Le filtrage de noms de domaine et d'URL est actuellement activé";
  $l_dnsfilter_off="Le filtrage de noms de domaine et d'URL est actuellement désactivé";
  $l_switch_filtering_on="Activer le filtrage";
  $l_switch_filtering_off="Désactiver le filtrage";
  $l_bl="Liste noire";
  $l_wl="Liste blanche";
  $l_list_version="Version de la liste : ";
  $l_bl_categories="Sélectionnez les catégories à filtrer";
  $l_wl_categories="Sélectionnez les catégories à autoriser";
  $l_download_bl="Télécharger la dernière version";
  $l_fingerprint="L'empreinte numérique du fichier téléchargé est : ";
  $l_fingerprint2="Vérifiez-là en suivant ce lien (ligne 'blacklists.tar.gz') : ";
  $l_activate_bl="Activer la nouvelle version";
  $l_reject_bl="Rejeter";
  $l_warning="Temps estimé : une minute.";
  $l_specific_filtering="Filtrage special";
  $l_forbidden_dns="Noms de domaine filtrés";
  $l_forbidden_dns_explain="Entrez un nom de domaine par ligne (exemple : .domaine.org)";
  $l_allowed_dns="Noms de domaine autorisés";
  $l_one_dns="Entrez un nom de domaine par ligne (exemple : .domaine.org)";
  $l_maj_rehabilitated="Noms de domaine ou URLs réhabilités";
  $l_rehabilitated_dns="Noms de domaine réhabilités";
  $l_rehabilitated_dns_explain="Entrez ici des noms de domaine bloqués par la liste noire <BR> que vous souhaitez réhabiliter.";
  $l_add_to_bl="Noms de domaine ou URLs ajoutés à la liste noire";
  $l_add_to_wl="Noms de domaine ou URLs ajoutés à la liste blanche";
  $l_forbidden_url="URL filtrés";
  $l_forbidden_url_explain="Entrez une URL par ligne (exemple : www.domaine.org/perso/index.htm)";
  $l_allowed_url="URL authorisés";
  $l_rehabilitated_url="URL réhabilités";
  $l_rehabilitated_url_explain="Entrez ici des URL bloquées par la liste noire <BR> que vous souhaitez réhabiliter.";
  $l_one_url="Entrez une URL par ligne (exemple : www.domaine.org/perso/index.htm)";
  $l_record="Enregistrer les modifications";
  $l_wait="Une fois validées, 30 secondes sont nécessaires pour traiter vos modifications";
  $l_ip_filtering="Filtrer les URLs contenant une adresse IP au lieu d'un nom de domaine (ex: http://25.56.58.59/index.htm)";
  $l_safe_searching="Activer le contrôle scolaire/parental des moteurs de recherche : google, yahoo, bing, metacrawler et Youtube.";
  $l_safe_youtube="Pour Youtube, entrez votre identifiant ici : "; 
  $l_youtube_id="(<a href='http://www.youtube.com/education_signup' target='cat_help' onclick=window.open('http://www.youtube.com/education_signup','cat_help','width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes') title='Youtube for school'>lien pour créer un identifiant Youtube (Id)</a>)";
}
else {
  $l_title1="Domain names and URL filtering";
  $l_dnsfilter_on="Actually, the Domain name and URL filter is on";
  $l_dnsfilter_off="Actually, the Domain name and URL filter is off";
  $l_switch_filtering_on="Switch the Filter on";
  $l_switch_filtering_off="Switch the Filter off";
  $l_bl="BlackList";
  $l_wl="WhiteList";
  $l_list_version="List version : ";
  $l_bl_categories="Select the categories to filter";
  $l_wl_categories="Select the categories to allow";
  $l_download_bl="Download the last version";
  $l_fingerprint="The digital fingerprint of the downloaded blacklist is : ";
  $l_fingerprint2="Verify it with this link (line 'blacklists.tar.gz') : ";
  $l_activate_bl="Activate the new version";
  $l_reject_bl="Reject";
  $l_warning="Estimated time : one minute.";
  $l_specific_filtering="Specific filtering";
  $l_forbidden_dns="Filtered domain names";
  $l_forbidden_dns_explain="Enter one domain name per row (exemple : .domain.org)";
  $l_allowed_dns="Allowed domain names";
  $l_one_dns="Enter one domain name per row (example : .domain.org)";
  $l_maj_rehabilitated="Domain names or URLs rehabilitated";
  $l_rehabilitated_dns="Rehabilitated domain names";
  $l_rehabilitated_dns_explain="Enter here domain names that are blocked by the blacklist <BR> and you want to rehabilitate.";
  $l_add_to_bl="Domain names or URLs to add to blacklist";
  $l_add_to_wl="Domain names or URLs to add to whitelist";
  $l_forbidden_url="Filtered URL";
  $l_forbidden_url_explain="Enter one URL per row (example : www.domaine.org/perso/index.htm)";
  $l_allowed_url="Allowed URL";
  $l_rehabilitated_url="Rehabilitated URL";
  $l_rehabilitated_url_explain="Enter here URL that are blocked by the blacklist <BR> and you want to rehabilitate.";
  $l_one_url="Enter one URL per row (example : www.domaine.org/perso/index.htm)";
  $l_record="Save changes";
  $l_wait="Once validated, 30 seconds is necessary to compute your modifications";
  $l_ip_filtering="Filtering URLs that contain an IP address instead of a domain name (ie: http://25.56.58.59/index.htm)";
  $l_safe_searching="Enabling school/parental control for the search engines google, yahoo, bing, metacrawler and Youtube."; 
  $l_safe_youtube="For Youtube, enter your ID here : "; 
  $l_youtube_id="(<a href='http://www.youtube.com/education_signup' target='cat_help' onclick=window.open('http://www.youtube.com/education_signup','cat_help','width=800,height=600,toolbar=no,scrollbars=yes,resizable=yes') title='Youtube for school'>link to create a Youtube Id</a>)";
}
$dir_etc="/usr/local/etc/";
$dir_dg="/etc/dansguardian/lists/";
$bl_categories=$dir_etc."alcasar-bl-categories";
$bl_categories_enabled=$dir_etc."alcasar-bl-categories-enabled";
$wl_categories=$dir_etc."alcasar-wl-categories";
$wl_categories_enabled=$dir_etc."alcasar-wl-categories-enabled";
$conf_file=$dir_etc."alcasar.conf";
$dir_blacklist=$dir_dg."blacklist/";
$urlregex_file=$dir_dg."urlregexplist";
$bannedsite_file=$dir_dg."bannedsitelist";
$dir_tmp="/tmp/blacklists";
# default values
if (is_file ($conf_file))
	{
	$tab=file($conf_file);
	if ($tab)
		{
		foreach ($tab as $line)
			{
			$field=explode("=", $line);
			if ($field[0] == "DNS_FILTERING")	{$DNS_FILTERING=trim($field[1]);}
			if ($field[0] == "YOUTUBE_ID")		{$YOUTUBE_ID=trim($field[1]);}
			}
		}
	}
else { echo "$l_error_open_file $conf_file";}
if (isset($_POST['choix'])){ $choix=$_POST['choix']; } else { $choix=""; }
switch ($choix)
{
case 'BL_On' :
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --on");
	$DNS_FILTERING="on";
	break;
case 'BL_Off' :
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --off");
	$DNS_FILTERING="off";
	break;
case 'Download_list' :
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --download");
	break;
case 'Active_list' :
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --adapt");
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --reload");
	break;
case 'Reject_list' :
	unlink ("$dir_tmp/blacklists.tar.gz"); unlink ("$dir_tmp/md5sum");
	break;
case 'MAJ_cat_bl' :
	$tab=file($bl_categories_enabled);	
	if ($tab)
		{
		$pointeur=fopen($bl_categories_enabled, "w+");
		foreach ($_POST as $key => $value)
			{
			if (strstr($key,'chk-'))
				{	
				$line=str_replace('chk-','',$key)."\n";
				fwrite($pointeur,$line);
				}
			}
		fclose($pointeur);
		}
	else {echo "$l_error_open_file $bl_categories_enabled";}
	$fichier=fopen($dir_dg."blacklists/ossi/domains","w+");
	fputs($fichier, form_filter($_POST['OSSI_bl_domains']));
	fclose($fichier);
	unset($_POST['OSSI_bl_domains']);
	$fichier=fopen($dir_dg."exceptionsitelist","w+");
	fputs($fichier, form_filter($_POST['BL_rehabilited_domains']));
	fclose($fichier);
	unset($_POST['BL_rehabilited_domains']);
	$fichier=fopen($dir_dg."blacklists/ossi/urls","w+");
	fputs($fichier, form_filter($_POST['OSSI_bl_urls']));
	fclose($fichier);
	unset($_POST['OSSI_bl_urls']);
	$fichier=fopen($dir_dg."exceptionurllist","w+");
	fputs($fichier, form_filter($_POST['BL_rehabilited_urls']));
	fclose($fichier);
	unset($_POST['BL_rehabilited_urls']);
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --reload");
	break;
case 'MAJ_cat_wl' :
	$tab=file($wl_categories_enabled);	
	if ($tab)
		{
		$pointeur=fopen($wl_categories_enabled, "w+");
		foreach ($_POST as $key => $value)
			{
			if (strstr($key,'chk-'))
				{	
				$line=str_replace('chk-','',$key)."\n";
				fwrite($pointeur,$line);
				}
			}
		fclose($pointeur);
		}
	else {echo "$l_error_open_file $wl_categories_enabled";}
	$fichier=fopen($dir_dg."blacklists/ossi/domains_wl","w+");
	fputs($fichier, form_filter($_POST['OSSI_wl_domains']));
	fclose($fichier);
	unset($_POST['OSSI_wl_domains']);
	$fichier=fopen($dir_dg."blacklists/ossi/urls_wl","w+");
	fputs($fichier, form_filter($_POST['OSSI_wl_urls']));
	fclose($fichier);
	unset($_POST['OSSI_wl_urls']);
	exec ("sudo /usr/local/sbin/alcasar-bl.sh --reload");
	break;
case 'Specific_filtering' :
	$pureip="-pureip_off"; $safesearch="-safesearch_off"; ;
	foreach ($_POST as $key => $value)
	{
		if (strstr($key,'chk-ip')) $pureip="-pureip_on";
		if (strstr($key,'chk-safesearch')) $safesearch="-safesearch_on";
	}
	if ($_POST['Youtube_ID'] == '') { $New_ID="ABCD1234567890abcdef";} // default ID (no action) 
	else {$New_ID=$_POST['Youtube_ID'];}
	file_put_contents($conf_file, str_replace("YOUTUBE_ID=$YOUTUBE_ID", "YOUTUBE_ID=$New_ID", file_get_contents($conf_file)));
	$YOUTUBE_ID=$New_ID;
	exec ("sudo /usr/local/sbin/alcasar-url_filter.sh $safesearch $pureip");
       	break;
}
?>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=0>
<tr><th><?php echo "$l_title1"; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width=1 height=2></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=0>
	<tr><td valign="middle" align="left">
<?php
if ($DNS_FILTERING == "on")
	{
	echo "<CENTER><H3>$l_dnsfilter_on</H3></CENTER>";
 	echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
	echo "<input type=hidden name='choix' value=\"BL_Off\">";
	echo "<input type=submit value=\"$l_switch_filtering_off\">";
}
else
	{
	echo "<CENTER><H3>$l_dnsfilter_off</H3></CENTER>";
 	echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
	echo "<input type=hidden name='choix' value=\"BL_On\">";
	echo "<input type=submit value=\"$l_switch_filtering_on\">";
	}
echo "</FORM>";
echo "</td></tr>";
echo "</TABLE>";
if ($DNS_FILTERING == "on") require ('bl_filter2.php');
?>
</BODY>
</HTML>
