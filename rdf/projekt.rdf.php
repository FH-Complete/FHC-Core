<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/projekt.class.php');
require_once('../include/datum.class.php');

$rdf_url='http://www.technikum-wien.at/projekt/';

if(isset($_GET['oe']))
	$oe=$_GET['oe'];
else 
	$oe=null;
$projekt_obj = new projekt();
if(isset($_REQUEST['filter']))
{
    if($_REQUEST['filter']=='' || $_REQUEST['filter']=='alle')
        $projekt_obj->getProjekte($oe);
    if($_REQUEST['filter']=='aktuell')
        $projekt_obj->getProjekteAktuell(false, $oe);
    if ($_REQUEST['filter'] =='kommende')
        $projekt_obj->getProjekteAktuell(true, $oe);
}
else
    $projekt_obj->getProjekte($oe);

//var_dump($projekt_obj);
$datum_obj = new datum();
echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PROJEKT="'.$rdf_url.'rdf#"
>';

$descr='';
$sequenz='';
for ($i=0;$i<count($projekt_obj->result);$i++)
{
	$projekt=$projekt_obj->result[$i];
	$descr.='<RDF:Description RDF:about="'.$rdf_url.$projekt->projekt_kurzbz.'" >
		<PROJEKT:projekt_kurzbz>'.$projekt->projekt_kurzbz.'</PROJEKT:projekt_kurzbz>
		<PROJEKT:oe_kurzbz>'.$projekt->oe_kurzbz.'</PROJEKT:oe_kurzbz>
		<PROJEKT:nummer><![CDATA['.$projekt->nummer.']]></PROJEKT:nummer>
		<PROJEKT:titel><![CDATA['.$projekt->titel.']]></PROJEKT:titel>
		<PROJEKT:beschreibung><![CDATA['.$projekt->beschreibung.']]></PROJEKT:beschreibung>
		<PROJEKT:beginn_iso>'.$projekt->beginn.'</PROJEKT:beginn_iso>
		<PROJEKT:beginn>'.$datum_obj->formatDatum($projekt->beginn,'d.m.Y').'</PROJEKT:beginn>
		<PROJEKT:ende_iso>'.$projekt->ende.'</PROJEKT:ende_iso>
		<PROJEKT:ende>'.$datum_obj->formatDatum($projekt->ende,'d.m.Y').'</PROJEKT:ende>
		<PROJEKT:budget>'.$projekt->budget.'</PROJEKT:budget>
      <PROJEKT:farbe>'.$projekt->farbe.'</PROJEKT:farbe>
      <PROJEKT:anzahl_ma>'.$projekt->anzahl_ma.'</PROJEKT:anzahl_ma>
      <PROJEKT:aufwand_pt>'.$projekt->aufwand_pt.'</PROJEKT:aufwand_pt>
      <PROJEKT:aufwandstyp_kurzbz>'.$projekt->aufwandstyp_kurzbz.'</PROJEKT:aufwandstyp_kurzbz>
      <PROJEKT:zeitaufzeichnung>'.$projekt->zeitaufzeichnung.'</PROJEKT:zeitaufzeichnung>
	</RDF:Description>'."\n";

	$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$projekt->projekt_kurzbz.'" />'."\n";
}
$sequenz='<RDF:Seq about="'.$rdf_url.'alle-projekte">'."\n\t".$sequenz.'
  	</RDF:Seq>'."\n";
echo $descr."\n";
echo $sequenz;
?>
</RDF:RDF>
