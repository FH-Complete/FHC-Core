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

// header f�r no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
	
$rdf_url='http://www.technikum-wien.at/note';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NOTE="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';
   
//Daten holen
$qry = "SET CLIENT_ENCODING TO 'UNICODE';SELECT * FROM lehre.tbl_note ORDER BY note";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		echo '
			  <RDF:li>
		         <RDF:Description  id="'.$row->note.'"  about="'.$rdf_url.'/'.$row->note.'" >
					<NOTE:note><![CDATA['.$row->note.']]></NOTE:note>
					<NOTE:bezeichnung><![CDATA['.$row->bezeichnung.']]></NOTE:bezeichnung>
					<NOTE:anmerkung><![CDATA['.$row->anmerkung.']]></NOTE:anmerkung>
		         </RDF:Description>
		      </RDF:li>';
	}
}
?>
   </RDF:Seq>
</RDF:RDF>