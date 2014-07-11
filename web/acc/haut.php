<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN//2.0">
<!-- Writen by Rexy -->
<!-- fenetre "haut" -->
<HTML>
<HEAD>
<TITLE>Haut</TITLE>
<!-- Fonctions JavaScript -->
<SCRIPT LANGUAGE="JavaScript">
function ouvrir(page)
	{
	window.open(page, "portail", "alwaysRaised=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=no,copyhistory=no,hotkeys=no,width=640 ,height=480");
	}
</script>
<!-- fin javascript -->
<?php
# $Id: haut.php 958 2012-07-19 09:01:30Z franck $
// Access counter incrementation
$name_fic="compteur.txt";
if (($fp=fopen($name_fic,"r")) == false) exit;
$nb=fgets($fp,10);
fclose($fp);
$nb+=1;
if (($fp=fopen($name_fic,"w")) == false) exit;
fputs($fp, "$nb\n");
fclose($fp);
?>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
	<TD valign="top" align="left"><A HREF=javascript:ouvrir("about.htm")><IMG height="80" border="0" SRC="/images/logo-alcasar.png"f></A></TD>
	<TD valign="top" align="center"><A HREF="http://www.alcasar.net" TARGET="_new"><IMG height="80" border="0" SRC="/images/titre-alcasar.png"></A></TD>
	<TD valign="top" align="right"><A HREF="admin/logo.php" TARGET="REXY2"><IMG height="80" border="0" SRC="/images/organisme.png"></A></TD>
</TABLE>
</BODY>
</HTML>
