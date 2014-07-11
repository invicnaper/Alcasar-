<?php
# $Id: stat.php 1233 2013-10-09 13:21:27Z richard $
	$select[0]="$l_stat_user_day";
	$select[1]="$l_stat_con";
	$select[2]="$l_stat_daily";
	$select[3]="$l_stat_network";
	$select[4]="$l_security";
	$fich[0]="manager/htdocs/user_stats.php";
	$fich[1]="manager/htdocs/accounting.php";
	$fich[2]="manager/htdocs/stats.php";
	$fich[3]="/nfsen";
	$fich[4]="manager/htdocs/security.php";
	$j=0;
	while ($j != count($select))
	{
		echo "<TR><TD valign=\"middle\" align=\"left\">&nbsp;<img src=\"/images/right2.gif\" height=10 width=10 border=no nosave><a href=\"$fich[$j]\" target=\"REXY2\"><font color=\"black\">$select[$j]</font></a></TD></TR>";
		$j++;
	}
?>
