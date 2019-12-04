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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

require_once('../config/vilesci.config.inc.php');
require_once('../include/lehreinheitmitarbeiter.class.php');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id']))
	$lehreinheit_id = $_GET['lehreinheit_id'];
else
	$lehreinheit_id = null;

if(isset($_GET['mitarbeiter_uid']))
	$mitarbeiter_uid = $_GET['mitarbeiter_uid'];
else
	$mitarbeiter_uid = null;

//Mitarbeiter holen
$DAO_obj = new lehreinheitmitarbeiter();
$DAO_obj->getLehreinheitmitarbeiter($lehreinheit_id, $mitarbeiter_uid);

$rdf_url='http://www.technikum-wien.at/lehreinheitmitarbeiter';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHREINHEITMITARBEITER="<?php echo $rdf_url; ?>/rdf#"
>

   <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($DAO_obj->lehreinheitmitarbeiter as $row)
{
	$vorname='unbekannt';
	$nachname='unbekannt';
	$aktiv=true;
	$db = new basis_db();
	$qry = "SELECT vorname, nachname, tbl_benutzer.aktiv FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid='".addslashes($row->mitarbeiter_uid)."'";
	if($db->db_query($qry))
	{
		if($row_lkt = $db->db_fetch_object())
		{
			$vorname = $row_lkt->vorname;
			$nachname = $row_lkt->nachname;
			$aktiv = $db->db_parse_bool($row_lkt->aktiv);
		}
	}


	$qry_verplant = "SELECT 1 FROM lehre.tbl_stundenplandev
			WHERE lehreinheit_id=".$db->db_add_param($row->lehreinheit_id)."
			AND mitarbeiter_uid=".$db->db_add_param($row->mitarbeiter_uid);

	if($result_verplant = $db->db_query($qry_verplant))
		if($db->db_num_rows($result_verplant)>0)
			$verplant = true;
		else
			$verplant = false;
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $row->lehreinheit_id.'/'.$row->mitarbeiter_uid; ?>"  about="<?php echo $rdf_url.'/'.$row->lehreinheit_id.'/'.$row->mitarbeiter_uid; ?>" >
            <LEHREINHEITMITARBEITER:lehreinheit_id><![CDATA[<?php echo $row->lehreinheit_id ?>]]></LEHREINHEITMITARBEITER:lehreinheit_id>
            <LEHREINHEITMITARBEITER:mitarbeiter_uid><![CDATA[<?php echo $row->mitarbeiter_uid ?>]]></LEHREINHEITMITARBEITER:mitarbeiter_uid>
            <LEHREINHEITMITARBEITER:vorname><![CDATA[<?php echo $vorname ?>]]></LEHREINHEITMITARBEITER:vorname>
            <LEHREINHEITMITARBEITER:nachname><![CDATA[<?php echo $nachname ?>]]></LEHREINHEITMITARBEITER:nachname>
            <LEHREINHEITMITARBEITER:lehrfunktion_kurzbz><![CDATA[<?php echo $row->lehrfunktion_kurzbz; ?>]]></LEHREINHEITMITARBEITER:lehrfunktion_kurzbz>
            <LEHREINHEITMITARBEITER:semesterstunden><![CDATA[<?php echo $row->semesterstunden ?>]]></LEHREINHEITMITARBEITER:semesterstunden>
            <LEHREINHEITMITARBEITER:planstunden><![CDATA[<?php echo $row->planstunden ?>]]></LEHREINHEITMITARBEITER:planstunden>
            <LEHREINHEITMITARBEITER:stundensatz><![CDATA[<?php echo $row->stundensatz ?>]]></LEHREINHEITMITARBEITER:stundensatz>
            <LEHREINHEITMITARBEITER:faktor><![CDATA[<?php echo $row->faktor ?>]]></LEHREINHEITMITARBEITER:faktor>
            <LEHREINHEITMITARBEITER:anmerkung><![CDATA[<?php echo $row->anmerkung ?>]]></LEHREINHEITMITARBEITER:anmerkung>
            <LEHREINHEITMITARBEITER:bismelden><![CDATA[<?php echo ($row->bismelden?'Ja':'Nein') ?>]]></LEHREINHEITMITARBEITER:bismelden>
			<LEHREINHEITMITARBEITER:verplant><![CDATA[<?php echo ($verplant?'true':'false'); ?>]]></LEHREINHEITMITARBEITER:verplant>
			<LEHREINHEITMITARBEITER:aktiv><![CDATA[<?php echo ($aktiv?'aktiv':'inaktiv'); ?>]]></LEHREINHEITMITARBEITER:aktiv>
             <LEHREINHEITMITARBEITER:vertrag_id><![CDATA[<?php echo $row->vertrag_id; ?>]]></LEHREINHEITMITARBEITER:vertrag_id>
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>

</RDF:RDF>
