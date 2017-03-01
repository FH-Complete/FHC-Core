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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

if(isset($_GET['studiengang_kz']))
{
	$stg = $_GET['studiengang_kz'];
	if(!is_numeric($stg))
		die('Ungueltiger Studiengang');
}
else
	$stg = '';

if(isset($_GET['semester']))
{
	$sem = $_GET['semester'];
	if(!is_numeric($sem))
		die('Ungueltiges Semester');
}
else
	$sem = '';
$db = new basis_db();

if(isset($_GET['lehrveranstaltung_id']) && is_numeric($_GET['lehrveranstaltung_id']))
{
	$lvid = $_GET['lehrveranstaltung_id'];

	$qry = "SELECT studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($lvid);


	if($db->db_query($qry))
	{
		if($row = $db->db_fetch_object())
		{
			$stg = $row->studiengang_kz;
			$sem = $row->semester;
		}
		else
			die('Fehler beim Laden der Daten');
	}
	else
		die('Fehler beim Laden der Daten');
}

$rdf_url='http://www.technikum-wien.at/lehrfach';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRFACH="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['lehrfach_id']) && is_numeric($_GET['lehrfach_id']))
{
	$lehrfach_id = $_GET['lehrfach_id'];
	$where =" OR lehrveranstaltung_id=".$db->db_add_param($lehrfach_id);
}
else
	$where = '';

//Alle Lehrfaecher mit Entsprechendem Studiengang und Semester holen bei
//denen sowohl das Lehrfach als auch der Fachbereich aktiv ist und
//zusaetzlich das Lehrfach das uebergeben wurde
$qry = "SELECT
			tbl_lehrveranstaltung.*, tbl_fachbereich.fachbereich_kurzbz
		FROM
			lehre.tbl_lehrveranstaltung
			JOIN public.tbl_fachbereich USING(oe_kurzbz)
		WHERE tbl_lehrveranstaltung.aktiv AND tbl_fachbereich.aktiv";
if($stg!='')
	$qry.=" AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($stg);
if($sem!='')
	$qry.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($sem);

$qry.=$where;
$qry.=" ORDER BY bezeichnung";
if($db->db_query($qry))
{
	while($lehrfach = $db->db_fetch_object())
	{
		echo '
      <RDF:li>
         <RDF:Description  id="'.$lehrfach->lehrveranstaltung_id.'"  about="'.$rdf_url.'/'.$lehrfach->lehrveranstaltung_id.'" >
            <LEHRFACH:lehrfach_id>'.$lehrfach->lehrveranstaltung_id.'</LEHRFACH:lehrfach_id>
            <LEHRFACH:studiengang_kz>'.$lehrfach->studiengang_kz.'</LEHRFACH:studiengang_kz>
            <LEHRFACH:fachbereich_kurzbz><![CDATA['.$lehrfach->fachbereich_kurzbz.']]></LEHRFACH:fachbereich_kurzbz>
            <LEHRFACH:kurzbz><![CDATA['.$lehrfach->kurzbz.']]></LEHRFACH:kurzbz>
            <LEHRFACH:bezeichnung><![CDATA['.$lehrfach->bezeichnung.']]></LEHRFACH:bezeichnung>
            <LEHRFACH:farbe><![CDATA['.$lehrfach->farbe.']]></LEHRFACH:farbe>
            <LEHRFACH:aktiv>'.$lehrfach->aktiv.'</LEHRFACH:aktiv>
            <LEHRFACH:semester>'.$lehrfach->semester.'</LEHRFACH:semester>
            <LEHRFACH:sprache>'.$lehrfach->sprache.'</LEHRFACH:sprache>
         </RDF:Description>
      </RDF:li>';
	}
}

?>
   </RDF:Seq>

</RDF:RDF>
