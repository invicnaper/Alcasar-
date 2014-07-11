<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- Written by Rexy -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>Modif logo organisme</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
<SCRIPT language="javascript" type="text/javascript">
function rafraichissement(cadre1, val1)
{
	eval(cadre1+".location='"+val1+"'");
}
</SCRIPT>	
</HEAD>
<body>
<?php
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_title = "Personnalisation du logo d'organisme";
  $l_current_logo = "Logo actuel";
  $l_logo_select ="S&eacute;lectionnez un nouveau logo";
  $l_logo_help1 = "votre logo doit &ecirc;tre un fichier au format libre 'PNG'";
  $l_logo_help2 = "la taille de ce fichier doit &ecirc;tre inf&eacute;rieure &agrave; 100Ko";
  $l_logo_help3 = "rafra&icirc;chissez les pages de votre navigateur pour voir le r&eacute;sultat";
} else {
  $l_title = "Customizing the agency logo";
  $l_current_logo = "Current logo";
  $l_logo_select ="Select a new logo";
  $l_logo_help1 = "your logo must be in open 'PNG' format";
  $l_logo_help2 = "the file size must be less than 100KB";
  $l_logo_help3 = "refresh your browser in order to see the result";
}

if(isset($_FILES['logo']))
{
unset($result);
$taille_max = 100000;
$destination = '/var/www/html/images/organisme.png';
$extension = strstr($_FILES['logo']['name'], '.'); 
if ($extension != '.png')
	{
	$result = 'Veuillez s&eacute;lectionner un fichier de type png !';
	}
elseif (file_exists($_FILES['logo']['tmp_name']) and filesize($_FILES['logo']['tmp_name']) > $taille_max)
	{
	$result = 'La taille du fichier doit &ecirc;tre inf&eacute;rieur &agrave; 100Ko !';
	}
if (!isset($result))
	{
	move_uploaded_file($_FILES['logo']['tmp_name'], $destination);
	}
}
?>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><? echo "$l_title";?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" 
height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=0 cellspacing=0 cellpadding=1>
	<tr bgcolor="#666666"><td>
	<TABLE width="100%" border=0 cellspacing=0 cellpadding=2>
		<tr><td valign="middle" align="left">
		<CENTER><H3><? echo "$l_current_logo";?> : <img src="/images/organisme.png" width="90"></H3></center><BR>
<? echo "$l_logo_select";?> :
		<FORM action="logo.php" method=POST ENCTYPE="multipart/form-data">
			<input type="file" name="logo">
			<input type="hidden" name="MAX_FILE_SIZE" value="100000">
			<input type="submit" value="Envoyer">
		</FORM>
<?php
if (isset($result))
{
	echo '<H3>'; echo $result; echo '</H3><BR>';
}
?>
	<li><? echo "$l_logo_help1";?>
	<li><? echo "$l_logo_help2";?>
	<li><? echo "$l_logo_help3";?>
		</TD></TR>
	</TABLE>
	</td></tr>
</TABLE>
</BODY>
</HTML>
