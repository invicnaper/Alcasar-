<tr><td colspan=2 align="center">
<?
echo "$l_static_dhcp_title</td></tr>";
echo "<tr><td align='center' valign='middle'>";
echo "<FORM action='network.php' method='POST'>";
echo "<table cellspacing=2 cellpadding=3 border=1>";
echo "<tr><th>$l_mac_address<th>$l_ip_address<th>$l_mac_del</tr>";
// Read the "ether" file
$line_exist=False;
$tab=file(ETHERS_FILE);
if ($tab)  # le fichier n'est pas vide
	{
	$line_exist=True;
	foreach ($tab as $line)
		{
		$field=explode(" ", $line);
		$mac_addr=$field[0];
		$ip_addr=$field[1];
		echo "<tr><td>$mac_addr";
		echo "<td>$ip_addr";
		echo "<td><input type='checkbox' name='$mac_addr'>";
		echo "</tr>";
		}
	}
echo "</table>";
if ($line_exist)
	{
	echo "<input type='hidden' name='choix' value='del_mac'>";
	echo "<input type='submit' value='$l_apply'>";
	}	
echo "</form></td><td valign='middle' align='center'>";
echo "<FORM action='network.php' method='POST'>";
echo "<table cellspacing=2 cellpadding=3 border=1>";
echo "<tr><th>$l_mac_address<th>$l_ip_address";
?>
<td></td></tr>
<tr><td>exemple : 12-2f-36-a4-df-43</td><td>exemple : 192.168.182.10</td><td></td></tr>
<tr><td><input type='text' name='add_mac' size='17'></td>
<td><input type='text' name='add_ip' size='10'></td>
<input type='hidden' name='choix' value='new_mac'>
<td><input type='submit' value='<?echo"$l_add_to_list";?>'></td>
</tr></table>
</form>
</td></tr>
</table>
