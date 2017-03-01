<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
// header fuer no cache
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/konto.class.php');
require_once('../include/person.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/datum.class.php');
require_once('../include/functions.inc.php');
require_once('../include/student.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');

if(isset($_SERVER['REMOTE_USER']))
{
	// Wenn das Script direkt aufgerufen wird muss es ein Admin sein
	$user=get_uid();
	$berechtigung = new benutzerberechtigung();
	$berechtigung->getBerechtigungen($user);
	if(!$berechtigung->isBerechtigt('student/stammdaten'))
		die('Sie haben keine Berechtigung fuer diese Seite');
}

$hier='';
if(isset($_GET['xmlformat']))
{
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	$xmlformat=$_GET['xmlformat'];
}
else
{
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	$xmlformat='rdf';
}

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
{
	$person_id=$_GET['person_id'];
}
else
	$person_id='';

if(isset($_GET['filter']))
	$filter=$_GET['filter'];
else
	$filter='alle';

if(isset($_GET['buchungsnr']) && is_numeric($_GET['buchungsnr']))
{
	$buchungsnr = $_GET['buchungsnr'];
}
else
	$buchungsnr = '';

if(isset($_GET['buchungsnummern']))
{
	$buchungsnummern = $_GET['buchungsnummern'];
}
else
	$buchungsnummern = '';

$studiengang_kz = (isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:'');

$datum = new datum();
$konto = new konto();

if(isset($_SERVER['REMOTE_USER']))
{
	$user = get_uid();
	loadVariables($user);
	if($kontofilterstg=='false')
		$studiengang_kz='';
}

if($person_id!='')
{
	$konto->getBuchungen($person_id, $filter, $studiengang_kz);
}
elseif($buchungsnr!='')
{
	if(!$konto->load($buchungsnr))
		die($konto->errormsg);
}
// ----------------------------------- RDF --------------------------------------
$rdf_url='http://www.technikum-wien.at/konto';
if ($xmlformat=='rdf')
{

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:KONTO="'.$rdf_url.'/rdf#"
>';

function drawrow($row)
{
	global $rdf_url, $datum;

	$stg = new studiengang($row->studiengang_kz);
	echo "
  		<RDF:Description  id=\"".$row->buchungsnr."\"  about=\"".$rdf_url.'/'.$row->buchungsnr."\" >
			<KONTO:buchungsnr><![CDATA[".$row->buchungsnr."]]></KONTO:buchungsnr>
			<KONTO:person_id><![CDATA[".$row->person_id."]]></KONTO:person_id>
			<KONTO:studiengang_kz><![CDATA[".$row->studiengang_kz."]]></KONTO:studiengang_kz>
			<KONTO:studiengang_kuerzel><![CDATA[".$stg->kuerzel."]]></KONTO:studiengang_kuerzel>
			<KONTO:studiensemester_kurzbz><![CDATA[".$row->studiensemester_kurzbz."]]></KONTO:studiensemester_kurzbz>
			<KONTO:buchungsnr_verweis><![CDATA[".$row->buchungsnr_verweis."]]></KONTO:buchungsnr_verweis>
			<KONTO:betrag><![CDATA[".$row->betrag."]]></KONTO:betrag>
			<KONTO:buchungsdatum_iso><![CDATA[".$row->buchungsdatum."]]></KONTO:buchungsdatum_iso>
			<KONTO:buchungsdatum><![CDATA[".$datum->convertISODate($row->buchungsdatum)."]]></KONTO:buchungsdatum>
			<KONTO:buchungstext><![CDATA[".$row->buchungstext."]]></KONTO:buchungstext>
			<KONTO:mahnspanne><![CDATA[".$row->mahnspanne."]]></KONTO:mahnspanne>
			<KONTO:buchungstyp_kurzbz><![CDATA[".$row->buchungstyp_kurzbz."]]></KONTO:buchungstyp_kurzbz>
			<KONTO:credit_points><![CDATA[".$row->credit_points."]]></KONTO:credit_points>
			<KONTO:zahlungsreferenz><![CDATA[".$row->zahlungsreferenz."]]></KONTO:zahlungsreferenz>
			<KONTO:anmerkung><![CDATA[".$row->anmerkung."]]></KONTO:anmerkung>
			<KONTO:updateamum><![CDATA[".$row->updateamum."]]></KONTO:updateamum>
			<KONTO:updatevon><![CDATA[".$row->updatevon."]]></KONTO:updatevon>
			<KONTO:insertamum><![CDATA[".$row->insertamum."]]></KONTO:insertamum>
			<KONTO:insertvon><![CDATA[".$row->insertvon."]]></KONTO:insertvon>
		</RDF:Description>";
}

if($person_id!='')
{
	foreach ($konto->result as $buchung)
	{
		if(isset($buchung['parent']))
		{
			$buchung = $buchung['parent'];
			//1. Ebene
			drawrow($buchung);

			$hier.="
	      	<RDF:li>
	      		<RDF:Seq about=\"".$rdf_url.'/'.$buchung->buchungsnr."\" >";

			if(isset($konto->result[$buchung->buchungsnr]['childs']))
			{
				//2. Ebene
				foreach ($konto->result[$buchung->buchungsnr]['childs'] as $row)
				{
					if(is_object($row))
					{
						drawrow($row);

						$hier.="
						<RDF:li resource=\"".$rdf_url.'/'.$row->buchungsnr.'" />';
					}
				}
			}

			$hier.="
	      		</RDF:Seq>
	      	</RDF:li>";
		}
	}
}
else
{
	$hier.="<RDF:li resource=\"".$rdf_url.'/'.$konto->buchungsnr.'" />';
	drawrow($konto);
}
$hier="
  	<RDF:Seq about=\"".$rdf_url."/liste\">".$hier."
  	</RDF:Seq>";

echo $hier;

echo '
</RDF:RDF>
';

} //endof xmlformat==rdf
// ----------------------------------- XML --------------------------------------
elseif ($xmlformat=='xml')
{
	echo "<konto>\n";
	function drawrow_xml($row)
	{
		global $datum, $btyp;
		$rueckerstattung=false;
		$stg = new studiensemester($row->studiensemester_kurzbz);

		echo "
  		<buchung>
			<buchungsnr><![CDATA[".$row->buchungsnr."]]></buchungsnr>
			<person_id><![CDATA[".$row->person_id."]]></person_id>
			<studiengang_kz><![CDATA[".$row->studiengang_kz."]]></studiengang_kz>
			<studiensemester_kurzbz><![CDATA[".$row->studiensemester_kurzbz."]]></studiensemester_kurzbz>
			<studienjahr_kurzbz><![CDATA[".$stg->studienjahr_kurzbz."]]></studienjahr_kurzbz>
			<buchungsnr_verweis><![CDATA[".$row->buchungsnr_verweis."]]></buchungsnr_verweis>
			<betrag><![CDATA[".sprintf('%.2f',abs($row->betrag))."]]></betrag>";
		if($row->buchungsnr_verweis!='')
		{
			$parent = new konto();
			$parent->load($row->buchungsnr_verweis);
			if($parent->betrag>0)
				$rueckerstattung=true;
		}
		else
		{
			if($row->betrag>0)
				$rueckerstattung=true;
		}

		if($rueckerstattung)
				echo "<rueckerstattung><![CDATA[true]]></rueckerstattung>";
		echo "
			<buchungsdatum><![CDATA[".$datum->convertISODate($row->buchungsdatum)."]]></buchungsdatum>
			<buchungstext><![CDATA[".$row->buchungstext."]]></buchungstext>
			<mahnspanne><![CDATA[".$row->mahnspanne."]]></mahnspanne>
			<buchungstyp_kurzbz><![CDATA[".$row->buchungstyp_kurzbz."]]></buchungstyp_kurzbz>
			<buchungstyp_beschreibung><![CDATA[".$btyp[$row->buchungstyp_kurzbz]."]]></buchungstyp_beschreibung>
			<updateamum><![CDATA[".$row->updateamum."]]></updateamum>
			<updatevon><![CDATA[".$row->updatevon."]]></updatevon>
			<insertamum><![CDATA[".$row->insertamum."]]></insertamum>
			<credit_points><![CDATA[".$row->credit_points."]]></credit_points>
			<zahlungsreferenz><![CDATA[".$row->zahlungsreferenz."]]></zahlungsreferenz>
			<anmerkung><![CDATA[".$row->anmerkung."]]></anmerkung>
		</buchung>";
	}
	function drawperson_xml($row)
	{
		global $conn, $datum;
		$pers = new person();

		$pers->load($row->person_id);

		$stg = new studiengang($row->studiengang_kz);
		$student_obj = new student();
		$student_obj->load_person($row->person_id, $row->studiengang_kz);

		$prestudent = new prestudent();
		$prestudent->getLastStatus($student_obj->prestudent_id, $row->studiensemester_kurzbz);

		$studiengang_bezeichnung_sto='';
		$studiengang_bezeichnung_sto_englisch='';
		$stpl = new studienplan();
		if($stpl->loadStudienplan($prestudent->studienplan_id))
		{
			$sto = new studienordnung();
			if($sto->loadStudienordnung($stpl->studienordnung_id))
			{
				$studiengang_bezeichnung_sto = $sto->studiengangbezeichnung;
				$studiengang_bezeichnung_sto_englisch = $sto->studiengangbezeichnung_englisch;
			}
		}

		switch($stg->typ)
		{
			case 'b':
				$studTyp = 'Bachelor';
				break;
			case 'm':
				$studTyp = 'Master';
				break;
			case 'd':
				$studTyp = 'Diplom';
				break;
			default:
				$studTyp ='';
		}

		echo "
  		<person>
			<person_id><![CDATA[".$pers->person_id."]]></person_id>
			<anrede><![CDATA[".$pers->anrede."]]></anrede>
			<geschlecht><![CDATA[".$pers->geschlecht."]]></geschlecht>
			<titelpost><![CDATA[".$pers->titelpost."]]></titelpost>
			<titelpre><![CDATA[".$pers->titelpre."]]></titelpre>
			<nachname><![CDATA[".$pers->nachname."]]></nachname>
			<vorname><![CDATA[".$pers->vorname."]]></vorname>
			<vornamen><![CDATA[".$pers->vornamen."]]></vornamen>
			<matr_nr><![CDATA[".$pers->matr_nr."]]></matr_nr>
			<name_gesamt><![CDATA[".trim($pers->anrede.' '.$pers->titelpre.' '.$pers->vorname.' '.$pers->nachname.' '.$pers->titelpost)."]]></name_gesamt>
			<name_titel><![CDATA[".trim($pers->titelpre.' '.$pers->vorname.' '.$pers->nachname.' '.$pers->titelpost)."]]></name_titel>
			<geburtsdatum><![CDATA[".$datum->convertISODate($pers->gebdatum)."]]></geburtsdatum>
			<sozialversicherungsnummer><![CDATA[".$pers->svnr."]]></sozialversicherungsnummer>
			<ersatzkennzeichen><![CDATA[".$pers->ersatzkennzeichen."]]></ersatzkennzeichen>
			<matrikelnr><![CDATA[".trim($student_obj->matrikelnr)."]]></matrikelnr>
			<tagesdatum><![CDATA[".date('d.m.Y')."]]></tagesdatum>
			<logopath>".DOC_ROOT."skin/images/</logopath>
			<studiengang><![CDATA[".$stg->bezeichnung."]]></studiengang>
			<studiengang_bezeichnung_sto><![CDATA[".$studiengang_bezeichnung_sto."]]></studiengang_bezeichnung_sto>
			<studiengang_bezeichnung_sto_englisch><![CDATA[".$studiengang_bezeichnung_sto_englisch."]]></studiengang_bezeichnung_sto_englisch>
			<studiengang_typ><![CDATA[".$studTyp."]]></studiengang_typ>
		</person>";
	}

	$buchungstyp = new konto();
	$buchungstyp->getBuchungstyp();
	$btyp = array();

	foreach ($buchungstyp->result as $row)
		$btyp[$row->buchungstyp_kurzbz]=$row->beschreibung;

	if($person_id!='')
		foreach ($konto->result as $buchung)
			drawrow_xml($buchung);
	elseif($buchungsnummern!='')
	{
		$buchungsnr = explode(';',$buchungsnummern);
		$drawperson=true;
		foreach($buchungsnr as $bnr)
		{
			if($bnr!='')
			{
				$konto->load($bnr);
				if($drawperson)
				{
					drawperson_xml($konto);
					$drawperson=false;
				}
				drawrow_xml($konto);
			}
		}
	}
	else
	{
		drawperson_xml($konto);
		drawrow_xml($konto);
	}

	echo "\n</konto>";
}
?>
