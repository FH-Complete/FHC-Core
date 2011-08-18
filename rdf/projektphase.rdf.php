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
require_once('../include/projektphase.class.php');

$rdf_url='http://www.technikum-wien.at/projektphase/';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PROJEKTPHASE="'.$rdf_url.'rdf#"
>';

if(isset($_GET['projektphase_id']))
{
	$phase = new projektphase();
	
	$phase->load($_GET['projektphase_id']);
	
	echo '
	<RDF:Description RDF:about="'.$rdf_url.$phase->projektphase_id.'" >
		<PROJEKTPHASE:projektphase_id><![CDATA['.$phase->projektphase_id.']]></PROJEKTPHASE:projektphase_id>
		<PROJEKTPHASE:projekt_kurzbz><![CDATA['.$phase->projekt_kurzbz.']]></PROJEKTPHASE:projekt_kurzbz>
		<PROJEKTPHASE:projektphase_fk><![CDATA['.$phase->projektphase_fk.']]></PROJEKTPHASE:projektphase_fk>
		<PROJEKTPHASE:bezeichnung><![CDATA['.$phase->bezeichnung.']]></PROJEKTPHASE:bezeichnung>
		<PROJEKTPHASE:beschreibung><![CDATA['.$phase->beschreibung.']]></PROJEKTPHASE:beschreibung>
		<PROJEKTPHASE:start><![CDATA['.$phase->start.']]></PROJEKTPHASE:start>
		<PROJEKTPHASE:ende><![CDATA['.$phase->ende.']]></PROJEKTPHASE:ende>
		<PROJEKTPHASE:budget><![CDATA['.$phase->budget.']]></PROJEKTPHASE:budget>
		<PROJEKTPHASE:personentage><![CDATA['.$phase->personentage.']]></PROJEKTPHASE:personentage>
	</RDF:Description>'."\n";
	
	echo '<RDF:Seq about="'.$rdf_url.'alle-projektphasen">
		<RDF:li RDF:resource="'.$rdf_url.$phase->projektphase_id.'" />
  	</RDF:Seq>'."\n";
}
else
{
	$projekt_obj = new projekt();
	$projekt_obj->getProjekte();
	$projektphase_obj = new projektphase();
	$sequenzProjektphase = array();
		
	$descr='';
	$sequenz='';
	$lastOE=null;
	for ($i=0;$i<count($projekt_obj->result);$i++)
	{
		$currentOE=$projekt_obj->result[$i]->oe_kurzbz;
		//echo $currentOE;
		$nextOE=(($i<count($projekt_obj->result)-1)?$projekt_obj->result[$i+1]->oe_kurzbz:null);
		//echo $nextOE;
		$projekt=$projekt_obj->result[$i];
		// Bin ich schon in der naechsten OE? Oder vielleicht in der ersten?
		if ($lastOE!=$currentOE || $i==0)
			$descr.='<RDF:Description RDF:about="'.$rdf_url.$projekt->oe_kurzbz.'" >
				<PROJEKTPHASE:bezeichnung>'.$projekt->oe_kurzbz.'</PROJEKTPHASE:bezeichnung>
				<PROJEKTPHASE:oe_kurzbz>'.$projekt->oe_kurzbz.'</PROJEKTPHASE:oe_kurzbz>
				<PROJEKTPHASE:projekt_kurzbz></PROJEKTPHASE:projekt_kurzbz>
				<PROJEKTPHASE:projekt_phase></PROJEKTPHASE:projekt_phase>
				<PROJEKTPHASE:projekt_phase_id></PROJEKTPHASE:projekt_phase_id>
				<PROJEKTPHASE:nummer></PROJEKTPHASE:nummer>
				<PROJEKTPHASE:titel></PROJEKTPHASE:titel>
				<PROJEKTPHASE:beschreibung></PROJEKTPHASE:beschreibung>
				<PROJEKTPHASE:beginn></PROJEKTPHASE:beginn>
				<PROJEKTPHASE:ende></PROJEKTPHASE:ende>
	      			</RDF:Description>'."\n";
		$descr.='<RDF:Description RDF:about="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'" >
			<PROJEKTPHASE:bezeichnung><![CDATA['.$projekt->titel.']]></PROJEKTPHASE:bezeichnung>
			<PROJEKTPHASE:oe_kurzbz><![CDATA['.$projekt->oe_kurzbz.']]></PROJEKTPHASE:oe_kurzbz>
			<PROJEKTPHASE:projekt_kurzbz><![CDATA['.$projekt->projekt_kurzbz.']]></PROJEKTPHASE:projekt_kurzbz>
			<PROJEKTPHASE:projekt_phase></PROJEKTPHASE:projekt_phase>
			<PROJEKTPHASE:projekt_phase_id></PROJEKTPHASE:projekt_phase_id>
			<PROJEKTPHASE:nummer><![CDATA['.$projekt->nummer.']]></PROJEKTPHASE:nummer>
			<PROJEKTPHASE:titel><![CDATA['.$projekt->titel.']]></PROJEKTPHASE:titel>
			<PROJEKTPHASE:beschreibung><![CDATA['.$projekt->beschreibung.']]></PROJEKTPHASE:beschreibung>
			<PROJEKTPHASE:beginn><![CDATA['.$projekt->beginn.']]></PROJEKTPHASE:beginn>
			<PROJEKTPHASE:ende><![CDATA['.$projekt->ende.']]></PROJEKTPHASE:ende>
		</RDF:Description>'."\n";
		
		$projektphase_obj->getProjektphasen($projekt->projekt_kurzbz);
		$tmpStr='';
		for ($j=0;$j<count($projektphase_obj->result);$j++)
		{
			$projektphase=$projektphase_obj->result[$j];
			//var_dump($projektphase);
			
			$descr.='<RDF:Description RDF:about="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id.'" >
			<PROJEKTPHASE:bezeichnung><![CDATA['.$projektphase->bezeichnung.']]></PROJEKTPHASE:bezeichnung>
			<PROJEKTPHASE:oe_kurzbz><![CDATA['.$projekt->oe_kurzbz.']]></PROJEKTPHASE:oe_kurzbz>
			<PROJEKTPHASE:projekt_kurzbz><![CDATA['.$projektphase->projekt_kurzbz.']]></PROJEKTPHASE:projekt_kurzbz>
			<PROJEKTPHASE:projekt_phase><![CDATA['.$projektphase->bezeichnung.']]></PROJEKTPHASE:projekt_phase>
			<PROJEKTPHASE:projekt_phase_id><![CDATA['.$projektphase->projektphase_id.']]></PROJEKTPHASE:projekt_phase_id>
			<PROJEKTPHASE:nummer><![CDATA[]]></PROJEKTPHASE:nummer>
			<PROJEKTPHASE:titel><![CDATA['.$projektphase->bezeichnung.']]></PROJEKTPHASE:titel>
			<PROJEKTPHASE:beschreibung><![CDATA['.$projektphase->beschreibung.']]></PROJEKTPHASE:beschreibung>
			<PROJEKTPHASE:beginn><![CDATA['.$projektphase->start.']]></PROJEKTPHASE:beginn>
			<PROJEKTPHASE:ende><![CDATA['.$projektphase->ende.']]></PROJEKTPHASE:ende>
			<PROJEKTPHASE:budget><![CDATA['.$projektphase->budget.']]></PROJEKTPHASE:budget>
			<PROJEKTPHASE:personentage><![CDATA['.$projektphase->personentage.']]></PROJEKTPHASE:personentage>
			'."\n";
			
			$descr.='</RDF:Description>'."\n";
			if (is_null($projektphase->projektphase_fk))
			{
				if ($j==0)
					$tmpStr='			<RDF:li>
					<RDF:Seq RDF:about="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'">'."\n";
				$tmpStr.='			<RDF:li RDF:resource="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id.'" />'."\n";
				$tmpStr.=check_subprojektphasen(&$projekt,&$projektphase_obj,$projektphase->projektphase_id);
				if ($j==count($projektphase_obj->result)-1)
					$tmpStr.='					</RDF:Seq>
					</RDF:li>'."\n";
				$sequenzProjektphase[$projekt->projekt_kurzbz]=$tmpStr;
			}
		}
		//var_dump($sequenzProjektphase);
		if ($lastOE!=$currentOE)
		{
			$sequenz.='	<RDF:li RDF:resource="'.$rdf_url.$projekt->oe_kurzbz.'" />
			<RDF:li>
	      				<RDF:Seq RDF:about="'.$rdf_url.$projekt->oe_kurzbz.'" >'."\n";
		}
		// Neue OE oder letzter Datensatz? Dann muss Sequenz geschlossen werden.
		if ($nextOE!=$currentOE || $i==count($projekt_obj->result)-1)
		{
			$sequenz.='	<RDF:li RDF:resource="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'" />'."\n";
			if (isset($sequenzProjektphase[$projekt->projekt_kurzbz]))
				$sequenz.=$sequenzProjektphase[$projekt->projekt_kurzbz];
			$sequenz.='			</RDF:Seq>
	      			</RDF:li>'."\n";
		}
		elseif ($lastOE==$currentOE || $nextOE==$currentOE || count($projekt_obj->result)==1)
		{
			$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'" />'."\n";
			if (isset($sequenzProjektphase[$projekt->projekt_kurzbz]))
				$sequenz.=$sequenzProjektphase[$projekt->projekt_kurzbz];
		}
		$lastOE=$currentOE;
	}
	$sequenz='<RDF:Seq about="'.$rdf_url.'alle-projektphasen">'."\n\t".$sequenz.'
	  	</RDF:Seq>'."\n";
	echo $descr."\n";
	echo $sequenz;
	
	
}
function check_subprojektphasen($projekt,$projektphase_obj,$projektphase_id)
{
	global $rdf_url;
	$tmpStr='';
	$i=0;
	for ($j=0;$j<count($projektphase_obj->result);$j++)
	{
		$projektphase=$projektphase_obj->result[$j];
		if ($projektphase->projektphase_fk==$projektphase_id)
		{
			//var_dump($projektphase);
			if ($i==0)
			{
				$tmpStr='			<RDF:li>
				<RDF:Seq RDF:about="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase_id.'">'."\n";
			}
			$tmpStr.='			<RDF:li RDF:resource="'.$rdf_url.$projekt->oe_kurzbz.'/'.$projekt->projekt_kurzbz.'/'.$projektphase->projektphase_id.'" />'."\n";
			$i++;
		}
	}
	if ($i>0)
	{
		$tmpStr.='					</RDF:Seq>
				</RDF:li>'."\n";
	}
	return $tmpStr;
}
?>
</RDF:RDF>
