<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- written by Rexy -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>ALCASAR Filter Exceptions</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<?
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_error_open_file="Erreur d'ouverture du fichier";
  $l_exception_IP = "Exception au filtrage";
  $l_exception_txt="Entrez ici les adresses IP des stations du réseau de consultation ne subissant ni filtrage de domaine ni filtrage réseau<BR>Entrez une adresse IP par ligne";
  $l_submit = "Enregistrer";
}
else {
  $l_error_open_file="Error opening the file";
  $l_exception_IP = "Network filtering exceptions";
  $l_exception_txt="Put here the stations IP address that won't be neither domain filtered nor network filtered<BR>Put one IP per row";
  $l_submit = "Submit";
}
$conf_file="/usr/local/etc/alcasar.conf";
if (isset($_POST['choix'])){ 
	switch ($_POST['choix'])
	{
	case 'IP_exceptions' :
		// ISO encode + unix format 
		$ip_list = str_replace("\r\n", "\n", utf8_decode($_POST['exception_list']));
		unset($_POST['exception_list']);
		// write exception IP for Dansguardian (URL filter)
		$fichier=fopen("/etc/dansguardian/lists/exceptioniplist", "w+");
		if (strlen($ip_list) > 7) { fputs($fichier,$ip_list);} //only if not empty
		fclose($fichier);
		// write exception IP for DnsMasq (DNS blackholl)
		$fichier=fopen("/usr/local/etc/alcasar-filter-exceptions", "w+");
		if (strlen($ip_list) > 7) { fputs($fichier, $ip_list);} // only if not empty
		fclose($fichier);
		// test if Dansguardian filter is enabled
		if (is_file ($conf_file))
			{
			$tab=file($conf_file);
			if ($tab)
				{
				foreach ($tab as $line)
					{
					$field=explode("=", $line);
					if ($field[0] == "DNS_FILTERING")	{$DNS_FILTERING=trim($field[1]);}
					if ($field[0] == "PROTOCOLS_FILTERING")	{$PROTOCOLS_FILTERING=trim($field[1]);}
					}
				}
			}
		else { echo "$l_error_open_file $conf_file";}
		if ($DNS_FILTERING == "on")
			{
			exec ("sudo service dansguardian restart");
			}
		if (($DNS_FILTERING == "on")||($PROTOCOLS_FILTERING == "on"))
			{
			exec ("sudo /usr/local/bin/alcasar-iptables.sh"); 
			}
	break;
	}	
}
?>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_exception_IP ;?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td valign="middle" align="left">
<TABLE width=70% border=0>
<?php
echo "<form action='$_SERVER[PHP_SELF]' method='POST'>";
echo " $l_exception_txt";
echo "<BR><textarea name='exception_list' rows=5 cols=40>";
$filename="/usr/local/etc/alcasar-filter-exceptions";
if (file_exists($filename))
	{
	if (filesize($filename) != 0)
		{
		$pointeur=fopen($filename,"r");
		$tampon = fread($pointeur, filesize($filename));
		fclose($pointeur);
		echo $tampon;
		}
	}
	else
	{
	echo "erreur d'ouverture du fichier $filename";
	}
echo "</textarea><BR>";
?>
<input type='hidden' name='choix' value='IP_exceptions'>
<input type='submit' value='<?php echo $l_submit ;?>'></CENTER>
</FORM>
</td></tr>
</TABLE>
</BODY>
</HTML>
