<?php

// Fonctions globales, utiles à toutes les pages du site

include_once("UsefulFunctions.php");

$GLOBALS["PageAccueil"]="index.php";
$GLOBALS["PageCompte"]="gestion_compte.php";

// Constantes de la base de données : nom des tables.

// cns_studio : id (int); owner_id (int); nom (text); prix_demi_journee (int)
$GLOBALS["TableName_studio"]="cns_studio";
// cns_studioimage :
$GLOBALS["TableName_studioImage"]="cns_studioimage";
// cns_utilisateur : id (int); email (text); pass (text); admin_rank (int)
$GLOBALS["TableName_utilisateurs"]="cns_utilisateur";


// Informations de connexion à la base de données.

$GLOBALS["DataBase_host"]="localhost";//"sjoubenehydb.mysql.db";//
$GLOBALS["DataBase_user"]="sjoubenehydb";
$GLOBALS["DataBase_name"]="sjoubenehydb";
$GLOBALS["DataBase_pass"]="-masqué-";

// Ancienne config locale
/*$GLOBALS["DataBase_host"]="localhost";
$GLOBALS["DataBase_user"]="root";
$GLOBALS["DataBase_pass"]="";
$GLOBALS["DataBase_name"]="dbclick";*/



// Fonction pour déconnecter un utilisateur (réinitialiser certaines variables de session)
// et rediriger vers la page d'accueil
function DisconnectUser() {
    global $GS_deconnexionEnCours; // variable existant hors de cette fonction
    global $GLOBALS;
    
    $_SESSION["UserEmail"]="";
    $_SESSION["UserPass"]="";
	$_SESSION["IdStudioSelectionne"]=-1;
    echo "Déconnexion...<br/>";
    echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageAccueil"].'\'"/>';
    if (isset($GS_deconnexionEnCours))
		$GS_deconnexionEnCours=true;
}

// Fonction pour se connecter à la base de données,
// Retourne un objet regroupant des fonctions utiles pour la gestion de la base (voir "UsefulFunctions.php" pour plus d'infos)
function DBClick_connect() {
	$DBHandler = new TDBHandler();
	$DBHandler->ConnectTo($GLOBALS["DataBase_host"], $GLOBALS["DataBase_user"], $GLOBALS["DataBase_pass"], $GLOBALS["DataBase_name"]);
	return $DBHandler;
}


// Fonction pour vérifier si l'utilisateur est valide,
// Retourne un objet DBHandler (gestion de la DB) et (via les alias) l'id de l'utilisateur et s'il est valide ou non (si les identifiants passés en paramètre correspondent à un utlisateur dans la DB)
function DBClick_checkUser_andReturnDBHandler($userEmail, $userPass, &$arg_UserIsValid, &$arg_UserId, $disconnectUserIfInvalid) {
	$DBHandler = DBClick_connect(); // connexion à la base
	$arg_UserId=-1;
	$arg_UserIsValid=false;
	$query="SELECT * FROM ".$GLOBALS["TableName_utilisateurs"]." WHERE email=? AND pass=?";
	// Recherche de l'utilisateur (protection contre les injections SQL)
	$qResult=false;
	$dbLink=$DBHandler->dbLink;
	if ($dbLink) {
		$stmt=$dbLink->prepare($query);
		$stmt->bind_param('ss', $userEmail, $userPass);
		$stmt->execute();
		$qResult=$stmt->get_result(); //$DBHandler->MakeQuery($query);
		$stmt->close();
	}
	// Si utilisateur valide (présent dans la DB), je mets à jour les variables passées par référence à cette fonction (alias)
	if ($qResult!=false)
	if ($qResult->num_rows==1) { // 0 aucun utilisateur, 2 ou + : ERREUR (une seule paire "email, pass" autorisée !)
		$A1UserVariables=$qResult->fetch_array(MYSQLI_BOTH); // tableau des variables de cet utilisateur
		$arg_UserId=$A1UserVariables["id"]; // id de l'utilisateur d'email $userEmail et de pass $userPass
		$arg_UserIsValid=true;
	}
	// Déconnexion de l'utlisateur et redirection vers l'accueil si demandé
	if ($arg_UserIsValid==false) {
		echo "DBClick_checkUser_andReturnDBHandler : NON CONNECTE<br/>";
		if ($disconnectUserIfInvalid) {
			echo htmlspecialchars("Vous n'êtes pas connecté.");
			echo "<br/>";
			echo htmlspecialchars("Accès à la page d'accueil...");
			echo "<br/>";
			//$DBHandler=NULL;
			DisconnectUser();
		}
	}
	
	return $DBHandler;
}

// Fonction pour regarder si un utilisateur existe, retourner son Id si c'est le cas, et se déconnecter de la base de données
function DBClick_checkUser_andFreeDBHandler($userEmail, $userPass, &$userId, $disconnectUserIfInvalid = false) {
	$userIsValid=false;
	$DBHandler=DBClick_checkUser_andReturnDBHandler($userEmail, $userPass, $userIsValid, $userId, $disconnectUserIfInvalid);
	$DBHandler=NULL; // destruction de l'objet
	return $userIsValid;
}

// Fonction servant à récupérer les variables d'un studio
// retourne $A1StudioVariables : les variables du studio ayant en id $studioId
function DBClick_getA1Studio($userId, $studioId, $arg_DBHandler = false) {
	if ($arg_DBHandler==false) {
		$DBHandler = DBClick_connect();
	} else {
		$DBHandler=$arg_DBHandler;
	}
	
	$arg_UserId=-1;
	$arg_UserIsValid=false;
	$query="SELECT * FROM ".$GLOBALS["TableName_studio"]." WHERE id=? AND owner_id=?"; // "AND owner_id" est superflu, c'est simplement une vérification de plus
	$qResult=false;
	$dbLink=$DBHandler->dbLink;
	if ($dbLink) { // requête et protection contre les injections SQL
		$stmt=$dbLink->prepare($query);
		$stmt->bind_param('ii', $studioId, $userId);
		$stmt->execute();
		$qResult=$stmt->get_result();
		$stmt->close();
	}
	
	if ($qResult!=false)
	if ($qResult->num_rows==1) { // 0 aucun studio, 2 ou + : ERREUR (id est unique)
		$A1StudioVariables=$qResult->fetch_array(MYSQLI_BOTH); // tableau des variables du studio
		return $A1StudioVariables;
	}
	
	$DBHandler->close();
	$DBHandler=NULL; // déconnexion de la DB
	
	return false; // echec de connexin à la DB ou aucune correspondance (aucun studio de ces $id et $owner_id)
}

// Réinitialiser les variables d'un studio (pour l'affichage du formulaire d'édition)
function StudioVariables_reset() {
	$_SESSION["IdStudioSelectionne"]=-1;
	$_POST["FES_nomStudio"]="";
	$_POST["FES_prixDemiJournee"]="";
	
}

// Fonction affichant (via "echo") tous les studios photo de l'utilisateur d'Id $userId
function DBClick_getStudiosOfUser(&$DBHandler, &$A1StudioId, $userId, $afficherForm = false) {
	// Je ne fais pas de requête si la table des studios n'existe pas
	if ($DBHandler->TableExists($GLOBALS["TableName_studio"])==false) {
		echo htmlspecialchars("ERREUR : Table des studios inexistante dans la base de données. ".$GLOBALS["TableName_studio"]." manquante");
		return false;
	}
	$query="SELECT * FROM ".$GLOBALS["TableName_studio"]." WHERE owner_id=?";
	// Requête et protection contre les injections SQL
	$qResult=false;
	$dbLink=$DBHandler->dbLink;
	
	if ($dbLink) {
		$stmt=$dbLink->prepare($query);
		$stmt->bind_param('i', $userId);
		$stmt->execute();
		$qResult=$stmt->get_result(); //$DBHandler->MakeQuery($query);
		$stmt->close();
	}
	
	//echo $query; débug uniquement
	//echo "<br/>";
	if ($qResult!=false) {
		$tabLength=$qResult->num_rows;
		// $A1StudioId
		//echo "qResult OK - tabLength=$tabLength";
		// Affichage de la liste des studios
		for ($i=0; $i<$tabLength; $i++) {
			// Je récupère et je stocke (dans $A1StudioId) les variables du studio
			$A1Studio=$qResult->fetch_array(MYSQLI_BOTH); // (MYSQLI_ASSOC suffirait)
			$A1StudioId[$i]=$A1Studio["id"];
			if ($afficherForm) {
				//echo $A1Studio["nom"]."  ";
				$nomStudio=$A1Studio["nom"];
				if ($A1Studio["id"]==$_SESSION["IdStudioSelectionne"]) {
					$nomStudio='->'.$A1Studio["nom"].htmlspecialchars(' [en cours d\'édition]');
				}
				// Ne correspond pas vraiment au modèle MVC (modèle-vue-contrôleur), je reprendrai ce code plus tard
				echo '<form name="Fm_affichageStudios" action="" method="POST" class="StudioSearch">'.
					 '<span class="StudioSearchTitle">'.$nomStudio.'</span><br/>'.
					 '<input type="submit" class="RS_valider" name="fm_affichageStudio_valider'."$i".'" value="Modifier ce studio" />'.
					 '</form><br/>';
			}
			//echo "<br/>Possede : id=".$A1Studio["id"]." ownerId=".$A1Studio["owner_id"]." nom=".$A1Studio["nom"]." prixDeJou=".$A1Studio["prix_demie_journee"];
		}
		
		
		// Je regarde si un studio a été sélectionné via un formulaire
		// Si oui, j'affecte une variable de session avec l'id du studio sélectonné
		for ($i=0; $i<$tabLength; $i++) {
			$studioId=$A1StudioId[$i];
			$postVariableName="fm_affichageStudio_valider"."$i";
			if (isset($_POST[$postVariableName])) {
				$_SESSION["IdStudioSelectionne"]=$studioId;
			}
			unset($_POST[$postVariableName]);
		}
		
	} else 
		echo "ERREUR : DBClick_getStudiosOfUser() : qResult==false.<br/>";
}


