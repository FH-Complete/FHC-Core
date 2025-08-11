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
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/reihungstest.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/prestudent.class.php');

$rdf_url='http://www.technikum-wien.at/reihungstest';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:RT="<?php echo $rdf_url; ?>/rdf#"
>

<RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
	<RDF:li>
		<RDF:Description  id=""  about="'.$rdf_url.'/" >
			<RT:reihungstest_id></RT:reihungstest_id>
			<RT:studiengang_kz></RT:studiengang_kz>
			<RT:ort_kurzbz></RT:ort_kurzbz>
			<RT:anmerkung></RT:anmerkung>
			<RT:datum></RT:datum>
			<RT:uhrzeit></RT:uhrzeit>
			<RT:bezeichnung>-- keine Auswahl --</RT:bezeichnung>
		</RDF:Description>
	</RDF:li>

';
}

$stg = array();
$stg_obj = new studiengang();
$stg_obj->getAll();
foreach ($stg_obj->result as $row)
	$stg[$row->studiengang_kz]=$row->kuerzel;

$rt = new reihungstest();

if(isset($_GET['include_id']) && isset($_GET['studiengang_kz']))
{
	$include_id=$_GET['include_id'];
	$studiengang_kz=$_GET['studiengang_kz'];
	$rt->getZukuenftige($include_id, $studiengang_kz);
}
elseif(isset($_GET['prestudent_id']))
{
	$include_ids=array();
	$prestudent = new prestudent();
	$prestudent->getPrestudentRolle($_GET['prestudent_id'], 'Interessent');
	$studienplan_arr = array();
	foreach($prestudent->result as $row)
	{
		$studienplan_arr[] = $row->studienplan_id;
	}

	// Zusaetzlich auch die Studienplaene holen bei denen die
	// Person schon zu Reihungstests zugeordnet ist
	$prestudent = new prestudent();
	$prestudent->load($_GET['prestudent_id']);
	$rt_help = new reihungstest();
	$rt_help->getReihungstestPerson($prestudent->person_id);
	foreach($rt_help->result as $row)
	{
		$studienplan_arr[] = $row->studienplan_id;
		$include_ids[] = $row->reihungstest_id;
	}

	$rt->getReihungstestStudienplan($studienplan_arr, $include_ids);
}
else
	$rt->getAll();

foreach ($rt->result as $row)
{
	$freieplaetze = '';
	if(isset($row->angemeldete_teilnehmer))
	{
		if($row->max_teilnehmer!='' && $row->max_teilnehmer>0)
			$freieplaetze = ' ('.$row->angemeldete_teilnehmer.'/'.$row->max_teilnehmer.')';
	}

	$bezeichnung = $row->studiensemester_kurzbz.' St.'.$row->stufe.' '.(array_key_exists($row->studiengang_kz, $stg)?$stg[$row->studiengang_kz].' ':'').$row->datum.' '.$row->uhrzeit.' '.$row->ort_kurzbz.' '.$row->anmerkung.$freieplaetze;


	// Convert date string into timestamp
	$unixTimestamp = strtotime($row->datum);

	// Get the day of the week
	$dayOfWeek = date("l", $unixTimestamp);
	switch($dayOfWeek)
	{
		case 'Monday':
			$dayOfWeek = 'Mo';
			break;
		case 'Tuesday':
			$dayOfWeek = 'Di';
			break;
		case 'Wednesday':
			$dayOfWeek = 'Mi';
			break;
		case 'Thursday':
			$dayOfWeek = 'Do';
			break;
		case 'Friday':
			$dayOfWeek = 'Fr';
			break;
		case 'Saturday':
			$dayOfWeek = 'Sa';
			break;
		case 'Sunday':
			$dayOfWeek = 'So';
			break;
	}
?>
	<RDF:li>
		<RDF:Description  id="<?php echo $row->reihungstest_id; ?>"  about="<?php echo $rdf_url.'/'.$row->reihungstest_id; ?>" >
			<RT:reihungstest_id><![CDATA[<?php echo $row->reihungstest_id;  ?>]]></RT:reihungstest_id>
			<RT:studiengang_kz><![CDATA[<?php echo $row->studiengang_kz;  ?>]]></RT:studiengang_kz>
			<RT:ort_kurzbz><![CDATA[<?php echo $row->ort_kurzbz;  ?>]]></RT:ort_kurzbz>
			<RT:anmerkung><![CDATA[<?php echo $row->anmerkung;  ?>]]></RT:anmerkung>
			<RT:datum><![CDATA[<?php echo $row->datum;  ?>]]></RT:datum>
			<RT:uhrzeit><![CDATA[<?php echo $row->uhrzeit;  ?>]]></RT:uhrzeit>
			<RT:bezeichnung><![CDATA[<?php echo $bezeichnung. ' ('. $dayOfWeek. ')' ;  ?>]]></RT:bezeichnung>
		</RDF:Description>
	</RDF:li>
<?php
}
?>
	</RDF:Seq>
</RDF:RDF>
