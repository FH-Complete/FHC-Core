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
require_once('../include/dokument.class.php');
require_once('../include/datum.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/dokumentprestudent';
	
$datum = new datum();

if(isset($_GET['prestudent_id']))
	if(is_numeric($_GET['prestudent_id']))
		$prestudent_id=$_GET['prestudent_id'];
	else 
		die('Prestudent_id ist ungueltig');
else 
	die('Fehlerhafte Parameteruebergabe');
	
$dok = new dokument($conn, null, null, true);
if(!$dok->getPrestudentDokumente($prestudent_id))
	die($dok->errormsg);
	
echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:DOKUMENT="'.$rdf_url.'/rdf#"
>

  <RDF:Seq about="'.$rdf_url.'/liste">
';

foreach ($dok->result as $row)
{
	echo '
	  <RDF:li>
	      	<RDF:Description  id="'.$row->dokument_kurzbz.'/'.$row->prestudent_id.'"  about="'.$rdf_url.'/'.$row->dokument_kurzbz.'/'.$row->prestudent_id.'" >
	        	<DOKUMENT:dokument_kurzbz><![CDATA['.$row->dokument_kurzbz.']]></DOKUMENT:dokument_kurzbz>
	    		<DOKUMENT:prestudent_id><![CDATA['.$row->prestudent_id.']]></DOKUMENT:prestudent_id>
	    		<DOKUMENT:mitarbeiter_uid><![CDATA['.$row->mitarbeiter_uid.']]></DOKUMENT:mitarbeiter_uid>
	    		<DOKUMENT:datum><![CDATA['.$datum->convertISODate($row->datum).']]></DOKUMENT:datum>
	    		<DOKUMENT:datum_iso><![CDATA['.$row->datum.']]></DOKUMENT:datum_iso>
	    		<DOKUMENT:bezeichnung><![CDATA['.$row->bezeichnung.']]></DOKUMENT:bezeichnung>
	      	</RDF:Description>
	  </RDF:li>
	';
}

?>

  </RDF:Seq>
</RDF:RDF>