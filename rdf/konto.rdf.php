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
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header fuer no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml

// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/konto.class.php');
require_once('../include/person.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
//require_once('../include/functions.inc.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$hier='';
if(isset($_GET['xmlformat']))
{
	echo '<?xml version="1.0" encoding="ISO-8859-15" standalone="yes"?>';
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


	
$datum = new datum();
if($xmlformat=='rdf')
	$konto = new konto($conn, null, true);
else
	$konto = new konto($conn, null, false);

if($person_id!='')
{
	$konto->getBuchungen($person_id, $filter);
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

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:KONTO="<?php echo $rdf_url; ?>/rdf#"
>

<?php
function drawrow($row)
{
	global $rdf_url, $datum;
	echo "
  		<RDF:Description  id=\"".$row->buchungsnr."\"  about=\"".$rdf_url.'/'.$row->buchungsnr."\" >
			<KONTO:buchungsnr><![CDATA[".$row->buchungsnr."]]></KONTO:buchungsnr>
			<KONTO:person_id><![CDATA[".$row->person_id."]]></KONTO:person_id>
			<KONTO:studiengang_kz><![CDATA[".$row->studiengang_kz."]]></KONTO:studiengang_kz>
			<KONTO:studiensemester_kurzbz><![CDATA[".$row->studiensemester_kurzbz."]]></KONTO:studiensemester_kurzbz>
			<KONTO:buchungsnr_verweis><![CDATA[".$row->buchungsnr_verweis."]]></KONTO:buchungsnr_verweis>
			<KONTO:betrag><![CDATA[".$row->betrag."]]></KONTO:betrag>
			<KONTO:buchungsdatum_iso><![CDATA[".$row->buchungsdatum."]]></KONTO:buchungsdatum_iso>
			<KONTO:buchungsdatum><![CDATA[".$datum->convertISODate($row->buchungsdatum)."]]></KONTO:buchungsdatum>
			<KONTO:buchungstext><![CDATA[".$row->buchungstext."]]></KONTO:buchungstext>
			<KONTO:mahnspanne><![CDATA[".$row->mahnspanne."]]></KONTO:mahnspanne>
			<KONTO:buchungstyp_kurzbz><![CDATA[".$row->buchungstyp_kurzbz."]]></KONTO:buchungstyp_kurzbz>
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
?>
</RDF:RDF>
<?php
} //endof xmlformat==rdf
// ----------------------------------- XML --------------------------------------
elseif ($xmlformat=='xml')
{
	echo "<konto>\n";
	function drawrow_xml($row)
	{
		global $datum, $btyp;
		
		echo "
  		<buchung>
			<buchungsnr><![CDATA[".$row->buchungsnr."]]></buchungsnr>
			<person_id><![CDATA[".$row->person_id."]]></person_id>
			<studiengang_kz><![CDATA[".$row->studiengang_kz."]]></studiengang_kz>
			<studiensemester_kurzbz><![CDATA[".$row->studiensemester_kurzbz."]]></studiensemester_kurzbz>
			<buchungsnr_verweis><![CDATA[".$row->buchungsnr_verweis."]]></buchungsnr_verweis>
			<betrag><![CDATA[".sprintf('%.2f',$row->betrag*(-1))."]]></betrag>
			<buchungsdatum><![CDATA[".$datum->convertISODate($row->buchungsdatum)."]]></buchungsdatum>
			<buchungstext><![CDATA[".$row->buchungstext."]]></buchungstext>
			<mahnspanne><![CDATA[".$row->mahnspanne."]]></mahnspanne>
			<buchungstyp_kurzbz><![CDATA[".$row->buchungstyp_kurzbz."]]></buchungstyp_kurzbz>
			<buchungstyp_beschreibung><![CDATA[".$btyp[$row->buchungstyp_kurzbz]."]]></buchungstyp_beschreibung>
			<updateamum><![CDATA[".$row->updateamum."]]></updateamum>
			<updatevon><![CDATA[".$row->updatevon."]]></updatevon>
			<insertamum><![CDATA[".$row->insertamum."]]></insertamum>
		</buchung>";
	}
	function drawperson_xml($row)
	{
		global $conn, $datum;
		$pers = new person($conn, null, null);
		
		$pers->load($row->person_id);
		
		$stg = new studiengang($conn, $row->studiengang_kz, null);
		
		echo "
  		<person>
			<person_id><![CDATA[".$pers->person_id."]]></person_id>
			<anrede><![CDATA[".$pers->anrede."]]></anrede>
			<titelpost><![CDATA[".$pers->titelpost."]]></titelpost>
			<titelpre><![CDATA[".$pers->titelpre."]]></titelpre>
			<nachname><![CDATA[".$pers->nachname."]]></nachname>
			<vorname><![CDATA[".$pers->vorname."]]></vorname>
			<vornamen><![CDATA[".$pers->vornamen."]]></vornamen>
			<geburtsdatum><![CDATA[".$datum->convertISODate($pers->gebdatum)."]]></geburtsdatum>
			<sozialversicherungsnummer><![CDATA[".$pers->svnr."]]></sozialversicherungsnummer>
			<ersatzkennzeichen><![CDATA[".$pers->ersatzkennzeichen."]]></ersatzkennzeichen>
			<tagesdatum><![CDATA[".date('d.m.Y')."]]></tagesdatum>
			<studiengang><![CDATA[".$stg->bezeichnung."]]></studiengang>
		</person>";
	}

	$buchungstyp = new konto($conn);
	$buchungstyp->getBuchungstyp();
	$btyp = array();
	
	foreach ($buchungstyp->result as $row)
		$btyp[$row->buchungstyp_kurzbz]=$row->beschreibung;	
	
	if($person_id!='')
		foreach ($konto->result as $buchung)
			drawrow_xml($buchung);
	elseif($buchungsnummern!='')
	{
		$buchungsnr = split(';',$buchungsnummern);
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