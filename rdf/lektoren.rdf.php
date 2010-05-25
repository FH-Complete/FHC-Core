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
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/fachbereich.class.php');

if(isset($_GET['studiengang_kz']))
{
	$stg = $_GET['studiengang_kz'];
	$obj = new studiengang();
	if (!$obj->load($stg))
		die($obj->errormsg);
}
else
	$stg = '';

if(isset($_GET['institut']))
{
	$institut = $_GET['institut'];
	$obj = new fachbereich();
	if (!$obj->load($institut))
		die($obj->errormsg);
}
else
	$institut = '';

if (!isset($obj))
	die('No Parameters!');
	
// content type setzen
header("Content-type: application/xhtml+xml");
	// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	
$db = new basis_db();

$rdf_url='http://www.technikum-wien.at/lektoren';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEKTOREN="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';

//Alle Lehrfaecher mit Entsprechendem Studiengang und Semester holen bei 
//denen sowohl das Lehrfach als auch der Fachbereich aktiv ist und
//zusaetzlich das Lehrfach das uebergeben wurde
$qry = "SELECT
			uid,fixangestellt,person_id,alias,anrede,titelpost,titelpre,nachname,vorname
		FROM
			campus.vw_mitarbeiter join public.tbl_benutzerfunktion USING (uid)
		WHERE
			funktion_kurzbz='oezuordnung' AND aktiv AND oe_kurzbz='$obj->oe_kurzbz'
		ORDER BY
			nachname,vorname,vornamen;";

if($db->db_query($qry))
{
	while($lektoren = $db->db_fetch_object())
	{
		echo '
      <RDF:li>
         <RDF:Description  id="'.$lektoren->uid.'"  about="'.$rdf_url.'/'.$lektoren->uid.'" >
            <LEKTOREN:uid><![CDATA['.$lektoren->uid.']]></LEKTOREN:uid>
            <LEKTOREN:person_id><![CDATA['.$lektoren->person_id.']]></LEKTOREN:person_id>
            <LEKTOREN:anrede><![CDATA['.$lektoren->anrede.']]></LEKTOREN:anrede>
            <LEKTOREN:titelpre><![CDATA['.$lektoren->titelpre.']]></LEKTOREN:titelpre>
            <LEKTOREN:vorname><![CDATA['.$lektoren->vorname.']]></LEKTOREN:vorname>
            <LEKTOREN:nachname><![CDATA['.$lektoren->nachname.']]></LEKTOREN:nachname>
            <LEKTOREN:titelpost><![CDATA['.$lektoren->titelpost.']]></LEKTOREN:titelpost>
            <LEKTOREN:email>'
            .($lektoren->alias=='' ? $lektoren->uid : $lektoren->alias).'@'.DOMAIN.
            '</LEKTOREN:email> 
            <LEKTOREN:fixangestellt>'
            .(strtolower($lektoren->fixangestellt)=='t' ? 'TRUE' : 'FALSE').
            '</LEKTOREN:fixangestellt> 
         </RDF:Description>
      </RDF:li>';
	}
}

?>
   </RDF:Seq>

</RDF:RDF>