<?php
include("../vilesci/config.inc.php");
?>

function LehreinheitenMitarbeiterHinzufuegen()
{
	tree = document.getElementById('tree-liste-lehreinheiten-mitarbeiter');
	var idx;
	if(tree.currentIndex>=0)
		idx = tree.currentIndex;
	else
	{
		alert('Bitte zuerst einen Lektor auswaehlen');
		return false;
	}

	//MitarbeiterLehreinheit_id holen
	var col = tree.columns ? tree.columns["tree-liste-lehreinheiten-mitarbeiter-col-mitarbeiter_id"] : "tree-liste-lehreinheiten-mitarbeiter-col-mitarbeiter_id";
	var mitarbeiter_id=tree.view.getCellText(idx,col);

	window.opener.MitarbeiterLehreinheitAuswahlAdd(mitarbeiter_id);
	window.close();
}