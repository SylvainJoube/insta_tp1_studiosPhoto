
<?php
	session_start();
	include_once("UsefulFunctions.php");
	include_once("functions.php");
	
	$GLOBALS["PageName"]="index.php"; //index.php
	$GLOBALS["PageGestionComptePath"]="gestion_compte.php"; //index.php
	
?>


<html>
	<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Document sans nom</title>
</head>

<body>
	<p>
    	Ajout du studio en cours...
        <br/>
        
    
    <?php
		$userLogged=false;
		if (isset($_SESSION["UserEmail"]))
		if (isset($_SESSION["UserPass"]))
		if ($_SESSION["UserEmail"]!="")
		if ($_SESSION["UserPass"]!="") {
			$userLogged=true; // utilisateur connecté
		}
		
		if ($userLogged==false) {
			DisconnectUser();
			exit();
		}
		
		
		// 1) connexion à la base de données
		// 2) Vérification des identifiants de l'utilisateur
		// 3) Ajout d'un nouveau studio photo, ayant pour nom "nouveau studio photo +$IdStudio"
		// 4) Redirection à la page "gestion_compte.php" avec $_SESSION["IdStudioSelectionne"] mis à l'ID du nouveau studio créé
		
		$uEmail=$_SESSION["UserEmail"];
		$uPass=$_SESSION["UserPass"];
		$userIsValid=false;
		$userId=-1;
		$DBHandler=DBClick_checkUser_andReturnDBHandler($uEmail, $uPass, $userIsValid, $userId);
		
		if ($userIsValid==false) {
			$DBHandler=NULL;
			DisconnectUser(); // retour en page d'accueil
			exit();
		}
		
		// Je récupère l'ID de l'utilisateur
		
		
		// Utilisateur connecté, j'ajoute un nouveau studio photo
		
		$studioAjoute=false;
		
		if ($DBHandler->TableExists($GLOBALS["TableName_studio"])==false) {
			// Je ne fais pas de requête si la table des studios n'existe pas
			echo htmlspecialchars("ERREUR : Table des studios inexistante dans la base de données. ".$GLOBALS["TableName_studio"]." manquante");
		} else {
			// Table des studios existe dans la base de données, je peux faire la requête
			if ($userId!=-1) {
				// Ajout du studio
				$studioId=1+$DBHandler->GetMaxValue("id", $GLOBALS["TableName_studio"]);
				echo "Ajout du studio pour l'utilisateur $userId";
				$query="INSERT INTO ".$GLOBALS["TableName_studio"]." (`id`, `owner_id`, `nom`, `prix_demi_journee`)
						VALUES ('$studioId', '$userId', 'Nouveau studio $studioId', '120')";
				echo "<br/> QUERY : ".$query;
				echo "<br/>";
				$qResult=$DBHandler->MakeQuery($query);
				if ($qResult!=false) {
					echo htmlspecialchars("Ajout du studio effectué !");
					$_SESSION["IdStudioSelectionne"]=$studioId;
					$studioAjoute=true;
				} else {
					echo htmlspecialchars("Echec de l'ajout du studio.");
				}
			}
			}
		$DBHandler=NULL;
		// -> redirection vers la page de gestion de compte
		echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageGestionComptePath"].'\'"/>';
		
	?>
    </p>
    
</body>
</html>
