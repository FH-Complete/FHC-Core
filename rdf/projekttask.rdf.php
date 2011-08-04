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
require_once('../include/projekttask.class.php');

$rdf_url='http://www.technikum-wien.at/projekttask/';


$projektphase_id=4; //zum Testen, ansonsten null
if (isset($_GET['projektphase_id']))
	$projektphase_id=$_GET['projektphase_id'];

$projekttask_obj = new projekttask();
$projekttask_obj->getProjekttasks($projektphase_id);
//var_dump($projekttask_obj);
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PROJEKTTASK="<?php echo $rdf_url; ?>rdf#"
>

<?php
$descr='';
$sequenz='';
$lastPT=null;
for ($i=0;$i<count($projekttask_obj->result);$i++)
{
	$projekttask=$projekttask_obj->result[$i];
	$currentPT=$projekttask->projekttask_id;
	$nextPT=(($i<count($projekttask_obj->result)-1)?$projekttask_obj->result[$i+1]->projekttask_id:null);
	
	$descr.='<RDF:Description RDF:about="'.$rdf_url.$projekttask->projektphase_id.'/'.$projekttask->projekttask_id.'" >
		<PROJEKTTASK:projekttask_id>'.$projekttask->projekttask_id.'</PROJEKTTASK:projekttask_id>
		<PROJEKTTASK:projektphase_id>'.$projekttask->projektphase_id.'</PROJEKTTASK:projektphase_id>
		<PROJEKTTASK:bezeichnung>'.$projekttask->bezeichnung.'</PROJEKTTASK:bezeichnung>
		<PROJEKTTASK:beschreibung>'.$projekttask->beschreibung.'</PROJEKTTASK:beschreibung>
		<PROJEKTTASK:aufwand>'.$projekttask->aufwand.'</PROJEKTTASK:aufwand>
		<PROJEKTTASK:mantis_id>'.$projekttask->mantis_id.'</PROJEKTTASK:mantis_id>
	</RDF:Description>'."\n";
	
	if ($lastPT!=$currentPT)
		$sequenz.='	<RDF:li RDF:resource="'.$rdf_url.$projekttask->projektphase_id.'/'.$projekttask->projekttask_id.'" />
		<RDF:li>
      				<RDF:Seq RDF:about="'.$rdf_url.$projekttask->projektphase_id.'/'.$projekttask->projekttask_id.'" >'."\n";
	// Neue OE oder letzter Datensatz? Dann muss Sequenz geschlossen werden.
	if ($nextPT!=$currentPT || $i==count($projekttask_obj->result)-1)
	{
		$sequenz.='	<RDF:li RDF:resource="'.$rdf_url.$projekttask->projektphase_id.'/'.$projekttask->projekttask_id.'" />'."\n";
		$sequenz.='			</RDF:Seq>
      			</RDF:li>'."\n";
	}
	elseif ($lastPT==$currentPT || $nextPT==$currentPT || count($projekttask_obj->result)==1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$projekttask->projektphase_id.'/'.$projekttask->projekttask_id.'" />'."\n";
	$lastPT=$currentPT;
}
$sequenz='<RDF:Seq about="'.$rdf_url.'alle-projekttasks">'."\n\t".$sequenz.'
  	</RDF:Seq>'."\n";
echo $descr."\n";
echo $sequenz;


?>
</RDF:RDF>
