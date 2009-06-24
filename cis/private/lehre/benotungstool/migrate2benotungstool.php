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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

 require_once('../../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
			
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
$time = microtime_float();

$inserted = 0;
$upgedated = 0;
$text = "";

$qry = "SELECT DISTINCT(lehreinheit_id) from campus.tbl_uebung order by lehreinheit_id";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		
		$query = "select count(*) from campus.tbl_uebung where liste_id is null and beispiele = 't' and lehreinheit_id = '".$row->lehreinheit_id."'";
		$res = $db->db_query($query);
		$anzahl = $db->db_fetch_object($res);
		if ($anzahl->count > 0)
		{
		
						
			$datum_obj = new datum();
			$uebung_obj = new uebung();
			$uebung_obj->get_next_nummer();
			$uebung_obj->gewicht=1;
			$uebung_obj->punkte='';
			$uebung_obj->angabedatei='';
			$uebung_obj->freigabevon = null;
			$uebung_obj->freigabebis = null;
			$uebung_obj->abgabe=false;
			$uebung_obj->beispiele=false;
			$uebung_obj->bezeichnung="Kreuzerllisten";
			$uebung_obj->positiv=false;
			$uebung_obj->defaultbemerkung='';
			$uebung_obj->lehreinheit_id=$row->lehreinheit_id;
			$uebung_obj->updateamum = null;
			$uebung_obj->updatevon = null;
			$uebung_obj->insertamum = date('Y-m-d H:i:s');
			$uebung_obj->insertvon = "sync";
			$uebung_obj->statistik = false;
			$uebung_obj->liste_id = null;
			$uebung_obj->nummer = $uebung_obj->next_nummer;
			
			if($uebung_obj->save(true))
			{
				$inserted++;				
				$liste_id = $uebung_obj->uebung_id;
				$update_qry = "UPDATE campus.tbl_uebung set liste_id = '".$liste_id."' where lehreinheit_id = '".$row->lehreinheit_id."' and uebung_id != '".$liste_id."' and beispiele = 't'";
				$r = $db->db_query($update_qry);
				$upgedated += $db->db_affected_rows($r);
			}
		}
	}
}

$text .= "Inserts: ".$inserted."<br>";
$text .= "Updated: ".$upgedated."<br>";
?>



<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title>Benotungstool</title>
<?php
	echo $text;
?>
</body>
</html>