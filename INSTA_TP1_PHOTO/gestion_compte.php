
<?php
	session_start();
	include_once("UsefulFunctions.php");
	include_once("functions.php");
	
	$GLOBALS["PageName"]="gestion_compte.php"; //index.php
	$GLOBALS["PageGestionComptePath"]="gestion_compte.php"; //index.php
		
?>





<html>
	<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Document sans nom</title>
</head>

<body>

<div class="container">
  <div class="header"><a href="#"><img src="" alt="Insérer le logo ici" name="Insert_logo" width="20%" height="90" id="Insert_logo" style="background-color: #8090AB; display:block;" /></a> 
    <!-- end .header --></div>
  <div class="sidebar1">
    <ul class="nav">
    	<?php
			//include("menu.php");
		?>
    	<li>
            <span class="sidebarTitle">Gestion de compte</span><br/>
      
   	  	</li>
		<?php
            // Affichage du menu de gauche
            include("menu_gauche_connecte_retour_accueil.php");
        ?>
    </ul>
    <!-- end .sidebar1 --></div>
  
  
  <!-- CONTENU -->
  <div class="content">
  
  
    <h2><a href="ajout_studio.php">-&gt; Ajouter un studio</a></h2>
    <!-- <h1>Modifier ce studio</h1> -->
    <p>
    
   	<?php
		
		/// -------- Affichage et modification du studio photo sélectionné (s'il y en a un) --------
		
		$userLogged=false;
		$validStudio=false;
		$A1StudioValues=false;
		$DBHandler=NULL;
		
		if (isset($_SESSION["UserEmail"]))
		if (isset($_SESSION["UserPass"]))
		if ($_SESSION["UserEmail"]!="")
		if ($_SESSION["UserPass"]!="") {
			// Requête : vérification que l'utilisateur est bien connecté (et que ses identifiants sont valides)
			$userIsValid=false;
			$userId=-1;
			$DBHandler=DBClick_checkUser_andReturnDBHandler($_SESSION["UserEmail"], $_SESSION["UserPass"], $userIsValid, $userId, true);
			
			if ($userIsValid==false) {
				$DBHandler=NULL;
				exit();
			}
			
			$userLogged=true;
			
			// Actualisation du studio sélectionné
			DBClick_getStudiosOfUser($DBHandler, $A1StudioId, $userId, false);
			
			// --- Recherche du studio sélectionné, s'il y en a un ---
			if (isset($_SESSION["IdStudioSelectionne"]))
			if ($_SESSION["IdStudioSelectionne"]!=-1) {
				
				$temp_A1StudioValues=DBClick_getA1Studio($userId, $_SESSION["IdStudioSelectionne"], $DBHandler);
				if ($temp_A1StudioValues==false) {
					$_SESSION["IdStudioSelectionne"]=-1;
				} else {
					$validStudio=true;
					$A1StudioValues=$temp_A1StudioValues;
				}
			}
			
		}
		
		// Mise à jour des informations du studio
		if ($validStudio) {
			//echo "Studio valide !";
			
			$studioWasUpdated=false; // actualiser les variables du studio si mise à jour des informations via le formulaire
			
			// prise en compte de la modification des changements
			if (filter_input(INPUT_POST, "FES_valider")) {
				if ($_POST["FES_nomStudio"]!="")
				if ($_POST["FES_prixDemiJournee"]!="") {
					if ($DBHandler==NULL)
						$DBHandler=DBClick_connect();
					$studioId=$A1StudioValues["id"];
					$nomStudio=$_POST["FES_nomStudio"];
					$prixDemiJournee=$_POST["FES_prixDemiJournee"];
					
					if ($DBHandler->TableExists($GLOBALS["TableName_studio"])==false) {
						echo htmlspecialchars("Table des studio manquante dans la base de données.");
					} else {
						$queryVal="UPDATE `".$GLOBALS["TableName_studio"]."` SET `nom` = ?, `prix_demi_journee` = ? WHERE `cns_studio`.`id` = ? ";
						$dbLink=$DBHandler->dbLink;
						if ($dbLink) {
							$stmt=$dbLink->prepare($queryVal);//$nomStudio, $prixDemiJournee, $studioId
							$stmt->bind_param('sii', $nomStudio, $prixDemiJournee, $studioId);
							$stmt->execute();
							$qSuccess=$stmt->get_result();//$DBHandler->MakeQuery($query);
							$stmt->close();
							$studioWasUpdated=true;
						}
					}
				}
			}
			
			// Si besoin, je récupère les informations du studio, modifiées via le précédent formulaire
			if ($studioWasUpdated) {
				if ($DBHandler==NULL)
					$DBHandler=DBClick_connect();
				$A1StudioValues=DBClick_getA1Studio($userId, $_SESSION["IdStudioSelectionne"], $DBHandler);
			}
			
			
			// Edition des variables du studio
			$_POST["FES_prixDemiJournee"]=$A1StudioValues["prix_demi_journee"];
			$_POST["FES_nomStudio"]=$A1StudioValues["nom"];
			
			echo '
			<h3>Modifier un studio</h3>
			<form name="FEditStudio_connexion" action="" method="POST" class="StudioModificaton">
				';
					form_echoRedTextIfNeeded("Nom du studio", "FES_nomStudio", "FES_valider");
					echo "<br/>";
					form_echoInputText("text", "FES_nomStudio", "required");
					echo "<br/>";
					form_echoRedTextIfNeeded("Prix à la demi journée", "FES_prixDemiJournee", "FES_valider");
					echo "<br/>";
					form_echoInputText("number", "FES_prixDemiJournee", "required");
					echo "<br/>";
				echo '
				<input type="submit" name="FES_valider" class="RS_valider" value="Valider ces changements" />
		  	</form>
			<br/><br/>';
			
			if ( isset($_SESSION["ImgUpload_messageErreur"]) )
			if  ($_SESSION["ImgUpload_messageErreur"]!="" ) {
				echo "Erreur lors de l'upload : ".$_SESSION["ImgUpload_messageErreur"]."<br/>";
				echo "Code erreur : ".$_SESSION["ImgUpload_codeErreurUpload"]." et ".$_SESSION["ImgUpload_codeErreurPerso"]." (perso).";
				
			}
			unset($_SESSION["ImgUpload_messageErreur"]);
			unset($_SESSION["ImgUpload_codeErreurUpload"]);
			unset($_SESSION["ImgUpload_codeErreurPerso"]);
			$_SESSION["ImgUpload_pagePrecedente"]=$GLOBALS["PageName"];
			
			$studioId=$_SESSION["IdStudioSelectionne"];
			
			$_SESSION["ImgUpload_idStudio"]=$studioId;
			
			
			echo '
			<form action="upload_studio_image.php" method="post" enctype="multipart/form-data">
				<label for="fileToUpload">Ajouter une image au studio -> </label>
				<input type="file" name="fileToUpload" id="fileToUpload"> <br/>
				<input type="submit" value="Envoyer !" name="submit" class="UploadPhotoButton">
			</form>';

			$DBHandler==NULL; // dans tous les cas.
			
		} else {
			//echo "Studio invalide.";
		}
		
    	echo "</p>
		<p>&nbsp;</p>
		<h3>Studios possédés :</h3>
		<p>";
		
		
		/// -------- Affichage des studios photo possédés par l'utilisateur --------
		$userLogged=false;
		$validStudio=false;
		$A1StudioValues=false;
		$DBHandler=NULL;
		
		if (isset($_SESSION["UserEmail"]))
		if (isset($_SESSION["UserPass"]))
		if ($_SESSION["UserEmail"]!="")
		if ($_SESSION["UserPass"]!="") {
			// Requête : vérification que l'utilisateur est bien connecté (et que ses identifiants sont valides)
			$userIsValid=false;
			$userId=-1;
			$DBHandler=DBClick_checkUser_andReturnDBHandler($_SESSION["UserEmail"], $_SESSION["UserPass"], $userIsValid, $userId, true);
			
			if ($userIsValid==false) {
				exit(); // déconnexion et redirection faites par la fonction DBClick_checkUser_andReturnDBHandler();
			} else {
				// Affichage de la liste des studios et bouton pour les modifier
				DBClick_getStudiosOfUser($DBHandler, $A1StudioId, $userId, true);
			}
		}
		
	?>
    </p>
    
    <!-- end .content -->
    </div>

  <div class="footer">
    <p>Blablabla.</p>
    <!-- end .footer --></div>
  <!-- end .container --></div>
</body>
</html>
