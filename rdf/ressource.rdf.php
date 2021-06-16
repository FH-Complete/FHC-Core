<?php
/* Copyright (C) 2011 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>.
 */

$projekt_kurzbz=(isset($_GET['projekt_kurzbz'])?$_GET['projekt_kurzbz']:null);
$projekt_phase=(isset($_GET['projekt_phase'])?$_GET['projekt_phase']:null);

if($projekt_phase != null && (is_numeric($projekt_phase) == false ))
	die('Ungültige ProjektphasenID');



// header for no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/ressource.class.php');
require_once('../include/basis_db.class.php');

$mitarbeiter = '';
$student='';
$betriebsmittel='';
$firma='';
$rdf_url='http://www.technikum-wien.at/ressource/';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:RESSOURCE="'.$rdf_url.'rdf#"
>
';

$optional = '';
$optional_description = '';
if(isset($_GET['optional']))
{
	$optional.="\n\t\t\t<RDF:li resource=\"".$rdf_url."opt"."\" />";

	$optional_description = '
	<RDF:Description about="'.$rdf_url.'opt" >
    	<RESSOURCE:ressource_id></RESSOURCE:ressource_id>
		<RESSOURCE:bezeichnung></RESSOURCE:bezeichnung>
		<RESSOURCE:typ><![CDATA[Auswahl]]></RESSOURCE:typ>
		<RESSOURCE:beschreibung></RESSOURCE:beschreibung>
		<RESSOURCE:mitarbeiter_uid></RESSOURCE:mitarbeiter_uid>
		<RESSOURCE:student_uid></RESSOURCE:student_uid>
		<RESSOURCE:betriebsmittel_id></RESSOURCE:betriebsmittel_id>
		<RESSOURCE:firma_id></RESSOURCE:firma_id>
		<RESSOURCE:insertamum></RESSOURCE:insertamum>
		<RESSOURCE:insertvon></RESSOURCE:insertvon>
		<RESSOURCE:updateamum></RESSOURCE:updateamum>
		<RESSOURCE:updatevon></RESSOURCE:updatevon>
		<RESSOURCE:rdf_description></RESSOURCE:rdf_description>
  	</RDF:Description>';
}

$ressource = new ressource();

if($projekt_kurzbz!=null)
	$ressource->getProjectRessourcen($projekt_kurzbz);
else if($projekt_phase!= null)
	$ressource->getPhaseRessourcen($projekt_phase);

else
	$ressource->getAllRessourcen();

foreach ($ressource->result as $res)
{
		draw_ressource($res);
}

$seq= "
	<RDF:Seq about=\"".$rdf_url."liste\" >
		<RDF:li>
			<RDF:Seq about=\"".$rdf_url."mitarbeiter\" >$mitarbeiter
			</RDF:Seq>
			<RDF:Seq about=\"".$rdf_url."studenten\" >$student
			</RDF:Seq>
			<RDF:Seq about=\"".$rdf_url."betriebsmittel\" >$betriebsmittel
			</RDF:Seq>
			<RDF:Seq about=\"".$rdf_url."firma\" >$firma
			</RDF:Seq>
		</RDF:li>
	\n\t\t</RDF:Seq>
		<RDF:Seq about=\"".$rdf_url."alle\" >
			$optional
			$mitarbeiter
			$student
			$betriebsmittel
			$firma
	\n\t\t</RDF:Seq>
		";

$seq.="\n\t</RDF:RDF>";


draw_caption('mitarbeiter');
draw_caption('studenten');
draw_caption('betriebsmittel');
draw_caption('firma');
echo $optional_description;
echo $seq;

function draw_caption($name)
{
	global $rdf_url;

		echo '
	<RDF:Description about="'.$rdf_url.$name.'" >
    	<RESSOURCE:ressource_id></RESSOURCE:ressource_id>
		<RESSOURCE:bezeichnung><![CDATA['.ucfirst($name).']]></RESSOURCE:bezeichnung>
		<RESSOURCE:typ><![CDATA['.ucfirst($name).']]></RESSOURCE:typ>
		<RESSOURCE:beschreibung></RESSOURCE:beschreibung>
		<RESSOURCE:mitarbeiter_uid></RESSOURCE:mitarbeiter_uid>
		<RESSOURCE:student_uid></RESSOURCE:student_uid>
		<RESSOURCE:betriebsmittel_id></RESSOURCE:betriebsmittel_id>
		<RESSOURCE:firma_id></RESSOURCE:firma_id>
		<RESSOURCE:insertamum></RESSOURCE:insertamum>
		<RESSOURCE:insertvon></RESSOURCE:insertvon>
		<RESSOURCE:updateamum></RESSOURCE:updateamum>
		<RESSOURCE:updatevon></RESSOURCE:updatevon>
		<RESSOURCE:rdf_description></RESSOURCE:rdf_description>
  	</RDF:Description>
  	';
}


// funktion zum ausgeben der einzelnen ressourcen -> es wird unterschienden ob mitarbeiter/student/betriebsmittel/firma
function draw_ressource($ressource)
{
	global $rdf_url;
	global $mitarbeiter, $student, $betriebsmittel, $firma;

	$db = new basis_db();
	$RdfDescription ='';
	$typ = '';

	// Ressource ist ein Mitarbeiter
	if($ressource->mitarbeiter_uid != '')
	{
		$qry = "SELECT vorname, nachname from campus.vw_mitarbeiter where uid='".addslashes($ressource->mitarbeiter_uid)."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
				$RdfDescription = $row->vorname.' '.$row->nachname;
			else
				die('Fehler beim Laden der Mitarbeiter-daten');
		}
		else
			die('Fehler beim Laden der Mitarbeiter-daten');

		$mitarbeiter.="\n\t\t\t<RDF:li resource=\"".$rdf_url.$ressource->ressource_id.'/'.$ressource->projekt_ressource_id."\" />";
		$typ ='Mitarbeiter';
	}
	// Ressource ist ein Student
	if($ressource->student_uid != '')
	{
		$qry = "SELECT vorname, nachname from campus.vw_student where uid='".addslashes($ressource->student_uid)."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
				$RdfDescription = $row->vorname.' '.$row->nachname;
			else
				die('Fehler beim Laden der Studenten-daten');
		}
		else
			die('Fehler beim Laden der Studenten-daten');

		$student.="\n\t\t\t<RDF:li resource=\"".$rdf_url.$ressource->ressource_id."\" />";
		$typ ='Student';
	}

	// Ressource ist ein Betriebsmittel
	if($ressource->betriebsmittel_id != '')
	{
		$qry = "SELECT betriebsmitteltyp, beschreibung from wawi.tbl_betriebsmittel where betriebsmittel_id='".addslashes($ressource->betriebsmittel_id)."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
				$RdfDescription = $row->betriebsmitteltyp.', '.$row->beschreibung;
			else
				die('Fehler beim Laden der Betriebsmittel-daten');
		}
		else
			die('Fehler beim Laden der Betriebsmittel-daten');

		$betriebsmittel.="\n\t\t\t<RDF:li resource=\"".$rdf_url.$ressource->ressource_id."\" />";
		$typ = 'Betriebsmittel';
	}

	// Ressource ist eine Firma
	if($ressource->firma_id != '')
	{
		$qry = "SELECT name from public.tbl_firma where firma_id='".addslashes($ressource->firma_id)."'";
		if($db->db_query($qry))
		{
			if($row = $db->db_fetch_object())
				$RdfDescription = $row->name;
			else
				die('Fehler beim Laden der Firmen-daten');
		}
		else
			die('Fehler beim Laden der Firmen-daten');

		$firma.="\n\t\t\t<RDF:li resource=\"".$rdf_url.$ressource->ressource_id."\" />";
		$typ = 'Firma';
	}

	echo '
	<RDF:Description about="'.$rdf_url.$ressource->ressource_id.'/'.$ressource->projekt_ressource_id.'" >
    	<RESSOURCE:ressource_id><![CDATA['.$ressource->ressource_id.']]></RESSOURCE:ressource_id>
		<RESSOURCE:bezeichnung><![CDATA['.$ressource->bezeichnung.']]></RESSOURCE:bezeichnung>
		<RESSOURCE:typ><![CDATA['.$typ.']]></RESSOURCE:typ>
		<RESSOURCE:beschreibung><![CDATA['.$ressource->beschreibung.']]></RESSOURCE:beschreibung>
		<RESSOURCE:mitarbeiter_uid><![CDATA['.$ressource->mitarbeiter_uid.']]></RESSOURCE:mitarbeiter_uid>
		<RESSOURCE:student_uid><![CDATA['.$ressource->student_uid.']]></RESSOURCE:student_uid>
		<RESSOURCE:betriebsmittel_id><![CDATA['.$ressource->betriebsmittel_id.']]></RESSOURCE:betriebsmittel_id>
		<RESSOURCE:firma_id><![CDATA['.$ressource->firma_id.']]></RESSOURCE:firma_id>
		<RESSOURCE:insertamum><![CDATA['.$ressource->insertamum.']]></RESSOURCE:insertamum>
		<RESSOURCE:insertvon><![CDATA['.$ressource->insertvon.']]></RESSOURCE:insertvon>
		<RESSOURCE:updateamum><![CDATA['.$ressource->updateamum.']]></RESSOURCE:updateamum>
		<RESSOURCE:updatevon><![CDATA['.$ressource->updatevon.']]></RESSOURCE:updatevon>
		<RESSOURCE:aufwand><![CDATA['.$ressource->aufwand.']]></RESSOURCE:aufwand>
		<RESSOURCE:funktion_kurzbz><![CDATA['.$ressource->funktion_kurzbz.']]></RESSOURCE:funktion_kurzbz>
		<RESSOURCE:projekt_ressource_id><![CDATA['.$ressource->projekt_ressource_id.']]></RESSOURCE:projekt_ressource_id>
		<RESSOURCE:rdf_description><![CDATA['.$RdfDescription.']]></RESSOURCE:rdf_description>
  	</RDF:Description>
  	';
}
?>