<?php

//require_once(FCPATH.'include/benutzerberechtigung.class.php');

function isAllowed($uid, $berechtigung_kurzbz, $art = NULL, $oe_kurzbz = NULL, $kostenstelle_id = NULL)
{
	/*$bb = benutzerberechtigung();
	$bb->getBerechtigungen($uid);
	return $bb->isBerechtigt($berechtigung_kurzbz, $art, $oe_kurzbz, $kostenstelle_id);*/
	
	return TRUE;
}