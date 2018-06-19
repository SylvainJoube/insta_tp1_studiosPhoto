<?php
/*
 * Librairie regroupant des fonctions souvent utiles
 * écrites par Sylvain Joube, pour me former au php, html et css.
 * 
 */

// --------------------------------------------------------------------
// Base de données

class TDBHandler { // gestion de la base de données utilisateurs
    // Même si c'est non nécessaire en php, je trouve ça plus clair et rigoureux de déclarer les variables de cet objet ici
	public $host = 'localhost';
    public $user = 'root';
    public $password = '';
    public $dataBaseName = 'unknown';
    public $dbLink = NULL;
	public $isConnected = false;
    public $enableLog = false;
    public $dbTableName='';
    
	
	
    // Ecrire (via echo) une ligne de texte compatible avec un affichage HTML
    public function EchoForHTML($text) {
        echo htmlspecialchars($text)."<br/>";
        return true;
    }
	
	// Ecriture des logs, si la variable $enableLog est mise à vrai
	public function WriteLog($text)  {
		if ($this->enableLog) {
			echo $this->EchoForHTML($text);
		}
	}
    
	
    // Destructeur de l'objet
    public function __destruct() {
		$this->isConnected=false;
        if ($this->dbLink!=NULL) {
            mysqli_close($this->dbLink); // = $this->dbLink->close();
        }
        $this->WriteLog("DBHandler->__destruct()");
    }
	
	
	// Fonction pour se connecter à la base de données décrites dans les variables de l'objet
    public function Connect() {
		
		// COnnexion à la base
		$this->isConnected=false;
        $this->dbLink = mysqli_connect($this->host, $this->user, $this->password, $this->dataBaseName); // utilisation du port par défaut, et gestion automatique des sockets
        $errorCode= mysqli_connect_errno();//mysqli_errno($this->dbLink);
        
		if ($errorCode==0) { // connexion réussie
            $this->WriteLog("TDBHandler : connexion OK ! dataBaseName=$this->dataBaseName");
            // Je vérifie bien que j'ai accès à la base de donnée choisie
            $baseOK=mysqli_select_db($this->dbLink, "$this->dataBaseName");
            if ($baseOK) {
                $this->WriteLog("BASE OK !");
				$this->isConnected=true;
			} else
                 $this->WriteLog("BASE INACCESSIBLE ! (mais connexion OK)");
			           
        } else { // echec de la connexion
			// $this->isConnected=false; implicite
            if ($this->enableLog) {
                $errorCodeStr = (string) $errorCode;
                $this->WriteLog('ERREUR - TDBHandler : connexion échouée. codeErreur='.$errorCodeStr);
            }
        }
		
		return $this->isConnected;
		// la déconnexion à la base ($this->dbLink) s'effectue lors de la destruction de cet objet (DBHandler)
    }
    
	// Se connecter à une base en passant en argument les variables nécessaires
    public function ConnectTo ($arg_host, $arg_user, $arg_password, $arg_dataBaseName) {
        $this->host=$arg_host;
        $this->user=$arg_user;
        $this->password=$arg_password;
        $this->dataBaseName=$arg_dataBaseName;
        return $this->Connect();
    }
    
	// constructeur (rien à initialiser pour l'instant)
    public function __construct() {
        return true;
    }
    
	
	// Fonction MakeQuery($query);
	// Effectuer une requête sur la base de données (après d'y être connecté)
	// /!\ Vulnérable aux injections SQL s'il y a des paramètres entrés par l'utilisateur dans la requête $query
	// /!\ à n'utiliser que quand il n'y a pas de risque d'injection SQL.
	/// Utiliser : 
	/*  $qResult=false;
	 *   $dbLink=$DBHandler->dbLink;
	 *	if ($dbLink) {
	 *		$stmt=$dbLink->prepare($query);
	 *		$stmt->bind_param('ss', $userEmail, $userPass);
	 *		$stmt->execute();
	 *		$qResult=$stmt->get_result(); //$DBHandler->MakeQuery($query);
	 *		$stmt->close();
	 *	}
	 */
	/// s'il y a des "user input"
    public function MakeQuery($query) {
		if ($this->isConnected==false)
			return false; // requête impossible.
		
        $queryResult = mysqli_query($this->dbLink, $query);
        if ($queryResult == false)  {
            $this->WriteLog("ERREUR TDBHandler->MakeQuery : requête échouée \$query = $query");
            return false;
        }
        return $queryResult; // retourne le résultat de la reqête (= $stmt->get_result(); mis en commentaire, plus haut)
    }
	
	
    
	// Fonction pour récupérer la valeur maximale d'une entrée d'une table dans la base de donnée actuelle
	// (nécessite d'être connecté à la base)
	// /!\ Pas de protection contre les injections SQL ici
    public function GetMaxValue($TableEntryName, $TableName) { // $TableName : nom de la table (de la DB) dans laquelle rechercher l'entrée
		if ($this->isConnected==false)
			return 0;
		
        $query = "SELECT MAX($TableEntryName) FROM $TableName";
        $qResult = $this->MakeQuery($query); // pas de risque d'injection SQL ici, les variables passées en argument sont fixes et directement mises dans le code php
		
        if ($qResult==false) {
            return 0; // requête échouée
		}
        $A1MaxValue = mysqli_fetch_array($qResult); // La notation A1 signifie (pour moi) que c'est un tableau à 1 dimension
		
        if ( ($A1MaxValue!=NULL) and ($A1MaxValue!=false) ) {
            $tabLen=count($A1MaxValue);
            if ($tabLen>0) {
                $this->WriteLog("GetMaxValue tabLen=$tabLen, maxValue=".$A1MaxValue[0]);
                return $A1MaxValue[0];
            }
        }
        return 0;
    }
    
	// ACtualiser les variables de session pour indiquer que l'utilisateur est bien connecté
    public function LogUserOn($userName, $userPass) {
        $_SESSION["LoggedOn"]=true;
        $_SESSION["UserName"]=$userName;
        $_SESSION["UserPass"]=$userPass;
        return true;
    }
	
	// Fonction pour vérifier qu'una table existe
	// (pas de protection contre les injections SQL pour $tableName)
	public function TableExists($tableName) {
		if ($this->isConnected==false)
			return false;
		$query="SHOW TABLES LIKE '$tableName'";
		$qResult=$this->MakeQuery($query);
		if ($qResult) {
			if ($qResult->num_rows==1)
				return true;
		}
		return false;
	}
}



// --------------------------------------------------------------------
// Formulaires
// Recupère les valeurs d'un formulaire
function form_getPostInput($formVariableName) {
	$formVariable=filter_input(INPUT_POST ,$formVariableName);
	if ( $formVariable==false ) return false;
	if ( $formVariable==NULL )  return false;
	if ( $formVariable==="" )   return false;
	if ( $formVariable===0 )    return false;
	return $formVariable;
}

function form_getGetInput($formVariableName) {
	$formVariable=filter_input(INPUT_GET ,$formVariableName);
	if ( $formVariable==false ) return false;
	if ( $formVariable==NULL )  return false;
	if ( $formVariable==="" )   return false;
	if ( $formVariable===0 )    return false;
	return $formVariable;
}

// form_echoRedTextIfNeeded(...) :
// Affiche le texte d'un formulaire en rouge si le champ associé n'a pas été corectement rempli (si formulaire validé)
// Retourne faux si le champ est non défini ou vide
function form_echoRedTextIfNeeded($echoText, $inputVariableName, $validateVariableName) {
	$valider=filter_input(INPUT_POST, $validateVariableName); // "AA_valider"
	if ( ($valider!=false) and ($valider!=NULL) ) { // si validé
		$currentVariable=filter_input(INPUT_POST, $inputVariableName);
		if ( ($currentVariable!=false) and ($currentVariable!=NULL) ) { // variable définie
			// action faite à la fin
		} else { // dessiner en rouge et ajouter "requis"
			echo "<span class='redText'>";
			echo htmlspecialchars($echoText);
			echo "<span class='miniText'> (requis)</span>";
			echo "</span>";
			return false; // texte rouge
		}
	}
	echo htmlspecialchars($echoText);
	return true; // texte normal
}

function form_echoInputText($inputType, $inputName, $addMoreTextToInput) { // $inputType : text, password, email ...
	$value="";
	$postVal=false;
	if (isset($_POST[$inputName]))
		$postVal=$_POST[$inputName];
		//$postVal=filter_input(INPUT_POST, $inputName);
	// -> pas de filter_input parce que j'affecte des variables $_POST dans mon code,
	//    et que je ne peux pas les récupérer avec filter_input (alors que je le peux avec $_POST[..])
	if ( ($postVal!=NULL) and ($postVal!=false) ) {
		$value=$postVal;
	}
	echo "<input type='$inputType' name='$inputName' value='$value' class='formInput' $addMoreTextToInput />";
}


// --------------------------------------------------------------------
// Fonction diverses

function ValueIsInArray(&$A1Array, $value) { // passage par référence pour éviter d'avoir à copier le tableau
	$cLen=count($A1Array);
	for ($i=0; $i<$cLen; $i++) {
		if ($A1Array[$i]==$value) {
			return true;
		}
	}
	return false;
}








?>