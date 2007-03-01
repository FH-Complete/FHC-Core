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
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/lehrfach.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if(isset($_GET['studiengang_kz']))
	$stg = $_GET['studiengang_kz'];
else 
	$stg = '';

if(isset($_GET['semester']))
	$sem = $_GET['semester'];
else 
	$sem = '';
	
if(isset($_GET['lehrveranstaltung_id']) && is_numeric($_GET['lehrveranstaltung_id']))
{
	$lvid = $_GET['lehrveranstaltung_id'];
	
	$qry = "SELECT studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$lvid'";
	
	$result = pg_query($conn, $qry);
	if($row = pg_fetch_object($result))
	{
		$stg = $row->studiengang_kz;
		$sem = $row->semester;
	}
	else 
		die('Fehler beim laden der Daten');
}
	

// Einheiten holen
$lehrfachDAO=new lehrfach($conn);
$lehrfachDAO->getTab($stg, $sem);

$rdf_url='http://www.technikum-wien.at/lehrfach';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRFACH="<?php echo $rdf_url; ?>/rdf#"
>

   <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($lehrfachDAO->lehrfaecher as $lehrfach)
{
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $lehrfach->lehrfach_id; ?>"  about="<?php echo $rdf_url.'/'.$lehrfach->lehrfach_id; ?>" >
            <LEHRFACH:lehrfach_id><?php echo $lehrfach->lehrfach_id  ?></LEHRFACH:lehrfach_id>
            <LEHRFACH:studiengang_kz><?php echo $lehrfach->studiengang_kz  ?></LEHRFACH:studiengang_kz>
            <LEHRFACH:fachbereich_kurzbz><![CDATA[<?php echo $lehrfach->fachbereich_kurzbz ?>]]></LEHRFACH:fachbereich_kurzbz>
            <LEHRFACH:kurzbz><![CDATA[<?php echo $lehrfach->kurzbz ?>]]></LEHRFACH:kurzbz>
            <LEHRFACH:bezeichnung><![CDATA[<?php echo $lehrfach->bezeichnung ?>]]></LEHRFACH:bezeichnung>
            <LEHRFACH:farbe><![CDATA[<?php echo $lehrfach->farbe ?>]]></LEHRFACH:farbe>
            <LEHRFACH:aktiv><?php echo $lehrfach->aktiv ?></LEHRFACH:aktiv>
            <LEHRFACH:semester><?php echo $lehrfach->semester ?></LEHRFACH:semester>
            <LEHRFACH:sprache><?php echo $lehrfach->sprache ?></LEHRFACH:sprache>
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>

</RDF:RDF>