
<?php
	// Par Sylvain Joube,
	// html et css issus d'un "template" de Dreamweaver CS6.
	
	// Ne correspond pas vraiment au modèle MVC (modèle-vue-contrôleur), je reprendrai tous les codes plus tard.
	
	// Index du site
	// Menu pour rechercher un studio (non encore fonctionnel)
	// Interface de connexion et inscription
	// Affichage d'une liste de studios aléatoirement sélectionnés dans la base de données
	// /!\ Je suis conscient que cette page ne respecte pas le modèle MCV (modèle-vue-contrôleur),
	//     je vais peu à peu la modifier pour la rendre plus lisible et rigoureuse.
	
	
	
	/*
	En cours de construction
	*/
	
	session_start();
	include_once("UsefulFunctions.php");
	include_once("functions.php");
	include_once("gestion_connexion_deconnexion.php");
	
	$GLOBALS["PageName"]="index.php"; //index.php
	
?>



<html>
	<head>
	<meta charset="utf-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
	<title>Document sans nom</title>
</head>

<body>

<div class="container">
  <div class="header"><a href="#"><img src="" alt="Logo ici" name="Insert_logo" width="20%" height="90" id="Insert_logo" style="background-color: #8090AB; display:block;" /></a> 
    <!-- end .header --></div>
  <div class="sidebar1">
    <ul class="nav">
		<?php
    		// Affichage du menu de recherche de studios
        	include("menu_gauche_recherche.php");
        ?>
		<li>
			<?php 
				StudioVariables_reset(); // déclarée dans functions.php
            	
				$GS_deconnexionEnCours=false;
            	$GS_connexionEnCours=false;
				
				
				// connexion - déconnexion ici
				// gestion et affectation des variables
				//     $GLOBALS["AfficherTexteDessousInscription"]
				// et  $GLOBALS["AfficherTexteDessousConnexion"]
				
				HandleClientConnection(); // via gestion_connexion_deconnexion.php
				HandleClientRegistration(); // via gestion_connexion_deconnexion.php
				
				
				$userLogged=false;
				
				if (isset($_SESSION["UserEmail"]))
				if ($_SESSION["UserEmail"]!="")
					$userLogged=true; // utilisateur connecté
				
				$neRienAfficherEnDessous=false;
				if ($GS_deconnexionEnCours) {
					$neRienAfficherEnDessous=true;
				}
				
				// Affichage du bouton "se déconnecter" si l'utilisateur est connecté
				// et affichage de la fenêtre de connexion sinon
				
				if ($userLogged) {
					
					$disconnect=form_getPostInput("FC_deconnecter"); // via formulaire
					if ($disconnect==false) {
						$disconnectStr=form_getGetInput("disconnect"); // via lien (méthode GET du coup)
						if ($disconnectStr!=false)
						if ($disconnectStr=="true")
							$disconnect=true;
					}
					
					if ($disconnect) {
						DisconnectUser();
					}
					
					//FG_gestionCompte
					// Aller à l'interface de gestion de compte
					$goGestionDeCompte=form_getPostInput("FG_gestionCompte");
					if ($goGestionDeCompte) {
						// J'aurais aussi très bien pu faire un simple "<a href=... >Aller à la page de gestion de compte</a>))
						echo "Accès à la page de gestion de compte...<br/>";
						echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageCompte"].'\'"/>';
						$GS_deconnexionEnCours=true;
						//exit();
					}
					
					if ($GS_deconnexionEnCours==false)
					if ($GS_connexionEnCours==false) { // affichage uniquement si la page a été actualisée (moche sinon, ça vient après "connexion...")
						
						echo '<span class="messageBienvenue">'; //</li><li> <a href="pok.php">
						echo htmlspecialchars("Connecté en tant que : ")."<br/>->".htmlspecialchars($_SESSION["UserEmail"]).'<br/>';
						echo '</span>';
						// Lien vers la page de gestion de compte (ajout/suppression de studios)
      					echo '</li><li><a href="'.$GLOBALS["PageCompte"].'">-> Gérer votre compte</a></li>';
						// Lien vers sa déconnexion
      					echo '<li><a href="?disconnect=true">'.htmlspecialchars("-> Vous déconnecter").'</a></li><li>'; //'.$GLOBALS["PageName"].'
						
						
						//echo "<br/><br/>";
						
						// Quand-même fonctionnel :
						// Bouton (méthode POST) pour se déconnecter, remplacé par un simple lien (méthode GET), plus esthétique.
						/* bouton (pas beau) vers la déconnexion, remplacé par un lien avec la méthode GET
						echo '
							<form name="Fm_connexion" action="" method="POST" class="SearchBox">
								<input type="submit" name="FC_deconnecter" class="RS_valider" value="'.htmlspecialchars("Se déconnecter").'" />
							</form>
							</li>
						';*/
						$neRienAfficherEnDessous=true;
						
					}
				} else {
					// Utilisateur non connecté
					// Affichage du formulaire de connexion
					// J'aurais, pour plus de lisibilité, pu mettre ça dans un fichier php à part.
					
					echo '
					<form name="Fm_connexion" action="" method="POST" class="SearchBox">
						<span class="darkBlueTextColor">
						<span class="sidebarTitle">Se connecter</span><br/>
						';
						
							form_echoRedTextIfNeeded("Adresse mail", "FC_email", "FC_valider");
							echo "<br/>";
							form_echoInputText("email", "FC_email", "required");
							form_echoRedTextIfNeeded("Mot de passe", "FC_pass", "FC_valider");
							echo "<br/>";
							form_echoInputText("password", "FC_pass", "requdired");
						
						echo '
						<input type="submit" name="FC_valider" class="RS_valider" value="Se connecter" />
						
						<!-- équivaut à (sans php dynamique) :
						Nom de compte (mail)
						<input type="text" name="FC_nom" value="" class="form-control">
						Mot de passe
						<input type="password" name="FC_pass" value="" class="form-control">
						<input type="submit" name="FC_valider" class="RS_valider" value="Se connecter" />
						-->
					  </span>
				  </form>';
					if ($GLOBALS["AfficherTexteDessousConnexion"]!="") {
						echo $GLOBALS["AfficherTexteDessousConnexion"];
					}
				}
				
				if ($GS_deconnexionEnCours==false)
				if ($neRienAfficherEnDessous==false) {
					echo '	  
					</li>
					<li>';
					
					$userLogged=false;
					if (isset($_SESSION["UserEmail"]))
					if ($_SESSION["UserEmail"]!="")
						$userLogged=true; // utilisateur connecté
					
					// Affichage de la fenêtre d'inscription si l'utilisateur est déconnecté et pas en cours de connexion
					// (si l'utilisateur a fait une demande de connexion juste au-dessus, je n'affiche pas la fenêtre d'inscription)
					
					if ( $GS_deconnexionEnCours==false ) // ne rien afficher si la déconnexion est en cours
					if ( $userLogged==false ) {
					echo '
						<form name="Fm_inscription" action="" method="POST" class="SearchBox">
							<span class="darkBlueTextColor">
							<span class="sidebarTitle">'.htmlspecialchars('S\'inscrire').'</span><br/>
							';
							
							form_echoRedTextIfNeeded("Adresse mail", "FI_nom", "FI_valider");
							form_echoInputText("email", "FI_nom", "required");
							//echo "<br/>";
							form_echoRedTextIfNeeded("Mot de passe", "FI_pass", "FI_valider");
							form_echoInputText("password", "FI_pass", "required");
							//echo "<br/>";
							form_echoRedTextIfNeeded("Confirmer le mot de passe", "FI_passDouble", "FI_valider");
							form_echoInputText("password", "FI_passDouble", "required");
							// si validé + mot de passe 1 & 2 remplis ET que pot de passe 1 & 2 ne correspondent pas :
							// afficher "les mdp ne correnpondent pas"
							
						echo'
							<input type="submit" name="FI_valider" class="RS_valider" value="'.htmlspecialchars('Créer un compte').'" />
							</span>
						</form>
						';
						if ($GLOBALS["AfficherTexteDessousInscription"]!="") {
							echo $GLOBALS["AfficherTexteDessousInscription"];
						}
					}
				}
				echo "</li>";
				
			?>
    </ul>
    <!-- end .sidebar1 --></div>
  
  
  <!-- CONTENU -->
  <div class="content">
    <h1>Studios photos</h1>
    <p><strong>Ici</strong> : iste des studios photos, sélectionnés aléatoirement dans la base de données.<br/>
       <strong>Bientôt</strong> : affichage des images des studios, et une bien plus grande presonnalisation des studios.<br/>
       <strong>Puis</strong>, ajout au panier utilisateur d'un studio et réservation d'options sur ce studio, passage au paiement et mise du studio à l'état "réservé" de telle période à telle autre.<br/>
    <br/>
   	<?php
		
		// Affichage (en liste simple, au début) d'une liste de studio photos placés dans la base de données
		
		$DBHandler=DBClick_connect();
		
		if ($DBHandler->TableExists($GLOBALS["TableName_studio"])==false) {
			// Je ne fais pas de requête si la table des studios n'existe pas
			echo htmlspecialchars("ERREUR : Table des studios inexistante dans la base de données. ".$GLOBALS["TableName_studio"]." manquante");
		} else {
			// Table des studios existe dans la base de données, je peux faire la requête
			$query="SELECT * FROM ".$GLOBALS["TableName_studio"]; // plus tard, je ferai une recherche en fonction de la popularité du studio
			$qResult=$DBHandler->MakeQuery($query); // pas besoin de protection contre injection SQL pour cette requête
			
			if ($qResult) {
				$len=$qResult->num_rows;
				// -> pour optimiser beaucoup plus, faire une requête SQL renvoyant le minimum de studios
				// Je récupère les informations des studios
				for ($i=0; $i<$len; $i++) {
					$A2Studio[$i]=$qResult->fetch_array(MYSQLI_BOTH);
				}
				
				// Sélection aléatoire des 10 premiers studios (maximum)
				$newLen=min(10, $len);
				$ajouterUnS="";
				if ($newLen>1)
					$ajouterUnS="s";
				if ($newLen>0)
					echo htmlspecialchars("Affichage de $newLen studio"."$ajouterUnS").".<br/>";
				else // si $newLen==0
					echo htmlspecialchars("Aucun studio à afficher : base de données vide !")."<br/>";
				
				// Je crée une liste $A1AleaNumber avec des index aléatoires de studios
				$firstIteration=true;
				$A1AleaNumber = array();
				for ($i=0; $i<$newLen; $i++) {
					$choosenIndex=rand(0, $len-1); // len-1>=0 puisque $newLen>=1
					if ($firstIteration) {
						$firstIteration=false;
						$A1AleaNumber[$i]=$choosenIndex; // avec $i=0
						continue;
					}
					
					while (ValueIsInArray($A1AleaNumber, $choosenIndex)) {
						$choosenIndex=rand(0, $len-1);
					}
					$A1AleaNumber[$i]=$choosenIndex;
				}
				
				// Affichage de la liste des studios
				$len=count($A1AleaNumber);
				for ($i=0; $i<$len; $i++) {
					$newAleaIndex=$A1AleaNumber[$i];
					$A1Studio=$A2Studio[$newAleaIndex];
					$sId=$A1Studio["id"];
					$sNom=$A1Studio["nom"];
					echo "<a href='afficher_studio.php?id=$sId'>Studio [$sId] - $sNom </a><br/>";
				}
				
			}
		}
		
		if (isset($_SESSION["UserEmail"]))
		if ($_SESSION["UserEmail"]!="") {
			// Si l'utilisteur est connecté, il peut ajouter un studio photo
				
		}
	?>
   	    <!-- end .content --></p>
  </div>

  <div class="footer">
    <p>Blablablabla. Copyright toussa toussa...</p>
    <!-- end .footer --></div>
  <!-- end .container --></div>
</body>
</html>
