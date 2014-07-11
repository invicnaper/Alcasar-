<?php
# $Id: system.php 958 2012-07-19 09:01:30Z franck $
	$select[0]=$l_activity;
	$select[1]=$l_network;
	$select[2]=$l_ldap;
	$select[3]="Services";
	$fich[0]="admin/activity.php";
	$fich[1]="admin/network.php";
	$fich[2]="admin/ldap.php";
	$fich[3]="admin/services.php";
	$j=0;
	$nb=count($select);
	while ($j != $nb)
	{
		echo "<TR><TD valign=\"middle\" align=\"left\">&nbsp;&nbsp;<img src=\"/images/right2.gif\" height=10 width=10 border=no nosave><a href=\"$fich[$j]\" target=\"REXY2\"><font color=\"black\">$select[$j]</font></a></TD></TR>";
		$j++;
	}
?>
