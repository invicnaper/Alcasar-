#! /bin/bash
# $Id: alcasar-mysql.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-mysql.sh
# by Franck BOUIJOUX, Pascal LEVANT and Richard REY
# This script is distributed under the Gnu General Public License (GPL)

# Gestion (sauvegarde / import / RAZ) de la base MySQL 'radius'. Fermeture des sessions de comptabilité ouvertes
# Management of mysql 'radius' database (save / import / RAZ). Close the accounting open sessions

rep_tr="/var/Save/base" 	 	# répertoire d'accueil des sauvegardes
ext="sql"  				# extension des fichiers de sauvegarde
DB_RADIUS="radius"
DB_USER="radius"
radiuspwd="iDTxxBGa"
new="$(date +%G%m%d-%Hh%M)"  		# date et heure des fichiers
fichier="$DB_RADIUS-$new.$ext"		# nom du fichier de sauvegarde


stop_acct ()
{
	date_now=`date "+%F %X"`
	echo "UPDATE radacct SET acctstoptime = '$date_now', acctterminatecause = 'Admin-Reset' WHERE acctstoptime IS NULL" | mysql -u$DB_USER -p$radiuspwd $DB_RADIUS
}
check ()
{
	echo "check (and repair if needed) the database :"
	mysqlcheck --databases $DB_RADIUS -u $DB_USER -p$radiuspwd --auto-repair
}

expire_user ()
{
	del_date=`date +%F`
	MYSQL=`/usr/bin/mysql -u$DB_USER -p$radiuspwd $DB_RADIUS -ss --exec  "SELECT username FROM radcheck WHERE ( DATE_SUB(CURDATE(),INTERVAL 7 DAY) > STR_TO_DATE(value,'%d %M %Y')) AND attribute='Expiration';"`
	for u in $MYSQL
	do
		 /usr/bin/mysql -u$DB_USER -p$radiuspwd $DB_RADIUS --exec "DELETE FROM radusergroup WHERE username = '$u'; DELETE FROM radreply WHERE username = '$u'; DELETE FROM userinfo WHERE UserName = '$u'; DELETE FROM radcheck WHERE username = '$u'"
		if [ $? = 0 ]
		then
			echo "User $u was deleted $del_date" >> /var/log/mysqld/delete_user.log
		else
			echo "Delete User $u : Error $del_date" >> /var/log/mysqld/delete_user.log
		fi
 	 done
}
usage="Usage: alcasar-mysql.sh { -d or --dump } | { -c or --check } | { -i or --import } | { -r or --raz } | { -acct_stop } | [ --expire_user ]"
nb_args=$#
args=$1
if [ $nb_args -eq 0 ]
then
	nb_args=1
	args="-h"
fi
case $args in
	-\? | -h* | --h*)
		echo "$usage"
		exit 0
		;;
	-d | --dump | -dump)	
		[ -d $rep_tr ] || mkdir -p $rep_tr
		if [ -e  $fichier ];
			then rm -f  $fichier 
		fi
		check
		echo "Export the database in file : $fichier"
		mysqldump -u $DB_USER -p$radiuspwd --opt -BcQC  $DB_RADIUS > $rep_tr/$fichier
		echo "End of export $( date "+%Hh %Mmn" )"
		;;
	-c | --check | -check)	
		check
		;;
	-i | --import | -import)
		if [ $nb_args -ne 2 ]
			then
				echo "Enter a SQL file name (.sql)"
			exit 0
		else
			mysql -u $DB_USER -p$radiuspwd < $2
			stop_acct	
		fi
		;;
	-r | --raz | -raz)
		mysqldump -u $DB_USER -p$radiuspwd --opt -BcQC  $DB_RADIUS > $rep_tr/$fichier && \
		mysql -u$DB_USER -p$radiuspwd $DB_RADIUS < /etc/raddb/radiusd-db-vierge.sql
		;;
	-acct_stop)
		stop_acct
		;;
	--expire_user)
		expire_user	
		;;
	*)
		echo "Unknown argument :$1";
		echo "$usage"
		exit 1
		;;
esac
