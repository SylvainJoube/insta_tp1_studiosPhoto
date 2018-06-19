<?php

// Affichage du menu de gauche, lorsque l'utilisateur est connecté
// Indiqant le nom (mail) de l'utilsiateur
// et avec les liens "déconnexion" et 

// déjà fait sur les scripts qui appellent cette page : session_start();
include_once("UsefulFunctions.php");
include_once("functions.php");

// Par défaut, retour à l'accueil si le client n'est pas connecté
if (isset($GLOBALS["HasToBeLogged"])==false) $GLOBALS["HasToBeLogged"]=true;
	
/// --- Gestion & Affichage du menu de gauche
/// ShowSidebarMenu();
function ShowSidebarMenu() {
	
	// Initialisation de la variable de session "IdStudioSelectionne"
	if (isset($_SESSION["IdStudioSelectionne"])==false)
		$_SESSION["IdStudioSelectionne"]=-1; 
	
	// Je regarde si l'utilisateur est connecté ou non
	$userLogged=false;
	if (isset($_SESSION["UserEmail"]))
	if ($_SESSION["UserEmail"]!="")
		$userLogged=true; // utilisateur connecté
	
	if ($userLogged) {	
		// Affichage du message de bienvenue "Connecté en tant que ...@..."
		echo '<li><span class="messageBienvenue">'.
			 htmlspecialchars("Connecté en tant que : ")."<br/>->".htmlspecialchars($_SESSION["UserEmail"]).
			 '</span></li>';
	}
	
	// Lien vers l'accueil
	echo '<li><a href="'.$GLOBALS["PageAccueil"].'">-> Retrour à l\'Accueil</a></li>';
	
	if ($userLogged) {
		
      	if ($GLOBALS["PageGestionComptePath"]!=$GLOBALS["PageName"])
			echo '</li><li><a href="'.$GLOBALS["PageGestionComptePath"].'">-> Gérer votre compte</a></li>';
		// Lien vers sa déconnexion
		echo '<li><a href="'.$GLOBALS["PageAccueil"].'?disconnect=true">'.htmlspecialchars("-> Vous déconnecter").'</a></li>';
		
	} elseif ($GLOBALS["HasToBeLogged"]==true) {
		// Utilisateur non connecté, retour à la page d'accueil
		echo "Accès à la page d'accueil...<br/>";
		DisconnectUser();
	}
}

// Affichage du menu de gauche
ShowSidebarMenu();
unset($GLOBALS["HasToBeLogged"]);
?>

