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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */
/*
 * Created on 02.12.2004
 *
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
require_once('../config/vilesci.config.inc.php');
require_once('../include/dokument.class.php');
require_once('../include/akte.class.php'); 
require_once('../include/prestudent.class.php'); 
require_once('../include/datum.class.php'); 

$rdf_url='http://www.technikum-wien.at/dokument';

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else 
	die('Fehlerhafte Parameteruebergabe');
	
if(isset($_GET['prestudent_id']))
	if(is_numeric($_GET['prestudent_id']))
		$prestudent_id=$_GET['prestudent_id'];
	else 
		die('Prestudent_id ist ungueltig');
else 
	$prestudent_id = null;
	
$dok = new dokument();
if(!$dok->getFehlendeDokumente($studiengang_kz, $prestudent_id))
	die($dok->errormsg);

$prestudent = new prestudent(); 
if(!$prestudent->load($prestudent_id))
	die($prestudent->errormsg); 

$date = new datum(); 
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:DOKUMENT="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php

foreach ($dok->result as $row)
{
	$akte = new akte(); 
	$akte->getAkten($prestudent->person_id, $row->dokument_kurzbz); 
	$datum=(isset($akte->result[0]->insertamum))?$date->formatDatum($akte->result[0]->insertamum, 'd.m.Y'):''; 
	$nachgereicht = (isset($akte->result[0]->nachgereicht))?'ja':''; 
	$info = (isset($akte->result[0]->anmerkung))?$akte->result[0]->anmerkung:''; 
	
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $row->dokument_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$row->dokument_kurzbz; ?>" >
        	<DOKUMENT:dokument_kurzbz><![CDATA[<?php echo $row->dokument_kurzbz  ?>]]></DOKUMENT:dokument_kurzbz>
    		<DOKUMENT:bezeichnung><![CDATA[<?php echo $row->bezeichnung  ?>]]></DOKUMENT:bezeichnung>
			<DOKUMENT:datum><?php echo $datum; ?></DOKUMENT:datum>
			<DOKUMENT:nachgereicht><?php echo $nachgereicht; ?></DOKUMENT:nachgereicht>
			<DOKUMENT:infotext><?php echo $info; ?></DOKUMENT:infotext>
      	</RDF:Description>
  </RDF:li>
	  <?php
}

?>

  </RDF:Seq>
</RDF:RDF>