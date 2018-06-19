
<?php
	// déjà appelé dans la page qui appelle ce script : session_start();
	include_once("UsefulFunctions.php");
	include_once("functions.php");
	// Ce script gère la connexion et l'inscription du client
	// Bientôt, il prendra aussi en charge la déconneixon
	
	// ------------ Gestion de la demande de connexion du client
	
	function HandleClientConnection() {
		global $GS_deconnexionEnCours;
		global $GS_connexionEnCours;
		
		// afficher ce texte après la boîte de connexion, pas avant :
		$GLOBALS["AfficherTexteDessousConnexion"]=""; 
		
		// gestion php de la connection du client via formulaire
		if ( $GS_deconnexionEnCours==false )
		if ( form_getPostInput("FC_valider")!=false ) {
			$_SESSION["UserEmail"]=""; // réinitialisation de la session de connexion du client
			$_SESSION["UserPass"]="";
			$_SESSION["UserAdminRank"]=0;
			
			$email=form_getPostInput("FC_email");
			$pass=form_getPostInput("FC_pass");
			unset($_POST["FC_email"]);
			unset($_POST["FC_pass"]);
			
			if ( ($email==false) or ($pass==false) ) { // champs invalides, affichage d'un message d'erreur
				$GLOBALS["AfficherTexteDessousConnexion"]="<br/><span class='redText'>[local] Email ou mot de passe invalides.</span>";
			} else { // champs apparemment valides, tentative de connexion
				// Connexion à la base de donnée (fonction déclarée dans functions.php)
				$DBHandler=DBClick_connect();
				$query="SELECT * FROM ".$GLOBALS["TableName_utilisateurs"]." WHERE email=? AND pass=?";
				
				// Je regarde si l'ulisateur est valide (correspondance email et mot de passe
				// Requête avec protection contre les injections SQL
				$qResult=false;
				$dbLink=$DBHandler->dbLink;
				if ($dbLink) {
					$stmt=$dbLink->prepare($query);
					if ($stmt) {
						$stmt->bind_param('ss', $email, $pass);
						$stmt->execute();
						$qResult=$stmt->get_result(); //$DBHandler->MakeQuery($query);
						$stmt->close();
					}
				}
				if ($qResult==false) { // requête échouée, peut-être echec de connexion à la base de données
					$GLOBALS["AfficherTexteDessousConnexion"]="<span class='redText'>".htmlspecialchars("[serv] Echec de la requête.")."</span>";
				} else {
					if (mysqli_num_rows($qResult)==0) { // aucune correspondance
						$GLOBALS["AfficherTexteDessousConnexion"]="<span class='redText'>[serv] Email ou mot de passe invalides.</span>";
					} else { // (au moins) une correspondance
						$_SESSION["UserEmail"]=$email;
						$_SESSION["UserPass"]=$pass;
						echo "Connexion...";
						// pas besoin d'utiliser $GLOBALS["AfficherTexteDessousConnexion"] (j'utilise juste echo ici)
						//   -> $GS_connexionEnCours=true, ce qui fait que les fenêtre de connexion et d'inscription ne seront pas affichées.
						echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageName"].'\'"/>';
						$GS_connexionEnCours=true;
						//exit();
					}
				}
			}
			$DBHandler=NULL;
		}
	}
	
	function HandleClientRegistration() {
		global $GS_deconnexionEnCours;
		global $GS_connexionEnCours;
		
		
		// Je vérifie que le client n'est pas connecté
		$userLogged=false;
		if (isset($_SESSION["UserEmail"]))
		if ($_SESSION["UserEmail"]!="")
			$userLogged=true; // utilisateur connecté
		
		$GLOBALS["AfficherTexteDessousInscription"]=""; // afficher ce texte après la boîte d'inscription, pas avant
		
		// ------------ Gestion de la demande d'inscription du client (PHP)
		if ( $GS_deconnexionEnCours==false )
		if ( $userLogged==false )
		if ( form_getPostInput("FI_valider")!=false ) {
			$_SESSION["UserEmail"]="";
			$_SESSION["UserPass"]="";
			$_SESSION["UserAdminRank"]=0;
			
			$email=form_getPostInput("FI_nom");
			$pass=form_getPostInput("FI_pass");
			$passDouble=form_getPostInput("FI_passDouble");
			unset($_POST["FI_nom"]);
			unset($_POST["FI_pass"]);
			unset($_POST["FI_passDouble"]);
			
			// Je vérifie si les champs entrés sont valides
			if ( ($email==false) or ($pass==false) or ($passDouble==false) ) {
				// afficher ce texte après la boîte de connexion, pas avant
				$GLOBALS["AfficherTexteDessousInscription"]="<span class='redText'>[local] Un des champs n'a pas été rempli correctement.</span>";
			} else {
				// Je vérifie si les mots de passe coïncident
				if ( ($pass!=$passDouble) ) {
					$GLOBALS["AfficherTexteDessousInscription"]="<span class='redText'>[local] Les deux mots de passe ne correspondent pas !</span>";
				} else {
					// Nouvelle interface de connexion à la base de données
					$DBHandler = DBClick_connect();
					// Je regarde si le nom d'utilisateur (email) est libre
					$query="SELECT * FROM ".$GLOBALS["TableName_utilisateurs"]." WHERE email=?";
					// Requête avec protection contre injection SQL
					$qResult=false;
					$dbLink=$DBHandler->dbLink;
					if ($dbLink) {
						$stmt=$dbLink->prepare($query);
						if ($stmt) {
							$stmt->bind_param('s', $email);
							$stmt->execute();
							$qResult=$stmt->get_result(); //$DBHandler->MakeQuery($query);
							$stmt->close();
						}
					}
					
					if ($qResult==false) {
						// requête échouée
						$GLOBALS["AfficherTexteDessousInscription"]="<span class='redText'>".htmlspecialchars("[serv error] Requête (liste des utilisateurs du même email) échouée.")."</span>";
					} else {
						// requête OK
						if (mysqli_num_rows($qResult)!=0) {
							// il y a déjà un utilisateur ayant cet email
							$GLOBALS["AfficherTexteDessousInscription"]="<span class='redText'>".htmlspecialchars("[serv] Création impossible : email déjà utilisé.")."</span>";
						} else {
							// aucune correspondance, je peux ajouter cet utilisateur
							$maxID=$DBHandler->GetMaxValue("id", $GLOBALS["TableName_utilisateurs"]);
							$thisUserID=$maxID+1;
							
							// Requête pour ajouter cet utilisateur
							$query="INSERT INTO ".$GLOBALS["TableName_utilisateurs"]." (id, email, pass, admin_rank) VALUES (?, ?, ?, ?);";
							$qResult=false;
							$dbLink=$DBHandler->dbLink;
							if ($dbLink) {
								$stmt=$dbLink->prepare($query);
								$adminRank=0;
								$stmt->bind_param('issi', $thisUserID, $email, $pass, $adminRank);
								$qResult=$stmt->execute();
								$stmt->close();
							}
							
							if ($qResult==false) {
								// Echec de la requête pour ajouter l'utilisateur
								$GLOBALS["AfficherTexteDessousInscription"]="<span class='redText'>".htmlspecialchars("[serv error] Requête (ajout de l'utilisateur, première requête OK) échouée.")."</span>";
							} else {
								// Ajout de l'untilisateur OK
								$_SESSION["UserEmail"]=$email;
								$_SESSION["UserPass"]=$pass;
								echo "Inscription...";
								// pas besoin d'utiliser $GLOBALS["AfficherTexteDessousInscription"] (j'utilise juste echo ici)
								//   -> $GS_connexionEnCours=true, ce qui fait que les fenêtre de connexion et d'inscription ne seront pas affichées.
								echo '<meta http-equiv="refresh" content="1; url=\''.$GLOBALS["PageName"].'\'"/>';
								$GS_connexionEnCours=true;
								//echo "Bienvenue".htmlspecialchars(" $email !!");
							}
							
						}
					
					}
					$DBHandler=NULL; // déconnexion de la base de données et destruction de l'objet gérant la DB.
				}
			}
		}
	}
	
?>