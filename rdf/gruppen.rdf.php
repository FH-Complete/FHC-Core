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
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/datum.class.php');
require_once('../include/basis_db.class.php');

$filter = null;
$aufnahmegruppe = null;

if(isset($_GET['filter']))
{
	$filter = strtolower($_GET['filter']);
}
elseif(isset($_GET['aufnahmegruppe']))
{
	$aufnahmegruppe = true;
}
else
{
	if(isset($_GET['uid']))
		$uid = $_GET['uid'];
	else
		die('uid muss uebergeben werden');

	if(isset($_GET['studiensemester_kurzbz']))
		$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
	else
		die('studiensemester_kurzbz muss uebergeben werden');
}

$datum = new datum();


$rdf_url='http://www.technikum-wien.at/gruppen';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:GRP="'.$rdf_url.'/rdf#"
>
	<RDF:Seq about="'.$rdf_url.'/liste">
';

$db = new basis_db();

if($filter)
{
	$qry = "SELECT * FROM tbl_gruppe WHERE LOWER(gruppe_kurzbz) LIKE '%" . $db->db_escape($filter) . "%'";
}
elseif($aufnahmegruppe)
{
	$qry = "SELECT * FROM tbl_gruppe WHERE aufnahmegruppe=true";
}
else
	$qry = "SELECT * FROM public.tbl_benutzergruppe JOIN tbl_gruppe using(gruppe_kurzbz) WHERE uid=".$db->db_add_param($uid)." AND (studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." OR studiensemester_kurzbz is null)";

if(isset($_GET['optional']))
{
	echo '
		<RDF:li>
			<RDF:Description  id=""  about="'.$rdf_url.'/" >
				<GRP:gruppe_kurzbz><![CDATA[]]></GRP:gruppe_kurzbz>
				<GRP:bezeichnung><![CDATA[-- keine Auswahl --]]></GRP:bezeichnung>
				<GRP:generiert><![CDATA[]]></GRP:generiert>
			</RDF:Description>
		</RDF:li>
		';
}
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		if($filter || $aufnahmegruppe)
		{
			echo '
				<RDF:li>
					<RDF:Description  id="'.$row->gruppe_kurzbz.'"  about="'.$rdf_url.'/'.$row->gruppe_kurzbz.'" >
						<GRP:gruppe_kurzbz><![CDATA['.$row->gruppe_kurzbz.']]></GRP:gruppe_kurzbz>
						<GRP:bezeichnung><![CDATA['.$row->bezeichnung.']]></GRP:bezeichnung>
						<GRP:generiert><![CDATA['.($row->generiert=='t'?'Ja':'Nein').']]></GRP:generiert>
					</RDF:Description>
				</RDF:li>
				';
		}
		else
		{
			echo '
				<RDF:li>
					<RDF:Description  id="'.$row->uid.'/'.$row->gruppe_kurzbz.'"  about="'.$rdf_url.'/'.$row->uid.'/'.$row->gruppe_kurzbz.'" >
						<GRP:gruppe_kurzbz><![CDATA['.$row->gruppe_kurzbz.']]></GRP:gruppe_kurzbz>
						<GRP:bezeichnung><![CDATA['.$row->bezeichnung.']]></GRP:bezeichnung>
						<GRP:generiert><![CDATA['.($row->generiert=='t'?'Ja':'Nein').']]></GRP:generiert>
						<GRP:uid><![CDATA['.$row->uid.']]></GRP:uid>
						<GRP:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></GRP:studiensemester_kurzbz>
					</RDF:Description>
				</RDF:li>
				';
		}
	}
}
?>
	</RDF:Seq>
</RDF:RDF>