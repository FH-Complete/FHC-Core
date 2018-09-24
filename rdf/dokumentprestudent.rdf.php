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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/dokument.class.php');
require_once('../include/datum.class.php');
require_once('../include/akte.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/mitarbeiter.class.php');

$rdf_url='http://www.technikum-wien.at/dokumentprestudent';

$date = new datum();

if(isset($_GET['prestudent_id']))
	if(is_numeric($_GET['prestudent_id']))
		$prestudent_id=$_GET['prestudent_id'];
	else
		die('Prestudent_id ist ungueltig');
else
	die('Fehlerhafte Parameteruebergabe');

$dok = new dokument();
if(!$dok->getPrestudentDokumente($prestudent_id, false))
	die($dok->errormsg);

$prestudent = new prestudent();
if(!$prestudent->load($prestudent_id))
	die($prestudent->errormsg);

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:DOKUMENT="'.$rdf_url.'/rdf#"
>

  <RDF:Seq about="'.$rdf_url.'/liste">
';

foreach ($dok->result as $row)
{

	$akte = new akte();
	$akte->getAkten($prestudent->person_id, $row->dokument_kurzbz);
	$datum=(isset($row->datum))?$date->formatDatum($row->datum, 'd.m.Y'):'';
	$mitarbeiter = new mitarbeiter($row->mitarbeiter_uid);

	if(count($akte->result) != 0)
	{
		foreach($akte->result as $a)
		{
			$datumhochgeladen=(isset($a->insertamum))?$date->formatDatum($a->insertamum, 'd.m.Y'):'';
			$nachgereicht = (isset($a->nachgereicht) && $a->nachgereicht)?'ja':'';
			$info = (isset($a->anmerkung))?$a->anmerkung:'';
			$vorhanden = (isset($a->dms_id) || $a->inhalt_vorhanden)?'ja':'nein';

				echo 	'
				  <RDF:li>
						<RDF:Description  id="'.$row->dokument_kurzbz.'/'.$a->akte_id.'"  about="'.$rdf_url.'/'.$row->dokument_kurzbz.'/'.$a->akte_id.'" >
							<DOKUMENT:dokument_kurzbz><![CDATA['.$row->dokument_kurzbz.']]></DOKUMENT:dokument_kurzbz>
							<DOKUMENT:bezeichnung><![CDATA['.($row->dokument_kurzbz=='Sonst' && $a->titel_intern!==''?$row->bezeichnung.' ('.$a->titel_intern.')':$row->bezeichnung).']]></DOKUMENT:bezeichnung>
							<DOKUMENT:mitarbeiter_uid><![CDATA['.$mitarbeiter->vorname." ".$mitarbeiter->nachname.']]></DOKUMENT:mitarbeiter_uid>
							<DOKUMENT:datum>'.$datum.'</DOKUMENT:datum>
							<DOKUMENT:datumhochgeladen>'.$datumhochgeladen.'</DOKUMENT:datumhochgeladen>
							<DOKUMENT:nachgereicht>'.$nachgereicht.'</DOKUMENT:nachgereicht>
							<DOKUMENT:infotext>'.$info.'</DOKUMENT:infotext>
							<DOKUMENT:vorhanden>'.$vorhanden.'</DOKUMENT:vorhanden>
							<DOKUMENT:akte_id>'.$a->akte_id.'</DOKUMENT:akte_id>
							<DOKUMENT:titel_intern><![CDATA['.$a->titel_intern.']]></DOKUMENT:titel_intern>
							<DOKUMENT:anmerkung_intern><![CDATA['.$a->anmerkung_intern.']]></DOKUMENT:anmerkung_intern>
							<DOKUMENT:nachgereicht_am><![CDATA['.$a->nachgereicht_am.']]></DOKUMENT:nachgereicht_am>
						</RDF:Description>
				  </RDF:li>
					  ';
		}
	}
	else // Wenn keine Akten vorhanden sind -> Abzugebende anzeigen
	{
			echo 	'
			  <RDF:li>
					<RDF:Description  id="'.$row->dokument_kurzbz.'"  about="'.$rdf_url.'/'.$row->dokument_kurzbz.'" >
						<DOKUMENT:dokument_kurzbz><![CDATA['.$row->dokument_kurzbz.']]></DOKUMENT:dokument_kurzbz>
						<DOKUMENT:bezeichnung><![CDATA['.$row->bezeichnung.']]></DOKUMENT:bezeichnung>
						<DOKUMENT:mitarbeiter_uid><![CDATA['.$mitarbeiter->vorname." ".$mitarbeiter->nachname.']]></DOKUMENT:mitarbeiter_uid>
						<DOKUMENT:datum><![CDATA['.$datum.']]></DOKUMENT:datum>
						<DOKUMENT:nachgereicht></DOKUMENT:nachgereicht>
						<DOKUMENT:infotext></DOKUMENT:infotext>
						<DOKUMENT:vorhanden><![CDATA[nein]]></DOKUMENT:vorhanden>
						<DOKUMENT:akte_id></DOKUMENT:akte_id>
						<DOKUMENT:titel_intern></DOKUMENT:titel_intern>
						<DOKUMENT:anmerkung_intern></DOKUMENT:anmerkung_intern>
						<DOKUMENT:nachgereicht_am></DOKUMENT:nachgereicht_am>
					</RDF:Description>
			  </RDF:li>
				  ';
	}
}

?>

  </RDF:Seq>
</RDF:RDF>
