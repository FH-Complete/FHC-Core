<?php
include("../vilesci/config.inc.php");
?>

/**
 * Fuegt eine neue Lehreinheit hinzu.
 * Wenn das anlegen erfolgreich war, wird das Dialogfenster geschlossen. 
 */
function LehreinheitenLVAHinzufuegen()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	//Markierten Eintrag holen
	tree = document.getElementById('tree-liste-lehreinheiten-lehrveranstaltung');
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Lektor auswaehlen');
		return false;
	}
		
	//LVA_id holen
	var col = tree.columns ? tree.columns["tree-liste-lehreinheiten-lehrveranstaltung-col-lehrveranstaltung_id"] : "tree-liste-lehreinheiten-lehrveranstaltung-col-lehrveranstaltung_id";
	var lehrveranstaltung_id=tree.view.getCellText(idx,col);

	if(window.opener.LehreinheitenNeu1(lehrveranstaltung_id))
		window.close();
		
}