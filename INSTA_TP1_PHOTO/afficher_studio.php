<?php
	
	// Page affichant un studio photo dont l'ID est passé via la méthode GET (variable "id")
	// Bientôt : bien plus de champs et possibilité d'uploader des images (plusieurs par studio)
	
	/*
	En cours de construction
	*/
	
	session_start();
	include_once("UsefulFunctions.php");
	include_once("functions.php");
	
	$GLOBALS["PageAccueil"]="index.php";
	$GLOBALS["PageName"]="afficher_studio.php"; //index.php
	$GLOBALS["PageGestionComptePath"]="gestion_compte.php"; //index.php
	

	
?>


<html>
	<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Afficher un studio</title>
</head>

<body>

<div class="container">
  <div class="header"><a href="#"><img src="" alt="Insérer le logo ici" name="Insert_logo" width="20%" height="90" id="Insert_logo" style="background-color: #8090AB; display:block;" /></a> 
    <!-- end .header --></div>
  <div class="sidebar1">
    <ul class="nav">
    	<?php
			// Menus de recherche et de déconnexion/gestion de compte/retour accueil
        	include("menu_gauche_recherche.php");
			$GLOBALS["HasToBeLogged"]=false; // pour l'include suivant "menu_gauche_connecte_retour_accueil.php"
			// Pas besoin d'être connecté pour afficher les studios photo
            include("menu_gauche_connecte_retour_accueil.php");
        ?>
        
    </ul>
    <!-- end .sidebar1 --></div>
	<!-- CONTENU -->
  <div class="content">
  
  
    <h2Blablabla !</h2>
    <!-- <h1>Modifier ce studio</h1> -->
    <p>
    
	<?php
		
		$studioId=form_getGetInput("id");
		
		if ($studioId==false) {
			echo htmlspecialchars("Studio demandé d'ID invalide. Redirection en page d'accueil.")."<br/>";
    		echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageAccueil"].'\'"/>';
			exit();
		}
		
    	$DBHandler=DBClick_connect();
		
		
		$qResult=false;
		if ($DBHandler->TableExists($GLOBALS["TableName_studio"])==false) {
			// Je ne fais pas de requête si la table des studios n'existe pas
			echo htmlspecialchars("ERREUR : Table des studios inexistante dans la base de données. ".$GLOBALS["TableName_studio"]." manquante");
		} else {
			// Table des studios existe dans la base de données, je peux faire la requête
			$queryVal="SELECT * FROM ".$GLOBALS["TableName_studio"]." WHERE id= ? ";
			$dbLink=$DBHandler->dbLink;
			if ($dbLink) {
				$stmt=$dbLink->prepare($queryVal);
				$stmt->bind_param('i', $studioId);
				$stmt->execute();
				$qResult=$stmt->get_result();//$DBHandler->MakeQuery($query);
				$stmt->close();
			}
		}
		
		if ( ($qResult==false) or ($qResult==NULL) ) {
			echo htmlspecialchars("Studio demandé d'ID invalide : introuvable dans la base de données. Redirection en page d'accueil.")."<br/>";
    		echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageAccueil"].'\'"/>';
			exit();
		}
		
		$nbRows=$qResult->num_rows;
		if ($nbRows>1) {
			echo htmlspecialchars("ERREUR : $nbRows > 1 studios trouvés pour cet ID (=$studioId). Merci de signaler cette erreur au dévelppeur du site.")."<br/>";
    		echo "<a href='".$GLOBALS["PageAccueil"]."'>Revenir en page d'accueil</a>";
			//echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageAccueil"].'\'"/>';
			exit();
		}
		
		if ( $nbRows<=0 ) {
			echo htmlspecialchars("Studio demandé d'ID invalide : introuvable dans la base de données. Redirection en page d'accueil. (nbRows = $nbRows)")."<br/>";
    		echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageAccueil"].'\'"/>';
			exit();
		}
		
		$A1StudioValues=$qResult->fetch_array(MYSQLI_BOTH);
		
        $nomStudio=$A1StudioValues["nom"];
        $prixDemiJournee=$A1StudioValues["prix_demi_journee"];
		
		echo htmlspecialchars("Studio : $nomStudio - prix 1/2 journée : $prixDemiJournee €")."<br/>";
		echo "<button onClick='jumpToHome()'>Retour</button>"; //Page(".$GLOBALS["PageAccueil"].")
		// -> /!\ perd les résultats de la précédente recherche (quand elle sera implémentée)
		
		
        
    ?>
    
    
    
	<script>
		<!--  NE PAS UTILISER goBack() : souci avec l'expiration des formulaires de recherche quand ça fait "précédent" -->
        function goBack() {
			window.history.back();
		}
		function jumpToPage(pageURL) {
			window.location=pageURL;
		}
		function jumpToHome() {
			window.location="index.php";
		}
    </script>
    
    </p>
    
    <!-- end .content -->
    </div>

  <div class="footer">
    <p>Blablabla.</p>
    <!-- end .footer --></div>
  <!-- end .container --></div>
</body>
</html>