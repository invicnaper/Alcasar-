<?php
function echo_file ($filename)
	{
	if (file_exists($filename))
		{
		if (filesize($filename) != 0)
			{
			$pointeur=fopen($filename,"r");
			$tampon = fread($pointeur, filesize($filename));
			fclose($pointeur);
			echo $tampon;
			}
		}
	else
		{
		echo "$filename doesn't exist";
		}
	}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th>
	<?php echo $l_list_version; echo date ("F d Y", filemtime ('/etc/dansguardian/lists/blacklists/README'));?>
	</th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td valign="middle" align="left" colspan=10>
<FORM action='bl_filter.php' method=POST>
<?php
if ((file_exists("$dir_tmp/blacklists.tar.gz")) && (file_exists("$dir_tmp/md5sum")))
	{
	echo "$l_fingerprint"; echo_file ("$dir_tmp/md5sum");
	echo "<br>$l_fingerprint2<a href='http://dsi.ut-capitole.fr/blacklists/download/MD5SUM.LST' target='cat_help' onclick=window.open('http://dsi.ut-capitole.fr/blacklists/download/MD5SUM.LST','cat_help','width=600,height=150,toolbar=no,scrollbars=yes,resizable=yes') title='verify fingerprint'>dsi.ut-capitole.fr/blacklists/download/MD5SUM.LST</a><br>";
	echo "<input type='hidden' name='choix' value='Active_list'>";
	echo "<input type='submit' value='$l_activate_bl'> ($l_warning)</FORM>";
	echo "<FORM action='bl_filter.php' method=POST>";
	echo "<input type='hidden' name='choix' value='Reject_list'>";
	echo "<input type='submit' value='$l_reject_bl'></form>";
	}
else
	{
	echo "<input type='hidden' name='choix' value='Download_list'>";
	echo "<input type='submit' value='$l_download_bl'>";
	echo " ($l_warning)";
	}
?>
</FORM>
</td></tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_bl; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<table width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td valign="middle" align="left" colspan=10>
<FORM action='bl_filter.php' method=POST>
<input type='hidden' name='choix' value='MAJ_cat_bl'>
<?php
echo "<center>$l_bl_categories</center></td></tr>";
//on lit et on interprete le fichier de catégories
$cols=1; 
if (file_exists($bl_categories))
	{
	$pointeur=fopen($bl_categories,"r");
	while (!feof ($pointeur))
		{
		$ligne=fgets($pointeur, 4096);
		if ($ligne)
			{
			if ($cols == 1) { echo "<tr>";}
			$categorie=trim(basename($ligne));
			echo "<td><a href='bl_categories_help.php?cat=$categorie' target='cat_help' onclick=window.open('bl_categories_help.php','cat_help','width=600,height=150,toolbar=no,scrollbars=no,resizable=yes') title='categories help page'>$categorie</a><br>";
			echo "<input type='checkbox' name='chk-$categorie'";
			// si la ligne est commentée -> categorie non selectionnée
			if (preg_match('/^#/',$ligne, $r)) { echo ">";}
			else { echo "checked>"; }
			echo "</td>";
			$cols++;
			if ($cols > 10) {
				echo "</tr>";
				$cols=1; }
			}
		}
	fclose($pointeur);
	}
else	{
	echo "$l_error_open_file $bl_categories";
	}
echo "</td></tr>";
echo "<tr><td valign='middle' align='left' colspan=10>";
echo "<center><b>$l_maj_rehabilitated</b></center></td></tr>";
echo "<tr><td colspan=5 align=center>";
echo "<H3>$l_rehabilitated_dns</H3>$l_rehabilitated_dns_explain<BR>$l_one_dns<BR>";
echo "<textarea name='BL_rehabilited_domains' rows=3 cols=40>";
echo_file ($dir_dg."exceptionsitelist");
echo "</textarea></td>";
echo "<td colspan=5 align=center>";
echo "<H3>$l_rehabilitated_url</H3>$l_rehabilitated_url_explain<BR>$l_one_url<BR>";
echo "<textarea name='BL_rehabilited_urls' rows=3 cols=40>";
echo_file ($dir_dg."exceptionurllist");
echo "</textarea></td></tr><tr><td colspan=10>";
echo "<tr><td valign='middle' align='left' colspan=10>";
echo "<center><b>$l_add_to_bl</b></center></td></tr>";
echo "<tr><td colspan=5 align=center>";
echo "<H3>$l_forbidden_dns</H3>$l_forbidden_dns_explain<BR>";
echo "<textarea name='OSSI_bl_domains' rows=3 cols=40>";
echo_file ($dir_dg."blacklists/ossi/domains");
echo "</textarea></td>";
echo "<td colspan=5 align=center>";
echo "<H3>$l_forbidden_url</H3>$l_forbidden_url_explain<BR>";
echo "<textarea name='OSSI_bl_urls' rows=3 cols=40>";
echo_file ($dir_dg."blacklists/ossi/urls");
echo "</textarea></td></tr><tr><td colspan=10>";
echo "<input type='submit' value='$l_record'>";
echo "</form> ($l_wait)";
?>
</td></tr>
</TABLE>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_wl; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
<tr><td valign="middle" align="left" colspan=10>
<FORM action='bl_filter.php' method=POST>
<input type='hidden' name='choix' value='MAJ_cat_wl'>
<?php
echo "<center>$l_wl_categories</center></td></tr>";
//on lit et on interprete le fichier de catégories
$cols=1; 
if (file_exists($wl_categories))
	{
	$pointeur=fopen($wl_categories,"r");
	while (!feof ($pointeur))
		{
		$ligne=fgets($pointeur, 4096);
		if ($ligne)
			{
			if ($cols == 1) { echo "<tr>";}
			$categorie=trim(basename($ligne));
			echo "<td><a href='bl_categories_help.php?cat=$categorie' target='cat_help' onclick=window.open('bl_categories_help.php','cat_help','width=600,height=150,toolbar=no,scrollbars=no,resizable=yes') title='categories help page'>$categorie</a><br>";
			echo "<input type='checkbox' name='chk-$categorie'";
			// si la ligne est commentée -> categorie non selectionnée
			if (preg_match('/^#/',$ligne, $r)) { echo ">";}
			else { echo "checked>"; }
			echo "</td>";
			$cols++;
			if ($cols > 10) {
				echo "</tr>";
				$cols=1; }
			}
		}
	fclose($pointeur);
	}
else	{
	echo "$l_error_open_file $wl_categories";
	}
echo "<tr><td valign='middle' align='left' colspan=10>";
echo "<center><b>$l_add_to_wl</b></center></td></tr>";
echo "<tr><td colspan=5 align=center>";
echo "<H3>$l_allowed_dns</H3>$l_forbidden_dns_explain<BR>";
echo "<textarea name='OSSI_wl_domains' rows=3 cols=40>";
echo_file ($dir_dg."blacklists/ossi/domains_wl");
echo "</textarea></td>";
echo "<td colspan=5 align=center>";
echo "<H3>$l_allowed_url</H3>$l_forbidden_url_explain<BR>";
echo "<textarea name='OSSI_wl_urls' rows=3 cols=40>";
echo_file ($dir_dg."blacklists/ossi/urls_wl");
echo "</textarea></td></tr><tr><td colspan=10>";
echo "<input type='submit' value='$l_record' disabled>";
echo "</form> (Please wait for the next vesion of ALCASAR)";
?>
</td></tr>
</TABLE>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><th><?php echo $l_specific_filtering; ?></th></tr>
	<tr bgcolor="#FFCC66"><td><img src="/images/pix.gif" width="1" height="2"></td></tr>
</table>
<TABLE width="100%" border=1 cellspacing=0 cellpadding=1>
<FORM action='bl_filter.php' method='POST'>
<input type='hidden' name='choix' value='Specific_filtering'>
<tr><td>
<input type='checkbox' name='chk-ip'
<?php
// verify "pureip" filtering state
if (file_exists($bannedsite_file))
	{
	$pointeur=fopen($bannedsite_file,"r");
	while (!feof ($pointeur))
		{
		$ligne=fgets($pointeur, 4096);
		if ($ligne)
			{
			if (preg_match('/^\*ip$/',$ligne, $r)) 
				{
				echo " checked";
				break;
				}
			}
		}
	fclose($pointeur);
	}
else	{
	echo "$l_error_open_file $bannedsite_file";
	}
echo "> $l_ip_filtering";
?>
</td></tr>
<tr><td>
<input type='checkbox' name='chk-safesearch'
<?php
// verify "safesearch" filtering state
if (file_exists($urlregex_file))
	{
	$pointeur=fopen($urlregex_file,"r");
	while (!feof ($pointeur))
		{
		$ligne=fgets($pointeur, 4096);
		if ($ligne)
			{
			if (preg_match('/^\"\(\^http\:\/\/\[0\-9a\-z\]\+\\\.google/',$ligne, $r))
				{
				echo " checked";
				break;
				}
			}
		}
	fclose($pointeur);
	}
else	{
	echo "$l_error_open_file $urlregex_file";
	}
echo "> $l_safe_searching";
echo "<br>$l_safe_youtube";
echo "<input type='text' name='Youtube_ID' size='30' value='";
if ($YOUTUBE_ID == "ABCD1234567890abcdef") // generic ID (do nothing)
	{
	echo "'>";
	}
else {
	echo "$YOUTUBE_ID'>";
	} 
echo " $l_youtube_id<tr><td>";
echo "<input type='submit' value='$l_record'>";
?>
</form> 
</td></tr>
</TABLE>
