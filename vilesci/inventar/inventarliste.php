<?php
/* Copyright (C) 2010 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/ort.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/wawi.class.php');	
require_once('../../include/person.class.php');	
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/betriebsmitteltyp.class.php');
require_once('../../include/betriebsmittelstatus.class.php');
require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
require_once('../../include/datum.class.php');

if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$inventarnummer=trim((isset($_REQUEST['inventarnummer']) ? $_REQUEST['inventarnummer']:''));
$seriennummer=trim((isset($_REQUEST['seriennummer']) ? $_REQUEST['seriennummer']:''));
$ort_kurzbz=trim((isset($_REQUEST['ort_kurzbz']) ? $_REQUEST['ort_kurzbz']:''));
$oe_kurzbz=trim((isset($_REQUEST['oe_kurzbz']) ? $_REQUEST['oe_kurzbz']:''));
$beschreibung=trim((isset($_REQUEST['beschreibung']) ? $_REQUEST['beschreibung']:''));
$betriebsmittel_id=trim((isset($_REQUEST['betriebsmittel_id']) ? $_REQUEST['betriebsmittel_id']:''));
$betriebsmitteltyp=trim((isset($_REQUEST['betriebsmitteltyp']) ? $_REQUEST['betriebsmitteltyp']:''));
$betriebsmittelstatus_kurzbz=trim((isset($_REQUEST['betriebsmittelstatus_kurzbz']) ? $_REQUEST['betriebsmittelstatus_kurzbz']:''));
$firma_id=trim(isset($_REQUEST['firma_id'])?$_REQUEST['firma_id']:'');
$bestellnr=trim(isset($_REQUEST['bestellnr'])?$_REQUEST['bestellnr']:'');
$bestellung_id=trim(isset($_REQUEST['bestellung_id'])?$_REQUEST['bestellung_id']:'');
$bestelldetail_id=trim(isset($_REQUEST['bestelldetail_id'])?$_REQUEST['bestelldetail_id']:'');
$hersteller=trim((isset($_REQUEST['hersteller']) ? $_REQUEST['hersteller']:''));
$jahr_monat=trim(isset($_REQUEST['jahr_monat']) ? $_REQUEST['jahr_monat']:'');
$afa=trim(isset($_REQUEST['afa']) ? $_REQUEST['afa']:'');
$inventur_jahr=trim(isset($_REQUEST['inventur_jahr']) ? $_REQUEST['inventur_jahr']:'');
$order = trim(isset($_REQUEST['order']) ? $_REQUEST['order']:'');
$person_id = trim(isset($_REQUEST['person_id']) ? $_REQUEST['person_id']:'');

$debug = false;
$schreib_recht_administration=false;
$schreib_recht=false;
$delete_recht=false;
$default_status_vorhanden='vorhanden';

$oBetriebsmittelstatus = new betriebsmittelstatus();
$oBetriebsmittelstatus->result=array();
	
$resultBetriebsmittelstatus=$oBetriebsmittelstatus->result;

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>Inventar - Betriebsmittel - Suche</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../include/js/jquery.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
		<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	</head>
<body>
';
$oBetriebsmittel = new betriebsmittel();
if (!$oBetriebsmittel->betriebsmittel_inventar($order,$inventarnummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer,$person_id,$betriebsmittel_id))
		$errormsg[]=$oBetriebsmittel->errormsg;


echo output_inventar($debug,$oBetriebsmittel->result,$resultBetriebsmittelstatus,$schreib_recht,$delete_recht,$schreib_recht_administration,$default_status_vorhanden);
	
/**
 * Ausgabe der Bestellungen in Listenform
 *
 * @param unknown_type $debug
 * @param unknown_type $resultBetriebsmittel
 * @param unknown_type $resultBetriebsmittelstatus
 * @param unknown_type $schreib_recht
 * @param unknown_type $delete_recht
 * @param unknown_type $schreib_recht_administration
 * @return unknown
 */
function output_inventar($debug=false,$resultBetriebsmittel=null,$resultBetriebsmittelstatus=array(),$schreib_recht=false,$delete_recht=false,$schreib_recht_administration=2)
{
	$datum_obj=new datum();
	
	$htmlstring='';
	if (is_null($resultBetriebsmittel) || !is_array($resultBetriebsmittel) || count($resultBetriebsmittel)<1)
		return $htmlstring;
	$htmlstring.='<table  id="t1" class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
			<thead>';
	if (is_array($resultBetriebsmittel) && count($resultBetriebsmittel)>1)
		$htmlstring.='<tr><th colspan="12">'.count($resultBetriebsmittel).' Eintr&auml;ge gefundenen</th></tr>';
	$htmlstring.='<tr>
				<th class="table-sortable:default">Inv.nr.</th>
				<th class="table-sortable:default">Bezeichnung</th>
				<th class="table-sortable:default">Verwendung</th>
				<th class="table-sortable:default">Ser.nr.</th>
				<th class="table-sortable:default">Ort</th>
				<th class="table-sortable:default">Bestellnummer</th>
				<th class="table-sortable:default">Datum</th>
				<th class="table-sortable:default">Org.</th>
				<th colspan="3" class="table-sortable:default">Status</th>
			</tr>
			</thead>
		';

	for ($pos=0;$pos<count($resultBetriebsmittel);$pos++)
	{
		if ($pos%2)
			$classe='liste1';
		else
			$classe='liste0';

		// Pruefen ob OE vorhanden ist - ansonst suchen ob ein Benutzer vorhanden ist	
		$resultBetriebsmittel[$pos]->oe_kurzbz=trim($resultBetriebsmittel[$pos]->oe_kurzbz);
		if (empty($resultBetriebsmittel[$pos]->oe_kurzbz))
		{
			$resultBetriebsmittel[$pos]->oe_kurzbz='Fehlt';
			$oBetriebsmittelOrganisationseinheit = new betriebsmittel();
			if ($oBetriebsmittelOrganisationseinheit->load_betriebsmittel_oe($resultBetriebsmittel[$pos]->betriebsmittel_id))
				$resultBetriebsmittel[$pos]->oe_kurzbz=$oBetriebsmittelOrganisationseinheit->oe_kurzbz;
			else if ($oBetriebsmittelOrganisationseinheit->errormsg)	
				$resultBetriebsmittel[$pos]->oe_kurzbz=$oBetriebsmittelOrganisationseinheit->errormsg;
		}
		
		$oOrganisationseinheit->bezeichnung='';
		$oOrganisationseinheit = new organisationseinheit($resultBetriebsmittel[$pos]->oe_kurzbz);
		// String - Daten Leerzeichen am Ende entfernen
		$resultBetriebsmittel[$pos]->bestellnr=trim($resultBetriebsmittel[$pos]->bestellnr);

		$resultBetriebsmittel[$pos]->titel=trim($resultBetriebsmittel[$pos]->titel);
		$resultBetriebsmittel[$pos]->beschreibung=trim($resultBetriebsmittel[$pos]->beschreibung);

		$resultBetriebsmittel[$pos]->firma_id=trim($resultBetriebsmittel[$pos]->firma_id);
		$resultBetriebsmittel[$pos]->firmenname=trim($resultBetriebsmittel[$pos]->firmenname);
						
		$htmlstring.='<tr class="'.$classe.'">
			<td>'.($resultBetriebsmittel[$pos]->inventarnummer?$resultBetriebsmittel[$pos]->inventarnummer:$resultBetriebsmittel[$pos]->betriebsmittel_id).'&nbsp;</td>
			<td>'.StringCut((!empty($resultBetriebsmittel[$pos]->beschreibung)?$resultBetriebsmittel[$pos]->beschreibung:$resultBetriebsmittel[$pos]->betriebsmitteltyp),20).'&nbsp;</td>
			<td>'.$resultBetriebsmittel[$pos]->verwendung.'&nbsp;</td>
			<td>'.$resultBetriebsmittel[$pos]->seriennummer.'&nbsp;</td>
			<td>'.$resultBetriebsmittel[$pos]->ort_kurzbz.'&nbsp;</td>
			';

		$htmlstring.='<td align="right">'.$resultBetriebsmittel[$pos]->bestellnr.'&nbsp;</td>';
		/*
		$htmlstring.='
			<td align="right">';
		$htmlstring.=$resultBetriebsmittel[$pos]->bestelldetail_id;
		$htmlstring.='&nbsp;</td>';
		*/
		$htmlstring.='<td><span style="display: none;">'.$resultBetriebsmittel[$pos]->betriebsmittelstatus_datum.'</span>'.$datum_obj->formatDatum($resultBetriebsmittel[$pos]->betriebsmittelstatus_datum,'d.m.Y').'&nbsp;</td>';
		$htmlstring.='<td>'.StringCut(($oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:$resultBetriebsmittel[$pos]->oe_kurzbz),20).'&nbsp;</td>';

		$htmlstring.='<td>';
		// mit Berechtigung ist der Status zum bearbeiten
		$betriebsmittelstatus_kurzbz_select=trim($resultBetriebsmittel[$pos]->betriebsmittelstatus_kurzbz);
		$htmlstring.=$betriebsmittelstatus_kurzbz_select;
			
		$htmlstring.='&nbsp;</td>';
			
		$htmlstring.='
		</tr>
		';
	if ($resultBetriebsmittel[$pos]->bestellung_id && !$resultBetriebsmittel[$pos]->bestellnr)
		$htmlstring.='<tr class="'.$classe.'"  style="font-size:smaller;"><td colspan="12" class="error">Achtung! Bestellung nicht mehr vorhanden!</td></tr>';
	}
	$htmlstring.='</table>';
	return 	$htmlstring;
}
?>
</body>
</html>