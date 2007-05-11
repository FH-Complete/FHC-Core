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
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/akte.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else 
	$person_id = '';

if(isset($_GET['dokument_kurzbz']))
	$dokument_kurzbz = $_GET['dokument_kurzbz'];
else 
	$dokument_kurzbz = '';
	
if(isset($_GET['uid']))
	$uid = $_GET['uid'];
else 
	$uid = '';

$akten = new akte($conn);
if(!$akten->getAkten($person_id, $dokument_kurzbz))
	die($akten->errormsg);
$rdf_url='http://www.technikum-wien.at/akte';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AKTE="<?php echo $rdf_url; ?>/rdf#"
>

   <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($akten->result as $row)
{
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $row->akte_id; ?>"  about="<?php echo $rdf_url.'/'.$row->akte_id; ?>" >
            <AKTE:akte_id><![CDATA[<?php echo $row->akte_id  ?>]]></AKTE:akte_id>
            <AKTE:person_id><![CDATA[<?php echo $row->person_id  ?>]]></AKTE:person_id>
            <AKTE:dokument_kurzbz><![CDATA[<?php echo $row->dokument_kurzbz  ?>]]></AKTE:dokument_kurzbz>
            <AKTE:mimetype><![CDATA[<?php echo $row->mimetype  ?>]]></AKTE:mimetype>
            <AKTE:erstelltam><![CDATA[<?php echo $row->erstelltam  ?>]]></AKTE:erstelltam>
			<AKTE:gedruckt><![CDATA[<?php echo ($row->gedruckt?'Ja':'Nein')  ?>]]></AKTE:gedruckt>
			<AKTE:titel><![CDATA[<?php echo $row->titel  ?>]]></AKTE:titel>
			<AKTE:bezeichnung><![CDATA[<?php echo $row->bezeichnung;  ?>]]></AKTE:bezeichnung>
			<AKTE:updateamum><![CDATA[<?php echo $row->updateamum; ?>]]></AKTE:updateamum>
			<AKTE:updatevon><![CDATA[<?php echo $row->updatevon  ?>]]></AKTE:updatevon>
			<AKTE:insertamum><![CDATA[<?php echo $row->insertamum; ?>]]></AKTE:insertamum>
			<AKTE:insertvon><![CDATA[<?php echo $row->insertvon  ?>]]></AKTE:insertvon>
			<AKTE:uid><![CDATA[<?php echo $row->uid; ?>]]></AKTE:uid>			
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>

</RDF:RDF>