<?php
/* Copyright (C) 2004 Technikum-Wien
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
// header for no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/functions.inc.php');
require_once('../include/datum.class.php');

$user = get_uid();
$datum = new datum();

loadVariables($user);

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	die('Filter muss uebergeben werden');

$rdf_url='http://www.technikum-wien.at/person';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:PERSON="'.$rdf_url.'/rdf#"
>


  <RDF:Seq RDF:about="'.$rdf_url.'/liste">
';
$db = new basis_db();
//$filter = utf8_encode($filter);
$qry = "SELECT
			distinct person_id, vorname, nachname, titelpre, titelpost,
			CASE
				WHEN EXISTS (SELECT 1 FROM public.tbl_benutzer JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid) WHERE person_id=tbl_person.person_id) THEN 'Mitarbeiter'
				WHEN EXISTS (SELECT 1 FROM public.tbl_benutzer JOIN public.tbl_student ON(uid=student_uid) WHERE person_id=tbl_person.person_id) THEN 'Student'
				ELSE 'Person'
			END as status
		FROM
			public.tbl_person
		WHERE
			lower(nachname) like '%".$db->db_escape(mb_strtolower($filter))."%'
			OR
			lower(nachname || ' ' || vorname) like '%".$db->db_escape(mb_strtolower($filter))."%'
			OR
			lower(vorname || ' ' || nachname) like '%".$db->db_escape(mb_strtolower($filter))."%' 
		ORDER BY nachname, vorname, titelpre, titelpost";

if(isset($_GET['nurmittitel']))
{
	$qry.=" AND (titelpre<>'' OR titelpost<>'')";
}

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		echo '
		  <RDF:li>
	      	<RDF:Description RDF:about="'.$rdf_url.'/'.$row->person_id.'" >
	        	<PERSON:person_id NC:parseType="Integer"><![CDATA['.$row->person_id.']]></PERSON:person_id>
	        	<PERSON:vorname><![CDATA['.$row->vorname.']]></PERSON:vorname>
	        	<PERSON:nachname><![CDATA['.$row->nachname.']]></PERSON:nachname>
	    		<PERSON:anzeigename><![CDATA['.$row->nachname.' '.$row->vorname.' '.$row->titelpre.' '.$row->titelpost.']]></PERSON:anzeigename>
	    		<PERSON:status><![CDATA['.$row->status.']]></PERSON:status>
	      	</RDF:Description>
	      </RDF:li>
		';
	}
}
?>
	</RDF:Seq>
</RDF:RDF>
