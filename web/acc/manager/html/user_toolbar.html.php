<?php
//Gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
$Login = urlencode($login);
print <<<EOM
<tr valign=top>
<td align=center bgcolor="#FFCC66">
<a href="user_admin.php?login=$Login"><font color="black"><b>$l_status</b></font></a></td>
<td align=center bgcolor="#FFCC66">
<a href="user_edit.php?login=$Login"><font color="black"><b>$l_attributes</b></font></a></td>
<td align=center bgcolor="#FFCC66">
<a href="user_info.php?login=$Login"><font color="black"><b>$l_personal_info</b></font></a></td>
</tr>
<tr valign=top>
<td align=center bgcolor="#FFCC66">
<a href="user_accounting.php?login=$Login"><font color="black"><b>$l_connections</b></font></a></td>
<!--<td align=center bgcolor="#FFCC66">
<a href="badusers.php?login=$Login" title="Show User Unauthorized Actions"><font color="black"><b>BADUSERS</b></font></a></td>
-->
<td align=center bgcolor="#FFCC66">
<a href="user_delete.php?login=$Login"><font color="black"><b>$l_remove</b></font></a></td>
<!--<td align=center bgcolor="#FFCC66">
<a href="user_test.php?login=$Login" title="Test de l'usager"><font color="black"><b>TEST</b></font></a></td>
-->
<td align=center bgcolor="#FFCC66">
<a href="clear_opensessions.php?login=$Login"><font color="black"><b>$l_open_sessions</b></font></a></td>
</tr>
EOM;
?>
