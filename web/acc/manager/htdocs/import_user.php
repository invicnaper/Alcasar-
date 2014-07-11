<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML><!-- Written by Rexy, Romero P. & 3abTux -->
<HEAD>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<TITLE>Users import</TITLE>
<link rel="stylesheet" href="/css/style.css" type="text/css">
</HEAD>
<body>
<?php
# Choice of language
$Language = 'en';
if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
  $Langue = explode(",",$_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $Language = strtolower(substr(chop($Langue[0]),0,2)); }
if($Language == 'fr'){
  $l_title = "Import d'usagers";
  $l_database_state = "&Eacute;tat actuel de la base : nombre de groupes =";
  $l_number_of_users = "Nombre d'usagers";
  $l_text_import = "Importer &agrave; partir d'un fichier texte ('.txt')";
  $l_text_import_help = "Ce fichier ne doit contenir que des noms d'usagers &eacute;crits les uns sous les autres.";
  $l_file = "Fichier";
  $l_users_service = "D&eacute;finissez leur service (facultatif)";
  $l_users_group = "D&eacute;finissez leur groupe (conseill&eacute;)";
  $l_send = "Envoyer";
  $l_imported_files = "Fichiers des identifiants/mot_de_passe import&eacute;s durant les derni&egrave;res 24h :";
  $l_db_import = "Importer &agrave; partir d'une sauvegarde de la base d'usagers (format SQL)";
  $l_db_import_help = "Afin de pouvoir imputer les derni&egrave;res traces de connexion, une sauvegarde de la base actuelle sera automatiquement r&eacute;alis&eacute;e.";
  $l_db_reset = "Remise &agrave; z&eacute;ro de la base usagers";
  $l_error_ext_txt = "Erreur! Veuillez s&eacute;lectionner un fichier avec l'extension '.csv' ou '.txt'";
  $l_error_ext_sql = "Erreur! Veuillez s&eacute;lectionner un fichier avec l'extension '.sql'";
  $l_group_empty = "La liste des groupes est vide";
  $l_out_title = "   ---  Accès à Internet via ALCASAR  ---  ";
  $l_out_login = "Nom de connexion :";
  $l_out_passwd = "Mot de passe :";
  $l_out_mind = "Pensez à changer votre mot de passe (lien sur la page d'authentification)";
}
else {
  $l_title = "Users import";
  $l_database_state ="State of the database : number of groups =";
  $l_number_of_users = "Number of users";
  $l_text_import = "Import from a text file ('.txt')";
  $l_text_import_help = "In this file, you must write only the user login one below the other.";
  $l_file = "File";
  $l_users_service = "Define their service (optional)";
  $l_users_group = "Define their group (advisable)";
  $l_send = "Send";
  $l_imported_files = "Logins/passwords file imported during the last 24h :";
  $l_db_import = "Import from a saved users database file (SQL format)";
  $l_db_import_help = "In order to impute the last connections, the actual users database will be automaticly saved.";
  $l_db_reset = "Reset the users database";
  $l_error_ext_txt = "Error! Please select a file with '.txt' or '.csv' extension";
  $l_error_ext_sql = "Error! Please select a file with '.sql' extension";
  $l_group_empty = "The group list is empty";
  $l_out_title = "   ---  Internet access via ALCASAR  ---  ";
  $l_out_login = "Login :";
  $l_out_passwd = "Password :";
  $l_out_mind = "Don't forget to change your password (a link is on the authentication window)";
}
function getImportFileList(){
	$importFile = array();
	if ($handle = opendir('/tmp')) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				$ext = pathinfo($file ,PATHINFO_EXTENSION);
				$name = substr($file, 0, -(strlen($ext)+1)); //Retirer les lettres de l'extension ET le point
				if ($ext=="pwd"){
				$importFile[] = $name;
				}
			}
		}
		closedir($handle);
	}
	return $importFile;
}
function GenPassword($nb_car="8")
	{
 /* generation aléatoire du mot de passe */
	$password = "";
	$chaine  = "aAzZeErRtTyYuUIopP152346897mMLkK";
	$chaine .= "jJhHgGfFdDsSqQwWxXcCvVbBnN152346897";
	while($nb_car != 0)
		{
		$i = rand(0,71);
		$password .= $chaine[$i];
		$nb_car --;
		}
	return $password ;
	}
?>
<TABLE width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><th><? echo "$l_title"; ?></th></tr>
<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</TABLE>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td valign="middle" align="left">
<CENTER><H3> 
<?php
echo "$l_database_state";

$LIBpath = "../lib/";
require('/etc/freeradius-web/config.php');
if (is_file($LIBpath."sql/drivers/$config[sql_type]/functions.php"))
	{
	include_once($LIBpath."sql/drivers/$config[sql_type]/functions.php");
	}
else
	{
	echo "<b>Could not include SQL library</b><br>\n";
	exit();
	}
include_once($LIBpath.'functions.php');
if ($config['sql_use_operators'] == 'true')
	{
	include($LIBpath."operators.php");
	$text = ',op';
	$passwd_op = ",':='";
	}
$link = @da_sql_pconnect($config);
if (isset ($_POST ['choix'])) {	$choix = $_POST ['choix']; }
	else { $choix = ''; }
if ($choix == "raz")
	{
	exec ("sudo /usr/local/sbin/alcasar-mysql.sh --raz");
	}
# un fichier est importé
if(isset($_FILES['import-users']))
	{
	unset($result);
	if (isset ($_POST['service'])) $service = $_POST['service'];
	if (isset ($_POST['groupe'])) $group = $_POST ['groupe'];
	$destination = '/tmp/import_file.txt';
	list($name_file , $extension) = explode("." , $_FILES['import-users']['name']); 
	$extension = strstr($_FILES['import-users']['name'], '.');
	if ($choix == "csv")
//import d'un fichier txt
		{
		if (($extension != '.csv') && ($extension != '.txt')) $result = $l_error_ext_txt;
		else 
			{
			exec ("sudo /usr/local/sbin/alcasar-mysql.sh --dump");
			move_uploaded_file($_FILES['import-users']['tmp_name'], $destination);
			$RS_in   = file ($destination);
			$da_abort=0;
			if ($link)
				{
				if (is_file($LIBpath."crypt/$config[general_encryption_method].php"))
					{
					include($LIBpath."crypt/$config[general_encryption_method].php");			
					$tmpdate = date("Ymd-his");
					$file_out = "/tmp/$tmpdate-$name_file.pwd" ;
					$RS_out = fopen ("$file_out", "wb");
					foreach ($RS_in as $no => $ligne)
						{
						if (substr($ligne,0,3) == pack('CCC',239,187,191)) # remove UTF8-BOM 
							{
								$ligne = substr ($ligne,3);
							}
						$tligne = split(" ",$ligne);
						$login = trim ($tligne[0]);
						$password = trim ($tligne[1]);
						if ($login != '')
							{
							if ($password == "")
								{
								$password = GenPassword();
								}	
							$login = da_sql_escape_string($login);
							$passwd = da_sql_escape_string($passwd);
							$passwd = da_encrypt($password);
/* insertion (login + password) dans la table "radcheck" (si l'usager existe --> changement de mot de passe) */
							$res = @da_sql_query($link,$config,"INSERT INTO $config[sql_check_table] (attribute,value,username $text) VALUES ('$config[sql_password_attribute]','$passwd','$login' $passwd_op);");
							if (!$res || !@da_sql_affected_rows($link,$res,$config))
								{
								echo "<b>Unable to add user $login: " . da_sql_error($link,$config) . "</b><br>\n";
								$da_abort=1;
								}
							else
								{
/* create the user informations file */
								fputs($RS_out,"$l_out_title\r\n\r\n");
								if ($service != "" ) { fputs($RS_out,"Service          : $service\r\n\r\n");}
								fputs($RS_out,"$l_out_login $login   |   $l_out_passwd $password\r\n\r\n");
								fputs($RS_out,"$l_out_mind\r\n\r\n");
								fputs($RS_out,"--------------------------------------------------------------------------------\r\n\r\n");
								}	
/* insertion de l'usager dans la table "userinfo" */
							if ($config[sql_use_user_info_table] == 'true' && !$da_abort)
								{
							$res = @da_sql_query($link,$config, "SELECT username FROM $config[sql_user_info_table] WHERE username = '$login';");
							if ($res)
								{
								if (!@da_sql_num_rows($res,$config))
									{
									$res = @da_sql_query($link,$config,"INSERT INTO $config[sql_user_info_table] (username,department) VALUES ('$login','$service');");
									if (!$res || !@da_sql_affected_rows($link,$res,$config))
										echo "<b>Could not add user information in user info table: " . da_sql_error($link,$config) . "</b><br>\n";
									}	
								else
									echo "<b>User already exists in user info table.</b><br>\n";
								}
							else
								echo "<b>Could not add user information in user info table: " . da_sql_error($link,$config) . "</b><br>\n";
								if ($group != '')
									{
									$group = da_sql_escape_string($group);
									$res = @da_sql_query($link,$config,"SELECT username FROM $config[sql_usergroup_table] WHERE username = '$login' AND groupname = '$group';");
									if ($res)
										{
										if (!@da_sql_num_rows($res,$config))
											{
											$res = @da_sql_query($link,$config,"INSERT INTO $config[sql_usergroup_table] (username,groupname) VALUES ('$login','$group');");
											if (!$res || !@da_sql_affected_rows($link,$res,$config))
												echo "<b>Could not add user to group $group. SQL Error</b><br>\n";
											} # end if 
										else
											echo "<b>User already is a member of group $group</b><br>\n";
										} # end if
									else
										echo "<b>Could not add user to group $group: " . da_sql_error($link,$config) . "</b><br>\n";
									} # end if ($group)
						 		} # end if ($config)		
							} # end if ($login !='')
						} # end foreach
					fclose($RS_out);
					} # end if (is file)
				} # end if (link)
			}
		}
	else if ($choix == "bdd")
//import d'une Bdd
		{
		if ($extension != '.sql') $result = $l_error_ext_sql;
		else 
			{
			exec ("sudo /usr/local/sbin/alcasar-mysql.sh --dump");
			move_uploaded_file($_FILES['import-users']['tmp_name'], $destination);
			exec ("sudo /usr/local/sbin/alcasar-mysql.sh --import $destination");
			}
		}
	}
if ($link)
	{
	$res = @da_sql_query($link,$config,"SELECT GroupName FROM radusergroup GROUP BY GroupName");
	if ($res)
		{
		$nb_group = @da_sql_num_rows($res,$config);
		echo $nb_group;
		}
	}
echo ", $l_number_of_users = ";
if ($link)
	{
	$res = @da_sql_query($link,$config,"SELECT UserName FROM userinfo");
	if ($res)
		{
		$nb_user = @da_sql_num_rows($res,$config);
		echo "$nb_user";
		}
	}
echo "</td></tr><tr><td>";
echo "<TABLE width=\"100%\" border=0 cellspacing=0 cellpadding=1>";
echo "<tr><td valign=\"middle\" align=\"left\" colspan=\"2\">";
echo "<CENTER><H3>$l_text_import</H3></CENTER></td></tr>";
echo "<tr><td valign=\"middle\" align=\"left\">";
echo "$l_text_import_help<br>";
echo "<tr><td valign=\"middle\" align=\"left\">";
echo "<br><FORM action='$_SERVER[PHP_SELF]' method=POST ENCTYPE=\"multipart/form-data\">";
echo "$l_file (.txt) : <input type=\"file\" name=\"import-users\"><br>";
echo "$l_users_service : <input type=\"input\" name=\"service\" value=\"\"><br>";
echo "$l_users_group : ";
require("../lib/defaults.php");
include_once("../lib/$config[general_lib_type]/group_info.php");
if (isset($existing_groups)){
	echo "<select name=\"groupe\">";
	echo "<option value=\"\" selected>";
	foreach ($member_groups as $group)
		echo "<option value=\"$group\">$group\n";
	echo " </select>";
	}
else echo "$l_group_empty";
echo "<br>";
echo "<input type='hidden' name='choix' value='csv'>";
if (($choix == "csv") && isset($result)) echo "<b>".$result."</b><BR>";
echo "<input type=\"submit\" value=\"$l_send\">";
echo "</FORM></td>";
echo "<td>";
$ImportFileList = getImportFileList();
if (count($ImportFileList) > 0){
	echo "$l_imported_files";
	echo "<ul>";
	foreach ( $ImportFileList as $ImportFile ) //on parcours le tableau 
	{
	echo "<li>".$ImportFile." ( <a href=\"import_file.php?file=$ImportFile\">txt</a> - <a href=\"import_file.php?file=$ImportFile&format=pdf\">pdf</a> )</li>";
	} 
	echo "</ul>";
} else {
	echo "<br>";
}
echo "</td></tr></table>";
echo "<tr><td valign=\"middle\" align=\"left\">";
echo "<H3><CENTER>$l_db_import</CENTER></H3>";
echo "$l_db_import_help <br><br>";
echo "<FORM action='$_SERVER[PHP_SELF]' method=POST ENCTYPE=\"multipart/form-data\">";
echo "$l_file (.sql) : <input type=\"file\" name=\"import-users\"><br>";
echo "<input type='hidden' name='choix' value='bdd'>";
if (($choix == "bdd") && isset($result)) echo "<b>".$result."</b><BR>";
echo "<input type=\"submit\" value=\"$l_send\">";
echo "</FORM>";
echo "</td></tr>";
echo "<tr><td valign=\"middle\" align=\"left\">";
echo "<H3><CENTER>$l_db_reset</CENTER></H3>";
echo "$l_db_import_help<br><br>";
echo "<FORM action='$_SERVER[PHP_SELF]' method=POST>";
echo "<input type='hidden' name='choix' value='raz'>";
echo "<input type=\"submit\" value=\"$l_send\">";
echo "</FORM>";
echo "</TD></TR></TABLE>";
?>
</BODY>
</HTML>
