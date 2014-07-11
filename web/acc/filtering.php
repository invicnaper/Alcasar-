<?
	$select[0]="$l_blacklist";
	$select[1]=$l_network;
	$select[2]="Exceptions";
	$fich[0]="admin/bl_filter.php";
	$fich[1]="admin/net_filter.php";
	$fich[2]="admin/filter_exceptions.php";
	$j=0;
	$nb=count($select);
	while ($j != $nb)
	{
		echo "<TR><TD valign=\"middle\" align=\"left\">&nbsp;<img src=\"/images/right2.gif\" height=10 width=10 border=no nosave><a href=\"$fich[$j]\" target=\"REXY2\"><font color=\"black\">$select[$j]</font></a></TD></TR>";
		$j++;
	}
?>
