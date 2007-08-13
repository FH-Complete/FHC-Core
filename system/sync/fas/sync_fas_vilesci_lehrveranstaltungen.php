<?php
// **************************************
// Syncronisiert alle Lehrveranstaltungen
// FAS -> VILESCI
// setzt vorraus: - tbl_sprache
//                - tbl_studiengang
// **************************************
	require_once('../../../vilesci/config.inc.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	require_once('../sync_config.inc.php');
	$adress='fas_sync@technikum-wien.at';
	//$adress='oesi@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';

	$conn=pg_connect(CONN_STRING);
	$conn_fas=pg_connect(CONN_STRING_FAS);

	$plausi_error=0;
	$update_error=0;
	$insert_error=0;
	$double_error=0;
	$double_lva_error=0;
	$missing_lva=0;
	$anz_update=0;
	$anz_insert=0;
	$headtext='';
	$head_stg_text="Dies ist eine automatische Mail!\n\nFolgende Fehler sind bei der Synchronisation der Lehrveranstaltungen aufgetreten:\n\n";
	$text='';
	$double_lva = array();
	$stg_data = array();
	$studiensemester = array();
	$studiengang_kz='';
	$notin='';

	//**** FUNCTIONS ****

	//Plausi checks
	function validate($row)
	{
		global $text, $plausi_error, $stg_data, $studiensemester;

		if($row->studiensemester_fk==0)
		{
			$stg_data[$row->kennzahl]['text'] .= '   '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' '.$row->name." hat Studiensemester 0\n";
			$plausi_error++;
			return false;
		}

		if($row->kurzbezeichnung == '')
		{
			//$text.= 'LVA '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' LVA '.$row->name." hat keine Kurzbezeichnung\n";
			$stg_data[$row->kennzahl]['text'] .= '   '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' ('.$studiensemester[$row->studiensemester_fk].') '.$row->name." hat keine Kurzbezeichnung\n";
			$plausi_error++;
			return false;
		}
		if(strlen($row->name)>128)
		{
			$stg_data[$row->kennzahl]['text'] .= '   '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' ('.$studiensemester[$row->studiensemester_fk].') '.$row->name." hat einen zu langen LV-Titel (maximal 128 Zeichen)\n";
			$plausi_error++;
			return false;
		}
		if(strlen($row->kurzbezeichnung)>16)
		{
			$stg_data[$row->kennzahl]['text'] .= '   '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' ('.$studiensemester[$row->studiensemester_fk].') '.$row->name." hat eine zu lange Kurzbezeichnung (2-4 Zeichen + 1 Ziffer)\n";
			$plausi_error++;
			return false;
		}
		if(strlen($row->beschreibung)>64)
		{
			$stg_data[$row->kennzahl]['text'] .= '   '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' ('.$studiensemester[$row->studiensemester_fk].') '.$row->name."  hat eine zu lange Beschreibung (maximal 64 Zeichen)\n";
			$plausi_error++;
			return false;
		}
		if($row->ectspunkte>40)
		{
			$stg_data[$row->kennzahl]['text'] .= '   '.$stg_data[$row->kennzahl]['kuerzel'].' Semester '.$row->semester.' ('.$studiensemester[$row->studiensemester_fk].') '.$row->name." hat mehr als 40 ECTS-Punkte\n";
			$plausi_error++;
			return false;
		}
		return true;
	}

	//Liefert Bezeichnung der Lehrveranstaltung
	function getlvabez($row)
	{
		global $studiensemester, $stg_data;
		return $stg_data[$row->kennzahl]['kuerzel']." Semester $row->semester $row->kurzbezeichnung ".$studiensemester[$row->studiensemester_fk]." ($row->lehrveranstaltung_pk)";
	}

	// Fuegt einen Eintrag in die Synctabelle hinzu
	function synctabentry($lehrveranstaltung_id_vilesci, $lehrveranstaltung_id_fas)
	{
		global $conn;
		$qry = "INSERT INTO sync.tbl_synclehrveranstaltung(lva_fas,lva_vilesci) VALUES($lehrveranstaltung_id_fas, $lehrveranstaltung_id_vilesci);";
		pg_query($conn, $qry);
	}

	//Schaut welche Felder aktualisiert gehoeren und liefert den Update Befehl
	function getupdateqry($row_found, $row_fas_alle)
	{
		$update=false;
		$qry="UPDATE lehre.tbl_lehrveranstaltung SET";
		if($row_fas_alle->name!=$row_found->bezeichnung)
		{
			$qry.=($update?',':'');
			$qry.=" bezeichnung='".addslashes($row_fas_alle->name)."'";
			$update=true;
		}
		if($row_fas_alle->kurzbezeichnung!=$row_found->kurzbz)
		{
			$qry.=($update?',':'');
			$qry.=" kurzbz='".addslashes($row_fas_alle->kurzbezeichnung)."'";
			$update=true;
		}
		if($row_fas_alle->kennzahl!=$row_found->studiengang_kz)
		{
			$qry.=($update?',':'');
			$qry.=" studiengang_kz=$row_fas_alle->kennzahl";
			$update=true;
		}
		if($row_fas_alle->semester!=$row_found->semester)
		{
			$qry.=($update?',':'');
			$qry.=" semester=$row_fas_alle->semester";
			$update=true;
		}
		if($row_fas_alle->ectspunkte!=$row_found->ects)
		{
			$qry.=($update?',':'');
			if($row_fas_alle->ectspunkte!='')
				$qry.=" ects='$row_fas_alle->ectspunkte'";
			else
				$qry.=" ects=null";
			$update=true;
		}
		if($row_fas_alle->beschreibung!=$row_found->anmerkung)
		{
			$qry.=($update?',':'');
			$qry.=" anmerkung='".addslashes($row_fas_alle->beschreibung)."'";
			$update=true;
		}

		$qry.=" WHERE lehrveranstaltung_id='$row_found->lehrveranstaltung_id'";

		if($update)
			return $qry;
		else
			return '';
	}

	//**** BEGIN OF SYNCRONISATION ****
	// Assistenz-Email holen
	$sql_query="SELECT studiengang_kz, email, UPPER(typ::varchar(1) || kurzbz) as kuerzel FROM public.tbl_studiengang";
	$result = pg_query($conn, $sql_query);
	while($row=pg_fetch_object($result))
	{
		$stg_data[$row->studiengang_kz]['mail']=$row->email;
		$stg_data[$row->studiengang_kz]['kuerzel']=$row->kuerzel;
		$stg_data[$row->studiengang_kz]['text']='';
	}
	//Fehler fuer die Freifaecher an Augustin schicken
	$stg_data[0]['mail']='caugust@technikum-wien.at';

	//Studiensemester holen
	$sql_query="SELECT studiensemester_pk,
	                   CASE WHEN art=1 THEN 'WS'
	                        WHEN art=2 THEN 'SS'
	                   END || jahr AS stsem FROM studiensemester";
	$result = pg_query($conn_fas, $sql_query);
	while($row=pg_fetch_object($result))
	{
		$studiensemester[$row->studiensemester_pk]=$row->stsem;
	}

	// Lehreinheiten ohne Lehrveranstaltung suchen
	$qry = "SELECT bezeichnung, semester, kennzahl, studiensemester_fk FROM lehreinheit, studiengang, ausbildungssemester WHERE ausbildungssemester_fk=ausbildungssemester_pk AND lehreinheit.studiengang_fk=studiengang_pk AND lehrveranstaltung_fk NOT IN(SELECT lehrveranstaltung_pk FROM lehrveranstaltung)";
	$result = pg_query($conn_fas, $qry);
	while($row = pg_fetch_object($result))
	{
		$stg_data[$row->kennzahl]['text'] .= '   Lehreinheit '.$stg_data[$row->kennzahl]['kuerzel'].' '.$row->semester.' ('.$studiensemester[$row->studiensemester_fk].') '.$row->bezeichnung." hat keine zugehoerige Lehrveranstaltung\n";
		$missing_lva++;
	}

 	// Anzahl der Lehrveranstaltungen in VileSci
	$sql_query="SELECT count(*) AS anz FROM lehre.tbl_lehrveranstaltung";
	$result=pg_query($conn, $sql_query);
	$row=pg_fetch_object($result);
	$vilesci_anz_lva = $row->anz;
	
	foreach ($dont_sync_php as $notstg)
	{
		if($notin=='')
		{
			$notin="'".$notstg."'";
		}
		else 
		{
			$notin.=", '".$notstg."'";
		}
	}
	
	// Start LVA Synchro
	$sql_query="SELECT lehrveranstaltung.*, ausbildungssemester.semester, studiengang.kennzahl
	            FROM lehrveranstaltung, ausbildungssemester, studiengang
	            WHERE ausbildungssemester_fk=ausbildungssemester_pk AND
	                  lehrveranstaltung.studiengang_fk=studiengang_pk AND
	                  studiengang.kennzahl NOT IN (".$notin.") AND
	                  studiensemester_fk<>0 AND
	                  lehrveranstaltung.lehrveranstaltung_pk NOT IN(
	                  	SELECT lv1.lehrveranstaltung_pk
	                  	FROM lehrveranstaltung lv1, lehrveranstaltung lv2
	                  	WHERE lv1.lehrveranstaltung_pk<>lv2.lehrveranstaltung_pk AND
	                  	      lv1.ausbildungssemester_fk=lv2.ausbildungssemester_fk AND
	                  	      lv1.kurzbezeichnung=lv2.kurzbezeichnung AND
	                  	      lv1.name<>lv2.name AND lv1.kurzbezeichnung is not null AND
	                  	      lv1.kurzbezeichnung<>'' AND lv2.kurzbezeichnung is not null AND lv2.kurzbezeichnung<>'')
	            ORDER BY kennzahl, semester, studiensemester_fk";
	flush();
	$result_fas_alle=pg_query($conn_fas, $sql_query);
	$num_rows=pg_num_rows($result_fas_alle);
	$headtext.="Dies ist eine automatische eMail!\n\n";
	$headtext.="Es wurde eine Synchronisation mit FAS durchgeführt.\n";
	$headtext.="Anzahl der Lehrveranstaltungen vom FAS: $num_rows \n";
	$headtext.="Anzahl der Lehrveranstaltungen in Vilesci: $vilesci_anz_lva \n\n";

	for ($i=0;$row_fas_alle=pg_fetch_object($result_fas_alle);$i++)
	{
		//btec auf 0 umlenken (Freifaecher)
		if($row_fas_alle->kennzahl==203 && $row_fas_alle->studiensemester_fk>5)
			$row_fas_alle->kennzahl='0';

		// Plausibilitaetscheck
		if(validate($row_fas_alle))
		{
			//schauen ob lva schon in synctabelle ist
			$qry = "SELECT * FROM sync.tbl_synclehrveranstaltung WHERE lva_fas='$row_fas_alle->lehrveranstaltung_pk'";
			$result = pg_query($conn, $qry);
			if(pg_num_rows($result)==0)
			{
				//WEITERSUCHEN
				//Gleicher /Stg/Sem/Kurzbz
				$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE
				        studiengang_kz='$row_fas_alle->kennzahl' AND
				        semester='$row_fas_alle->semester' AND
				        kurzbz='$row_fas_alle->kurzbezeichnung'";
				$result = pg_query($conn, $qry);
				$studiengang_kz=$row_fas_alle->kurzbezeichnung;

				if(pg_num_rows($result)==1)
				{
					//$text.='FOUND on Kurzbz LVA '.getlvabez($row_fas_alle)." -> UPDATE & SYNCTAB-Insert\n";

					if($row_found = pg_fetch_object($result))
					{
						if (!in_array($studiengang_kz,$dont_sync_php))
						{
							//Datensatz aktualisieren
							$qry = getupdateqry($row_found, $row_fas_alle);

							if($qry!='')
							{
								if(pg_query($conn, $qry))
								{
									//Eintrag zur Synctabelle hinzufuegen
									synctabentry($row_found->lehrveranstaltung_id, $row_fas_alle->lehrveranstaltung_pk);
									//$text.="LVA wurde aktualisiert: $qry\n";
									$anz_update++;
								}
								else
								{
									$text.="Fehler beim Update einer LVA: $qry\n";
									$update_error++;
								}
							}
							else
							{
								synctabentry($row_found->lehrveranstaltung_id, $row_fas_alle->lehrveranstaltung_pk);
								$text.="SYNC-Eintrag wurde angelegt!\n";
							}
						}
					}
					else
					{
						$text.='Fehler beim Lesen des Datensatzes:'.getlvabez($row_fas_alle)."\n";
						$update_error++;
					}
				}
				elseif(pg_num_rows($result)==0)
				{
					//WEITERSUCHEN
					//Gleicher /Stg/Sem/Bezeichnung
					$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE
					        studiengang_kz='$row_fas_alle->kennzahl' AND
					        semester='$row_fas_alle->semester' AND
					        bezeichnung='$row_fas_alle->name'";
					$result = pg_query($conn, $qry);
					$studiengang_kz=$row_fas_alle->kurzbezeichnung;

					if(pg_num_rows($result)==1)
					{
						if($row_found = pg_fetch_object($result))
						{
							if(!in_array($studiengang_kz,$dont_sync_php))
							{
								//Gefunden->Update und Synctab-Eintrag
								//$text.='FOUND on Name LVA '.getlvabez($row_fas_alle)." -> UPDATE & SYNCTAB-Insert\n";
								$qry = getupdateqry($row_found, $row_fas_alle);

								if($qry!='')
								{
									if(pg_query($conn, $qry))
									{
										//Eintrag zur Synctabelle hinzufuegen
										synctabentry($row_found->lehrveranstaltung_id, $row_fas_alle->lehrveranstaltung_pk);
										//$text.="LVA wurde aktualisiert: $qry\n";
										$anz_update++;
									}
									else
									{
										$text.="Fehler beim Update einer LVA: $qry\n";
										$update_error++;
									}
								}
								else
								{
									synctabentry($row_found->lehrveranstaltung_id, $row_fas_alle->lehrveranstaltung_pk);
									$text.="SYNC-Eintrag wurde angelegt!\n";
								}
							}
						}
						else
						{
							$text.='Fehler beim Lesen des Datensatzes:'.getlvabez($row_fas_alle)."\n";
							$update_error++;
						}
					}
					elseif(pg_num_rows($result)==0)
					{
						//$text.="NOT FOUND -> INSERT & SYNCTAB-Insert\n";
						//Neue Lehrveranstaltung anlegen
						$lv = new lehrveranstaltung($conn);
						$lv->kurzbz = $row_fas_alle->kurzbezeichnung;
						$lv->bezeichnung = $row_fas_alle->name;
						$lv->studiengang_kz = $row_fas_alle->kennzahl;
						$lv->semester = $row_fas_alle->semester;
						$lv->sprache = 'German';
						$lv->ects = $row_fas_alle->ectspunkte;
						$lv->semesterstunden = 0;
						$lv->anmerkung = $row_fas_alle->beschreibung;
						$lv->lehre = false;
						$lv->lehreverzeichnis = strtolower($row_fas_alle->kurzbezeichnung);
						$lv->aktiv = false;
						$lv->planfaktor = 0;
						$lv->planlektoren = 0;
						$lv->planpersonalkosten = 0;
						$lv->plankostenprolektor=0;
						$lv->updateamum = date('Y-m-d H:i:s');
						$lv->updatevon = 'Sync';
						$lv->insertamum = date('Y-m-d H:i:s');
						$lv->insertvon = 'Sync';

						if($lv->save(true))
						{
							synctabentry($lv->lehrveranstaltung_id, $row_fas_alle->lehrveranstaltung_pk);
							$text.="Eine neue Lehrveranstaltung wurde angelegt: ".getlvabez($row_fas_alle)."\n";
							$anz_insert++;
						}
						else
						{
							$text.="Fehler beim Anlegen der LVA ".getlvabez($row_fas_alle).": $lv->errormsg\n";
							$insert_error++;
						}
					}
					else
					{
						$text.="MULTIFOUND Bezeichnung".getlvabez($row_fas_alle)."-> BREAK\n";
					}
				}
				else
				{
					$text.="MULTIFOUND kurzbz ".getlvabez($row_fas_alle)."-> BREAK\n";
					if(!isset($double_lva[$row_fas_alle->lehrveranstaltung_pk]))
						$double_lva[$row_fas_alle->lehrveranstaltung_pk] = getlvabez($row_fas_alle);
					$double_lva_error++;
				}
			}
			elseif(pg_num_rows($result)==1)
			{
				$row_id = pg_fetch_object($result);
				$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$row_id->lva_vilesci'";
				$result = pg_query($conn, $qry);
				$studiengang_kz=$row_fas_alle->kurzbezeichnung;
				//UPDATE
				if($row_found = pg_fetch_object($result))
				{
					if(!in_array($studiengang_kz,$dont_sync_php))
					{
						//Datensatz aktualisieren
						$qry = getupdateqry($row_found, $row_fas_alle);

						if($qry!='')
						{
							if(pg_query($conn, $qry))
							{
								//$text.="LVA wurde aktualisiert: $qry\n";
								$anz_update++;
							}
							else
							{
								$text.="Fehler beim Update einer LVA: $qry\n";
								$update_error++;
							}
						}
					}
				}
				else
				{
					$text.='Fehler beim Lesen des Datensatzes:'.getlvabez($row_fas_alle);
					$update_error++;
				}
			}
			else
			{
				$text.="\nLVA ".getlvabez($row_fas_alle)." hat mehrere Eintraege in tbl_synclehrveranstaltung\n";
				$double_error++;
			}
		}
		else
		{
			//$text.="\nLVA ".getlvabez($row_fas_alle)." hat nicht plausible Daten\n";
		}
	}

	$headtext.="\n$plausi_error Fehler beim Plausibilitaetscheck!\n";
	$headtext.="$update_error Fehler bei LVA-Update!\n";
	$headtext.="$insert_error Fehler bei LVA-Insert!\n";
	$headtext.="$double_error Fas_id's kommen in Synctab mehrmals vor!\n";
	$headtext.="$double_lva_error Lehrveranstaltungen kommen in VileSci mehrmals vor!\n";
	$headtext.="$anz_update LVAs wurden aktualisiert.\n";
	$headtext.="$anz_insert LVAs wurden neu angelegt.\n";
	$headtext.="$missing_lva Lehreinheiten haben keine LV.\n";

	$qry = "Select count(*) as anzahl FROM (SELECT distinct lv1.lehrveranstaltung_pk
	                  	FROM lehrveranstaltung lv1, lehrveranstaltung lv2
	                  	WHERE lv1.lehrveranstaltung_pk<lv2.lehrveranstaltung_pk AND
	                  	      lv1.ausbildungssemester_fk=lv2.ausbildungssemester_fk AND
	                  	      lv1.kurzbezeichnung=lv2.kurzbezeichnung AND
	                  	      lv1.name<>lv2.name AND lv1.kurzbezeichnung is not null AND
	                  	      lv1.kurzbezeichnung<>'' AND lv2.kurzbezeichnung is not null AND lv2.kurzbezeichnung<>'') as a";
	$result = pg_query($conn_fas, $qry);
	$row = pg_fetch_object($result);
	if($row->anzahl>0)
	{
		$headtext.="$row->anzahl LVAs haben verschiedene Bezeichnungen";
		$text.="Gleiche LVAs mit unterschiedlicher Bezeichnung vorhanden: \n\nSELECT distinct lv1.lehrveranstaltung_pk FROM lehrveranstaltung lv1, lehrveranstaltung lv2 WHERE lv1.lehrveranstaltung_pk<>lv2.lehrveranstaltung_pk AND lv1.ausbildungssemester_fk=lv2.ausbildungssemester_fk AND lv1.kurzbezeichnung=lv2.kurzbezeichnung AND lv1.name<>lv2.name AND lv1.kurzbezeichnung is not null AND lv1.kurzbezeichnung<>'' AND lv2.kurzbezeichnung is not null AND lv2.kurzbezeichnung<>''\n";
	}

	if(count($double_lva)>0)
	{
		$headtext.="\nDoppelte Lehrveranstaltungen:\n\n";
		foreach ($double_lva as $bez)
			$headtext.=$bez."\n";
	}


	foreach ($stg_data as $stg=>$trash)
	{
		$msg = $stg_data[$stg]['text'];
		if($msg!='')
		{
			$text.="\nMails an Studiengang ".$stg_data[$stg]['kuerzel'].'('.$stg_data[$stg]['mail'].") ... ";
			if(mail($adress,"FAS - Vilesci (Lehrveranstaltungen) ".$stg_data[$stg]['kuerzel'],$head_stg_text.$msg,"From: vilesci@technikum-wien.at"))
				$text.="gesendet\n\n$msg";
			else
				$text.="FEHLER beim senden\n\n$msg";
		}
	}

	$text.="\nEND OF SYNCHRONISATION\n";

	if (mail($adress,"FAS - Vilesci (Lehrveranstaltungen)",$headtext."\n\n".$text,"From: vilesci@technikum-wien.at"))
		$sendmail=true;
	else
		$sendmail=false;
?>

<html>
<head>
	<title>FAS - Vilesci (Lehrveranstaltungen)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php

if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";
echo nl2br($headtext);
echo "<br><br>";
echo nl2br($text);

?>
</body>
</html>