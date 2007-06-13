<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Lehreinheitendatens�tze von FAS DB in PORTAL DB
//*
//*

require_once('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) 
	or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) 
	or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$ausgabe_all='';
$ausgabe='';
$ausgabe_le='';
$ausgabe_lm='';
$ausgabe_lg='';
$ausgabe1='';
$anzahl_part=0;
$anzahl_part_gesamt=0;
$anzahl_eingefuegt=0;
$anzahl_geaendert=0;
$anzahl_fehler=0;
$anzahl_quelle=0;
$anzahl_eingefuegt_lg=0;
$anzahl_geaendert_lg=0;
$anzahl_fehler_lg=0;
$anzahl_eingefuegt_lm=0;
$anzahl_geaendert_lm=0;
$anzahl_fehler_lm=0;
$anzahl_lehrfaecher=0;
$le_iu='';
$lm_iu='';
$lg_iu='';

$m_uid='';
$lektor='';

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/*************************
 * FAS-VILESCI - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Lehreinheiten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
echo "Lehreinheiten Sync<br>----------------------<br>";
echo "Lehreinheitensynchro Beginn: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."<br><br>";

//studiensemester
$qry="SELECT studiensemester_kurzbz, ext_id FROM public.tbl_studiensemester;";
if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$studiensemester[$row->ext_id]=$row->studiensemester_kurzbz;
	}
}
//fachbereiche
$qry="SELECT fachbereich_kurzbz,ext_id FROM public.tbl_fachbereich WHERE ext_id IS NOT NULL;";
if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$fachbereiche[$row->ext_id]=$row->fachbereich_kurzbz;
	}
}
//lehrformen
$qry="SELECT kurzbezeichnung,lehrform_pk FROM lehrform;";
if($result = pg_query($conn_fas, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$lehrformen[$row->lehrform_pk]=trim($row->kurzbezeichnung);
	}
}
//raumtypen
$qry="SELECT kurzbezeichnung,raumtyp_pk FROM raumtyp;";
if($result = pg_query($conn_fas, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$raumtypen[$row->raumtyp_pk]=trim($row->kurzbezeichnung);
	}
}
//print_r($raumtypen);

//lehrfunktionen
$qry="SELECT bezeichnung, lehrfunktion_pk FROM lehrfunktion;";
if($result = pg_query($conn_fas, $qry))
{
	while($row=pg_fetch_object($result))
	{ 
		$qry2="SELECT lehrfunktion_kurzbz FROM lehre.tbl_lehrfunktion WHERE beschreibung='".$row->bezeichnung."';";
		if($result2 = pg_query($conn, $qry2))
		{
			if($row2=pg_fetch_object($result2))
			{
				$lehrfunktionen[$row->lehrfunktion_pk]=trim($row2->lehrfunktion_kurzbz);
				//echo "Lehrfunktionen[".$row->lehrfunktion_pk."] = ".$lehrfunktionen[$row->lehrfunktion_pk]."<br>";
			}
		}
	}
}
		
$qry_main = "SELECT *,lehreinheit.lehreinheit_fk as le_fk, mitarbeiter_lehreinheit.creationdate as lm_creationdate,
			lehreinheit.ivar1 as wochenrythmus, lehreinheit.ivar2 as start_kw, lehreinheit.ivar3 as stundenblockung 
		FROM lehreinheit, mitarbeiter_lehreinheit 
		WHERE lehreinheit.lehreinheit_pk=mitarbeiter_lehreinheit.lehreinheit_fk  
		ORDER BY lehreinheit.lehreinheit_fk;";
//AND mitarbeiter_fk='1512' 
if($result = pg_query($conn_fas, $qry_main))
{
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		//pg_query($conn, "BEGIN");
		$error=false;
		//$lehrveranstaltung_id	='';
		$studiensemester_kurzbz	=$studiensemester[$row->studiensemester_fk];
		//$lehrfach_id			='';
		$lehrform_kurzbz		=$lehrformen[$row->lehrform_fk];
		$stundenblockung		=$row->stundenblockung;
		$wochenrythmus		=$row->wochenrythmus;
		$start_kw			=$row->start_kw;
		$raumtyp			=$raumtypen[$row->raumtyp_fk];
		$raumtypalternativ		=$raumtypen[$row->alternativraumtyp_fk];
		$sprache			='German';
		$lehre				=true;
		$anmerkung			=$row->bemerkungen;
		$unr				="";
		$lvnr				=$row->nummer;
		//$updateamum		='';
		$updatevon			='SYNC';
		$insertamum			=$row->creationdate;
		//$insertvon			='';
		$ext_id			=$row->lehreinheit_pk;
		$kurzbezeichnung		=$row->kurzbezeichnung;
		$bezeichnung		=$row->bezeichnung;
		$farbe				="CCCCCC";
		
		$lehrfunktion			=$row->lehrfunktion_fk;
		
		$lektor				=$row->mitarbeiter_fk;
		$gruppe_fk			=$row->gruppe_fk;
		$lehreinheit_part		=$row->le_fk;
		$fachbereich_kurzbz		=$fachbereiche[$row->fachbereich_fk];
		
		$semester='';
		$verband='';
		$gruppe='';
		
		if($start_kw<1 || $start_kw>53)
		{
			$start_kw=NULL;
		}
		//insertvon ermitteln
		$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
		if($resultcu = pg_query($conn_fas, $qrycu))
		{
			if($rowcu=pg_fetch_object($resultcu))
			{
				$insertvon=$rowcu->name;
			}
		}
		//lehrveranstaltung ermitteln
		$qry="SELECT lva_vilesci FROM sync.tbl_synclehrveranstaltung WHERE lva_fas='".$row->lehrveranstaltung_fk."';";
		if($results = pg_query($conn, $qry))
		{
			if($rows=pg_fetch_object($results))
			{ 
				$lva=$rows->lva_vilesci;	
			}
			else 
			{
				$error=true;
				$error_log.="LVA_FAS=".$row->lehrveranstaltung_fk." in Tabelle tbl_synclehrveranstaltung nicht gefunden!\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//studiengang ermitteln
		$qry="SELECT lehrveranstaltung_id, studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='".$lva."';";
		if($result1 = pg_query($conn, $qry))
		{
			if($row1=pg_fetch_object($result1))
			{ 
				$lehrveranstaltung_id=$row1->lehrveranstaltung_id;
				$studiengang_kz=$row1->studiengang_kz;
				$semester=$row1->semester;
			}
			else 
			{
				$error=true;
				$error_log.="Lehrveranstaltung mit ext_id='".$row->lehrveranstaltung_fk."' nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
		//lehrfach ermitteln
		$qry="SELECT lehrfach_id FROM lehre.tbl_lehrfach WHERE kurzbz='".$kurzbezeichnung."' AND fachbereich_kurzbz='".$fachbereich_kurzbz."' AND semester='".$semester."' AND studiengang_kz='".$studiengang_kz."';";
		if($resulto = pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$lehrfach_id=$rowo->lehrfach_id;
			}
			else 
			{
				//lehrfach nicht vorhanden => anlegen
				$qry="INSERT INTO lehre.tbl_lehrfach (studiengang_kz, fachbereich_kurzbz, kurzbz, bezeichnung, farbe, aktiv, 
					semester, sprache, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES (".
					myaddslashes($studiengang_kz).", ".
					myaddslashes($fachbereich_kurzbz).", ".
					myaddslashes($kurzbezeichnung).", ".
					myaddslashes($bezeichnung).", ".
					myaddslashes($farbe).", ".
					"false, ".
					myaddslashes($semester).", ".
					myaddslashes($sprache).", ".
					"now(), ".
					"'Sync', ".
					"now(), ".
					"'Sync', ".
					"NULL);";
				if($result2 = pg_query($conn, $qry))
				{
					$qryu = "SELECT currval('lehre.tbl_lehrfach_lehrfach_id_seq') AS id;";
					if($rowu=pg_fetch_object(pg_query($conn,$qryu)))
						$lehrfach_id=$rowu->id;
					else
					{					
						$error=true;
						$error_log.='Lehrfach-Sequence konnte nicht ausgelesen werden';
					}
					$anzahl_lehrfaecher++;
					$ausgabe.="Lehrfach '".$bezeichnung."' ('".$kurzbezeichnung."'), Fachbereich '".$fachbereich_kurzbz."', Studiengang '".$studiengang_kz."' und Semester '".$semester."' angelegt!\n";
					echo "Lehrfach '".$bezeichnung."' ('".$kurzbezeichnung."'), Fachbereich '".$fachbereich_kurzbz."', Studiengang '".$studiengang_kz."' und Semester '".$semester."' angelegt!<br>";
					
				}
				//$ausgabe.="Lehrfach '".$lehrfach_id."' angelegt: Studiengang '".$studiengang_kz."', Fachbereich '".$fachbereich_kurzbz."', Kurzbezeichnung '".$kurzbezeichnung."', Semester '".$semester."' und ext_id '".$ext_id."'!\n";
				//$error=true;
				//$error_log.="Lehrfach mit Fachbereich='".$fachbereich_kurzbz."', Semester='".$semester."' und Studiengang='".$studiengang_kz."' nicht gefunden.\n";
			}
		}
		if($error)
		{
			$anzahl_fehler++;
			continue;
		}
				
		//unterrichtenden lektor ermitteln
		$qry="SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE ext_id='".$lektor."';";
		if($resulto = pg_query($conn, $qry))
		{
			if($rowo=pg_fetch_object($resulto))
			{ 
				$m_uid=$rowo->mitarbeiter_uid;
			}
		}
		//gruppe ermitteln
		//spezialgruppe?
		$qry="SELECT * FROM sync.tbl_syncgruppe WHERE fas_gruppe='".$gruppe_fk."';";
		if($result2 = pg_query($conn, $qry))
		{
			if($row2=pg_fetch_object($result2))
			{ 
				$gruppe_kurzbz=$row2->vilesci_gruppe;
				$semeter=NULL;
				$verband=NULL;
				$gruppe=NULL;
			}
			else 
			{
				//verbandsgruppe
				$gruppe_kurzbz=NULL;
				$qry2="SELECT * FROM gruppe WHERE gruppe_pk='".$gruppe_fk."';";
				if($result2 = pg_query($conn_fas, $qry2))
				{
					if($row2=pg_fetch_object($result2))
					{ 
						$typ=$row2->typ;
						if($row2->typ=='1')
						{
							$semester=$row2->name;
							$verband=NULL;
							$gruppe=NULL;
							
						}
						elseif ($row2->typ=='2')
						{
							$verband=$row2->name;
							$gruppe=NULL;
							
							$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
							if($result3 = pg_query($conn_fas, $qry3))
							{
								if($row3=pg_fetch_object($result3))
								{ 
									$semester=$row3->name;	
								}
								else 
								{
									$error_log="Gruppe mit gruppe_pk=".$row2->obergruppe_fk."nicht gefunden (1).\n";
									$error=true;
								}
							}
							else 
							{
								$error_log="Fehler beim Zugriff auf Tabelle gruppe (1).\n";
								$error=true;
							}	
						}
						elseif ($row2->typ=='3')
						{
							$gruppe=$row2->name;
							
							$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
							if($result3 = pg_query($conn_fas, $qry3))
							{
								if($row3=pg_fetch_object($result3))
								{ 
									$verband=$row3->name;	
									$qry4="SELECT * FROM gruppe WHERE gruppe_pk='".$row3->obergruppe_fk."';";
									if($result4 = pg_query($conn_fas, $qry4))
									{
										if($row4=pg_fetch_object($result4))
										{ 
											$semester=$row4->name;	
										}
										else 
										{
											$error_log="Gruppe mit gruppe_pk=".$row2->obergruppe_fk."nicht gefunden (3).\n";
											$error=true;
										}
									}
									else 
									{
										$error_log="Fehler beim Zugriff auf Tabelle gruppe (3).\n";
										$error=true;
									}
								}
								else 
								{
									$error_log="Gruppe mit gruppe_pk=".$row2->obergruppe_fk."nicht gefunden (2).\n";
									$error=true;
								}
							}
							else 
							{
								$error_log="Fehler beim Zugriff auf Tabelle gruppe (2).\n";
								$error=true;
							}
						}
						elseif($row2->typ=='10' && strlen($row2->name)==1)
						{
							if($row2->obergruppe_fk!=0)
							{
								$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
								if($result3 = pg_query($conn_fas, $qry3))
								{
									if($row3=pg_fetch_object($result3))
									{ 
										if($row3->obergruppe_fk!=0)
										{
											$qry4="SELECT * FROM gruppe WHERE gruppe_pk='".$row3->obergruppe_fk."';";
											if($result4 = pg_query($conn_fas, $qry4))
											{
												if($row4=pg_fetch_object($result4))
												{ 
													$semester=$row4->name;
													$verband=$row3->name;
													$gruppe=$row2->name;	
												}
											}
										}
										else 
										{
											$semester=$row3->name;
											$verband=$row2->name;
											$gruppe=NULL;
										}
									}
								}
							}
							else 
							{
								$semester=$row2->name;
								$verband=NULL;
								$gruppe=NULL;
							}
						}
						else
						{
							//$error_log="Gruppentyp nicht 1, 2, 3 oder 10.\n";
							//$error=true;
							continue;
						}
					}
					else
					{
						$error_log="Eintragung in Tabelle gruppe mit gruppe_pk='".$row->gruppe_fk."' nicht gefunden.\n";
						$error=true;
					}
				}
				else 
				{
					$error_log.="Fehler beim Zugriff auf Tabelle gruppe (1).\n";
					$error=true;
				}
			}
		}
		if($error)
		{
			continue;
		}
		if($lehreinheit_part<0)
		{
			//nicht-partizipierend
			pg_query($conn,'BEGIN;');
			$qry="SELECT * FROM lehre.tbl_lehreinheit WHERE ext_id='".$ext_id."';";
			if($result3 = pg_query($conn, $qry))
			{
				if(!$row3=pg_fetch_object($result3))
				{	
					//ext_id nicht gefunden
					/*$qry="	SELECT * FROM campus.vw_lehreinheit WHERE lehrveranstaltung_id='".$lehrveranstaltung_id."' 
						AND studiensemester_kurzbz='".$studiensemester_kurzbz."' AND lehrform_kurzbz='".$lehrform_kurzbz."' 
						AND lvnr='".($lvnr)."' 
						AND ".($m_uid!=''?"mitarbeiter_uid=".myaddslashes($m_uid):"mitarbeiter_uid IS NULL")." 
						AND lehrfach_bez=".myaddslashes($bezeichnung)." AND lehrfach=".myaddslashes($kurzbezeichnung)." 
						AND fachbereich_kurzbz=".myaddslashes($fachbereich_kurzbz)." 
						AND studiengang_kz=".myaddslashes($studiengang_kz)." 
						AND ((".($gruppe_kurzbz!=''?"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz):"gruppe_kurzbz IS NULL")." AND gruppe_kurzbz IS NOT NULL) OR
						(".($semester!=''?"semester=".myaddslashes($semester):"semester IS NULL")." AND ".($verband!=''?"verband=".myaddslashes($verband):"verband IS NULL")." 
						AND ".($gruppe!=''?"gruppe=".myaddslashes($gruppe):"gruppe IS NULL")." AND semester IS NOT NULL AND gruppe_kurzbz IS NULL));";
					*/
					$qry="	SELECT * FROM campus.vw_lehreinheit WHERE lehrveranstaltung_id='".$lehrveranstaltung_id."' 
						AND studiensemester_kurzbz='".$studiensemester_kurzbz."' AND lehrform_kurzbz='".$lehrform_kurzbz."' 
						AND lvnr='".($lvnr)."' 
						AND ".($raumtyp!=''?"raumtyp=".myaddslashes($raumtyp):"raumtyp IS NULL")." 
						AND ".($raumtypalternativ!=''?"raumtypalternativ=".myaddslashes($raumtypalternativ):"raumtypalternativ IS NULL")." 
						AND ".($stundenblockung!=''?"stundenblockung=".myaddslashes($stundenblockung):"stundenblockung IS NULL")." 
						AND ".($start_kw!=''?"start_kw=".myaddslashes($start_kw):"start_kw IS NULL")." 
						AND ".(round($row->gesamtstunden)!=''?"planstunden=".myaddslashes(round($row->gesamtstunden)):"planstunden IS NULL")." 
						AND ".($m_uid!=''?"mitarbeiter_uid=".myaddslashes($m_uid):"mitarbeiter_uid IS NULL")." 
						AND lehrfach_bez=".myaddslashes($bezeichnung)." AND lehrfach=".myaddslashes($kurzbezeichnung)." 
						AND fachbereich_kurzbz=".myaddslashes($fachbereich_kurzbz)." 
						AND studiengang_kz=".myaddslashes($studiengang_kz)." 
						AND ((".($gruppe_kurzbz!=''?"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz):"gruppe_kurzbz IS NULL")." AND gruppe_kurzbz IS NOT NULL) OR
						(".($semester!=''?"semester=".myaddslashes($semester):"semester IS NULL")." AND ".($verband!=''?"verband=".myaddslashes($verband):"verband IS NULL")." 
						AND ".($gruppe!=''?"gruppe=".myaddslashes($gruppe):"gruppe IS NULL")." AND semester IS NOT NULL AND gruppe_kurzbz IS NULL));";
					
					//echo "-".$start_kw."-".$qry;exit;
					if($result2 = pg_query($conn, $qry))
					{
						if(pg_num_rows($result2)>0)
						{
							if(pg_num_rows($result2)>1)
							{
								echo pg_num_rows($result2)."/".$qry."<br>";
								$error_log.=pg_num_rows($result2)."/".$qry."\n";
								$anzahl_fehler++;
								pg_query($conn,'ROLLBACK;');
								continue;
								
							}
							 elseif($row2=pg_fetch_object($result2))
							{ 	
								//update
								$le_iu='u';
								$update=false;
								if($row2->lehrveranstaltung_id!=$lehrveranstaltung_id)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Lehrveranstaltung ID: '".$lehrveranstaltung_id."' statt('".$row2->lehrveranstaltung_id."')";
									}
									else
									{
										$ausgabe_le="Lehrveranstaltung ID: '".$lehrveranstaltung_id."' statt('".$row2->lehrveranstaltung_id."')";
									}
								}
								if($row2->studiensemester_kurzbz!=$studiensemester_kurzbz)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Studiensemester: '".$studiensemester_kurzbz."' statt('".$row2->studiensemester_kurzbz."')";
									}
									else
									{
										$ausgabe_le="Studiensemester: '".$studiensemester_kurzbz."' statt('".$row2->studiensemester_kurzbz."')";
									}
								}
								if($row2->lehrfach_id!=$lehrfach_id)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Lehrfach ID: '".$lehrfach_id."' statt('".$row2->lehrfach_id."')";
									}
									else
									{
										$ausgabe_le="Lehrfach ID: '".$lehrfach_id."' statt('".$row2->lehrfach_id."')";
									}
								}
								if($row2->lehrform_kurzbz!=$lehrform_kurzbz)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Lehrform: '".$lehrform_kurzbz."' statt('".$row2->lehrform_kurzbz."')";
									}
									else
									{
										$ausgabe_le="Lehrform: '".$lehrform_kurzbz."' statt('".$row2->lehrform_kurzbz."')";
									}
								}
								if($row2->stundenblockung!=$stundenblockung)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Stundenblockung: '".$stundenblockung."' statt('".$row2->stundenblockung."')";
									}
									else
									{
										$ausgabe_le="Stundenblockung: '".$stundenblockung."' statt('".$row2->stundenblockung."')";
									}
								}
								if($row2->wochenrythmus!=$wochenrythmus)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Wochenrythmus: '".$wochenrythmus."' statt('".$row2->wochenrythmus."')";
									}
									else
									{
										$ausgabe_le="Wochenrythmus: '".$wochenrythmus."' statt('".$row2->wochenrythmus."')";
									}
								}
								if($row2->start_kw!=$start_kw)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Start_kw: '".$start_kw."' statt('".$row2->start_kw."')";
									}
									else
									{
										$ausgabe_le="Start_kw: '".$start_kw."' statt('".$row2->start_kw."')";
									}
								}
								if($row2->raumtyp!=$raumtyp)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Raumtyp: '".$raumtyp."' statt('".$row2->raumtyp."')";
									}
									else
									{
										$ausgabe_le="Raumtyp: '".$raumtyp."' statt('".$row2->raumtyp."')";
									}
								}
								if($row2->raumtypalternativ!=$raumtypalternativ)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Raumtyp alterativ: '".$raumtypalternativ."' statt('".$row2->raumtypalternativ."')";
									}
									else
									{
										$ausgabe_le="Raumtyp alternativ: '".$raumtypalternativ."' statt('".$row2->raumtypalternativ."')";
									}
								}
								if($row2->lehre!=($lehre?'t':'f'))
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Lehre: '".($lehre?'true':'false')."' statt('".($row2->lehre?'true':'false')."')";
									}
									else
									{
										$ausgabe_le="Lehre: '".($lehre?'true':'false')."' statt('".($row2->lehre?'true':'false')."')";
									}
								}						
								if($row2->anmerkung!=$anmerkung)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Anmerkung: '".$anmerkung."' statt('".$row2->anmerkung."')";
									}
									else
									{
										$ausgabe_le="Anmerkung: '".$anmerkung."' statt('".$row2->anmerkung."')";
									}
								}
								if($row2->unr!=$unr)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", UNR: '".$unr."' statt('".$row2->unr."')";
									}
									else
									{
										$ausgabe_le="UNR: '".$unr."' statt('".$row2->unr."')";
									}
								}
								if($row2->lvnr!=$lvnr)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", LVNR: '".$lvnr."' statt('".$row2->lvnr."')";
									}
									else
									{
										$ausgabe_le="LVNR: '".$lvnr."' statt('".$row2->lvnr."')";
									}
								}
							
								/*if($row2->ext_id!=$ext_id)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Ext_ID: '".$ext_id."' statt('".$row2->ext_id."')";
									}
									else
									{
										$ausgabe_le="Ext_ID: '".$ext_id."' statt('".$row2->ext_id."')";
									}
								}*/
								if(date("d.m.Y", $row2->insertamum)!=date("d.m.Y", $insertamum))
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Insertamum: '".$insertamum."' statt('".$row2->insertamum."')";
									}
									else
									{
										$ausgabe_le="Insertamum: '".$insertamum."' statt('".$row2->insertamum."')";
									}
								}
								if($row2->insertvon!=$insertvon)
								{
									$update=true;
									if(strlen(trim($ausgabe_le))>0)
									{
										$ausgabe_le.=", Insertvon: '".$insertvon."' statt('".$row2->insertvon."')";
									}
									else
									{
										$ausgabe_le="Insertvon: '".$insertvon."' statt('".$row2->insertvon."')";
									}
								}
								$lehreinheit_id=$row2->lehreinheit_id;
								if ($update)
								{
									$qry="UPDATE lehre.tbl_lehreinheit SET ".
									"lehrveranstaltung_id=".myaddslashes($lehrveranstaltung_id).", ".
									"studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz).", ".
									"lehrfach_id=".myaddslashes($lehrfach_id).", ".
									"lehrform_kurzbz=".myaddslashes($lehrform_kurzbz).", ".
									"stundenblockung=".myaddslashes($stundenblockung).", ".
									"wochenrythmus=".myaddslashes($wochenrythmus).", ".
									"start_kw=".myaddslashes($start_kw).", ".
									"raumtyp=".myaddslashes($raumtyp).", ".
									"raumtypalternativ=".myaddslashes($raumtypalternativ).", ".
									//"sprache=".myaddslashes($sprache).", ".
									"lehre=".($lehre?'true':'false').", ".
									"anmerkung=".myaddslashes($anmerkung).", ".
									"unr=".myaddslashes($unr).", ".
									"lvnr=".myaddslashes($lvnr).", ".
									"updateamum=now(), ".
									"updatevon=".myaddslashes($updatevon).", ".
									"insertamum=".myaddslashes($insertamum).", ".
									"insertvon=".myaddslashes($insertvon).", ".
									"ext_id=".myaddslashes($ext_id)." ".
									"WHERE lehreinheit_id=".myaddslashes($row2->lehreinheit_id).
									";";
									//echo $qry."<BR>";
									if(pg_query($conn, $qry))
									{
										//in synclehreinheit eintragen
										$qry3="SELECT * FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='".$lehreinheit_id."' AND lehreinheit_pk='".$ext_id."';";
										if($result3 = pg_query($conn, $qry3))
										{
											if(!(pg_num_rows($result3)>0))
											{ 
												$qry4="INSERT INTO sync.tbl_synclehreinheit (lehreinheit_id, lehreinheit_pk) VALUES (".
													myaddslashes($lehreinheit_id).", ".
													myaddslashes($ext_id).
													");";
												if(!pg_query($conn, $qry4))
												{
													$error=true;
													$error_log.="Eintrag in tbl_synclehreinheit fehlgeschlagen (".$lehreinheit_id."/".$ext_id.").";
												}
											}
										}
										$ausgabe.="Lehreinheit lvnr='".$lvnr." Studiensemester='".$studiensemester_kurzbz."' ver�ndert: ".$ausgabe_le.".\n";
										$anzahl_geaendert++;
										
									}
									else 
									{
										$error=true;
										$error_log.="Fehler beim Speichern in tbl_lehreinheit mit lehrveranstaltung_id='".$lehrveranstaltung_id."', studiensemester_kurzbz='".$studiensemester_kurzbz."', lehrform_kurzbz='".$lehrform_kurzbz."' und lvnr='".$lvnr."'.";
										$anzahl_fehler++;
									}
									$ausgabe_le='';
								}
							}
						}
						else 
						{
							//insert
							$le_iu='i';
							$qry="INSERT INTO lehre.tbl_lehreinheit (lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id, ".
								"lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, ".
								"lehre, anmerkung, unr, lvnr, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
								myaddslashes($lehrveranstaltung_id).", ".
								myaddslashes($studiensemester_kurzbz).", ".
								myaddslashes($lehrfach_id).", ".
								myaddslashes($lehrform_kurzbz).", ".
								myaddslashes($stundenblockung).", ".
								myaddslashes($wochenrythmus).", ".
								myaddslashes($start_kw).", ".
								myaddslashes($raumtyp).", ".
								myaddslashes($raumtypalternativ).", ".
								myaddslashes($sprache).", ".
								($lehre?'true':'false').", ".
								myaddslashes($anmerkung).", ".
								myaddslashes($unr).", ".
								myaddslashes($lvnr).", ".
								'now(),'.
								"'SYNC'".', '.
								myaddslashes($insertamum).','.
								myaddslashes($insertvon).','.
								myaddslashes($ext_id).'); ';
								");";
								//echo $qry."<BR>";					
							if(pg_query($conn, $qry))
							{
								$qryu = "SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') AS id;";
								if($rowu=pg_fetch_object(pg_query($conn,$qryu)))
									$lehreinheit_id=$rowu->id;
								else
								{					
									$error=true;
									$error_log.='Lehreinheit-Sequence konnte nicht ausgelesen werden';
								}
								//in synclehreinheit eintragen
								$qry3="SELECT * FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='".$lehreinheit_id."' AND lehreinheit_pk='".$ext_id."';";
								if($result3 = pg_query($conn, $qry3))
								{
									if(!(pg_num_rows($result3)>0))
									{ 
										$qry4="INSERT INTO sync.tbl_synclehreinheit (lehreinheit_id, lehreinheit_pk) VALUES (".
											myaddslashes($lehreinheit_id).", ".
											myaddslashes($ext_id)." ".
											");";
										if(!pg_query($conn, $qry4))
										{
											$error=true;
											$error_log.="Eintrag in tbl_synclehreinheit fehlgeschlagen (".$lehreinheit_id."/".$ext_id.")!";
										}
									}
								}
								$ausgabe.="Lehreinheit lvnr='".$lvnr." eingef�gt.\n";
								$anzahl_eingefuegt++;
							}
							else 
							{
								$error=true;
								$error_log.="Fehler beim Speichern in tbl_lehreinheit mit lehrveranstaltung_id='".$lehrveranstaltung_id."', studiensemester_kurzbz='".$studiensemester_kurzbz."', lehrform_kurzbz='".$lehrform_kurzbz."' und lvnr='".$lvnr."'!";
								$anzahl_fehler++;
							}
						}
					}
				}
				else 
				{
					//ext_id gefunden
					if(pg_num_rows($result3)>1)
					{
						echo pg_num_rows($result3)."/".$qry."<br>";
						$error_log.=pg_num_rows($result3)."/".$qry."\n>";
						$anzahl_fehler++;
						pg_query($conn,'ROLLBACK;');
						continue;
					}
					//update
					$le_iu='u';
					$update=false;
					if($row3->lehrveranstaltung_id!=$lehrveranstaltung_id)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Lehrveranstaltung ID: '".$lehrveranstaltung_id."' statt('".$row3->lehrveranstaltung_id."')";
						}
						else
						{
							$ausgabe_le="Lehrveranstaltung ID: '".$lehrveranstaltung_id."' statt('".$row3->lehrveranstaltung_id."')";
						}
					}
					if($row3->studiensemester_kurzbz!=$studiensemester_kurzbz)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Studiensemester: '".$studiensemester_kurzbz."' statt('".$row3->studiensemester_kurzbz."')";
						}
						else
						{
							$ausgabe_le="Studiensemester: '".$studiensemester_kurzbz."' statt('".$row3->studiensemester_kurzbz."')";
						}
					}
					if($row3->lehrfach_id!=$lehrfach_id)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Lehrfach ID: '".$lehrfach_id."' statt('".$row3->lehrfach_id."')";
						}
						else
						{
							$ausgabe_le="Lehrfach ID: '".$lehrfach_id."' statt('".$row3->lehrfach_id."')";
						}
					}
					if($row3->lehrform_kurzbz!=$lehrform_kurzbz)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Lehrform: '".$lehrform_kurzbz."' statt('".$row3->lehrform_kurzbz."')";
						}
						else
						{
							$ausgabe_le="Lehrform: '".$lehrform_kurzbz."' statt('".$row3->lehrform_kurzbz."')";
						}
					}
					if($row3->stundenblockung!=$stundenblockung)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Stundenblockung: '".$stundenblockung."' statt('".$row3->stundenblockung."')";
						}
						else
						{
							$ausgabe_le="Stundenblockung: '".$stundenblockung."' statt('".$row3->stundenblockung."')";
						}
					}
					if($row3->wochenrythmus!=$wochenrythmus)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Wochenrythmus: '".$wochenrythmus."' statt('".$row3->wochenrythmus."')";
						}
						else
						{
							$ausgabe_le="Wochenrythmus: '".$wochenrythmus."' statt('".$row3->wochenrythmus."')";
						}
					}
					if($row3->start_kw!=$start_kw)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Start_kw: '".$start_kw."' statt('".$row3->start_kw."')";
						}
						else
						{
							$ausgabe_le="Start_kw: '".$start_kw."' statt('".$row3->start_kw."')";
						}
					}
					if($row3->raumtyp!=$raumtyp)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Raumtyp: '".$raumtyp."' statt('".$row3->raumtyp."')";
						}
						else
						{
							$ausgabe_le="Raumtyp: '".$raumtyp."' statt('".$row3->raumtyp."')";
						}
					}
					if($row3->raumtypalternativ!=$raumtypalternativ)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Raumtyp alterativ: '".$raumtypalternativ."' statt('".$row3->raumtypalternativ."')";
						}
						else
						{
							$ausgabe_le="Raumtyp alternativ: '".$raumtypalternativ."' statt('".$row3->raumtypalternativ."')";
						}
					}
					if($row3->lehre!=($lehre?'t':'f'))
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Lehre: '".($lehre?'true':'false')."' statt('".($row3->lehre?'true':'false')."')";
						}
						else
						{
							$ausgabe_le="Lehre: '".($lehre?'true':'false')."' statt('".($row3->lehre?'true':'false')."')";
						}
					}						
					if($row3->anmerkung!=$anmerkung)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Anmerkung: '".$anmerkung."' statt('".$row3->anmerkung."')";
						}
						else
						{
							$ausgabe_le="Anmerkung: '".$anmerkung."' statt('".$row3->anmerkung."')";
						}
					}
					if($row3->unr!=$unr)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", UNR: '".$unr."' statt('".$row3->unr."')";
						}
						else
						{
							$ausgabe_le="UNR: '".$unr."' statt('".$row3->unr."')";
						}
					}
					if($row3->lvnr!=$lvnr)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", LVNR: '".$lvnr."' statt('".$row3->lvnr."')";
						}
						else
						{
							$ausgabe_le="LVNR: '".$lvnr."' statt('".$row3->lvnr."')";
						}
					}
				
					/*if($row3->ext_id!=$ext_id)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Ext_ID: '".$ext_id."' statt('".$row3->ext_id."')";
						}
						else
						{
							$ausgabe_le="Ext_ID: '".$ext_id."' statt('".$row3->ext_id."')";
						}
					}*/
					if(date("d.m.Y", $row3->insertamum)!=date("d.m.Y", $insertamum))
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Insertamum: '".$insertamum."' statt('".$row3->insertamum."')";
						}
						else
						{
							$ausgabe_le="Insertamum: '".$insertamum."' statt('".$row3->insertamum."')";
						}
					}
					if($row3->insertvon!=$insertvon)
					{
						$update=true;
						if(strlen(trim($ausgabe_le))>0)
						{
							$ausgabe_le.=", Insertvon: '".$insertvon."' statt('".$row3->insertvon."')";
						}
						else
						{
							$ausgabe_le="Insertvon: '".$insertvon."' statt('".$row3->insertvon."')";
						}
					}
					$lehreinheit_id=$row3->lehreinheit_id;
					if ($update)
					{
						$qry="UPDATE lehre.tbl_lehreinheit SET ".
						"lehrveranstaltung_id=".myaddslashes($lehrveranstaltung_id).", ".
						"studiensemester_kurzbz=".myaddslashes($studiensemester_kurzbz).", ".
						"lehrfach_id=".myaddslashes($lehrfach_id).", ".
						"lehrform_kurzbz=".myaddslashes($lehrform_kurzbz).", ".
						"stundenblockung=".myaddslashes($stundenblockung).", ".
						"wochenrythmus=".myaddslashes($wochenrythmus).", ".
						"start_kw=".myaddslashes($start_kw).", ".
						"raumtyp=".myaddslashes($raumtyp).", ".
						"raumtypalternativ=".myaddslashes($raumtypalternativ).", ".
						//"sprache=".myaddslashes($sprache).", ".
						"lehre=".($lehre?'true':'false').", ".
						"anmerkung=".myaddslashes($anmerkung).", ".
						"unr=".myaddslashes($unr).", ".
						"lvnr=".myaddslashes($lvnr).", ".
						"updateamum=now(), ".
						"updatevon=".myaddslashes($updatevon).", ".
						"insertamum=".myaddslashes($insertamum).", ".
						"insertvon=".myaddslashes($insertvon).", ".
						"ext_id=".myaddslashes($ext_id)." ".
						"WHERE lehreinheit_id=".myaddslashes($row3->lehreinheit_id).
						";";
						//echo $qry."<BR>";
						if(pg_query($conn, $qry))
						{
							//in synclehreinheit eintragen
							$qry4="SELECT * FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='".$lehreinheit_id."' AND lehreinheit_pk='".$ext_id."';";
							if($result4 = pg_query($conn, $qry4))
							{
								if(!(pg_num_rows($result4)>0))
								{ 
									$qry4="INSERT INTO sync.tbl_synclehreinheit (lehreinheit_id, lehreinheit_pk) VALUES (".
										myaddslashes($lehreinheit_id).", ".
										myaddslashes($ext_id).
										");";
									if(!pg_query($conn, $qry4))
									{
										$error=true;
										$error_log.="Eintrag in tbl_synclehreinheit fehlgeschlagen (".$lehreinheit_id."/".$ext_id.").";
									}
								}
							}
							$ausgabe.="Lehreinheit lvnr='".$lvnr." Studiensemester='".$studiensemester_kurzbz."' ver�ndert: ".$ausgabe_le.".\n";
							$anzahl_geaendert++;
							
						}
						else 
						{
							$error=true;
							$error_log.="Fehler beim Speichern in tbl_lehreinheit mit lehrveranstaltung_id='".$lehrveranstaltung_id."', studiensemester_kurzbz='".$studiensemester_kurzbz."', lehrform_kurzbz='".$lehrform_kurzbz."' und lvnr='".$lvnr."'.";
							$anzahl_fehler++;
						}
						$ausgabe_le='';
					}
				}
			}
			
			//lehreinheitmitarbeiter synchronisieiren
			
			//$lehreinheit_id
			//$mitarbeiter_uid				=m_uid;
			$lehrfunktion_kurzbz				=$lehrfunktionen[$lehrfunktion];
			$semesterstunden				=round($row->gesamtstunden,2);
			$planstunden					=round($row->gesamtstunden);
			$stundensatz					=round($row->plankostenprolektor,2);
			$faktor					=round($row->faktor,2);
			$anmerkung					='';
			$bismelden					=true;
			//$lm_updateamum				='';
			//$lm_updatevon				='';
			//$lm_insertvon				='';
			$lm_insertamum				=$row->lm_creationdate;
			$lm_ext_id					=$row->mitarbeiter_lehreinheit_pk;
			
			//insertvon ermitteln
			$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
			if($resultcu = pg_query($conn_fas, $qrycu))
			{
				if($rowcu=pg_fetch_object($resultcu))
				{
					$lm_insertvon=$rowcu->name;
				}
			}
			
			$qry="SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".myaddslashes($lehreinheit_id)." AND mitarbeiter_uid=".myaddslashes($m_uid).";";
			if($result3 = pg_query($conn, $qry))
			{
				if($row3=pg_fetch_object($result3))
				{ 
					//update
					$lm_iu='u';
					$update=false;
					if($row3->lehrfunktion_kurzbz!=$lehrfunktion_kurzbz)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Lehrfunktion: '".$lehrfunktion_kurzbz."' statt('".$row3->lehrfunktion_kurzbz."')";
						}
						else
						{
							$ausgabe_lm="Lehrfunktion: '".$lehrfunktion_kurzbz."' statt('".$row3->lehrfunktion_kurzbz."')";
						}
					}
					if($row3->semesterstunden!=$semesterstunden)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Semesterstunden: '".$semesterstunden."' statt('".$row3->semesterstunden."')";
						}
						else
						{
							$ausgabe_lm="Semesterstunden: '".$semesterstunden."' statt('".$row3->semesterstunden."')";
						}
					}
					if($row3->planstunden!=$planstunden)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Planstunden: '".$planstunden."' statt('".$row3->planstunden."')";
						}
						else
						{
							$ausgabe_lm="Planstunden: '".$planstunden."' statt('".$row3->planstunden."')";
						}
					}
					if($row3->stundensatz!=$stundensatz)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Stundensatz: '".$stundensatz."' statt('".$row3->stundensatz."')";
						}
						else
						{
							$ausgabe_lm="Stundensatz: '".$stundensatz."' statt('".$row3->stundensatz."')";
						}
					}
					if($row3->faktor!=$faktor)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Faktor: '".$faktor."' statt('".$row3->faktor."')";
						}
						else
						{
							$ausgabe_lm="Faktor: '".$faktor."' statt('".$row3->faktor."')";
						}
					}
					if($row3->anmerkung!=$anmerkung)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Anmerkung: '".$anmerkung."' statt('".$row3->anmerkung."')";
						}
						else
						{
							$ausgabe_lm="Anmerkung: '".$anmerkung."' statt('".$row3->anmerkung."')";
						}
					}
					if($row3->insertvon!=$lm_insertvon)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Insertvon: '".$lm_insertvon."' statt('".$row3->insertvon."')";
						}
						else
						{
							$ausgabe_lm="Insertvon: '".$lm_insertvon."' statt('".$row3->insertvon."')";
						}
					}
					if(date("d.m.Y", $row3->insertamum)!=date("d.m.Y", $lm_insertamum))
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Insertamum: '".$lm_insertamum."' (statt '".$row3->insertamum."')";
						}
						else
						{
							$ausgabe_lm="Insertamum: '".$lm_insertamum."' (statt '".$row3->insertamum."')";
						}
					}
					if($update)
					{
						$qry="UPDATE lehre.tbl_lehreinheitmitarbeiter SET ".
						"lehrfunktion_kurzbz=".myaddslashes($lehrfunktion_kurzbz).", ".
						"semesterstunden=".myaddslashes($semesterstunden).", ".
						"planstunden=".myaddslashes($planstunden).", ".
						"stundensatz=".myaddslashes($stundensatz).", ".
						"faktor=".myaddslashes($faktor).", ".
						"anmerkung=".myaddslashes($anmerkung).", ".
						"bismelden=".($bismelden?'true':'false').", ".
						"insertvon=".myaddslashes($lm_insertvon).", ".
						"insertamum=".myaddslashes($lm_insertamum).", ".
						"ext_id=".myaddslashes($lm_ext_id)." ".
						"WHERE lehreinheit_id=".myaddslashes($lehreinheit_id)." AND mitarbeiter_uid=".myaddslashes($m_uid).";";
						if(pg_query($conn, $qry))
						{
							$ausgabe.="Lehreinheitmitarbeiter '".$m_uid."' aktualisiert bei Lehreinheit='".$lehreinheit_id."': ".$ausgabe_lm."\n";
							$anzahl_geaendert_lm++;
						}
						else 
						{
							$anzahl_fehler_lm++;
							$error=true;
							$error_log.="Lehreinheitmitarbeiter '".$m_uid."' mit LE '".$lehreiheit_id."' konnte nicht aktualisiert werden!\n";
						}
					}
					$ausgabe_lm='';
				}
				else 
				{
					//insert
					$lm_iu='i';
					$qry="INSERT INTO lehre.tbl_lehreinheitmitarbeiter (lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, semesterstunden, 
						planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon,
						ext_id) VALUES (".
						myaddslashes($lehreinheit_id).", ".
						myaddslashes($m_uid).", ".
						myaddslashes($lehrfunktion_kurzbz).", ".
						myaddslashes($semesterstunden).", ".
						myaddslashes($planstunden).", ".
						myaddslashes($stundensatz).", ".
						myaddslashes($faktor).", ".
						myaddslashes($anmerkung).", ".
						myaddslashes($bismelden).", ".
						"now(), ".
						"'SYNC', ".
						myaddslashes($lm_insertamum).", ".
						myaddslashes($lm_insertvon).", ".
						myaddslashes($lm_ext_id).
						");";
						
					if(pg_query($conn, $qry))
					{
						$anzahl_eingefuegt_lm++;
						$ausgabe.="Lehreinheitmitarbeiter '".$m_uid."' mit Lehreinheit='".$lehreinheit_id."' eingef�gt.\n";
					}
					else 
					{
						$anzahl_fehler_lm++;
						$error=true;
						$error_log.="Lehreinheitmitarbeiter '".$m_uid."' konnte nicht eingef�gt werden!\n";
					}
				}
			}
			//lehreinheitgruppe synchronisieren
			
			//$lehreinheit_id				
			//$studiengang_kz
			//$semester
			//$verband
			//$gruppe
			//$gruppe_kurzbz
			//$lg_updateamum				='';
			//$lg_updatevon				='';
			//$lg_insertvon				='';
			$lg_insertamum				='';
			$lg_ext_id					='';
			
			//insertvon ermitteln
			$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
			if($resultcu = pg_query($conn_fas, $qrycu))
			{
				if($rowcu=pg_fetch_object($resultcu))
				{
					$lg_insertvon=$rowcu->name;
				}
			}			
			$qry="SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".myaddslashes($lehreinheit_id)." AND studiengang_kz=".$studiengang_kz." AND ".($semester!=''?"semester=".myaddslashes($semester):"semester IS NULL")." AND ".($verband!=''?"verband=".myaddslashes($verband):"verband IS NULL")." AND ".($gruppe!=''?"gruppe=".myaddslashes($gruppe):"gruppe IS NULL")." AND ".($gruppe_kurzbz!=''?"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz):"gruppe_kurzbz IS NULL")." ;";
			if($result3 = pg_query($conn, $qry))
			{
				if($row3=pg_fetch_object($result3))
				{ 
					//update
					$lg_iu='u';
					$update=false;
					if($row3->lehreinheit_id!=$lehreinheit_id)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Lehreinheit: '".$lehreinheit_id."' statt('".$row3->lehreinheit_id."')";
						}
						else
						{
							$ausgabe_lm="Lehreinheit: '".$lehreinheit_id."' statt('".$row3->lehreinheit_id."')";
						}
					}
					if($row3->studiengang_kz!=$studiengang_kz)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Studiengang: '".$studiengang_kz."' statt('".$row3->studiengang_kz."')";
						}
						else
						{
							$ausgabe_lm="Studiengang: '".$studiengang_kz."' statt('".$row3->studiengang_kz."')";
						}
					}
					if($row3->semester!=$semester)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Semester: '".$semester."' statt('".$row3->semester."')";
						}
						else
						{
							$ausgabe_lm="Semester: '".$semester."' statt('".$row3->semester."')";
						}
					}
					if($row3->verband!=$verband)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Verband: '".$verband."' statt('".$row3->verband."')";
						}
						else
						{
							$ausgabe_lm="Verband: '".$verband."' statt('".$row3->verband."')";
						}
					}
					if($row3->gruppe!=$gruppe)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Gruppe: '".$gruppe."' statt('".$row3->gruppe."')";
						}
						else
						{
							$ausgabe_lm="Gruppe: '".$gruppe."' statt('".$row3->gruppe."')";
						}
					}
					if($row3->gruppe_kurzbz!=$gruppe_kurzbz)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Spezialgruppe: '".$gruppe_kurzbz."' statt('".$row3->gruppe_kurzbz."')";
						}
						else
						{
							$ausgabe_lm="Spezialgruppe: '".$gruppe_kurzbz."' statt('".$row3->gruppe_kurzbz."')";
						}
					}
					if($row3->insertvon!=$lg_insertvon)
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Insertvon: '".$lg_insertvon."' statt('".$row3->insertvon."')";
						}
						else
						{
							$ausgabe_lm="Insertvon: '".$lg_insertvon."' statt('".$row3->insertvon."')";
						}
					}
					if(date("d.m.Y", $row3->insertamum)!=date("d.m.Y", $lg_insertamum))
					{
						$update=true;
						if(strlen(trim($ausgabe_lm))>0)
						{
							$ausgabe_lm.=", Insertamum: '".$lg_insertamum."' (statt '".$row3->insertamum."')";
						}
						else
						{
							$ausgabe_lm="Insertamum: '".$lg_insertamum."' (statt '".$row3->insertamum."')";
						}
					}
					if($update)
					{
						$qry="UPDATE lehre.tbl_lehreinheitgruppe SET ".
						"lehreinheit_id=".myaddslashes($lehreinheit_id).", ".
						"studiengang_kz=".myaddslashes($studiengang_kz).", ".
						"semester=".myaddslashes($semester).", ".
						"verband=".myaddslashes($verband).", ".
						"gruppe=".myaddslashes($gruppe).", ".
						"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz).", ".
						"insertvon=".myaddslashes($lg_insertvon).", ".
						"insertamum=".myaddslashes($lg_insertamum).", ".
						"ext_id=".myaddslashes($lg_ext_id)." ".
						"WHERE lehreinheit_id=".myaddslashes($lehreinheit_id).";";
						if(pg_query($conn, $qry))
						{
							$anzahl_geaendert_lg++;
							$ausgabe.="Lehreinheitgruppe '".$row3->lehreinheitgruppe_id."' aktualisiert bei Lehreinheit='".$lehreinheit_id."': ".$ausgabe_lm."\n";
						}
						else 
						{
							$anzahl_fehler_lg++;
							$error=true;
							$error_log.="Lehreinheitgruppe '".$row3->lehreinheitgruppe_id."' mit LE '".$lehreiheit_id."' konnte nicht aktualisiert werden!\n";
						}
					}
					$ausgabe_lm='';
				}
				else 
				{
					//insert
					$lg_iu='i';
					$qry="INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, verband, gruppe,
						gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
						myaddslashes($lehreinheit_id).", ".
						myaddslashes($studiengang_kz).", ".
						myaddslashes($semester).", ".
						myaddslashes($verband).", ".
						myaddslashes($gruppe).", ".
						myaddslashes($gruppe_kurzbz).", ".
						"now(), ".
						"'SYNC', ".
						myaddslashes($lg_insertamum).", ".
						myaddslashes($lg_insertvon).", ".
						myaddslashes($lg_ext_id)." ".
						");";
					if(pg_query($conn, $qry))
					{
						$anzahl_eingefuegt_lg++;
						$ausgabe.="Lehreinheitgruppe mit Lehreinheit='".$lehreinheit_id."' und Studiengang '".$studiengang_kz."'eingef�gt.\n";
					}
					else 
					{
						$anzahl_fehler_lg++;
						echo $qry."<br>";
						$error=true;
						$error_log.="Lehreinheitgruppe mit LE '".$lehreinheit_id."' in Studiengang '".$studiengang_kz."' konnte nicht eingef�gt werden!\n";
					}
				}
			}
			if(!$error)
			{
				$ausgabe_all.=$ausgabe;
				$ausgabe='';
				pg_query($conn,'COMMIT;');
				
			}
			else 
			{
				if($le_iu=='i')
				{
					$anzahl_eingefuegt--;
				}
				else 
				{
					$anzahl_geaendert--;
				}
				if($lm_iu=='i')
				{
					$anzahl_eingefuegt_lm--;
				}
				else 
				{
					$anzahl_geaendert_lm--;
				}
				$ausgabe='';
				pg_query($conn,'ROLLBACK;');
			}
		}
		
		else 
		{
			//partizipierend
			//nur in synclehreinheit eintragen
			pg_query($conn,'BEGIN;');
			$anzahl_part_gesamt++;
			$qry5="SELECT * FROM sync.tbl_synclehreinheit WHERE lehreinheit_pk='".$row->lehreinheit_fk."';";
			if($result5 = pg_query($conn, $qry5))
			{
				if($row5=pg_fetch_object($result5))
				{ 
					$qry3="SELECT * FROM sync.tbl_synclehreinheit WHERE lehreinheit_id='".$row5->lehreinheit_id."' AND lehreinheit_pk='".$ext_id."';";
					if($result3 = pg_query($conn, $qry3))
					{
						if(!(pg_num_rows($result3)>0))
						{
							$qry4="INSERT INTO sync.tbl_synclehreinheit (lehreinheit_id, lehreinheit_pk) VALUES (".
								myaddslashes($row5->lehreinheit_id).", ".
								myaddslashes($ext_id)." ".
								");";
							if(!pg_query($conn, $qry4))
							{
								$error=true;
								$error_log.="Eintrag in tbl_synclehreinheit fehlgeschlagen (".$row5->lehreinheit_id."/".$ext_id.")!";
							}
							else 
							{
								$ausgabe.="Lehreinheit lvnr='".$lvnr." partizipierend eingef�gt.\n";
								$anzahl_part++;
							}
						}
					}
					
					//lehreinheitgruppe synchronisieren
				
					//lehrveranstaltung ermitteln
					$qry="SELECT lva_vilesci FROM sync.tbl_synclehrveranstaltung WHERE lva_fas='".$row->lehrveranstaltung_fk."';";
					if($results = pg_query($conn, $qry))
					{
						if($rows=pg_fetch_object($results))
						{ 
							$lva=$rows->lva_vilesci;	
						}
						else 
						{
							$error=true;
							$error_log.="LVA_FAS=".$row->lehrveranstaltung_fk." in Tabelle tbl_synclehrveranstaltung nicht gefunden!\n";
						}
					}
					if($error)
					{
						$anzahl_fehler++;
						pg_query($conn,'ROLLBACK;');
						continue;
					}
					//studiengang ermitteln
					$qry="SELECT lehrveranstaltung_id, studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='".$lva."';";
					if($result1 = pg_query($conn, $qry))
					{
						if($row1=pg_fetch_object($result1))
						{ 
							$lehrveranstaltung_id=$row1->lehrveranstaltung_id;
							$studiengang_kz=$row1->studiengang_kz;
							$semester=$row1->semester;
						}
						else 
						{
							$error=true;
							$error_log.="Lehrveranstaltung mit ext_id='".$row->lehrveranstaltung_fk."' nicht gefunden.\n";
						}
					}
					if($error)
					{
						$anzahl_fehler++;
						pg_query($conn,'ROLLBACK;');
						continue;
					}
					//gruppe ermitteln
					//spezialgruppe?
					$qry="SELECT * FROM sync.tbl_syncgruppe WHERE fas_gruppe='".$gruppe_fk."';";
					if($result2 = pg_query($conn, $qry))
					{
						if($row2=pg_fetch_object($result2))
						{ 
							$gruppe_kurzbz=$row2->vilesci_gruppe;
							$semeter=NULL;
							$verband=NULL;
							$gruppe=NULL;
						}
						else 
						{
							//verbandsgruppe
							$gruppe_kurzbz=NULL;
							$qry2="SELECT * FROM gruppe WHERE gruppe_pk='".$gruppe_fk."';";
							if($result2 = pg_query($conn_fas, $qry2))
							{
								if($row2=pg_fetch_object($result2))
								{ 
									$typ=$row2->typ;
									if($row2->typ=='1')
									{
										$semester=$row2->name;
										$verband=NULL;
										$gruppe=NULL;
										
									}
									elseif ($row2->typ=='2')
									{
										$verband=$row2->name;
										$gruppe=NULL;
										
										$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
										if($result3 = pg_query($conn_fas, $qry3))
										{
											if($row3=pg_fetch_object($result3))
											{ 
												$semester=$row3->name;	
											}
											else 
											{
												$error_log="Gruppe mit gruppe_pk=".$row2->obergruppe_fk."nicht gefunden (1).\n";
												$error=true;
											}
										}
										else 
										{
											$error_log="Fehler beim Zugriff auf Tabelle gruppe (1).\n";
											$error=true;
										}	
									}
									elseif ($row2->typ=='3')
									{
										$gruppe=$row2->name;
										
										$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
										if($result3 = pg_query($conn_fas, $qry3))
										{
											if($row3=pg_fetch_object($result3))
											{ 
												$verband=$row3->name;	
												$qry4="SELECT * FROM gruppe WHERE gruppe_pk='".$row3->obergruppe_fk."';";
												if($result4 = pg_query($conn_fas, $qry4))
												{
													if($row4=pg_fetch_object($result4))
													{ 
														$semester=$row4->name;	
													}
													else 
													{
														$error_log="Gruppe mit gruppe_pk=".$row2->obergruppe_fk."nicht gefunden (3).\n";
														$error=true;
													}
												}
												else 
												{
													$error_log="Fehler beim Zugriff auf Tabelle gruppe (3).\n";
													$error=true;
												}
											}
											else 
											{
												$error_log="Gruppe mit gruppe_pk=".$row2->obergruppe_fk."nicht gefunden (2).\n";
												$error=true;
											}
										}
										else 
										{
											$error_log="Fehler beim Zugriff auf Tabelle gruppe (2).\n";
											$error=true;
										}
									}
									elseif($row2->typ=='10' && strlen($row2->name)==1)
									{
										if($row2->obergruppe_fk!=0)
										{
											$qry3="SELECT * FROM gruppe WHERE gruppe_pk='".$row2->obergruppe_fk."';";
											if($result3 = pg_query($conn_fas, $qry3))
											{
												if($row3=pg_fetch_object($result3))
												{ 
													if($row3->obergruppe_fk!=0)
													{
														$qry4="SELECT * FROM gruppe WHERE gruppe_pk='".$row3->obergruppe_fk."';";
														if($result4 = pg_query($conn_fas, $qry4))
														{
															if($row4=pg_fetch_object($result4))
															{ 
																$semester=$row4->name;
																$verband=$row3->name;
																$gruppe=$row2->name;	
															}
														}
													}
													else 
													{
														$semester=$row3->name;
														$verband=$row2->name;
														$gruppe=NULL;
													}
												}
											}
										}
										else 
										{
											$semester=$row2->name;
											$verband=NULL;
											$gruppe=NULL;
										}
									}
									else
									{
										//$error_log="Gruppentyp nicht 1, 2, 3 oder 10.\n";
										//$error=true;
										pg_query($conn,'ROLLBACK;');
										continue;
									}
								}
								else
								{
									$error_log="Eintragung in Tabelle gruppe mit gruppe_pk='".$row->gruppe_fk."' nicht gefunden.\n";
									$error=true;
								}
							}
							else 
							{
								$error_log.="Fehler beim Zugriff auf Tabelle gruppe (1).\n";
								$error=true;
							}
						}
					}
					$lehreinheit_id				=$row5->lehreinheit_id;
					//$studiengang_kz				='';
					//$semester					='';
					//$verband					='';
					//$gruppe					='';
					//$gruppe_kurzbz				='';
					//$lg_updateamum				='';
					//$lg_updatevon				='';
					//$lg_insertvon				='';
					$lg_insertamum				='';
					$lg_ext_id					='';
					
					//insertvon ermitteln
					$qrycu="SELECT name FROM public.benutzer WHERE benutzer_pk='".$row->creationuser."';";
					if($resultcu = pg_query($conn_fas, $qrycu))
					{
						if($rowcu=pg_fetch_object($resultcu))
						{
							$lg_insertvon=$rowcu->name;
						}
					}
					$qry="SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".myaddslashes($lehreinheit_id)." AND studiengang_kz=".$studiengang_kz." AND ".($semester!=''?"semester=".myaddslashes($semester):"semester IS NULL")." AND ".($verband!=''?"verband=".myaddslashes($verband):"verband IS NULL")." AND ".($gruppe!=''?"gruppe=".myaddslashes($gruppe):"gruppe IS NULL")." AND ".($gruppe_kurzbz!=''?"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz):"gruppe_kurzbz IS NULL")." ;";
					if($result3 = pg_query($conn, $qry))
					{
						if($row3=pg_fetch_object($result3))
						{ 
							//update
							$lg_iu='u';
							$update=false;
							if($row3->lehreinheit_id!=$lehreinheit_id)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Lehreinheit: '".$lehreinheit_id."' statt('".$row3->lehreinheit_id."')";
								}
								else
								{
									$ausgabe_lm="Lehreinheit: '".$lehreinheit_id."' statt('".$row3->lehreinheit_id."')";
								}
							}
							if($row3->studiengang_kz!=$studiengang_kz)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Studiengang: '".$studiengang_kz."' statt('".$row3->studiengang_kz."')";
								}
								else
								{
									$ausgabe_lm="Studiengang: '".$studiengang_kz."' statt('".$row3->studiengang_kz."')";
								}
							}
							if($row3->semester!=$semester)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Semester: '".$semester."' statt('".$row3->semester."')";
								}
								else
								{
									$ausgabe_lm="Semester: '".$semester."' statt('".$row3->semester."')";
								}
							}
							if($row3->verband!=$verband)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Verband: '".$verband."' statt('".$row3->verband."')";
								}
								else
								{
									$ausgabe_lm="Verband: '".$verband."' statt('".$row3->verband."')";
								}
							}
							if($row3->gruppe!=$gruppe)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Gruppe: '".$gruppe."' statt('".$row3->gruppe."')";
								}
								else
								{
									$ausgabe_lm="Gruppe: '".$gruppe."' statt('".$row3->gruppe."')";
								}
							}
							if($row3->gruppe_kurzbz!=$gruppe_kurzbz)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Spezialgruppe: '".$gruppe_kurzbz."' statt('".$row3->gruppe_kurzbz."')";
								}
								else
								{
									$ausgabe_lm="Spezialgruppe: '".$gruppe_kurzbz."' statt('".$row3->gruppe_kurzbz."')";
								}
							}
							if($row3->insertvon!=$lg_insertvon)
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Insertvon: '".$lg_insertvon."' statt('".$row3->insertvon."')";
								}
								else
								{
									$ausgabe_lm="Insertvon: '".$lg_insertvon."' statt('".$row3->insertvon."')";
								}
							}
							if(date("d.m.Y", $row3->insertamum)!=date("d.m.Y", $lg_insertamum))
							{
								$update=true;
								if(strlen(trim($ausgabe_lm))>0)
								{
									$ausgabe_lm.=", Insertamum: '".$lg_insertamum."' (statt '".$row3->insertamum."')";
								}
								else
								{
									$ausgabe_lm="Insertamum: '".$lg_insertamum."' (statt '".$row3->insertamum."')";
								}
							}
							if($update)
							{
								$qry="UPDATE lehre.tbl_lehreinheitgruppe SET ".
								"lehreinheit_id=".myaddslashes($lehreinheit_id).", ".
								"studiengang_kz=".myaddslashes($studiengang_kz).", ".
								"semester=".myaddslashes($semester).", ".
								"verband=".myaddslashes($verband).", ".
								"gruppe=".myaddslashes($gruppe).", ".
								"gruppe_kurzbz=".myaddslashes($gruppe_kurzbz).", ".
								"insertvon=".myaddslashes($lg_insertvon).", ".
								"insertamum=".myaddslashes($lg_insertamum).", ".
								"ext_id=".myaddslashes($lg_ext_id)." ".
								"WHERE lehreinheit_id=".myaddslashes($lehreinheit_id).";";
								if(pg_query($conn, $qry))
								{
									$anzahl_geaendert_lg++;
									$ausgabe.="Lehreinheitgruppe '".$row3->lehreinheitgruppe_id."' aktualisiert bei Lehreinheit='".$lehreinheit_id."': ".$ausgabe_lm."\n";
								}
								else 
								{
									$anzahl_fehler_lg++;
									$error=true;
									$error_log.="Lehreinheitgruppe '".$row3->lehreinheitgruppe_id."' mit LE '".$lehreiheit_id."' konnte nicht aktualisiert werden!\n";
								}
							}
							$ausgabe_lm='';
						}
						else 
						{
							//insert
							$lg_iu='i';
							$qry="INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheit_id, studiengang_kz, semester, verband, gruppe,
								gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
								myaddslashes($lehreinheit_id).", ".
								myaddslashes($studiengang_kz).", ".
								myaddslashes($semester).", ".
								myaddslashes($verband).", ".
								myaddslashes($gruppe).", ".
								myaddslashes($gruppe_kurzbz).", ".
								"now(), ".
								"'SYNC', ".
								myaddslashes($lg_insertamum).", ".
								myaddslashes($lg_insertvon).", ".
								myaddslashes($lg_ext_id)." ".
								");";
							if(pg_query($conn, $qry))
							{
								$anzahl_eingefuegt_lg++;
								$ausgabe.="Lehreinheitgruppe '".$row3->lehreinheitgruppe_id."' mit Lehreinheit='".$lehreinheit_id."' eingef�gt.\n";
							}
							else 
							{
								$anzahl_fehler_lg++;
								$error=true;
								$error_log.="Lehreinheitgruppe '".$row3->lehreinheitgruppe_id."' mit LE '".$lehreiheit_id."' konnte nicht eingef�gt werden!\n";
							}
						}
					}
					
				}
			}
			if(!$error)
			{
				$ausgabe_all.=$ausgabe;
				$ausgabe='';
				pg_query($conn,'COMMIT;');
			}
			else 
			{
				$ausgabe='';
				pg_query($conn,'ROLLBACK;');
			}
		}
	}
	
	$error_log="Sync Lehreinheiten\n-----------------------\n\n".$error_log."\n";
	echo "Lehreinheitensynchro Ende: ".date("d.m.Y H:i:s")." von ".$_SERVER['HTTP_HOST']."\<br><br>";
	echo "Gesamt: ".$anzahl_quelle." / Eingef�gt: ".$anzahl_eingefuegt." / Ge�ndert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."<br>";
	echo "Partizipierende LEs Gesamt: ".$anzahl_part_gesamt." / Eingef�gt: ".$anzahl_part."<br><br>";
	echo "Lehreinheit-Mitarbeiter: Eingef�gt:".$anzahl_eingefuegt_lm." / Ge�ndert:".$anzahl_geaendert_lm." / Fehler:".$anzahl_fehler_lm."<br>";
	echo "Lehreinheit-Gruppen: Eingef�gt:".$anzahl_eingefuegt_lg." / Ge�ndert:".$anzahl_geaendert_lg." / Fehler:".$anzahl_fehler_lg."<br>";
	echo "Lehrf�cher eingef�gt: ".$anzahl_lehrfaecher.".<br><br>";
	echo nl2br($error_log. "\n------------------------------------------------------------------------\n".$ausgabe_all);
	
	mail($adress, 'SYNC-Fehler Lehreinheiten  von '.$_SERVER['HTTP_HOST'], $error_log, "From: vilesci@technikum-wien.at");
	mail($adress, 'SYNC Lehreinheiten von '.$_SERVER['HTTP_HOST'], "Sync Lehreinheiten\n-----------------------\n\nGesamt: ".$anzahl_quelle." / Eingef�gt: ".$anzahl_eingefuegt." / Ge�ndert: ".$anzahl_geaendert." / Fehler: ".$anzahl_fehler."\nPartizipierende LEs Gesamt: ".$anzahl_part_gesamt." / Eingef�gt: ".$anzahl_part."\n\nLehreinheit-Mitarbeiter: Eingef�gt:".$anzahl_eingefuegt_lm." / Ge�ndert:".$anzahl_geaendert_lm." / Fehler:".$anzahl_fehler_lm."\nLehreinheit-Gruppen: Eingef�gt:".$anzahl_eingefuegt_lg." / Ge�ndert:".$anzahl_geaendert_lg." / Fehler:".$anzahl_fehler_lg."\nLehrf�cher eingef�gt: ".$anzahl_lehrfaecher."\n\n".$ausgabe_all, "From: vilesci@technikum-wien.at");
	
}
?>
</body>
</html>