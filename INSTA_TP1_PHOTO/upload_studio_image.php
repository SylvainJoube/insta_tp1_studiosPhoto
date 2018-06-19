
<?php

session_start();
include_once("UsefulFunctions.php");
include_once("functions.php");

// Script copié depuis  " https://www.w3schools.com/Php/php_file_upload.asp "
// et ensuite légèrement modifié


//basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;

// $_SESSION["ImgUpload_pagePrecedente"]
// $_SESSION["ImgUpload_codeErreur"]
// $_SESSION["ImgUpload_messageErreur"]
// $_SESSION["ImgUpload_tailleMaximaleOctets"]

$_SESSION["ImgUpload_messageErreur"]="";
$_SESSION["ImgUpload_codeErreurUpload"]=0;
$_SESSION["ImgUpload_codeErreurPerso"]=0;

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
	
	$fError=$_FILES["fileToUpload"]["error"];
	$errorMessage="";
	
	$_SESSION["ImgUpload_codeErreurUpload"]=$fError;
	
	// UPLOAD_ERR_OK tout va bien
	if ($fError==UPLOAD_ERR_OK) {
		$fTempName=$_FILES["fileToUpload"]["tmp_name"];
		$fSize=$_FILES["fileToUpload"]["size"];
		
		if ( ($fTempName!="") and ($fSize>0) ) {	
			$check = getimagesize($fTempName);
			if($check !== false) {
				// OK rien à afficher $errorMessage = "Le fichier est une image. (".$check["mime"].")";
				$uploadOk = 1;
			} else {
				$errorMessage = "Le fichier n'est pas une image.";
				$uploadOk = 0;
				$_SESSION["ImgUpload_codeErreurPerso"]=5;
			}
		} else {
			$uploadOk = 0; // nom invalide (vide)
		}
	} else switch ($fError) {
		case UPLOAD_ERR_INI_SIZE:
			$errorMessage = "La taille du fichier excède upload_max_filesize configuré dans le fichier php.ini.";
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$errorMessage = "La taille du fichier excède MAX_FILE_SIZE spécifié dans le formulaire HTML.";
			break;
		case UPLOAD_ERR_PARTIAL:
			$errorMessage = "Upload partiel, un bout de fichier est manquant sur le serveur.";
			break;
		case UPLOAD_ERR_NO_FILE:
			$errorMessage = "Aucun fichier n'a été téléchargé.";
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$errorMessage = "Répertoire temporaire inaccesible.";
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$errorMessage = "Impossible d'écrire sur le disque du serveur.";
			break;
		case UPLOAD_ERR_EXTENSION:
			$errorMessage = "Une extension php a bloqué l'upload.";
			break;

		default:
			$errorMessage = "Erreur inconnue.";
			break;
	}
	if ($errorMessage!="") {
		echo htmlspecialchars("ERREUR lors de l'upload : $errorMessage")."<br/>";
		$_SESSION["ImgUpload_messageErreur"]=$errorMessage;
	}
} else {
	$uploadOk=0;
}

$imgToUpload_baseName=basename($_FILES["fileToUpload"]["name"]);
$imageFileType=strtolower(pathinfo($imgToUpload_baseName, PATHINFO_EXTENSION));

// Allow certain file formats
if ($uploadOk==1)
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
	$uploadOk = 0;
	$localError="Seuls les formats JPG, JPEG, PNG et GIF sont acceptés.";
	$_SESSION["ImgUpload_codeErreurPerso"]=3;
	$_SESSION["ImgUpload_messageErreur"]+=$localError;
	echo $localError;
}

// Check file size
if ($uploadOk==1)
if ($_FILES["fileToUpload"]["size"] > 500000) {
	$uploadOk = 0;
	$localError="Le fichier est trop grand.";
	$_SESSION["ImgUpload_codeErreurPerso"]=2;
	$_SESSION["ImgUpload_messageErreur"]+=$localError;
	echo $localError;
}

$target_file="";
$imageId_original=-1;
$imageExtension="";


if ($uploadOk==1) {
	$target_dir="studio_images/";
	// je demande à la base de données le dernier identifiant des images des studios
	$DBHandler=DBClick_connect();
	$maxId=$DBHandler->GetMaxValue("id", "cns_studioimage");
	$DBHandler=NULL;
	$newId=$maxId+1;
	$imageId_original=$newId;
	
	$baseNameImage="studio".$_SESSION["ImgUpload_idStudio"]."_img"."$newId"."_"."$imageFileType";
	$imageExtension="$imageFileType";
	$target_file=$target_dir.$baseNameImage."_original."."$imageExtension";
	
}

// Check if file already exists
if ($uploadOk==1)
if (file_exists($target_file)) {
	$uploadOk = 0;
	$localError="Le fichier existe déjà. !!";
	$_SESSION["ImgUpload_codeErreurPerso"]=1;
	$_SESSION["ImgUpload_messageErreur"]+=$localError;
	echo $localError;
}




// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    //echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        
		echo htmlspecialchars("Le fichier ". basename( $_FILES["fileToUpload"]["name"]). " a été correctement uploadé.");
		
		$DBHandler=DBClick_connect();
		$query="INSERT INTO `cns_studioimage` (`id`, `studio_id`, `nom_de_base`, `taille_affichage`, `ordre_apparition`, `extension`)
		VALUES ('4', '2', 'nom de base', '10', '1', 'jpg')";
		
		
		
		
		$DBHandler=NULL;
		
		
    } else {
		$localError="Echec lors du déplacement du fichier à son emplacement définitif.";
		$_SESSION["ImgUpload_codeErreurPerso"]=4;
		$_SESSION["ImgUpload_messageErreur"]+=$localError;
		echo $localError;
    }
}

echo '<meta http-equiv="refresh" content="1; url=\''.$_SESSION["ImgUpload_pagePrecedente"].'\'"/>';

?>


