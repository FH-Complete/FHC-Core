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
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/functions.inc.php');

if (isset($_GET['lektor']))
{
	if($_GET['lektor']=='true')
		$lektor=true;
	else
		$lektor=false;
}
else
	$lektor=null;

if (isset($_GET['fixangestellt']))
	$fixangestellt=$_GET['fixangestellt'];
else
	$fixangestellt=null;

if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;

if (isset($_GET['fachbereich_id']))
	$fachbereich_id=$_GET['fachbereich_id'];
else
	$fachbereich_id=null;

if (isset($_GET['user']))
	$user=$_GET['user'];
else
	$user=false;

if(isset($_GET['filter']))
	$filter = $_GET['filter'];
else
	$filter=null;

if(isset($_GET['mitarbeiter_uid']))
	$mitarbeiter_uid=$_GET['mitarbeiter_uid'];
else
	$mitarbeiter_uid=null;

if(isset($_GET['lehrveranstaltung_id']) && is_numeric($_GET['lehrveranstaltung_id']))
{
	$lehrveranstaltung_id = $_GET['lehrveranstaltung_id'];
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	$mitarbeiter=new mitarbeiter();
}
else
{
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
	$lehrveranstaltung_id=null;
	$mitarbeiter=new mitarbeiter();
}

// Mitarbeiter holen

$rdf_url='http://www.technikum-wien.at/mitarbeiter/';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MITARBEITER="'.$rdf_url.'rdf#"
>
';

function draw_row($mitarbeiter)
{
	global $rdf_url;

	if (is_bool($mitarbeiter->fixangestellt))
		$fixangestellt = ($mitarbeiter->fixangestellt == true?'Ja':'Nein');
	else
		$fixangestellt = '';
	echo '
	<RDF:Description about="'.$rdf_url.$mitarbeiter->uid.'" >
    	<MITARBEITER:uid><![CDATA['.$mitarbeiter->uid.']]></MITARBEITER:uid>
		<MITARBEITER:titelpre><![CDATA['.$mitarbeiter->titelpre.']]></MITARBEITER:titelpre>
		<MITARBEITER:titelpost><![CDATA['.$mitarbeiter->titelpost.']]></MITARBEITER:titelpost>
		<MITARBEITER:wahlname><![CDATA['.$mitarbeiter->wahlname.']]></MITARBEITER:wahlname>
		<MITARBEITER:vornamen><![CDATA['.$mitarbeiter->vornamen.']]></MITARBEITER:vornamen>
		<MITARBEITER:vorname><![CDATA['.$mitarbeiter->vorname.']]></MITARBEITER:vorname>
		<MITARBEITER:nachname><![CDATA['.$mitarbeiter->nachname.']]></MITARBEITER:nachname>
		<MITARBEITER:kurzbz><![CDATA['.$mitarbeiter->kurzbz.']]></MITARBEITER:kurzbz>
		<MITARBEITER:aktiv><![CDATA['.($mitarbeiter->aktiv == true?'aktiv':'inaktiv').']]></MITARBEITER:aktiv>
		<MITARBEITER:fixangestellt><![CDATA['.$fixangestellt.']]></MITARBEITER:fixangestellt>
		<MITARBEITER:studiengang_kz></MITARBEITER:studiengang_kz>
  	</RDF:Description>
  	';
}

if($lehrveranstaltung_id==null && $filter==null && $mitarbeiter_uid==null)
{
	$ma=$mitarbeiter->getMitarbeiter($lektor,$fixangestellt,$stg_kz);

	$stg_obj = new studiengang();
	$stg_obj->getAll('typ, kurzbz', false);
	foreach ($stg_obj->result as $stg)
		$stg_arr[$stg->studiengang_kz]=$stg->kuerzel;

	$alle='';
	foreach ($ma as $mitarbeiter)
	{
		draw_row($mitarbeiter);
		$alle.="\n\t\t\t<RDF:li resource=\"".$rdf_url.$mitarbeiter->uid."\" />";
	}
	$desc= '
			<RDF:Description about="'.$rdf_url.'_alle" >
				<MITARBEITER:uid></MITARBEITER:uid>
				<MITARBEITER:titelpre></MITARBEITER:titelpre>
				<MITARBEITER:titelpost></MITARBEITER:titelpost>
				<MITARBEITER:wahlname></MITARBEITER:wahlname>
				<MITARBEITER:vornamen></MITARBEITER:vornamen>
				<MITARBEITER:vorname></MITARBEITER:vorname>
				<MITARBEITER:nachname></MITARBEITER:nachname>
				<MITARBEITER:kurzbz>Alle</MITARBEITER:kurzbz>
				<MITARBEITER:studiengang_kz></MITARBEITER:studiengang_kz>
			</RDF:Description>
	';

	$seq= "
	<RDF:Seq about=\"".$rdf_url."liste\" >
		<RDF:li>
			<RDF:Seq about=\"".$rdf_url."_alle\" >$alle
			</RDF:Seq>
		</RDF:li>
		";

	if ($user)
	{
		$bb=new benutzerberechtigung();
		if($bb->getBerechtigungen(get_uid()))
		{
			$stge=$bb->getStgKz('admin');
			$stge=array_merge($stge, $bb->getStgKz('assistenz'));
			$ma=$mitarbeiter->getMitarbeiterStg($lektor,$fixangestellt,$stge, 'lkt', 'typ, stg_kurzbz, nachname, vorname, vw_mitarbeiter.kurzbz');
			$laststg=-1;
			if(count($ma)>0)
			{
				foreach ($ma as $mitarbeiter)
				{
					if($mitarbeiter->studiengang_kz!=$laststg)
					{
						if($laststg!=-1)
						{
							$seq.="\n\t\t</RDF:Seq>\n\t</RDF:li>\n";
						}
						$desc.="\n\t\t<RDF:Description about=\"".$rdf_url.$mitarbeiter->studiengang_kz."\" >".
								"\n\t\t\t<MITARBEITER:uid></MITARBEITER:uid>".
								"\n\t\t\t<MITARBEITER:titelpre></MITARBEITER:titelpre>".
								"\n\t\t\t<MITARBEITER:titelpost></MITARBEITER:titelpost>".
								"\n\t\t\t<MITARBEITER:wahlname></MITARBEITER:wahlname>".
								"\n\t\t\t<MITARBEITER:vornamen></MITARBEITER:vornamen>".
								"\n\t\t\t<MITARBEITER:vorname></MITARBEITER:vorname>".
								"\n\t\t\t<MITARBEITER:nachname></MITARBEITER:nachname>".
								"\n\t\t\t<MITARBEITER:kurzbz>".$stg_arr[$mitarbeiter->studiengang_kz]."</MITARBEITER:kurzbz>".
								"\n\t\t\t<MITARBEITER:studiengang_kz>$mitarbeiter->studiengang_kz</MITARBEITER:studiengang_kz>".
								"\n\t\t</RDF:Description>\n";

						$seq.="\n\t<RDF:li>\n\t\t<RDF:Seq about=\"".$rdf_url.$mitarbeiter->studiengang_kz."\" >";

						$laststg = $mitarbeiter->studiengang_kz;
					}
					$seq.="\n\t\t\t<RDF:li resource=\"".$rdf_url.$mitarbeiter->uid."\" />";
				}
				$seq.="\n\t\t</RDF:Seq>\n\t</RDF:li>";
			}
		}
	}
	echo $desc;
	echo $seq;
}
else
{
	$filter = $filter;
	echo "<RDF:Seq about=\"".$rdf_url."liste\" >";
	if(isset($_GET['optional']) && $_GET['optional']=='true')
	{
		echo '
		<RDF:li>
		<RDF:Description about="" >
	    	<MITARBEITER:uid><![CDATA[]]></MITARBEITER:uid>
			<MITARBEITER:titelpre><![CDATA[]]></MITARBEITER:titelpre>
			<MITARBEITER:titelpost><![CDATA[]]></MITARBEITER:titelpost>
			<MITARBEITER:wahlname><![CDATA[]]></MITARBEITER:wahlname>
			<MITARBEITER:vornamen><![CDATA[]]></MITARBEITER:vornamen>
			<MITARBEITER:vorname><![CDATA[]]></MITARBEITER:vorname>
			<MITARBEITER:nachname><![CDATA[-- Keine Auswahl --]]></MITARBEITER:nachname>
			<MITARBEITER:kurzbz><![CDATA[]]></MITARBEITER:kurzbz>
			<MITARBEITER:studiengang_kz></MITARBEITER:studiengang_kz>
	  	</RDF:Description>
	  	</RDF:li>
	  	';
	}

	if($mitarbeiter_uid!=null)
	{
		$mitarbeiter->load($mitarbeiter_uid);
		echo "
		<RDF:li>
			<RDF:Seq about=\"".$rdf_url."_alle\" >
      			<RDF:li>";
		draw_row($mitarbeiter);
		echo "
				</RDF:li>
			</RDF:Seq>
		</RDF:li>";
	}
	else
	{
		if($filter==null)
		{
			$mitarbeiter->getMitarbeiterFromLehrveranstaltung($lehrveranstaltung_id);
		}
		else
		{
			$mitarbeiter->getMitarbeiterFilter($filter);
		}

		foreach ($mitarbeiter->result as $row)
		{
			echo '<RDF:li>';
			draw_row($row);
			echo '</RDF:li>';
		}
	}
}

?>

</RDF:Seq>
</RDF:RDF>
