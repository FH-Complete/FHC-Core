<?php
// **************************************
// Syncronisiert alle Noten
// FAS -> VILESCI
// setzt vorraus: - tbl_sprache
//                - tbl_studiengang
// einschraenkung auf student_fk per http-get:
// sync_fas_vilesci_note.php?student_fk_von=x&student_fk_bis=y
// **************************************
	require_once('../../../vilesci/config.inc.php');
	require_once('../../../include/zeugnisnote.class.php');
	require_once('../../../include/pruefung.class.php');
	$adress='fas_sync@technikum-wien.at';
	//$adress='pam@technikum-wien.at';

	$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
	$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur Vilesci Datenbank fehlgeschlagen");

	$startzeit = time();

	$plausi_error=0;
	$double_error=0;

	$update_error_pruef=0;
	$insert_error_pruef=0;
	$update_error_zeug=0;
	$insert_error_zeug=0;

	$anz_update_pruef=0;
	$anz_not_updated_pruef=0;
	$anz_not_updated_zeug=0;
	$anz_insert_pruef=0;
	$anz_update_zeug=0;
	$anz_insert_zeug=0;
	$anz_processed_pruef=0;
	$anz_processed_zeug=0;
	$anz_processed=0;

	$headtext='';
	$head_stg_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Lehrveranstaltungen aufgetreten:\n\n";
	$text='';
	$double_lva = array();
	$stg_data = array();

	$fasnoten_arr = array();		//mehrdimensionaler array (lehrverstaltung_fk->(note_pk->("note"->"x","datum"->"y" ..)))
	$mitarbeiter_arr = array();		//array (ext_id->mitarbeiter_uid)
	$studenten_arr = array();		//array (ext_id->student_uid)
	$lv_arr = array();				//array (lehrveranstaltung_fk->lehrveranstaltung_id)
	$studsem_arr = array();			//array (ext_id->studiensemester_kurzbz)
	$lehreinheiten_fas_arr = array();
	$lehreinheiten_sync_arr = array();

	//array aller mitarbeiter (ext_id->mitarbeiter_uid)
	$sqlstr = "SELECT ext_id, mitarbeiter_uid FROM tbl_mitarbeiter";
	if($result = pg_query($conn, $sqlstr))
	{
		while($row = pg_fetch_object($result))
			$mitarbeiter_arr[$row->ext_id] = $row->mitarbeiter_uid;
	}

	//array aller studenten (ext_id->mitarbeiter_uid)
	$sqlstr = "SELECT ext_id, student_uid FROM tbl_student";
	if($result = pg_query($conn, $sqlstr))
	{
		while($row = pg_fetch_object($result))
			$studenten_arr[$row->ext_id] = $row->student_uid;
	}

	//array aller lehrveranstaltungen aus sync-tabelle (lva_fas->lva_vilesci)
	$sqlstr = "SELECT lva_fas, lva_vilesci FROM sync.tbl_synclehrveranstaltung";
	if($result = pg_query($conn, $sqlstr))
	{
		while($row = pg_fetch_object($result))
			$lv_arr[$row->lva_fas] = $row->lva_vilesci;
	}

	//array aller studiensemester  (ext_id->studiensemester_kurzbz)
	$sqlstr = "SELECT * FROM tbl_studiensemester";
	if($result = pg_query($conn, $sqlstr))
	{
		while($row = pg_fetch_object($result))
			$studsem_arr[$row->ext_id] = $row->studiensemester_kurzbz;
	}

	//array aller lehreinheiten in der synctabelle
	$sqlstr = "SELECT * FROM sync.tbl_synclehreinheit";
	if($result = pg_query($conn, $sqlstr))
	{
		while($row = pg_fetch_object($result))
			$lehreinheiten_sync_arr[$row->lehreinheit_pk] = $row->lehreinheit_id;
	}


	//**** FUNCTIONS ****
	function getNoten4Student($conn_fas, $student_fk)
	{
		$fasnoten_arr = array();
		$mehrfach_arr = array();

		$sqlstr = "SELECT note.note_pk,
						note.student_fk,
						note.lehrveranstaltung_fk,
						note.datum,
						note.note,
						note.status,
						note.bemerkung,
						note.creationdate,
						benutzer.name,
						lehrveranstaltung.studiensemester_fk,
						lehrveranstaltung.notenlektor_fk
					FROM note, benutzer, lehrveranstaltung
					WHERE note.creationuser = benutzer.benutzer_pk AND
						note.lehrveranstaltung_fk = lehrveranstaltung.lehrveranstaltung_pk AND
						note.student_fk = '".$student_fk."'
					ORDER BY note.lehrveranstaltung_fk ASC, note.datum DESC, note.creationdate DESC";
		if($result = pg_query($conn_fas, $sqlstr))
		{
			//$anzahl_quelle = pg_num_rows($result);
			while($row = pg_fetch_object($result))
			{
				$bemerkung_history = "";

				if (key_exists($row->lehrveranstaltung_fk,$fasnoten_arr))
				{
					$bemerkung_history = "Note am ".$row->datum.": ".$row->note;
					$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["main"] = 0;
				}
				else
				{
					$mehrfach_arr[$row->lehrveranstaltung_fk]["main"] = $row->note_pk;
					$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["main"] = 1;
				}
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["note"] = $row->note;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["status"] = $row->status;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["creationdate"] = $row->creationdate;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["creationuser_name"] = $row->name;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["name"] = $row->name;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["studiensemester_fk"] = $row->studiensemester_fk;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["bemerkung"] = $row->bemerkung;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["datum"] = $row->datum;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["notenlektor_fk"] = $row->notenlektor_fk;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["student_fk"] = $row->student_fk;
				$fasnoten_arr[$row->lehrveranstaltung_fk][$row->note_pk]["ext_id"] = $row->note_pk;
				if ($bemerkung_history != "")
					$fasnoten_arr[$row->lehrveranstaltung_fk][$mehrfach_arr[$row->lehrveranstaltung_fk]["main"]]["bemerkung"] .= "\n".$bemerkung_history;
			}

		}
		return $fasnoten_arr;
	}



	function getPruefungstyp($status)
	{

		$pruefungstyp = "";
		if ($status == 1)
			$pruefungstyp = "Termin1";
		else if ($status == 2)
			$pruefungstyp = "Termin2";
		else if ($status == 11)
			$pruefungstyp = "kommPruef";
		else
			$pruefungstyp = 'undefiniert';	//$status;
		return $pruefungstyp;
	}

	function checkUpdatePruefung($conn, $pruef)
	{
		$sqlstr = "select * from lehre.tbl_pruefung where ext_id = '".$pruef->ext_id."'";
		if($res = pg_query($conn, $sqlstr))
		{
			if($row = pg_fetch_object($res))
			{
				if ($row->lehreinheit_id == $pruef->lehreinheit_id && $row->student_uid == $pruef->student_uid && $row->mitarbeiter_uid == $pruef->mitarbeiter_uid && $row->note == $pruef->note && $row->pruefungstyp_kurzbz == $pruef->pruefungstyp_kurzbz && $row->datum == $pruef->datum and $row->anmerkung == $pruef->anmerkung)
					return -1;
				else
					return $row->pruefung_id;

			}
		}
		else
		{
			return false;
		}
	}

	function checkUpdateZeugnis($conn, $zeug)
	{
		$sqlstr = "select * from lehre.tbl_zeugnisnote where ext_id = '".$zeug->ext_id."'";
		if($res = pg_query($conn, $sqlstr))
		{
			if($row = pg_fetch_object($res))
			{
				if ($row->lehrveranstaltung_id == $zeug->lehrveranstaltung_id && $row->student_uid == $zeug->student_uid && $row->studiensemester_kurzbz == $zeug->studiensemester_kurzbz && $row->note == $zeug->note  && $row->bemerkung == $zeug->bemerkung)
					return -1;
				else
					return 1;
			}

			else
			{
				return false;
			}
		}
	}

	function getLehreinheitID($conn_fas, $note_pk)
	{
		$lehreinheiten_fas_arr = array();
		$sqlstr = "select lehreinheit.lehreinheit_pk,note.note_pk from lehreinheit, lehrveranstaltung, note, student_gruppe where lehrveranstaltung.lehrveranstaltung_pk = lehreinheit.lehrveranstaltung_fk  and note.lehrveranstaltung_fk = lehrveranstaltung.lehrveranstaltung_pk and note.student_fk=student_gruppe.student_fk and student_gruppe.gruppe_fk = lehreinheit.gruppe_fk and note.note_pk = '".$note_pk."'";
		if($result = pg_query($conn_fas, $sqlstr))
		{
			while($row = pg_fetch_object($result))
				$lehreinheiten_fas_arr[] = $row->lehreinheit_pk;
			return $lehreinheiten_fas_arr;
		}
		else
			return false;
	}


	$text .= "<table border='1'>";

	//query bauen: falls http-get-einschraenkungen fuer student_fk
	//sync_fas_vilesci_note.php?student_fk_von=x&student_fk_bis=y

	$getstr = "";
	$sqlstr = "SELECT DISTINCT student_fk FROM note";
	if (isset($_REQUEST["student_fk_von"]))
		$getstr .= " student_fk >='".$_REQUEST["student_fk_von"]."'";
	if (isset($_REQUEST["student_fk_bis"]))
	{
		if ($getstr != "")
			$getstr .= " AND";

		$getstr .= " student_fk <='".$_REQUEST["student_fk_bis"]."'";
	}
	if ($getstr != "")
		$getstr = " WHERE ".$getstr;

	$sqlstr = $sqlstr.$getstr." order by student_fk";

	if($result = pg_query($conn_fas, $sqlstr))
	{

		while($row = pg_fetch_object($result))
		{
			$fasnoten_arr = getNoten4Student($conn_fas,$row->student_fk);

			$lvkeys_arr = array_keys($fasnoten_arr);
			foreach ($lvkeys_arr as $lvkey)
			{
				$idkeys_arr = array_keys($fasnoten_arr[$lvkey]);
				foreach ($idkeys_arr as $idkey)
				{
					$anz_processed++;

					$lehreinheit_id = $lvkey;
					if (key_exists($fasnoten_arr[$lvkey][$idkey]["student_fk"],$studenten_arr))
						$student_uid = $studenten_arr[$fasnoten_arr[$lvkey][$idkey]["student_fk"]];
					else
						$student_uid = "FEHLT";

					$mitarbeiter_uid = $mitarbeiter_arr[$fasnoten_arr[$lvkey][$idkey]["notenlektor_fk"]];
					$note = $fasnoten_arr[$lvkey][$idkey]["note"];
					$pruefungstyp_kurzbz = getPruefungstyp($fasnoten_arr[$lvkey][$idkey]["status"]);
					$datum = $fasnoten_arr[$lvkey][$idkey]["datum"];
					$anmerkung = $fasnoten_arr[$lvkey][$idkey]["bemerkung"];
					$insertamum = $fasnoten_arr[$lvkey][$idkey]["creationdate"];
					$insertvon = $fasnoten_arr[$lvkey][$idkey]["creationuser_name"];
					$updatevon = "sync";
					$ext_id = $idkey;

					$zeugnistabeintrag = $fasnoten_arr[$lvkey][$idkey]["main"];
					if (key_exists($lvkey,$lv_arr))
						$lehrveranstaltung_id = $lv_arr[$lvkey];
					else
						$lehrveranstaltung_id = "FEHLT";
					$studiensemester_kurzbz = $studsem_arr[$fasnoten_arr[$lvkey][$idkey]["studiensemester_fk"]];

					if($lehreinheit_id_arr = getLehreinheitID($conn_fas,$idkey))
					{
						$lehreinheit_id = "FEHLT";
						foreach($lehreinheit_id_arr as $lehreinh)
						{
							if (key_exists($lehreinh,$lehreinheiten_sync_arr))
							{
								$lehreinheit_id = $lehreinheiten_sync_arr[$lehreinh];
								break;
							}
						}
					}
					else
						$lehreinheit_id = "FEHLT";


					//begin insert tbl_pruefung
					$anz_processed_pruef++;

					if($student_uid == "FEHLT")
					{
						$insert_error_pruef++;
						$text .= "Pr&uuml;fung: Datensatz FAS ID".$idkey.": student_uid ohne zuordnung<br>";
					}
					else if($lehreinheit_id == "FEHLT")
					{
						$insert_error_pruef++;
						$text .= "Pr&uuml;fung: Datensatz FAS ID".$idkey.": Lehreinheit ohne zuordnung<br>";
					}
					else
					{
						$pruef = new pruefung($conn);

						$pruef->lehreinheit_id=$lehreinheit_id;
						$pruef->student_uid=$student_uid;
						$pruef->mitarbeiter_uid=$mitarbeiter_uid;
						$pruef->note=$note;
						$pruef->pruefungstyp_kurzbz=$pruefungstyp_kurzbz;
						$pruef->datum=$datum;
						$pruef->anmerkung=$anmerkung;
						$pruef->insertamum=$insertamum;
						$pruef->insertvon=$insertvon;
						$pruef->updateamum=date("Y-m-d H:m:s");
						$pruef->updatevon=$updatevon;
						$pruef->ext_id=$ext_id;

						if (!($pruef->pruefung_id=checkUpdatePruefung($conn,$pruef)))
							$pruef->new = 1;


						if($pruef->pruefung_id == -1)
							$anz_not_updated_pruef++;

						else
						{
							if(!$pruef->save())
							{
								echo $pruef->errormsg."<br>";
								$text .= "Pr&uuml;fung: Datensatz FAS ID".$idkey.": ".$pruef->errormsg."<br>";
								if($pruef->new)
									$insert_error_pruef++;
								else
									$update_error_pruef++;
							}
							else
								if($pruef->new)
									$anz_insert_pruef++;
								else
									$anz_update_pruef++;
						}
					}

					//begin insert tbl_zeugnisnote
					if ($zeugnistabeintrag == 1)
					{
						$anz_processed_zeug++;

						if($student_uid == "FEHLT")
						{
							$insert_error_zeug++;
							$text .= "<span style='background-color:#cccccc;'>Zeugnis: Datensatz FAS ID".$idkey.": student_uid ohne zuordnung</span><br>";
						}
						else if ($lehrveranstaltung_id == "FEHLT")
						{
							$insert_error_zeug++;
							$text .= "<span style='background-color:#cccccc;'>Zeugnis: Datensatz FAS ID".$idkey.": lehrveranstaltung_id ohne zuordnung</span><br>";
						}
						else
						{
							$zeug = new zeugnisnote($conn);

							$zeug->lehrveranstaltung_id = $lehrveranstaltung_id;
							$zeug->student_uid = $student_uid;
							$zeug->studiensemester_kurzbz = $studiensemester_kurzbz;
							$zeug->note = $note;
							$zeug->uebernahmedatum = null;
							$zeug->benotungsdatum = $datum;
							$zeug->updateamum = date("Y-m-d H:m:s");
							$zeug->updatevon = $updatevon;
							$zeug->insertamum = $insertamum;
							$zeug->insertvon = $insertvon;
							$zeug->ext_id = $ext_id;
							$zeug->bemerkung = $anmerkung;

							if (!($zeug->check = checkUpdateZeugnis($conn,$zeug)))
								$zeug->new = 1;

							if($zeug->check == -1)
								$anz_not_updated_zeug++;

							else
							{
								if(!$zeug->save())
								{
									$text .= "<span style='background-color:#cccccc;'>Zeugnis: Datensatz FAS ID".$idkey.": ".$zeug->errormsg."</span><br>";
									if($zeug->new)
										$insert_error_zeug++;
									else
										$update_error_zeug++;
								}
								else
									if($zeug->new)
										$anz_insert_zeug++;
									else
										$anz_update_zeug++;
							}
						}
					}

					//debug-output start
					/*
					if ($zeugnistabeintrag == 1)
						$text .= "<tr style='background:#eeeeee'>";
					else
						$text .= "<tr>";
					$text .= "<td>".$lehreinheit_id."</td>";
					$text .= "<td>".$student_uid."<br>(".$fasnoten_arr[$lvkey][$idkey]["student_fk"].")</td>";
					$text .= "<td>".$mitarbeiter_uid."</td>";
					$text .= "<td>".$note."</td>";
					$text .= "<td>".$pruefungstyp_kurzbz."</td>";
					$text .= "<td><textarea cols='40'>".$anmerkung."</textarea></td>";
					$text .= "<td>".$insertamum."</td>";
					$text .= "<td>".$insertvon."</td>";
					$text .= "<td>now()</td>";
					$text .= "<td>".$updatevon."</td>";
					$text .= "<td>".$ext_id."</td>";
					if ($zeugnistabeintrag == 1)
					{
						$text .= "<td style='background:#cccccc'>".$lehrveranstaltung_id."</td>";
						$text .= "<td style='background:#cccccc'>".$studiensemester_kurzbz."</td>";
					}
					else
						$text .= "<td></td><td></td>";
					$text .= "</tr>";
					*/
					//debug-output ende

				}
			}

		}
	$text .= "</table>";
	$text .= "<hr><h3>Stats</h3><hr>";
	$text .= "Anzahl der bearbeiteten Datens&auml;tze: ".$anz_processed."<br>";
	$text .= "Anzahl Pr&uuml;fungseintr&auml;ge: ".$anz_processed_pruef."<br>";
	$text .= "Pr&uuml;fungen insert fehler/ok: <span style='color:red'>".$insert_error_pruef."</span>/".$anz_insert_pruef."<br>";
	$text .= "Pr&uuml;fungen update fehler/ok/noupdate: <span style='color:red'> ".$update_error_pruef."</span>/".$anz_update_pruef."/".$anz_not_updated_pruef."<br>";
	$text .= "Anzahl Zeugniseintr&auml;ge: ".$anz_processed_zeug."<br>";
	$text .= "Zeugnisnoten insert fehler/ok: <span style='color:red'>".$insert_error_zeug."</span>/".$anz_insert_zeug.")<br>";
	$text .= "Zeugnisnoten update fehler/ok/noupdate: <span style='color:red'> ".$update_error_zeug."</span>/".$anz_update_zeug."/".$anz_not_updated_zeug."<br>";

	$stopzeit = time();
	$runzeit = $stopzeit - $startzeit;
	$text .= "Dauer: ".$runzeit." s";

	}
	
	$text.="\nEND OF SYNCHRONISATION\n";

	if (mail($adress,"FAS - Vilesci (Noten/Pruefungen)",$headtext."\n\n".$text,"From: vilesci@technikum-wien.at"))
		$sendmail=true;
	else
		$sendmail=false;
?>

<html>
<head>
	<title>FAS - Vilesci (Noten)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";
echo $headtext;
echo "<br><br>";
echo $text;

?>
</body>
</html>