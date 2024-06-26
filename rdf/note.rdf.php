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
require_once('../include/functions.inc.php');
require_once('../include/basis_db.class.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rdf_url='http://www.technikum-wien.at/note';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTE="'.$rdf_url.'/rdf#"
>
	<RDF:Seq about="'.$rdf_url.'/liste">
';

//Daten holen
$qry = 'SELECT * FROM lehre.tbl_note ORDER BY note';
if(isset($_GET['optional']))
{
	echo '
		<RDF:li>
			<RDF:Description  id=""  about="'.$rdf_url.'/" >
				<NOTE:note><![CDATA[]]></NOTE:note>
				<NOTE:bezeichnung><![CDATA[-- keine Auswahl --]]></NOTE:bezeichnung>
				<NOTE:anmerkung><![CDATA[]]></NOTE:anmerkung>
				<NOTE:aktiv><![CDATA[]]></NOTE:aktiv>
			</RDF:Description>
		</RDF:li>';
}
$db = new basis_db();

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		echo '
			<RDF:li>
				<RDF:Description  id="'.$row->note.'"  about="'.$rdf_url.'/'.$row->note.'" >
					<NOTE:note><![CDATA['.$row->note.']]></NOTE:note>
					<NOTE:bezeichnung><![CDATA['.$row->bezeichnung.']]></NOTE:bezeichnung>
					<NOTE:anmerkung><![CDATA['.$row->anmerkung.']]></NOTE:anmerkung>
					<NOTE:aktiv><![CDATA['.($db->db_parse_bool($row->aktiv)?'true':'false') .']]></NOTE:aktiv>
				</RDF:Description>
			</RDF:li>';
	}
}
?>
   </RDF:Seq>
</RDF:RDF>