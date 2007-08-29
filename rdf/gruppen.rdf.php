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
require_once('../vilesci/config.inc.php');
require_once('../include/datum.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	die('uid muss uebergeben werden');
	
if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else
	die('studiensemester_kurzbz muss uebergeben werden');

$datum = new datum();

	
$rdf_url='http://www.technikum-wien.at/gruppen';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:GRP="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

$qry = "SELECT * FROM public.tbl_benutzergruppe JOIN tbl_gruppe using(gruppe_kurzbz) WHERE uid='".addslashes($uid)."' AND (studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' OR studiensemester_kurzbz is null)";
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		
		echo '
	      <RDF:li>
	         <RDF:Description  id="'.$row->uid.'/'.$row->gruppe_kurzbz.'"  about="'.$rdf_url.'/'.$row->uid.'/'.$row->gruppe_kurzbz.'" >
	            <GRP:gruppe_kurzbz><![CDATA['.$row->gruppe_kurzbz.']]></GRP:gruppe_kurzbz>
	            <GRP:generiert><![CDATA['.($row->generiert=='t'?'Ja':'Nein').']]></GRP:generiert>
	            <GRP:uid><![CDATA['.$row->uid.']]></GRP:uid>
	            <GRP:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></GRP:studiensemester_kurzbz>
	         </RDF:Description>
	      </RDF:li>
	      ';
	}
}
?>
   </RDF:Seq>
</RDF:RDF>