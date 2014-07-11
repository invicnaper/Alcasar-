<?
	$select[0]=$l_create_voucher;
	$select[1]=$l_create_user;
	$select[2]=$l_edit_user;
	$select[3]=$l_create_group;
	$select[4]=$l_edit_group;
	$select[5]=$l_import_empty;
	$select[6]="Exceptions";
	$select[7]="$l_activity";
	$fich[0]="manager/htdocs/voucher_new.php";
	$fich[1]="manager/htdocs/user_new.php";
	$fich[2]="manager/htdocs/find.php";
	$fich[3]="manager/htdocs/group_new.php";
	$fich[4]="manager/htdocs/show_groups.php";
	$fich[5]="manager/htdocs/import_user.php";
	$fich[6]="admin/auth_exceptions.php";
	$fich[7]="manager/activity.php";
	$j=0;
	$nb=count($select);
	while ($j != $nb)
	{
		echo "<TR><TD valign=\"middle\" align=\"left\">&nbsp;&nbsp;<img src=\"/images/right2.gif\" height=10 width=10 border=no nosave><a href=\"$fich[$j]\" target=\"REXY2\"><font color=\"black\">$select[$j]</font></a></TD></TR>";
		$j++;
	}
?>
