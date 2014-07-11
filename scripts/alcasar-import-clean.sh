#!/bin/bash
# $Id: alcasar-import-clean.sh 1056 2013-03-26 21:46:36Z stephane $

# alcasar-import-clean.sh
# by Franck BOUIJOUX
# This script is distributed under the Gnu General Public License (GPL)

# nettoyage des fichiers de mots de passe générés après l'import d'une liste de nom (après 24h).
# delete password files generated during the importation of a names list (after 24 hrs)

DATE=`date +%F`
REP="/tmp"
delay=0

find $REP -mtime +$delay -name '*.pwd' -exec rm -f {} \;

exit 0
