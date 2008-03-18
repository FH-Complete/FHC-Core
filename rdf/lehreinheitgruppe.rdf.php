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
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';

require_once('../vilesci/config.inc.php');
require_once('../include/lehreinheitgruppe.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/gruppe.class.php');
require_once('../include/lehrverband.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id']))
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = null;

//Gruppen holen
$DAO_obj = new lehreinheitgruppe($conn);
$DAO_obj->getLehreinheitgruppe($lehreinheit_id);

$stg_obj = new studiengang($conn);
$stg_obj->getAll();
$stg = array();
foreach ($stg_obj->result as $row)
	$stg[$row->studiengang_kz]=$row->kuerzel;

$rdf_url='http://www.technikum-wien.at/lehreinheitgruppe';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHREINHEITGRUPPE="<?php echo $rdf_url; ?>/rdf#"
>

   <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($DAO_obj->lehreinheitgruppe as $row)
{
	if($row->gruppe_kurzbz!='')
	{
		$bezeichnung = $row->gruppe_kurzbz;
		$gruppe = new gruppe($conn);
		$gruppe->load($row->gruppe_kurzbz);
		$beschreibung = $gruppe->bezeichnung;
		
	}
	else
	{
		$bezeichnung = $stg[$row->studiengang_kz].$row->semester.$row->verband.$row->gruppe;
		$gruppe = new lehrverband($conn);
		$gruppe->load($row->studiengang_kz, $row->semester, $row->verband, $row->gruppe);
		$beschreibung = $gruppe->bezeichnung;
	}
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $row->lehreinheitgruppe_id; ?>"  about="<?php echo $rdf_url.'/'.$row->lehreinheitgruppe_id; ?>" >
            <LEHREINHEITGRUPPE:lehreinheitgruppe_id><![CDATA[<?php echo $row->lehreinheitgruppe_id; ?>]]></LEHREINHEITGRUPPE:lehreinheitgruppe_id>
            <LEHREINHEITGRUPPE:bezeichnung><![CDATA[<?php echo $bezeichnung; ?>]]></LEHREINHEITGRUPPE:bezeichnung>
            <LEHREINHEITGRUPPE:beschreibung><![CDATA[<?php echo $beschreibung; ?>]]></LEHREINHEITGRUPPE:beschreibung>
            <LEHREINHEITGRUPPE:studiengang_kz><![CDATA[<?php echo $row->studiengang_kz; ?>]]></LEHREINHEITGRUPPE:studiengang_kz>
            <LEHREINHEITGRUPPE:studiengang_bezeichnung><![CDATA[<?php echo $stg[$row->studiengang_kz]; ?>]]></LEHREINHEITGRUPPE:studiengang_bezeichnung>
            <LEHREINHEITGRUPPE:semester><![CDATA[<?php echo $row->semester; ?>]]></LEHREINHEITGRUPPE:semester>
            <LEHREINHEITGRUPPE:verband><![CDATA[<?php echo $row->verband; ?>]]></LEHREINHEITGRUPPE:verband>
            <LEHREINHEITGRUPPE:gruppe><![CDATA[<?php echo $row->gruppe; ?>]]></LEHREINHEITGRUPPE:gruppe>
            <LEHREINHEITGRUPPE:gruppe_kurzbz><![CDATA[<?php echo $row->gruppe_kurzbz; ?>]]></LEHREINHEITGRUPPE:gruppe_kurzbz>
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>

</RDF:RDF>