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
require_once('../../include/person.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/betriebsmittelperson.class.php');
require_once('../../include/betriebsmitteltyp.class.php');
require_once('../../include/betriebsmittelstatus.class.php');
require_once('../../include/betriebsmittel_betriebsmittelstatus.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/wawi_bestelldetail.class.php');
require_once('../../include/Excel/excel.php');

if (!$uid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('wawi/inventar:begrenzt'))
	die($rechte->errormsg);

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
$anlage_jahr_monat=trim(isset($_REQUEST['anlage_jahr_monat']) ? $_REQUEST['anlage_jahr_monat']:'');

$debug = false;
$schreib_recht_administration=false;
$schreib_recht=false;
$delete_recht=false;
$default_status_vorhanden='vorhanden';

$oBetriebsmittelstatus = new betriebsmittelstatus();
$oBetriebsmittelstatus->result=array();

$resultBetriebsmittelstatus=$oBetriebsmittelstatus->result;

$oBetriebsmittel = new betriebsmittel();
if (!$oBetriebsmittel->betriebsmittel_inventar($order,$inventarnummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer,$person_id,$betriebsmittel_id,$anlage_jahr_monat))
		$errormsg[]=$oBetriebsmittel->errormsg;

$resultBetriebsmittel = $oBetriebsmittel->result;

$datum_obj = new datum();

// Creating a workbook
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);

// sending HTTP headers
$workbook->send("Inventarliste.xls");

// Creating a worksheet
$worksheet =& $workbook->addWorksheet("Inventarliste");
$worksheet->setInputEncoding('utf-8');

$format_bold =& $workbook->addFormat();
$format_bold->setBold();
$format_bold->setAlign('center');

$format_date =& $workbook->addFormat();
$format_date->setNumFormat('DD.MM.YYYY');

$format_number =& $workbook->addFormat();
$format_number->setNumFormat('0,0.00');

$format_right =& $workbook->addFormat();
$format_right->setAlign('right');

$spalte=0;
$zeile=0;

if (is_null($resultBetriebsmittel) || !is_array($resultBetriebsmittel) || count($resultBetriebsmittel)<1)
	return false;

$worksheet->write($zeile,$spalte,'Inv.nr.',$format_bold);
$maxlength[$spalte]=7;
$worksheet->write($zeile,++$spalte,'Beschreibung',$format_bold);
$maxlength[$spalte]=12;
$worksheet->write($zeile,++$spalte,'Verwendung',$format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile,++$spalte,'Seriennr.',$format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile,++$spalte,'Ort',$format_bold);
$maxlength[$spalte]=3;
$worksheet->write($zeile,++$spalte,'Bestellnr',$format_bold);
$maxlength[$spalte]=10;
$worksheet->write($zeile,++$spalte,'Org.',$format_bold);
$maxlength[$spalte]=5;
$worksheet->write($zeile,++$spalte,'Datum',$format_bold);
$maxlength[$spalte]=5;
$worksheet->write($zeile,++$spalte,'Letzte Inventur',$format_bold);
$maxlength[$spalte]=15;
$worksheet->write($zeile,++$spalte,'Leasing bis',$format_bold);
$maxlength[$spalte]=11;
$worksheet->write($zeile,++$spalte,'Anschaffungsdatum',$format_bold);
$maxlength[$spalte]=17;
$worksheet->write($zeile,++$spalte,'Anschaffungswert',$format_bold);
$maxlength[$spalte]=16;
$worksheet->write($zeile,++$spalte,'Status',$format_bold);
$maxlength[$spalte]=16;
$worksheet->write($zeile,++$spalte,'Person',$format_bold);
$maxlength[$spalte]=20;

for ($pos=0;$pos<count($resultBetriebsmittel);$pos++)
{
	$zeile++;
	$spalte=0;
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


	$oOrganisationseinheit = new organisationseinheit($resultBetriebsmittel[$pos]->oe_kurzbz);
	//$oOrganisationseinheit->bezeichnung='';
	// String - Daten Leerzeichen am Ende entfernen
	$resultBetriebsmittel[$pos]->bestellnr=trim($resultBetriebsmittel[$pos]->bestellnr);

	$resultBetriebsmittel[$pos]->titel=trim($resultBetriebsmittel[$pos]->titel);
	$resultBetriebsmittel[$pos]->beschreibung=trim($resultBetriebsmittel[$pos]->beschreibung);

	$resultBetriebsmittel[$pos]->firma_id=trim($resultBetriebsmittel[$pos]->firma_id);
	$resultBetriebsmittel[$pos]->firmenname=trim($resultBetriebsmittel[$pos]->firmenname);


	InsertCell($zeile,$spalte,$resultBetriebsmittel[$pos]->inventarnummer);
	InsertCell($zeile,++$spalte,mb_str_replace("\r\n"," ",$resultBetriebsmittel[$pos]->beschreibung));
	InsertCell($zeile,++$spalte,mb_str_replace("\r\n"," ",$resultBetriebsmittel[$pos]->verwendung));
	InsertCell($zeile,++$spalte,$resultBetriebsmittel[$pos]->seriennummer,'string');
	InsertCell($zeile,++$spalte,$resultBetriebsmittel[$pos]->ort_kurzbz);
	InsertCell($zeile,++$spalte,$resultBetriebsmittel[$pos]->bestellnr);
	InsertCell($zeile,++$spalte,$oOrganisationseinheit->bezeichnung?$oOrganisationseinheit->bezeichnung:$resultBetriebsmittel[$pos]->oe_kurzbz);
	InsertCell($zeile,++$spalte,$datum_obj->formatDatum($resultBetriebsmittel[$pos]->betriebsmittelstatus_datum,'d.m.Y'),$format_date);
	InsertCell($zeile,++$spalte,$datum_obj->formatDatum($resultBetriebsmittel[$pos]->inventuramum,'d.m.Y'),$format_date);
	InsertCell($zeile,++$spalte,$datum_obj->formatDatum($resultBetriebsmittel[$pos]->leasing_bis,'d.m.Y'),$format_date);

	InsertCell($zeile,++$spalte,$datum_obj->formatDatum($resultBetriebsmittel[$pos]->anschaffungsdatum,'d.m.Y'),$format_date);
	InsertCell($zeile,++$spalte,$resultBetriebsmittel[$pos]->anschaffungswert, $format_number);
	InsertCell($zeile,++$spalte,$resultBetriebsmittel[$pos]->betriebsmittelstatus_kurzbz);

	$bmp = new betriebsmittelperson();
	$bmp->load_betriebsmittelpersonen($resultBetriebsmittel[$pos]->betriebsmittel_id);
	$person = new person();
	$person->load($bmp->person_id);
	$person_name=$person->vorname.' '.$person->nachname;
	InsertCell($zeile,++$spalte,$person_name, $format_number);
}
$maxlength[1]=30;
$maxlength[2]=30;
foreach($maxlength as $i=>$breite)
	$worksheet->setColumn(0, $i, $breite+2);
$workbook->close();


function InsertCell($zeile, $spalte, $value, $format=null)
{
	global $maxlength, $worksheet;

	if(!is_null($format))
	{
		if($format=='string')
			$worksheet->writeString($zeile,$spalte,$value);
		else
			$worksheet->write($zeile,$spalte,$value, $format);
	}
	else
		$worksheet->write($zeile,$spalte,$value);

	if(mb_strlen($value)>$maxlength[$spalte])
		$maxlength[$spalte]=mb_strlen($value);
}
?>
