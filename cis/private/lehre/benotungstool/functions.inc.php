<?php

//Filenamen für up-/downloads bauen
// which kann sein angabe, abgabe oder zip
function makeUploadName($db, $which, $lehreinheit_id=null, $uebung_id=null, $ss=null, $uid=null, $date=null)
{
	$query = "SELECT tbl_studiengang.kurzbzlang, lehrfach.semester, lehrfach.kurzbz from public.tbl_studiengang, lehre.tbl_lehrveranstaltung as lehrfach, lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit where tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id and tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id and tbl_lehrveranstaltung.studiengang_kz = tbl_studiengang.studiengang_kz and tbl_lehreinheit.lehreinheit_id = ".$db->db_add_param($lehreinheit_id, FHC_INTEGER);
	$result = $db->db_query($query);
	$row = $db->db_fetch_object($result);
	$name = $row->kurzbzlang."_".$row->semester."_".$row->kurzbz."_".$ss;

	if ($which == "angabe")
	{
		$name .= "_".$uebung_id;
	}
	else if ($which == "abgabe")
	{
		$query = "SELECT nachname, vorname from tbl_person, tbl_benutzer where tbl_benutzer.person_id = tbl_person.person_id and tbl_benutzer.uid = ".$db->db_add_param($uid);
		$result = $db->db_query($query);
		$row = $db->db_fetch_object($result);
		$name .= "_".$uebung_id."_".$row->nachname."_".$row->vorname."_".$uid."_".$date;
	}
	else if ($which == "zip")
	{

		$name .= "_".$uebung_id."_".$date;
	}
	$name = str_replace(":","-",$name);
	return $name;
}
?>
