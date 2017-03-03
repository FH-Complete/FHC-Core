<?php
/* Copyright (C) 2015 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
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
require_once('../include/basis_db.class.php');

$db = new basis_db();
$data = array();

// Daten ermitteln
$qry = "SELECT begruendung_id, bezeichnung FROM lehre.tbl_anrechnung_begruendung";

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		$data[] = $row;
	}
}

$rdf_url='http://www.technikum-wien.at/anrechnungbegruendung';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BEGRUENDUNG="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

// AUSGABE
foreach($data as $row)
{
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->begruendung_id.'"  about="'.$rdf_url.'/'.$row->begruendung_id.'" >
            <BEGRUENDUNG:begruendung_id><![CDATA['.$row->begruendung_id.']]></BEGRUENDUNG:begruendung_id>
            <BEGRUENDUNG:bezeichnung><![CDATA['.$row->bezeichnung.']]></BEGRUENDUNG:bezeichnung>
         </RDF:Description>
      </RDF:li>
      ';
}
?>

	</RDF:Seq>
</RDF:RDF>