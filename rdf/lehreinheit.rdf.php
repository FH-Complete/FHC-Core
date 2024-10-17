<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
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
require_once('../include/lehreinheit.class.php');
require_once('../include/lehreinheitgruppe.class.php');
require_once('../include/lehrveranstaltung.class.php');

$lehreinheit_id = (isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:'');
$lehrveranstaltung_id = (isset($_GET['lehrveranstaltung_id'])?$_GET['lehrveranstaltung_id']:'');
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:'');

$lehreinheit=new lehreinheit();

$rdf_url='http://www.technikum-wien.at/lehreinheit';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHREINHEIT="'.$rdf_url.'/rdf#"
>
   <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
      <RDF:li>
         <RDF:Description  id=""  about="" >
            <LEHREINHEIT:lehreinheit_id><![CDATA[]]></LEHREINHEIT:lehreinheit_id>
            <LEHREINHEIT:lehrveranstaltung_id><![CDATA[]]></LEHREINHEIT:lehrveranstaltung_id>
            <LEHREINHEIT:studiensemester_kurzbz><![CDATA[]]></LEHREINHEIT:studiensemester_kurzbz>
            <LEHREINHEIT:lehrfach_id><![CDATA[]]></LEHREINHEIT:lehrfach_id>
            <LEHREINHEIT:lehrform_kurzbz><![CDATA[]]></LEHREINHEIT:lehrform_kurzbz>
            <LEHREINHEIT:stundenblockung><![CDATA[]]></LEHREINHEIT:stundenblockung>
            <LEHREINHEIT:wochenrythmus><![CDATA[]]></LEHREINHEIT:wochenrythmus>
            <LEHREINHEIT:start_kw><![CDATA[]]></LEHREINHEIT:start_kw>
            <LEHREINHEIT:raumtyp><![CDATA[]]></LEHREINHEIT:raumtyp>
            <LEHREINHEIT:raumtypalternativ><![CDATA[]]></LEHREINHEIT:raumtypalternativ>
            <LEHREINHEIT:sprache><![CDATA[]]></LEHREINHEIT:sprache>
            <LEHREINHEIT:lehre><![CDATA[]]></LEHREINHEIT:lehre>
            <LEHREINHEIT:anmerkung><![CDATA[]]></LEHREINHEIT:anmerkung>
            <LEHREINHEIT:unr><![CDATA[]]></LEHREINHEIT:unr>
            <LEHREINHEIT:lvnr><![CDATA[]]></LEHREINHEIT:lvnr>
            <LEHREINHEIT:bezeichnung><![CDATA[-- keine Auswahl --]]></LEHREINHEIT:bezeichnung>
         </RDF:Description>
      </RDF:li>
      ';
}

if($lehreinheit_id!='')
{
	$lehreinheit->load($lehreinheit_id);
	draw_row($lehreinheit);
}
else
{
	if($lehrveranstaltung_id!='')
	{
	    if($studiensemester_kurzbz!='')
		    $lehreinheit->load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz);
	    else
	        $lehreinheit->load_all_lehreinheiten($lehrveranstaltung_id);

		foreach ($lehreinheit->lehreinheiten as $row)
			draw_row($row);
	}
	else
		die('Fehlerhafte Parameteruebergabe');
}


function draw_row($row)
{
	global $rdf_url;

	$legrp = new lehreinheitgruppe();
	$legrp->getLehreinheitgruppe($row->lehreinheit_id);

	$grp='';
	foreach ($legrp->lehreinheitgruppe as $leg_row)
	{
		if($leg_row->gruppe_kurzbz!='')
			$grp .=" ".$leg_row->gruppe_kurzbz;
		else
			$grp .=" ".$leg_row->semester.$leg_row->verband.$leg_row->gruppe;
	}

	$qry = "SELECT kurzbz FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE
			lehreinheit_id='$row->lehreinheit_id'";

	$mitarbeiter='';
	$db = new basis_db();

	if($db->db_query($qry))
	{
		while($row_ma = $db->db_fetch_object())
			$mitarbeiter .=' '.$row_ma->kurzbz;
	}
	$mitarbeiter = '('.$mitarbeiter.')';

	$anzahl_studenten=0;
	$qry = "SELECT count(*) as anz FROM campus.vw_student_lehrveranstaltung WHERE lehreinheit_id='".addslashes($row->lehreinheit_id)."'";
	if($db->db_query($qry))
		if($row_std = $db->db_fetch_object())
			$anzahl_studenten = $row_std->anz;

	$lehrfach = new lehrveranstaltung();
	$lehrfach->load($row->lehrfach_id);

	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->lehreinheit_id.'"  about="'.$rdf_url.'/'.$row->lehreinheit_id.'" >
            <LEHREINHEIT:lehreinheit_id><![CDATA['.$row->lehreinheit_id.']]></LEHREINHEIT:lehreinheit_id>
            <LEHREINHEIT:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></LEHREINHEIT:lehrveranstaltung_id>
            <LEHREINHEIT:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></LEHREINHEIT:studiensemester_kurzbz>
            <LEHREINHEIT:lehrfach_id><![CDATA['.$row->lehrfach_id.']]></LEHREINHEIT:lehrfach_id>
            <LEHREINHEIT:lehrform_kurzbz><![CDATA['.$row->lehrform_kurzbz.']]></LEHREINHEIT:lehrform_kurzbz>
            <LEHREINHEIT:stundenblockung><![CDATA['.$row->stundenblockung.']]></LEHREINHEIT:stundenblockung>
            <LEHREINHEIT:wochenrythmus><![CDATA['.$row->wochenrythmus.']]></LEHREINHEIT:wochenrythmus>
            <LEHREINHEIT:start_kw><![CDATA['.$row->start_kw.']]></LEHREINHEIT:start_kw>
            <LEHREINHEIT:raumtyp><![CDATA['.$row->raumtyp.']]></LEHREINHEIT:raumtyp>
            <LEHREINHEIT:raumtypalternativ><![CDATA['.$row->raumtypalternativ.']]></LEHREINHEIT:raumtypalternativ>
            <LEHREINHEIT:sprache><![CDATA['.$row->sprache.']]></LEHREINHEIT:sprache>
            <LEHREINHEIT:lehre><![CDATA['.($row->lehre?'Ja':'Nein').']]></LEHREINHEIT:lehre>
            <LEHREINHEIT:anmerkung><![CDATA['.$row->anmerkung.']]></LEHREINHEIT:anmerkung>
            <LEHREINHEIT:unr><![CDATA['.$row->unr.']]></LEHREINHEIT:unr>
            <LEHREINHEIT:lvnr><![CDATA['.$row->lvnr.']]></LEHREINHEIT:lvnr>
            <LEHREINHEIT:bezeichnung><![CDATA['.$lehrfach->kurzbz.'-'.$row->lehrform_kurzbz.' '.$lehrfach->bezeichnung.' '.$grp.' '.$mitarbeiter.']]></LEHREINHEIT:bezeichnung>
            <LEHREINHEIT:anzahl_studenten><![CDATA['.$anzahl_studenten.']]></LEHREINHEIT:anzahl_studenten>
			<LEHREINHEIT:gewicht><![CDATA['.$row->gewicht.']]></LEHREINHEIT:gewicht>
         </RDF:Description>
      </RDF:li>
      ';
}
?>
   </RDF:Seq>
</RDF:RDF>