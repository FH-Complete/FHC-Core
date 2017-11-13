<?php
/* Copyright (C) 2011 Technikum-Wien
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
 * Authors: Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/dms.class.php');

$rdf_url='http://www.technikum-wien.at/dms/';


$dms = new dms();
$projekt_kurzbz='';
$projektphase_id='';
if(isset($_GET['projekt_kurzbz']))
{
	$projekt_kurzbz=$_GET['projekt_kurzbz'];
	$dms->getDokumenteProjekt($projekt_kurzbz);
}
elseif(isset($_GET['projektphase_id']))
{
	$projektphase_id = $_GET['projektphase_id'];
	$dms->getDokumenteProjektphase($projektphase_id);
}
elseif(isset($_GET['filter']))
{
	$filter = $_GET['filter'];
	$dms->search($filter);
}
elseif(isset($_GET['notiz_id']))
{
    $notiz_id = $_GET['notiz_id'];
    $dms->getDokumenteNotiz($notiz_id);
}
else
	die('projekt_kurzbz oder projektphase_id muss uebergeben werden');

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:DMS="'.$rdf_url.'rdf#"
>';

$descr='';
$sequenz='';
foreach($dms->result as $row)
{
	$descr.='<RDF:Description RDF:about="'.$rdf_url.$row->dms_id.'" >
		<DMS:dms_id>'.$row->dms_id.'</DMS:dms_id>
		<DMS:projekt_kurzbz>'.$projekt_kurzbz.'</DMS:projekt_kurzbz>
		<DMS:projektphase_id>'.$projektphase_id.'</DMS:projektphase_id>
		<DMS:name><![CDATA['.$row->name.']]></DMS:name>
		<DMS:insertamum>'.$row->insertamum.'</DMS:insertamum>
		<DMS:updateamum>'.$row->updateamum.'</DMS:updateamum>
		<DMS:insertvon>'.$row->insertvon.'</DMS:insertvon>
		<DMS:updatevon>'.$row->updatevon.'</DMS:updatevon>
	</RDF:Description>'."\n";

	$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$row->dms_id.'" />'."\n";
}
$sequenz='<RDF:Seq about="'.$rdf_url.'liste">'."\n\t".$sequenz.'
  	</RDF:Seq>'."\n";
echo $descr."\n";
echo $sequenz;
echo '</RDF:RDF>'; 
?>