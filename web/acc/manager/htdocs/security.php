<?php
//gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN//2.0">
<HTML>
<!-- written by Crox -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>menu</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><?php echo "$l_spoofing";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
		<tr bgcolor="#666666"><td>
		<DIV style="width:100%;height:150px;overflow-x:hidden;overflow-y:scroll;">
		<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
			<tr><td valign="middle" align="left">
			<?php
			$fichier='/var/Save/logs/security/watchdog.log';
			$pointeur=fopen($fichier,"r");
			if ($pointeur){
				while (!feof($pointeur)){
					$ligne=fgets($pointeur);
						echo "$ligne</br>";
					}
				}
			fclose($pointeur);
			?>
			</td></tr>
		</TABLE>
		</DIV>
	</TABLE>
</TABLE>
</br>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><?php echo "$l_virus";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
		<tr bgcolor="#666666"><td>
		<DIV style="width:100%;height:150px;overflow-x:hidden;overflow-y:scroll;">
		<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
			<tr><td valign="middle" align="left">
			<?php
			$fichier='/var/log/havp/access.log';
			$pointeur=fopen($fichier,"r");
			if ($pointeur){
				while (!feof($pointeur)){
					$ligne=fgets($pointeur);
						echo "$ligne</br>";
					}
				}
			fclose($pointeur);
			?>
			</td></tr>
		</TABLE>
		</DIV>
	</TABLE>
</TABLE>
</br>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><?php echo "$l_fail2ban";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
		<tr bgcolor="#666666"><td>
		<DIV style="width:100%;height:150px;overflow-x:hidden;overflow-y:scroll;">
		<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
			<tr><td valign="middle" align="left">
			<?php
			$fichier='/var/log/fail2ban.log';
			$unban="/Unban/";
			$ban="/Ban/";
			$pointeur=fopen($fichier,"r");
			if ($pointeur){
				while (!feof($pointeur)){
					$ligne=fgets($pointeur);
					if(preg_match($ban,$ligne,$r)){
						echo "$ligne</br>";
					}
					if(preg_match($unban,$ligne,$r)){
						echo " ---> $ligne</br>";
					}
				}
			fclose($pointeur);
			}	
			?>
			</td></tr>
		</TABLE>
		</DIV>
	</TABLE>
</TABLE>
</body>
</HTML>
