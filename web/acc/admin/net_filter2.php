<tr><td valign="middle" align="center">
<form action='net_filter.php' method='POST'>
<table cellspacing=2 cellpadding=3 border=1>
<?
echo "<tr><th>$l_port<th>$l_proto<th>$l_enabled<th>$l_remove</tr>";
// Read and compute the protocols list
$tab=file(SERVICES_LIST);
if ($tab) # the file isn't empty
	{
	foreach ($tab as $line)
		{
		if (trim($line) != '') # the line isn't empty
			{
			$proto=explode(" ", $line);
			$name_svc=trim($proto[0],"#");
			echo "<tr><td>$proto[1]<td>$name_svc";
			echo "<td><input type='checkbox' name='chk-$name_svc'";
			// if the line is commented -> protocol is not allowed
			if (preg_match('/^#/',$line, $r)) {
				echo ">";}
			else {
				echo "checked>";}
			echo "<td>";
			if ($name_svc != "icmp") {
				echo "<input type='checkbox' name='del-$name_svc'>";}
			else {
				echo "&nbsp;";}		
			echo "</tr>";
			}
		}
	}
?>
</table>
<input type='hidden' name='choix' value='change_port'>
<input type='submit' value='<?echo"$l_save";?>'>
</form></td><td valign='middle' align='center'>
<form action='net_filter.php' method='POST'>
<table cellspacing=2 cellpadding=3 border=1>
<tr><th><?echo"$l_port<th>$l_proto"?></tr>
<tr><td><input type='text' name='add_port' size='5'></td>
<td><input type='text' name='add_proto' size='10'></td>
<input type='hidden' name='choix' value='new_port'>
<td><input type='submit' value='<?echo"$l_add_to_list";?>'></td>
</tr></table>
</form>
</td></tr>
</TABLE>
</BODY>
</HTML>
