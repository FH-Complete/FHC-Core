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
// header fuer no cache
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
require_once('../include/fachbereich.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$user = get_uid();
loadVariables($user);

$studiensemester_kurzbz=$semester_aktuell;

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$fb = $rechte->getFbKz();

// fachbereiche holen
//$fachbereichDAO=new fachbereich($conn);
//$fb = $fachbereiche=$fachbereichDAO->getAll();

$qry = "SELECT * FROM public.tbl_fachbereich";

if(count($fb)>0)
{
	$in='';
	foreach($fb as $fbbz)
		$in.= ", '".addslashes($fbbz)."'";
	$qry.=" WHERE fachbereich_kurzbz in ('1'$in)";
}

$qry.=" ORDER BY bezeichnung";

$rdf_url='http://www.technikum-wien.at/fachbereich';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FACHBEREICH="'.$rdf_url.'/rdf#"
>

';

$hier = '';
$lektoren = '';
$lkt = array();
$db = new basis_db();

if($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		echo '
	      	<RDF:Description  id="'.$row->fachbereich_kurzbz.'"  about="'.$rdf_url.'/'.$row->fachbereich_kurzbz.'" >
	    		<FACHBEREICH:kurzbz>'.$row->fachbereich_kurzbz.'</FACHBEREICH:kurzbz>
	    		<FACHBEREICH:bezeichnung><![CDATA['.$row->bezeichnung.']]></FACHBEREICH:bezeichnung>
	    		<FACHBEREICH:farbe>'.$row->farbe.'</FACHBEREICH:farbe>
	    		<FACHBEREICH:studiengang_kz>'.$row->studiengang_kz.'</FACHBEREICH:studiengang_kz>
	    		<FACHBEREICH:uid></FACHBEREICH:uid>
	      	</RDF:Description>
	      	';
	  $hier .= "\n<RDF:li>";
	  $hier .= "\n".'   <RDF:Seq about="'.$rdf_url.'/'.$row->fachbereich_kurzbz.'">'."\n";

	  $qry = "SELECT
				distinct mitarbeiter_uid as uid, tbl_mitarbeiter.kurzbz, vorname, nachname, titelpre, titelpost
			FROM
				campus.vw_lehreinheit JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid)
				JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) JOIN public.tbl_person USING(person_id)
			WHERE
				fachbereich_kurzbz='".addslashes($row->fachbereich_kurzbz)."' AND
				studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
	  //echo $qry;
	  if($result_lkt = $db->db_query($qry))
	  {
	  	while($row_lkt = $db->db_fetch_object($result_lkt))
	  	{
	  		$hier .='      <RDF:li resource="'.$rdf_url.'/'.$row_lkt->uid.'" />'."\n";
	  		if(!in_array($row_lkt->uid, $lkt))
	  		{
	  			$lkt[]=$row_lkt->uid;
	  			$lektoren .='<RDF:Description  id="'.$row_lkt->uid.'"  about="'.$rdf_url.'/'.$row_lkt->uid.'" >
					    		<FACHBEREICH:kurzbz>'.$row_lkt->kurzbz.'</FACHBEREICH:kurzbz>
					    		<FACHBEREICH:bezeichnung><![CDATA['.trim($row_lkt->titelpre.' '.$row_lkt->vorname.' '.$row_lkt->nachname.' '.$row_lkt->titelpost).']]></FACHBEREICH:bezeichnung>
					    		<FACHBEREICH:farbe></FACHBEREICH:farbe>
					    		<FACHBEREICH:studiengang_kz></FACHBEREICH:studiengang_kz>
					    		<FACHBEREICH:uid><![CDATA['.$row_lkt->uid.']]></FACHBEREICH:uid>
					      	</RDF:Description>';
	  		}
	  	}
	  }
	  $hier .= "\n   </RDF:Seq>";
	  $hier .= "\n</RDF:li>";
	}
}

echo $lektoren;
echo '<RDF:Seq about="'.$rdf_url.'/liste">';
echo $hier;
echo '</RDF:Seq>';
?>

</RDF:RDF>