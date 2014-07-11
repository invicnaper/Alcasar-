<?php
function da_encrypt()
{
	$numargs=func_num_args();
	$passwd=func_get_arg(0);
	# calcul d'un salt pour forcer le chiffrement en MD5 au lieu de blowfish par defaut dans php version mdva > 2007.1
	$salt='$1$passwd$';
	if ($numargs == 2){
		$salt=func_get_arg(1);
		return crypt($passwd,$salt);
	}
        return crypt($passwd,$salt);
}
?>
