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

$rdf_url='http://www.technikum-wien.at/prestudentrolle';
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

if(isset($_GET['status_kurzbz']))
	$status_kurzbz = $_GET['status_kurzbz'];
else
	$status_kurzbz=null;

if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else
	$studiensemester_kurzbz=null;

if(isset($_GET['ausbildungssemester']))
	$ausbildungssemester=$_GET['ausbildungssemester'];
else
	$ausbildungssemester=null;

$ps = new prestudent();
$ps->getPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, 'datum desc, insertamum desc', $ausbildungssemester);

$statusgrund = new statusgrund();
$statusgrund->getAll();
$statusgrund_arr = array();
foreach($statusgrund->result as $row)
	$statusgrund_arr[$row->statusgrund_id]=$row->bezeichnung_mehrsprachig[DEFAULT_LANGUAGE];

foreach($ps->result as $row)
{

	echo '
	  <RDF:li>
      	<RDF:Description  id="'.$row->prestudent_id.'/'.$row->status_kurzbz.'/'.$row->studiensemester_kurzbz.'/'.$row->ausbildungssemester.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'/'.$row->status_kurzbz.'/'.$row->studiensemester_kurzbz.'/'.$row->ausbildungssemester.'" >
        	<ROLLE:prestudent_id><![CDATA['.$row->prestudent_id.']]></ROLLE:prestudent_id>
        	<ROLLE:status_kurzbz><![CDATA['.$row->status_kurzbz.']]></ROLLE:status_kurzbz>
        	<ROLLE:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></ROLLE:studiensemester_kurzbz>
        	<ROLLE:ausbildungssemester><![CDATA['.$row->ausbildungssemester.']]></ROLLE:ausbildungssemester>
        	<ROLLE:datum><![CDATA['.$datum->convertISODate($row->datum).']]></ROLLE:datum>
        	<ROLLE:datum_iso><![CDATA['.$row->datum.']]></ROLLE:datum_iso>
        	<ROLLE:orgform_kurzbz><![CDATA['.$row->orgform_kurzbz.']]></ROLLE:orgform_kurzbz>
        	<ROLLE:studienplan_id><![CDATA['.$row->studienplan_id.']]></ROLLE:studienplan_id>
        	<ROLLE:studienplan_bezeichnung><![CDATA['.$row->studienplan_bezeichnung.']]></ROLLE:studienplan_bezeichnung>
        	<ROLLE:bestaetigt_von><![CDATA['.$row->bestaetigtvon.']]></ROLLE:bestaetigt_von>
        	<ROLLE:bestaetigt_am><![CDATA['.$datum->convertISODate($row->bestaetigtam).']]></ROLLE:bestaetigt_am>
			<ROLLE:bewerbung_abgeschicktamum><![CDATA['.$datum->convertISODate($row->bewerbung_abgeschicktamum).']]></ROLLE:bewerbung_abgeschicktamum>
        	<ROLLE:anmerkung><![CDATA['.$row->anmerkung_status.']]></ROLLE:anmerkung>
			<ROLLE:rt_stufe><![CDATA['.$row->rt_stufe.']]></ROLLE:rt_stufe>
			<ROLLE:statusgrund_id><![CDATA['.$row->statusgrund_id.']]></ROLLE:statusgrund_id>
			<ROLLE:statusgrund><![CDATA['.(isset($statusgrund_arr[$row->statusgrund_id])?$statusgrund_arr[$row->statusgrund_id]:'').']]></ROLLE:statusgrund>
      	</RDF:Description>
      </RDF:li>
	';

}
?>
  </RDF:Seq>
</RDF:RDF>
