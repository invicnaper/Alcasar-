#!/bin/bash
# $Id: alcasar-dateLog.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-dateLog.sh
# by Franck BOUIJOUX
# This script is distributed under the Gnu General Public License (GPL)

# Permet de remettre les fichiers journaux à la date (time systeme) de leur rotation et archive (05h00)
# Utile lors de restauration système/copie sur le nouveau serveur pour être pris en compte 
# par le script de nettoyage des logs

DIR="/var/Save/logs"
DIR2="/var/log/"
REPS="firewall squid dansguardian httpd"
heurelog="0500"
extension="gz"
#extension=${2:=gz}

function changeDate {
extension="gz"
	fichier=$1
	echo $fichier
	court=`basename $fichier`
	fichierdate=${court%.$extension}
	datelog=${fichierdate#*-}
	touch -t $datelog$heurelog $fichier
	chmod 640 $fichier
	chown root:apache $fichier
}

for file in $( find $DIR $DIR2  \( -name '*.gz' \) -a \( -name '*access*log*.g*' -o -name 'firewall*.g*' -o -name 'ssl*.g*' \)  )
do
	changeDate $file
done

exit 0
