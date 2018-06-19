<?php

	include_once("UsefulFunctions.php");
	include_once("functions.php");
		
?>

<li>
      	
<form name="Fm_rechercheStudio" action="" method="POST" class="SearchBox">
    <span class="darkBlueTextColor">
    <span class="sidebarTitle">Réserver un studio</span><br/>
    Région
    <select name="RS_region" class="formInput">
      <option value="R1">Ile de France</option>
      <option value="R2">Région centre</option>
    </select>
    Ville
    <select name="RS_ville" class="formInput">
        <option value="Paris">Paris</option>
        <option value="Versailles">Versailles</option>
        <option value="Proche de Paris">Proche de Paris</option>
    </select>
    Taille
    <select name="RS_tailleStudio" class="formInput">
        <option value="T1">T1</option>
        <option value="T2">T2</option>
        <option value="T3">T3</option>
        <option value="T4">T4</option>
        <option value="T5">T5</option>
    </select>
    Prix
    <input type="text" value="0" class="priceInput"/>
    à
    <input type="text" value="10000" class="priceInput"/>
    €</span>
    <br/>
    <span class="RSValider">
        <input type="submit" name="RS_valider" value="Rechercher [bientôt dispo]" class="RS_valider" />
    </span>
</form>

</li>