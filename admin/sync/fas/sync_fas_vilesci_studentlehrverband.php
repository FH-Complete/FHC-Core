<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Studentlehrverbanddatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');


$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$error_log_fas='';
$text = '';
$anzahl_quelle=0;
$anzahl_quelle_student=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$anzahl_quelle2=0;
$anzahl_eingefuegt2=0;
$anzahl_update2=0;
$anzahl_fehler2=0;
$ausgabe='';
$ausgabe_slv='';
$ausgabe_all='';
$update=false;

function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Studentlehrverband</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry="SELECT * FROM person, student WHERE person_pk=person_fk AND uid IS NOT null AND uid<>'' AND perskz IS NOT null AND perskz<>'' ORDER BY Familienname, Vorname ;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Studentlehrverband Sync\n-------------------------\n");
	$anzahl_quelle_student=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$student_uid				=$row->uid;
		$studiensemester_kurzbz		="";
		$studiengang_kz			="";
		$semester				="";
		$verband				="";
		$gruppe				="";
		$updateamum			="";
		$updatevon				="SYNC";
		$insertamum				="";
		$insertvon				="SYNC";
		$ext_id				="";
		

		$update=false;
		$qry="SELECT * FROM student_gruppe WHERE student_fk='".$row->student_pk."';";
		if($result1 = pg_query($conn_fas, $qry))
		{
			While($row1=pg_fetch_object($result1))
			{ 
				//semester, verband, gruppe
				$error=false;
				$error_log="";
				$anzahl_quelle++;
				$insertamum=$row1->creationdate;
				$insertvon=$row1->creationuser;
				$qry2="SELECT * FROM gruppe WHERE gruppe_pk='".$row1->gruppe_fk."';";
				if($result2 = pg_query($conn_fas, $qry2))
				{
					if($row2=pg_fetch_object($result2))
					{ 
						if($row2->typ=='1')
						{
							$semester=$row2->name;
							$verband=' ';
							$gruppe=' ';
							$ext_id=$row1->gruppe_fk;
						}
						elseif ($row2->typ=='2')
						{
							$verband=$row2->name;
							$gruppe=' ';
							$ext_id=$row1->gruppe_fk;
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
							$ext_id=$row1->gruppe_fk;
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
												if($row4->obergruppe_fk!=0)
												{		
													$qry5="SELECT * FROM gruppe WHERE gruppe_pk='".$row4->obergruppe_fk."';";
													if($result5 = pg_query($conn_fas, $qry5))
													{
														if($row5=pg_fetch_object($result5))
														{ 
															$semester=$row5->name;
															$verband=$row4->name;
															$gruppe=$row3->name;	
														}
													}		
												}
												else 
												{
													$semester=$row4->name;
													$verband=$row3->name;
													$gruppe=' ';	
												}
											}
										}
									}
									else 
									{
										$semester=$row3->name;
										$verband=' ';
										$gruppe=' ';
									}
								}
							}
						}
						else
						{
							$error_log="Gruppentyp nicht 1, 2 oder 3.\n";
							$error=true;
						}
					}
					else
					{
						$error_log="Eintragung in Tabelle gruppe mit gruppe_pk='".$row1->gruppe_fk."' nicht gefunden.\n";
						$error=true;
					}
					if($semester==null || $semester=='') $semester=' ';
					if($verband==null || $verband=='') $verband=' ';
					if($gruppe==null || $gruppe=='') $gruppe=' ';
					//studiengang_kz
					$qry="SELECT * FROM studiengang WHERE studiengang_pk='".$row2->studiengang_fk."';";
					if($result3 = pg_query($conn_fas, $qry))
					{
						if($row3=pg_fetch_object($result3))
						{
							$studiengang_kz=$row3->kennzahl;
						}
						else 
						{
							$error_log.="Studiengang mit studiengang_pk='".$row2->studiengang_fk."' nicht gefunden.";
							$error=true;
						}
					}
					//studiensemester_kurzbz
					$qry="SELECT * FROM public.tbl_studiensemester WHERE ext_id='".$row2->studiensemester_fk."';";
					if($result3 = pg_query($conn, $qry))
					{
						if($row3=pg_fetch_object($result3))
						{
							$studiensemester_kurzbz=$row3->studiensemester_kurzbz;
						}
						else 
						{
							$error_log.="Studiensemester '".$row2->studiensemester_fk."' nicht gefunden.";
							$error=true;
						}
					}
				}
				else 
				{
					$error_log="Fehler beim Zugriff auf Tabelle gruppe (1).\n";
					$error=true;
				}
				if(!($semester==' ' && $verband==' ' && $gruppe==' ') && !$error)
				{
					$qry="SELECT student_uid FROM public.tbl_student WHERE matrikelnr='".$row->perskz."';";
					if($resultuid = pg_query($conn, $qry))
					{
						if($rowuid=pg_fetch_object($resultuid))
						{
							$student_uid=$rowuid->student_uid;
						}
						else 
						{
							$error=true;
							$error_log.="Student mit matrikelnr=".$row->perskz." in tbl_student nicht gefunden";
						}
					}
					else 
					{
						$error=true;
						$error_log.="Fehler beim Zugriff auf tbl_student.\n";
					}
					if(!$error)
					{
						$qry="SELECT * FROM public.tbl_studentlehrverband WHERE student_uid='".$student_uid."' AND studiensemester_kurzbz='".$studiensemester_kurzbz."';";
						if($result4 = pg_query($conn, $qry))
						{
							if(pg_num_rows($result4)>0)
							{
								if($row4=pg_fetch_object($result4))
								{
									//update
									$updates=false;	
									if(trim($row4->student_uid)!=trim($student_uid))
									{
										$updates=true;
										if(strlen(trim($ausgabe_slv))>0)
										{
											$ausgabe_slv.=", Student_UID: '".$student_uid."' (statt '".trim($row4->student_uid)."')";
										}
										else
										{
											$ausgabe_slv="Student_UID: '".$student_uid."' (statt '".trim($row4->student_uid)."')";
										}
									}
									if(trim($row4->studiensemester_kurzbz)!=trim($studiensemester_kurzbz))
									{
										$updates=true;
										if(strlen(trim($ausgabe_slv))>0)
										{
											$ausgabe_slv.=", Studiensemester_kurzbz: '".$studiensemester_kurzbz."' (statt '".trim($row4->studiensemester_kurzbz)."')";
										}
										else
										{
											$ausgabe_slv="Studiensemester_kurzbz: '".$studiensemester_kurzbz."' (statt '".trim($row4->studiensemester_kurzbz)."')";
										}
									}
									if(trim($row4->studiengang_kz)!=trim($studiengang_kz))
									{
										$updates=true;
										if(strlen(trim($ausgabe_slv))>0)
										{
											$ausgabe_slv.=", Studiengang_kz: '".$studiengang_kz."' (statt '".trim($row4->studiengang_kz)."')";
										}
										else
										{
											$ausgabe_slv="Studiengang_kz: '".$studiengang_kz."' (statt '".trim($row4->studiengang_kz)."')";
										}
									}
									if(trim($row4->semester)!=trim($semester))
									{
										$updates=true;
										if(strlen(trim($ausgabe_slv))>0)
										{
											$ausgabe_slv.=", Semester: '".$semester."' (statt '".trim($row4->semester)."')";
										}
										else
										{
											$ausgabe_slv="Semester: '".$semester."' (statt '".trim($row4->semester)."')";
										}
									}	
									if(trim($row4->verband)!=trim($verband))
									{
										$updates=true;
										if(strlen(trim($ausgabe_slv))>0)
										{
											$ausgabe_slv.=", verband: '".$verband."' (statt '".trim($row4->verband)."')";
										}
										else
										{
											$ausgabe_slv="verband: '".$verband."' (statt '".trim($row4->verband)."')";
										}
									}
									if(trim($row4->gruppe)!=trim($gruppe))
									{
										$updates=true;
										if(strlen(trim($ausgabe_slv))>0)
										{
											$ausgabe_slv.=", gruppe: '".$gruppe."' (statt '".trim($row4->gruppe)."')";
										}
										else
										{
											$ausgabe_slv="gruppe: '".$gruppe."' (statt '".trim($row4->gruppe)."')";
										}
									}
									if($updates)
									{
										$qry = 'UPDATE public.tbl_studentlehrverband SET'.
										       ' student_uid='.myaddslashes($student_uid).','.
										       ' studiensemester_kurzbz='.myaddslashes($studiensemester_kurzbz).','.
										       ' studiengang_kz='.myaddslashes($studiengang_kz).','.
										       ' semester='.myaddslashes($semester).','.
										       ' verband='.myaddslashes($verband).','.
										       ' gruppe='.myaddslashes($gruppe).','.
								        		       " updateamum=now()".','.
								        		       " updatevon=".myaddslashes($updatevon).','.
										       ' ext_id='.myaddslashes($ext_id).
										       " WHERE student_uid='".$student_uid."' AND studiensemester_kurzbz='".$studiensemester_kurzbz."';";
										       $ausgabe="SLV-Änderungen bei Student ".$student_uid.", ".$row->familienname.", (".$row->perskz."): ".$ausgabe_slv.".\n";
										       $ausgabe_slv='';
									}
									$anzahl_update++;
								}
							}
							else 
							{
								//insert
								$qry = "INSERT INTO public.tbl_studentlehrverband (student_uid, studiensemester_kurzbz, studiengang_kz, semester, verband, gruppe, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES("
									.myaddslashes($student_uid).", ".
									myaddslashes($studiensemester_kurzbz).", ".
									myaddslashes($studiengang_kz).", ".
								        myaddslashes($semester).", ".
								        myaddslashes($verband).", ".
								        myaddslashes($gruppe).",
								        now(), 
								        'SYNC', 
								        now(), 
								        'SYNC',".
								        myaddslashes($ext_id).");";
								        $ausgabe="SLV für Student mit UID/Name/Perskz ".$student_uid.", ".$row->familienname.", (".$row->perskz.") eingefügt: Studiengang '".$studiengang_kz."', Sem.'".$studiensemester_kurzbz."' gruppe_pk='".$row1->gruppe_fk."' mit Semester/Verband/Gruppe: '".$semester."'/'".$verband."'/'".$gruppe."'.\n";
								        $anzahl_eingefuegt++;
							}
						}
						if(!@pg_query($conn,$qry))
						{		
							$error_log.= "*****\nFehler beim Speichern des Studentlehrverband-Datensatzes: ".$row->familienname."\n".$qry."\n".pg_errormessage($conn)."\n*****\n";
							$error=true;
						}
						else 
						{
							$ausgabe_all.=$ausgabe;
						}
					}
					else 
					{
						$anzahl_fehler++;
						$error_log_fas.="### Student ".$student_uid.", ".$row->familienname.", (".$row->perskz.") gruppe_pk='".$row1->gruppe_fk."' : ".$error_log;			
					}
				}
				else 
				{
					$anzahl_fehler++;
					$error_log_fas.="### Student mit UID/Name/Perskz ".$student_uid.", ".$row->familienname.", (".$row->perskz.") gruppe_pk='".$row1->gruppe_fk."' : ".$error_log;					
				}
			}
		}
		else 
		{
			$error_log_fas.="Fehler beim Zugriff auf Tabelle student_gruppe.\n";
			$error=true;
		}
	}
}

//echo nl2br($text);
echo nl2br("\nStudentlehrverband\nStudenten: $anzahl_quelle_student / Gruppen: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler\n\n");
echo nl2br($error_log_fas);
echo nl2br ($ausgabe_all);
$ausgabe="\nStudentlehrverband\nStudenten: $anzahl_quelle_student / Gruppen: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler."
."\n\n".$ausgabe_all;

if(strlen(trim($error_log_fas))>0)
{
	mail($adress, 'SYNC-Fehler Studentlehrverband von '.$_SERVER['HTTP_HOST'], $error_log_fas,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Studentlehrverband von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");

?>
</body>
</html>