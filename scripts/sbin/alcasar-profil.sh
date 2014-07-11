#/bin/bash
# $Id: alcasar-profil.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-profil.sh
# by Richard REY
# This script is distributed under the Gnu General Public License (GPL)

# Gestion des comptes liés aux profiles
# Manage the profil logins

ADM_PROFIL="admin"
PROFILS="backup manager"
ALL_PROFILS=`echo $ADM_PROFIL $PROFILS`
DIR_KEY="/usr/local/etc/digest"
SED="/bin/sed -i"
HOSTNAME=`uname -n`

# liste les comptes de chaque profile
function list () {
	for i in $ALL_PROFILS
	do
	echo "Comptes liés au profil '$i' :"
	cat $DIR_KEY/key_only_$i | cut -d':' -f1|sort
	done
}
# ajoute les comptes du profil "admin" aux autres profils
# crée le fichier de clés contenant tous les compte (pour l'accès au centre de gestion)
function concat () {
	> $DIR_KEY/key_all
	for i in $PROFILS
	do
		cp -f $DIR_KEY/key_only_$ADM_PROFIL $DIR_KEY/key_$i
		cat $DIR_KEY/key_only_$i >> $DIR_KEY/key_$i
		cat $DIR_KEY/key_only_$i >> $DIR_KEY/key_all
	done
	cp -f $DIR_KEY/key_only_$ADM_PROFIL $DIR_KEY/key_$ADM_PROFIL
	cat $DIR_KEY/key_only_$ADM_PROFIL >> $DIR_KEY/key_all
	chown -R root:apache $DIR_KEY
	chmod 640 $DIR_KEY/key_*
}

usage="Usage: alcasar-profil.sh --list | --add | --del | --pass"
nb_args=$#
args=$1

# on met en place la structure minimale
if [ ! -e $DIR_KEY/key_$ADM_PROFIL ]
then
	touch $DIR_KEY/key_$ADM_PROFIL
fi
cp -f $DIR_KEY/key_$ADM_PROFIL $DIR_KEY/key_only_$ADM_PROFIL
for i in $PROFILS
do
	if [ ! -e $DIR_KEY/key_only_$i ]
	then
		touch $DIR_KEY/key_only_$i
	fi
done
concat
if [ $nb_args -eq 0 ]
then
	echo $usage
	exit 0
fi
case $args in
	-\? | -h* | --h*)
		echo "$usage"
		exit 0
		;;
	--add|-add)	
		# ajout d'un compte
		list
		echo -n "Choisissez un profil ($ALL_PROFILS) : "
		read profil
		echo -n "Entrez le nom du compte à créer (profil '$profil') : "
		read account
		# on teste s'il n'existe pas déjà
		for i in $ALL_PROFILS
		do
			tmp_account=`cat $DIR_KEY/key_only_$i | cut -d':' -f1`
			for j in $tmp_account
				do
				if [ "$j" = "$account" ]
					then echo "Ce compte existe déjà"
					exit 0
				fi
				done
		done
		/usr/sbin/htdigest $DIR_KEY/key_only_$profil $HOSTNAME $account
		concat
		list
		;;
	--del|-del)
		# suppression d'un compte
		list
		echo -n "entrez le nom du compte à supprimer : "
		read account
		for i in $ALL_PROFILS
			do
			$SED "/^$account:/d" $DIR_KEY/key_only_$i
			done
		concat
		list
		;;
	--pass|-pass)
		# changement du mot de passe d'un compte
		list
		echo "Changement de mot de passe"
		echo -n "Entrez le nom du compte : "
		read account
		for i in $ALL_PROFILS
		do
			tmp_account=`cat $DIR_KEY/key_only_$i | cut -d':' -f1`
			for j in $tmp_account
				do
				if [ "$j" = "$account" ]
					then
					/usr/sbin/htdigest $DIR_KEY/key_only_$i $HOSTNAME $account
				fi
				done
		done
		concat
		;;
	--list|-list)
		# liste des comptes par profile
		list
		;;
	*)
		echo "Argument inconnu :$1";
		echo "$usage"
		exit 1
		;;
esac
