<?php
/* Copyright (C) 2014 fhcomplete.org
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
* Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
*/
/**
 * Dabis Bibliotheksverwaltung - CSV Export
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/adresse.class.php'); 
require_once('../../../include/kontakt.class.php');
require_once('../../../include/benutzerberechtigung.class.php');


header('Content-type: application/octetstream');
header('Content-Disposition: inline; filename="dabis.csv"');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/person'))
{
	die('Sie haben keine Berechtigung für diese Seite');
}
$db = new basis_db();

$qry="SELECT 
		person_id, vorname, nachname, gebdatum, geschlecht,uid 
	FROM 
		public.tbl_person 
		JOIN public.tbl_benutzer USING(person_id) 
	WHERE 
		tbl_benutzer.aktiv
	ORDER BY nachname, vorname 
";

if($result = $db->db_query($qry))
{
	$out = fopen('php://output', 'w');
	
	while($row = $db->db_fetch_object($result))
	{
		if(check_lektor($row->uid))
			$benutzergruppe ='Lehrpersonal';
		else
			$benutzergruppe ='Student';
		
		$adresse = new adresse();
		$adresse->load_pers($row->person_id);
		if(isset($adresse->result[0]))
		{
			$ort = $adresse->result[0]->ort;
			$strasse = $adresse->result[0]->strasse;
			$plz = $adresse->result[0]->plz;
		}
		else
		{
			$ort='';
			$strasse='';
			$plz='';
		}

		$kontakt = new kontakt();
		$kontakt->load_persKontakttyp($row->person_id, 'telefon');
		if(isset($kontakt->result[0]))
			$telefon = $kontakt->result[0]->kontakt;
		else
			$telefon='';
		
		
		$arr_row=array($row->person_id, $benutzergruppe, $row->nachname, $row->vorname, $row->gebdatum, $row->geschlecht, 
					$ort, $plz, $strasse, $telefon, $row->uid.'@'.DOMAIN);
		
		fputcsv($out, $arr_row);

	}
	
	fclose($out);
}
?>