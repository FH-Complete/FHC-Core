<?php
	require_once('../../vilesci/config.inc.php');
	$adress='fas_sync@technikum-wien.at';

	function clean_string($string)
 	{
	 	$trans = array("ä" => "ae",
	 				   "ö" => "oe",
	 				   "ü" => "ue",
	 				   "Ä" => "ae",
	 				   "Ö" => "oe",
	 				   "Ü" => "ue",
	 				   "á" => "a",
	 				   "à" => "a",
	 				   "é" => "e",
	 				   "è" => "e",
	 				   "ó" => "o",
	 				   "ò" => "o",
	 				   "í" => "i",
	 				   "ì" => "i",
	 				   "ú" => "u",
	 				   "ù" => "u",
	 				   "ß" => "ss",
	 				   "´" => "",
	 				   "`" => "",
	 				   );
		$string = strtr($string, $trans);
    	return $string;
 	}

	//mail($adress,"FAS Synchro mit VileSci (Studenten)","BEGIN OF SYNCHRONISATION","From: vilesci@technikum-wien.at");
	$conn=pg_connect(CONN_STRING);
	$conn_fas=pg_connect(CONN_STRING_FAS);

 	// Anzahl der Studenten in VileSci
	$sql_query="SELECT count(*) AS anz FROM public.tbl_student";
	//echo $sql_query."<br>";
	$result=pg_query($conn, $sql_query);
	$row=pg_fetch_object($result);
	$vilesci_anz_std = $row->anz;

	// Start Studenten Synchro
	$sql_query="SELECT DISTINCT * FROM fas_view_student_vilesci WHERE semester >0 AND semester <9 AND";
	$sql_query.=" verband IS NOT NULL AND uid IS NOT NULL AND uid NOT LIKE ''";
	//echo $sql_query."<br>";
	flush();
	$result=pg_query($conn_fas, $sql_query);
	$num_rows=pg_num_rows($result);
	$text="Dies ist eine automatische eMail!\n\n";
	$text.="Es wurde eine Synchronisation mit FAS durchgeführt.\n";
	$text.="Anzahl der Studenten vom FAS-Import: $num_rows \n";
	$text.="Anzahl der Studenten in Portal: $vilesci_anz_std \n\n";
	echo $text.'<BR>';
	flush();
	$plausi_error=0;
	$update_error=0;
	$insert_error=0;
	$double_error=0;
	$anz_update=0;
	$anz_insert=0;
	for ($i=0;$row=pg_fetch_object($result);$i++)
	{

		$row->gebort=substr($row->gebort,0,30);
		$row->titel=substr($row->titel,0,15);
		$uid=str_replace(' ','',$row->uid);
		// Plausibilitaetscheck
		if ($row->gruppe==null)
			$row->gruppe='1';
		if ($row->verband>='A' && $row->verband<='D' && $row->semester<=8 && $row->gruppe>'0' && $row->gruppe<='2')
		{
			// SQL vorbereiten (jeden Studenten vom FAS im VileSci suchen
			$sql_query="SELECT tbl_person.person_id, uid,titelpre,vorname,nachname,gebdatum,gebort,";
			$sql_query.="trim(both ' ' from matrikelnr) AS matrikelnr,";
			$sql_query.=" studiengang_kz,semester,verband,gruppe";
			$sql_query.=" FROM public.tbl_person, public.tbl_benutzer, public.tbl_student WHERE
						  tbl_person.person_id=tbl_benutzer.person_id AND tbl_benutzer.uid=tbl_student.student_uid
			              AND tbl_benutzer.uid='$uid'";
			// echo $sql_query;
			$res_std=pg_query($conn, $sql_query);
			$num_rows_std=pg_num_rows($res_std);

			// neue Studenten
			if ($num_rows_std==0)
			{

				$text.="Der Student $row->vornamen $row->nachname ($row->uid) wird neu angelegt.\n";

				pg_query($conn, "BEGIN");

				// person
				if(!$len=strpos($row->vornamen,' '))
				{
					$vorname=$row->vornamen;
					$vornamen='';
				}
				else
				{
					$vorname=substr($row->vornamen,0,$len);
					$vornamen=substr($row->vornamen,$len+1,strlen($row->vornamen));
				}

				// tbl_person
				$qry_sync = "SELECT * FROM sync.tbl_syncperson WHERE person_fas='$row->person_pk'";
				if($result_sync = pg_query($conn, $qry_sync))
				{
					if(pg_num_rows($result_sync)>0)
					{
						//Personen Datensatz ist bereits vorhanden
						$row_sync = pg_fetch_object($result_sync);
						$person_id=$row_sync->person_portal;
					}
					else
					{
						//PesonenDatensatz noch nicht vorhanden
						$sql_query="INSERT INTO public.tbl_person(titelpre,vorname,vornamen, nachname, gebdatum, gebort, aktiv) ".
								 "VALUES('$row->titel','$vorname','$vornamen','$row->nachname','$row->gebdatum','$row->gebort', true)";
						//echo $sql_query.'<BR>';
						flush();

						if(!$res_insert=pg_query($conn, $sql_query))
						{
							$text.=$sql_query;
							$text.="\nFehler: ".pg_errormessage($conn)."\n";
							$insert_error++;
							pg_query($conn, 'ROLLBACK');
						}
						else
						{
							$qry = "SELECT currval('tbl_person_person_id_seq') AS id;";

							if(!$row_seq=pg_fetch_object(pg_query($conn,$qry)))
							{
								pg_query($conn, 'ROLLBACK');
								$text = 'Sequence konnte nicht ausgelesen werden\n';
								$insert_error++;
							}
							else
							{
								pg_query($conn, "INSERT INTO sync.tbl_syncperson(person_fas, person_portal) VALUES($row->person_pk, $row_seq->id);");
								$person_id = $row_seq->id;
							}
						}
					}

					if(isset($person_id) && $person_id!='')
					{
						//Schauen ob Benutzerdatensatz mit dieser UID schon vorhanden ist
						$qry = "SELECT * FROM public.tbl_benutzer WHERE uid='$row->uid'";
						if($result_bn = pg_query($conn, $qry))
						{
							$benutzer_insert_error=false;
							if(pg_num_rows($result_bn)==0)
							{
								//Benutzer Datensatz anlegen
								$qry = "INSERT INTO public.tbl_benutzer(uid, person_id, aktiv, insertamum, insertvon, updateamum, updatevon)
							    	    VALUES('$row->uid','$person_id','true',now(),'auto',now(),'auto');";

								if(!pg_query($conn, $qry))
								{
									$test.=$qry;
									$text.="\nFehler: ".pg_errormessage($conn)."\n";
									pg_query($conn, 'ROLLBACK');
									$insert_error++;
									$benutzer_insert_error=true;
								}
								else
								{
									//Alias erstellen
									$vn = split('[- .,]',strtolower($row->vornamen));
									$vn = clean_string($vn[0]);

									$nn = split('[- .,]',strtolower($row->nachname));
									$nn = clean_string($nn[0]);
									$alias = $vn.".".$nn;
									$qry = "SELECT * FROM public.tbl_benutzer WHERE alias='$alias'";
									$res_alias = pg_query($conn, $qry);
									if(pg_num_rows($res_alias)==0)
									{
										$qry = "UPDATE public.tbl_benutzer set alias='$alias' WHERE uid='$uid'";
										if(!$res_insert=@pg_query($conn, $qry))
										{
											$text.=$qry;
											$text.="\nFehler: ".pg_errormessage($conn);
										}
									}
									else
									{
										$text.="UPDATE public.tbl_benutzer set alias='$alias' WHERE uid='$uid'";
										$text.="\nAlias existiert bereits: $alias\n";
									}
								}
							}

							if(!$benutzer_insert_error)
							{
								//Lehrverband Check
								$sql_query = "SELECT * FROM public.tbl_lehrverband WHERE studiengang_kz='$row->kennzahl' AND semester='$row->semester' AND
											verband='$row->verband' AND gruppe='$row->gruppe'";
								if($result_verb = pg_query($conn, $sql_query))
								{
									if(pg_num_rows($result_verb)==0)
									{
										//Lehrverband anlegen
										$sql_query = "INSERT INTO public.tbl_lehrverband(studiengang_kz, semester, verband, gruppe, aktiv)
										              VALUES('$row->kennzahl', '$row->semester', '$row->verband', '$row->gruppe', true);";
										if(!pg_query($conn, $sql_query))
										{
											$text.= $sql_query;
											$text.= "\nFehler:".pg_errormessage($conn)."\n";
										}
									}
								}
								else
								{
									$text.= $sql_query;
									$text.= "\nFehler:".pg_errormessage($conn)."\n";
								}
								// tbl_student
								$sql_query="INSERT INTO public.tbl_student (student_uid,matrikelnr, studiengang_kz, semester, verband, gruppe) ".
										   "VALUES('$row->uid','$row->perskz',$row->kennzahl,$row->semester,'$row->verband','$row->gruppe')";
								echo $sql_query.'<BR>';
								if(!$res_insert=pg_query($conn, $sql_query))
								{
									$text.=$sql_query;
									$text.="\nFehler: ".pg_errormessage($conn)."\n";
									$insert_error++;
									pg_query($conn, 'ROLLBACK');
								}
								else
								{
									$anz_insert++;
									pg_query($conn, 'COMMIT');
								}
							}
						}
						else
						{
							$text.="\nFehler:".pg_errormessage($conn);
							pg_query($conn, 'ROLLBACK');
							$insert_error++;
						}
					}
				}
			}
			// bestehende Studenten
			elseif ($num_rows_std==1)
			{
				$update=0;
				$row_std=pg_fetch_object($res_std);
				if(!$len=strpos($row->vornamen,' '))
				{
					$vorname=$row->vornamen;
					$vornamen='';
				}
				else
				{
					$vorname=substr($row->vornamen,0,$len);
					$vornamen=substr($row->vornamen,$len+1,strlen($row->vornamen));
				}
				if ($row->gruppe==NULL)
					$row->gruppe=1;
				if ($row->titel!=$row_std->titelpre)
					$update=1;
				elseif ($vorname!=$vorname)
					$update=2;
				elseif ($row->nachname!=$row_std->nachname)
					$update=3;
				elseif ($row->gebdatum!=$row_std->gebdatum)
					$update=4;
				elseif ($row->gebort!=$row_std->gebort)
					$update=5;
				elseif ($row->perskz!=$row_std->matrikelnr)
					$update=6;
				elseif ($row->semester!=$row_std->semester)
					$update=7;
				elseif ($row->verband!=$row_std->verband)
					$update=8;
				elseif ($row->gruppe!=$row_std->gruppe)
					$update=9;
				elseif ($row->kennzahl!=$row_std->studiengang_kz)
					$update=10;
				if ($update)
				{
					$text.="Der Student $row->vornamen $row->nachname ($row->uid) [$update] wird upgedatet.\n";

					// person
					$sql_query="UPDATE public.tbl_person SET titelpre='$row->titel', vornamen='$vornamen', vorname='$vorname', ".
							   " nachname='$row->nachname', gebdatum='$row->gebdatum', gebort='$row->gebort'".
							   " WHERE person_id ='$row_std->person_id'";
					//echo $sql_query.'<BR>';
					if(!$res_update=pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\nFehler: ".pg_errormessage($conn)."\n";
						$update_error++;
					}
					//Lehrverband Check
					$sql_query = "SELECT * FROM public.tbl_lehrverband WHERE studiengang_kz='$row->kennzahl' AND semester='$row->semester' AND
								verband='$row->verband' AND gruppe='$row->gruppe'";
					if($result_verb = pg_query($conn, $sql_query))
					{
						if(pg_num_rows($result_verb)==0)
						{
							//Lehrverband anlegen
							$sql_query = "INSERT INTO public.tbl_lehrverband(studiengang_kz, semester, verband, gruppe, aktiv)
							              VALUES('$row->kennzahl', '$row->semester', '$row->verband', '$row->gruppe', true);";
							if(!pg_query($conn, $sql_query))
							{
								$text.= $sql_query;
								$text.= "\nFehler:".pg_errormessage($conn)."\n";
							}
						}
					}
					else
					{
						$text.= $sql_query;
						$text.= "\nFehler:".pg_errormessage($conn)."\n";
					}
					// student
					$sql_query="UPDATE public.tbl_student SET matrikelnr='$row->perskz', semester=$row->semester";
					if ($row->verband==NULL)
						$sql_query.=", verband=' '";
					else
						$sql_query.=", verband='$row->verband'";
					if ($row->gruppe==NULL)
						$sql_query.=", gruppe=' '";
					else
						$sql_query.=", gruppe='$row->gruppe'";
					$sql_query.=", studiengang_kz=".$row->kennzahl;
					$sql_query.=", updateamum=now(), updatevon='auto' WHERE student_uid = '$row->uid'";
					//echo $sql_query.'<BR>';
					if(!$res_update=pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\nFehler: ".pg_errormessage($conn)."\n";
						$update_error++;
					}
					else
						$anz_update++;

				}
			}
			// Student kommt mehrmals vor ->Warnung
			elseif ($num_rows_std>1)
			{
				$text.="\n!!! Der Student $row->vornamen $row->nachname ($row->uid) kommt mehrfach vor!\n";
				$double_error++;
			}
		}
		else
		{
			$plausi_error++;
			$text.="\n!!! Der Student $row->vornamen $row->nachname ($row->uid) STG:$row->kennzahl S:$row->semester V:$row->verband G:$row->gruppe hat nicht plausible Daten!";
		}
	}
	$text.="\n$plausi_error Fehler beim Plausibilitaetscheck!\n";
	$text.="$update_error Fehler bei Student-Update!\n";
	$text.="$insert_error Fehler bei Student-Insert!\n";
	$text.="$double_error Studenten kommen in VileSci doppelt vor!\n\n";
	$text.="$anz_update Studenten wurden upgedatet.\n";
	$text.="$anz_insert Studenten wurden neu angelegt.\n\n";
	$text.="\nEND OF SYNCHRONISATION\n";
	if (mail($adress,"FAS Synchro mit PORTAL (Studenten)",$text,"From: vilesci@technikum-wien.at"))
		$sendmail=true;
	else
		$sendmail=false;
?>

<html>
<head>
	<title>FAS-Synchro mit Portal (Studenten)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";
echo nl2br($text);

?>
</body>
</html>
