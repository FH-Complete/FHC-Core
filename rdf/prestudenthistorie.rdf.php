<?php
/* Copyright (C) 2006 fhcomplete.org
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
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

require_once('../config/vilesci.config.inc.php');
require_once('../include/person.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/datum.class.php');
require_once('../include/statusgrund.class.php');
require_once('../include/studiengang.class.php');

$rdf_url='http://www.technikum-wien.at/prestudenthistorie';
$datum = new datum();

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ROLLE="'.$rdf_url.'/rdf#"
>

  <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['prestudent_id']) && is_numeric($_GET['prestudent_id']))
	$prestudent_id = $_GET['prestudent_id'];
else
	die('Prestudent_id muss angegeben werden');

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$prestudent = new prestudent($prestudent_id);
$prestudent->getLastStatus($prestudent_id);
$prestudentGET_studiengang = $prestudent->studiengang_kz;
$prestudentGET_orgform_kurzbz = $prestudent->orgform_kurzbz;
$prestudent->getPrestudenten($prestudent->person_id);

foreach ($prestudent->result as $row)
{
	$prestudentLastStatus = new prestudent();
	$prestudentLastStatus->getLastStatus($row->prestudent_id);
	$row->studiensemester_kurzbz = $prestudentLastStatus->studiensemester_kurzbz;
	$row->ausbildungssemester = $prestudentLastStatus->ausbildungssemester;
	$row->datum = $prestudentLastStatus->datum;
	$row->orgform_kurzbz = $prestudentLastStatus->orgform_kurzbz;
	$row->studienplan_bezeichnung = $prestudentLastStatus->studienplan_bezeichnung;
	$row->status_kurzbz = $prestudentLastStatus->status_kurzbz;
	if ($prestudentLastStatus->statusgrund_id != '')
	{
		$statusgrund = new statusgrund($prestudentLastStatus->statusgrund_id);
		$row->statusgrund = $statusgrund->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];
	}
	else
	{
		$row->statusgrund = '';
	}
}

// Sortiert PreStudenten nach Studiensemester
function sortPrestudents($a, $b)
{
	$c = substr($b->studiensemester_kurzbz, 2) - substr($a->studiensemester_kurzbz, 2);
	$c .= strcmp(substr($b->studiensemester_kurzbz, 0, 2), substr($a->studiensemester_kurzbz, 0, 2));
	$c .= $a->priorisierung - $b->priorisierung;
	return $c;
}

usort($prestudent->result, "sortPrestudents");

$studiensemester_kurzbz = '';
$stdsem = '';

foreach ($prestudent->result as $row)
{
	// Allfällige Studentendaten laden
	$uid = '';
	$gruppe = '';
	$status = '';
	$aktiv = 'true';
	$bold = '';
	if ($row->status_kurzbz == 'Abgewiesener' || $row->status_kurzbz == 'Abbrecher' || $row->status_kurzbz == 'Absolvent' )
	{
		$aktiv = 'false';
	}
	$qry ="SELECT * FROM public.tbl_student WHERE prestudent_id='$row->prestudent_id'";
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>1)
		{
			$uid  ='ACHTUNG: Mehrere Studenteneinträge vorhanden!';
		}
		else
		{
			if($row_std = $db->db_fetch_object($result))
			{
				$uid = $row_std->student_uid;
				$gruppe = $row_std->semester.$row_std->verband.$row_std->gruppe;
			}
		}
	}

	$status = $row->status_kurzbz;
	if ($row->ausbildungssemester != '')
	{
		$status .= ' ('.$row->ausbildungssemester.'. Semester)';
	}
	if ($row->statusgrund != '')
	{
		$status .= ' - '.$row->statusgrund;
	}
	if ($row->studiengang_kz == $prestudentGET_studiengang && $row->orgform_kurzbz == $prestudentGET_orgform_kurzbz)
	{
		$bold = 'bold';
	}
	echo '
		<RDF:li>
			<RDF:Description id="'.$row->prestudent_id.'" about="'.$rdf_url.'/'.$row->prestudent_id.'" >
				<ROLLE:prestudent_id><![CDATA['.$row->prestudent_id.']]></ROLLE:prestudent_id>
				<ROLLE:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></ROLLE:studiensemester_kurzbz>
				<ROLLE:prioritaet><![CDATA['.$row->priorisierung.']]></ROLLE:prioritaet>
				<ROLLE:studiengang><![CDATA['.$studiengang->kuerzel_arr[$row->studiengang_kz].']]></ROLLE:studiengang>
				<ROLLE:orgform_kurzbz><![CDATA['.$row->orgform_kurzbz.']]></ROLLE:orgform_kurzbz>
				<ROLLE:studienplan_bezeichnung><![CDATA['.$row->studienplan_bezeichnung.']]></ROLLE:studienplan_bezeichnung>
				<ROLLE:reihung_absolviert><![CDATA[???]]></ROLLE:reihung_absolviert>
				<ROLLE:uid><![CDATA['.$uid.']]></ROLLE:uid>
				<ROLLE:status><![CDATA['.$status.']]></ROLLE:status>
				<ROLLE:aktiv><![CDATA['.$aktiv.']]></ROLLE:aktiv>
				<ROLLE:bold><![CDATA['.$bold.']]></ROLLE:bold>
			</RDF:Description>
		</RDF:li>
	';

}
?>
  </RDF:Seq>
</RDF:RDF>