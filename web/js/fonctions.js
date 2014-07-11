
/* Fonctions JavaScript*/


function password(size,formulaire)
/*Fonction création de mot de passe*/
	{
	var chars='0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ'
	var pass=''
	while(pass.length < size)
	{
		pass+=chars.charAt(Math.round(Math.random() * (chars.length)))
	}
	document.forms[formulaire].passwd.value=pass
	document.forms[formulaire].pwdgene.value=pass
}

function formControl(formulaire){
/*Fonction contrôle du formulaire*/
	var myregex = /[\S]+/gi; //un ou plusieurs caractères non blanc" (tous les caractères sauf espace, retour chariot, tabulation, saut de ligne, saut de page).
	if (myregex.test(document.forms[formulaire].login.value)){
		document.forms[formulaire].create.value=1;
		return true;
	} else {
		alert("Votre identifiant est invalide.");//non internationnalisé
		return false;
	}
}

function temps(selectbox,origine,formulaire) {
	/*
	Fonction qui effectue la conversion en seconde en fonction de l'unité choisi
	La valeur en seconde est écrite à la place de la valeur d'origine et la liste déroulante est replacée sur 's'
	*/
	i = selectbox.options.selectedIndex;
	/*unité correspond à 's' m' ou 'H' */
	unite = selectbox.options[i].value;
	/*multiple est le coéfficient multiplicateur pour obtenir la valeur en secondes*/
	multiple=1;
	if (unite == "m") {
		multiple=60;
		}
	if (unite=="H") {
		multiple=3600;
		}
	if (unite=="J") {
		multiple=86400;
		}
	/*valeur est la valeur en seconde d'origine petite condition pour traiter la valeur vide*/
	valeur = document.forms[formulaire].elements[origine].value;
	if (valeur!='')	valeur = valeur * multiple;
	document.forms[formulaire].elements[origine].value = valeur;
	selectbox.options.selectedIndex=0;
}

function lang_imp(selectbox,formulaire) {
/*Fonction permettant de remplir la valeur de langue d'impression*/
	i = selectbox.options.selectedIndex;
	document.forms[formulaire].langue_imp.value = selectbox.options[i].value;
}
function createTickets(formulaire, msg){
	//var nbtickets = prompt("Saisissez le nombre d'utilisateurs à créer", "");
	var nbtickets = prompt(msg, "");
	// On test la pression sur le boutton "annuler"
	if (nbtickets===null){
		alert('nbtickets===null');
		return false;
	}
	// On test la valeur saisie n'est pas un nombre
	if (isNaN(nbtickets)===true){
		return false;
	}	
	// Conversion en entier de nbtickets 
	nbtickets = parseInt(nbtickets)
	// Configuration et envoie du formulaire
	formulaire.nbtickets.value = nbtickets
	formulaire.action = "vouchers_new.php";
	formulaire.submit();
	
	return true;
}