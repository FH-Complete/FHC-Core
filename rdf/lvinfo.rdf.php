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
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../cis/config.inc.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/lvinfo';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVINFO="<?php echo $rdf_url; ?>/rdf#"
>

<?php
if(isset($_GET['stg_kz']) && is_numeric($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else 
	unset($stg_kz);

if(isset($_GET['semester']))
	if(is_numeric($_GET['semester']))
		$sem = $_GET['semester'];
	else 
		die('Semester muss eine gueltige Zahl sein');
else 
	unset($sem);

$qry = "SET CLIENT_ENCODING to 'UNICODE'; 
SELECT 
tbl_lehrveranstaltung.lehrveranstaltung_id as lv_lehrveranstaltung_id, 
tbl_lehrveranstaltung.kurzbz as lv_kurzbz,
tbl_lehrveranstaltung.lehreverzeichnis as lv_lehrevz,
tbl_lehrveranstaltung.bezeichnung as lv_bezeichnung,
tbl_lehrveranstaltung.bezeichnung_english as lv_bezeichnung_english,
tbl_lehrveranstaltung.studiengang_kz as lv_studiengang_kz,
tbl_lehrveranstaltung.semester as lv_semester,
tbl_lehrveranstaltung.sprache as unterrichtssprache,
tbl_lehrveranstaltung.ects as ects,
tbl_lehrveranstaltung.semesterstunden as lv_semesterstunden,
tbl_lehrveranstaltung.orgform_kurzbz as orgform_kurzbz,
lower(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg_kuerzel,
tbl_lvinfo.*
FROM (lehre.tbl_lehrveranstaltung JOIN campus.tbl_lvinfo USING(lehrveranstaltung_id)) JOIN public.tbl_studiengang USING(studiengang_kz)
WHERE 
tbl_lehrveranstaltung.aktiv=true AND
tbl_lehrveranstaltung.lehre=true AND
tbl_lvinfo.aktiv=true AND
tbl_lvinfo.genehmigt=true ";

if(isset($stg_kz))
	$qry.= " AND tbl_lehrveranstaltung.studiengang_kz='$stg_kz'";

if(isset($sem))
	$qry .= " AND tbl_lehrveranstaltung.semester='$sem'";

$qry .= "ORDER BY lv_studiengang_kz, lv_semester, lv_kurzbz, sprache";

if($result = pg_query($conn, $qry))
{
	$arr = array();
	while($row = pg_fetch_object($result))
	{
?>
      	<RDF:Description  id="<?php echo $row->lv_lehrveranstaltung_id.'/'.$row->sprache; ?>"  about="<?php echo $rdf_url.'/'.$row->lv_lehrveranstaltung_id.'/'.$row->sprache; ?>" >
			<LVINFO:lehrveranstaltung_id><![CDATA[<?php echo $row->lv_lehrveranstaltung_id; ?>]]></LVINFO:lehrveranstaltung_id>
			<LVINFO:kurzbz><![CDATA[<?php echo $row->lv_kurzbz; ?>]]></LVINFO:kurzbz>
			<LVINFO:bezeichnung><![CDATA[<?php echo ($row->sprache=='English'?$row->lv_bezeichnung_english:$row->lv_bezeichnung); ?>]]></LVINFO:bezeichnung>
			<LVINFO:studiengang_kz><![CDATA[<?php echo $row->lv_studiengang_kz; ?>]]></LVINFO:studiengang_kz>
			<LVINFO:semester><![CDATA[<?php echo $row->lv_semester; ?>]]></LVINFO:semester>
			<LVINFO:lehreverzeichnis>cis.technikum-wien.at/documents/<?php echo $row->stg_kuerzel.'/'.$row->lv_semester.'/'.$row->lv_lehrevz;?></LVINFO:lehreverzeichnis>
			<LVINFO:unterrichtssprache><![CDATA[<?php echo $row->unterrichtssprache; ?>]]></LVINFO:unterrichtssprache>
			<LVINFO:ects><![CDATA[<?php echo $row->ects; ?>]]></LVINFO:ects>
			<LVINFO:semesterstunden><![CDATA[<?php echo $row->lv_semesterstunden; ?>]]></LVINFO:semesterstunden>
			<LVINFO:sprache><![CDATA[<?php echo $row->sprache; ?>]]></LVINFO:sprache>
			<LVINFO:titel><![CDATA[<?php echo ($row->sprache=='German'?$row->lv_bezeichnung:$row->lv_bezeichnung_english); ?>]]></LVINFO:titel>
			<LVINFO:lehrziele><![CDATA[<?php echo $row->lehrziele; ?>]]></LVINFO:lehrziele>
			<LVINFO:lehrinhalte><![CDATA[<?php echo $row->lehrinhalte; ?>]]></LVINFO:lehrinhalte>
			<LVINFO:methodik><![CDATA[<?php echo $row->methodik; ?>]]></LVINFO:methodik>
			<LVINFO:voraussetzungen><![CDATA[<?php echo $row->voraussetzungen; ?>]]></LVINFO:voraussetzungen>
			<LVINFO:unterlagen><![CDATA[<?php echo $row->unterlagen; ?>]]></LVINFO:unterlagen>
			<LVINFO:pruefungsordnung><![CDATA[<?php echo $row->pruefungsordnung; ?>]]></LVINFO:pruefungsordnung>
			<LVINFO:anmerkungen><![CDATA[<?php echo $row->anmerkung; ?>]]></LVINFO:anmerkungen>
			<LVINFO:kurzbeschreibung><![CDATA[<?php echo $row->kurzbeschreibung; ?>]]></LVINFO:kurzbeschreibung>
			<LVINFO:orgform_kurzbz><![CDATA[<?php echo $row->orgform_kurzbz; ?>]]></LVINFO:orgform_kurzbz>
      	</RDF:Description>

<?php
		$arr[$row->lv_studiengang_kz][$row->lv_semester][$row->lv_lehrveranstaltung_id][$row->sprache]=$row->lv_lehrveranstaltung_id.'/'.$row->sprache;
	}
	//Hierarchie hinausschreiben
	echo '<RDF:Seq about="'.$rdf_url.'/liste"> '."\n";  
	foreach ($arr as $stg=>$stgitem)
	{
		echo '<RDF:li>'."\n\t".'<RDF:Seq about="'.$stg.'">'."\n";      	
		foreach ($stgitem as $sem=>$semitem)
		{
			echo "\t".'<RDF:li>'."\n\t\t".'<RDF:Seq about="'.$stg.'/'.$sem.'">'."\n";
			foreach ($semitem as $lvid=>$lvitem)
			{
				echo "\t\t<RDF:li>\n\t\t\t".'<RDF:Seq about="'.$rdf_url.'/'.$lvid.'" >'."\n";
				foreach ($lvitem as $sprache=>$spracheitem) 
				{
					echo "\t\t\t\t".'<RDF:li resource="'.$rdf_url.'/'.$lvid.'/'.$sprache.'" />'."\n";
				}
				echo "\t\t\t".'</RDF:Seq>'."\n\t\t".'</RDF:li>'."\n";
			}
			echo "\t\t</RDF:Seq>\n\t</RDF:li>\n";
		}
		echo "\t</RDF:Seq>\n</RDF:li>\n";
	}
	echo "</RDF:Seq>\n";
}
?>
</RDF:RDF>