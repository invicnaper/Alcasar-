<?php
//Gestion de la langue
if (is_file("../lib/langues.php"))
	include("../lib/langues.php");
$Login = urlencode($login);
print <<<EOM
<tr valign=top>
<td align=center bgcolor="#FFCC66">
<a href="group_admin.php?login=$Login"><font color="black"><b>$l_members</b></font></a></td>
<td align=center bgcolor="#FFCC66">
<a href="user_edit.php?login=$Login&user_type=group"><font color="black"><b>$l_attributes</b></font></a></td>
<td align=center bgcolor="#FFCC66">
<a href="user_delete.php?login=$Login&user_type=group"><font color="black"><b>$l_remove</b></font></a></td>
</tr>
EOM;
?>
