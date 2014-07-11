<?php
//Langue du Ticket d'impression en fonction de la liste déroulante
switch ($langue_imp){
	case 'fr':
		$l_title_imp = "TICKET D'ACCÈS";
		$l_login_imp = "Utilisateur :";
		$l_password_imp = "Mot de passe :";
		$l_max_all_session_imp="Période autorisée :";
		$l_session_timeout_imp="Durée d'une session :";
		$l_max_daily_session_imp="Durée quotidienne :";
		$l_max_monthly_session_imp ="Durée mensuelle :";
		$l_expiration_imp="Date d'expiration :";
		$l_unlimited="Illimitée";
		$l_without="Aucune";
		$l_duplicate="Duplicata";
		$l_explain = "Entrer 'http://alcasar' dans votre navigateur pour gérer votre compte
			(mot de passe, certificat, etc.).
			Entrer 'http://logout' dans votre navigateur pour vous déconnecter.";
		$l_footer_imp = "Généré par ALCASAR";
	break;
	case 'de':
		$l_title_imp = "ZUGANG TICKET";
		$l_login_imp = "Login :";
		$l_password_imp = "Passwort :";
		$l_max_all_session_imp="Autorisierte Zeitraum :";
		$l_session_timeout_imp="Dauer der Sitzung :";
		$l_max_daily_session_imp="Stunden t&auml;glich :";
		$l_max_monthly_session_imp ="monatlich Dauer :";
		$l_expiration_imp="Verfallsdatum :";
		$l_unlimited="Unbegrentz";
		$l_without="Ohne";
		$l_duplicate="Duplikat";
		$l_explain = "Geben Sie 'http://alcasar' in Ihrem Browser, um Ihr Konto zu verwalten (kennwort, zertifikat, etc.).
			Geben Sie 'http://logout' in Ihrem Browser zu trennen.
			";
		$l_footer_imp = "Präsentiert von ALCASAR";
	break;
	case 'nl':
		$l_title_imp = "TOERANG TICKET";
		$l_login_imp = "Gebruikers :";
		$l_password_imp = "Wachtwoord :";
		$l_max_all_session_imp="toegestane duur :";
		$l_session_timeout_imp="Sessieduur :";
		$l_max_daily_session_imp="Dagelijkse uren :";
		$l_max_monthly_session_imp ="Maandelijkse duur :";
		$l_expiration_imp="Vervaldatum :";
		$l_unlimited="Onbeperkte";
		$l_without="Ohne";
		$l_duplicate="Duplicaat";
		$l_explain = "Voer 'http://alcasar' in uw browser om uw account te beheren (wachtwoord, certificaat, etc.).
			  Voer 'http://logout' in uw browser de verbinding te verbreken.";
		$l_footer_imp = "Powered by ALCASAR";
	break;
	case 'es':
		$l_title_imp = "TURISTICA ACCESO";
		$l_login_imp = "Usuario :";
		$l_password_imp = "Contraseña :";
		$l_max_all_session_imp="periodo autorizado :";
		$l_session_timeout_imp="Duración de Sesión :";
		$l_max_daily_session_imp="Duración diario :";
		$l_max_monthly_session_imp ="Duraci&oacute;n mensual :";
		$l_expiration_imp="Fecha de caducidad :";
		$l_unlimited="Ilimitado";
		$l_without="Sin";
		$l_duplicate="Duplicado";
		$l_explain = "Escribe 'http://alcasar' de su navegador para administrar su cuenta (contraseña, certificado, etc.).
			Escribe 'http://logout' de su navegador para desconectar.";
		$l_footer_imp = "Desarrollado por ALCASAR";
	break;
	case 'it':
		$l_title_imp = "TICKET D'ACCESSO";
		$l_login_imp = "Utenti :";
		$l_password_imp = "Password :";
		$l_max_all_session_imp="periodo autorizzato :";
		$l_session_timeout_imp="Durata della sessione :";
		$l_max_daily_session_imp="Durata quotidiano :";
		$l_max_monthly_session_imp ="Durata mensile :";
		$l_expiration_imp="Data di scadenza :";
		$l_unlimited="Illimitato";
		$l_without="Senza";
		$l_duplicate="Duplicato";
		$l_explain = "Inserisci 'http://alcasar' nel tuo browser per gestire il tuo account (password, certificato, ecc).
			Inserisci 'http://logout' nel tuo browser per disconnettersi.";
		$l_footer_imp = "Powered by ALCASAR";
	break;
	case 'pt':
		$l_title_imp = "BILHETE DE ACESSO";
		$l_login_imp = "Usuário :";
		$l_password_imp = "Senha :";
		$l_max_all_session_imp="Período autorizado :";
		$l_session_timeout_imp="duração de uma sessão :";
		$l_max_daily_session_imp="Duração diária :";
		$l_max_monthly_session_imp ="Duração Mensal :";
		$l_expiration_imp="Data de validade :";
		$l_unlimited="Ilimitado";
		$l_without="Sem";
		$l_duplicate="Duplicado";
		$l_explain = "Digite 'http://alcasar' no seu navegador para gerenciar sua conta (senha, certidão, etc).
			Digite 'http://logout' no seu navegador para desligar.";
		$l_footer_imp = "Desenvolvido por ALCASAR";
	break;
	default:
		$l_title_imp = "ACCESS TICKET";
		$l_login_imp = "Login :";
		$l_password_imp = "Password :";
		$l_max_all_session_imp="Authorized period :";
		$l_session_timeout_imp="Session timeout :";
		$l_max_daily_session_imp="Max daily session :";
		$l_max_monthly_session_imp ="Max monthly session :";
		$l_expiration_imp="Expiration date :";
		$l_unlimited="Unlimited";
		$l_without="Without";
		$l_duplicate="Duplicate";
		$l_explain = "Enter 'http://alcasar' in your browser to manage your account (password, certificate, etc.).
			Enter 'http://logout' in your browser to disconnect.";
		$l_footer_imp = "Powered by ALCASAR";
	break;
	}
?>
