<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Adressendatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');
include('../../../include/adresse.class.php');
include('../../../include/firma.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");

//$adress='ruhan@technikum-wien.at';
$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_update=0;
$anzahl_fehler=0;
$anzahl_quelle2=0;
$anzahl_eingefuegt2=0;
$anzahl_update2=0;
$anzahl_fehler2=0;
$ausgabe='';
$ausgabe_adresse='';
$update=false;

function validate($row)
{
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Vilesci - Adresse</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
//nation
$qry = "SELECT * FROM adresse ORDER BY person_fk;";

if($result = pg_query($conn_fas, $qry))
{
	echo nl2br("Adresse Sync\n--------------\n");
	$anzahl_quelle=pg_num_rows($result);
	while($row = pg_fetch_object($result))
	{
		$error=false;
		$adresse				=new adresse($conn);
		$adresse->name			=$row->name;
		$adresse->strasse			=$row->strasse;
		$adresse->plz			=$row->plz;
		$adresse->ort			=$row->ort;
		$adresse->gemeinde		=$row->gemeinde;
		$adresse->nation			=$row->nation;
		$adresse->typ			=$row->typ;
		$adresse->heimatadresse		=$row->bismeldeadresse=='J'?true:false;
		$adresse->zustelladresse		=$row->zustelladresse=='J'?true:false;
		$adresse->firma_id			=null;
		//$adresse->updateamum		=$row->;
		$adresse->updatevon		="SYNC";
		//$adresse->insertamum		=$row->;
		$adresse->insertvon			="SYNC";
		$adresse->ext_id			=$row->adresse_pk;
		
		$ausgabe_adresse='';
		$update=false;
		//echo nl2br ($adresse->ext_id."\n");
		//person_id herausfinden
		$qry1="SELECT person_portal FROM sync.tbl_syncperson WHERE person_fas=".$row->person_fk.";";
		if($result1 = pg_query($conn, $qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1=pg_fetch_object($result1))
				{ 
					$adresse->person_id=$row1->person_portal;

					$qry2="SELECT * FROM public.tbl_adresse WHERE ext_id=".$row->adresse_pk.";";
					if($result2 = pg_query($conn, $qry2))
					{
						if(pg_num_rows($result2)>0) //eintrag gefunden
						{
							if($row2=pg_fetch_object($result2))
							{ 
								$update=false;
								if(trim($row2->name)!=trim($row->name))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Name: '".trim($row->name)."'";
									}
									else
									{
										$ausgabe_adresse="Name: '".trim($row->name)."'";
									}
								}
								if(trim($row2->strasse)!=trim($row->strasse))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Strasse: '".trim($row->strasse)."'";
									}
									else
									{
										$ausgabe_adresse="Strasse: '".trim($row->strasse)."'";
									}
								}
								if(trim($row2->plz)!=trim($row->plz))
								{
									$update=true;
									if(strlen(trim($ausgabe_plz))>0)
									{
										$ausgabe_adresse.=", Plz: '".trim($row->plz)."'";
									}
									else
									{
										$ausgabe_adresse="Plz: '".trim($row->plz)."'";
									}
								}
								if(trim($row2->ort)!=trim($row->ort))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Ort: '".trim($row->ort)."'";
									}
									else
									{
										$ausgabe_adresse="Ort: '".trim($row->ort)."'";
									}
								}
								if(trim($row2->gemeinde)!=trim($row->gemeinde))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Gemeinde: '".trim($row->gemeinde)."'";
									}
									else
									{
										$ausgabe_adresse="Gemeinde: '".trim($row->gemeinde)."'";
									}
								}
								if(trim($row2->nation)!=trim($row->nation))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Nation: '".trim($row->nation)."'";
									}
									else
									{
										$ausgabe_adresse="Nation: '".trim($row->nation)."'";
									}
								}
								if(trim($row2->typ)!=trim($row->typ))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Typ: '".trim($row->typ)."'";
									}
									else
									{
										$ausgabe_adresse="Typ: '".trim($row->typ)."'";
									}
								}
								if($row2->heimatadresse!=($row->bismeldeadresse=='J'?'t':'f'))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Heimatadresse: '".($row->bismeldeadresse=='J'?'true':'false')."'";
									}
									else
									{
										$ausgabe_adresse="Heimatadresse: '".($row->bismeldeadresse=='J'?'true':'false')."'";
									}
								}
								if($row2->zustelladresse!=($row->zustelladresse=='J'?'t':'f'))
								{
									$update=true;
									if(strlen(trim($ausgabe_adresse))>0)
									{
										$ausgabe_adresse.=", Zustelladresse: '".($row->zustelladresse=='J'?'true':'false')."'";
									}
									else
									{
										$ausgabe_adresse="Zustelladresse: '".($row->Zustelladresse=='J'?'true':'false')."'";
									}
								}
								// update adresse, wenn datensatz bereits vorhanden
								$adresse->new=false;
								$adresse->adresse_id=$row2->adresse_id;
							}
						}
						else 
						{
							// insert, wenn datensatz noch nicht vorhanden
							$adresse->new=true;
							
							//firma eintragen, wenn firmenadresse
							if ($row->typ==1 && strlen(trim($row->bezeichnung))>0 && $row->bezeichnung!=NULL)
							{
								$anzahl_quelle2++;
								$firma=new firma($conn);
								$firma->name=$row->bezeichnung;
								$firma->anmerkung=null;
								$firma->ext_id=$row->adresse_pk;
								$firma->firmentyp_kurzbz='Partnerfirma';
								$qry3="SELECT firma_id, ext_id FROM tbl_firma WHERE ext_id=".$row->adresse_pk.";";
								if($result3 = pg_query($conn, $qry3))
								{
									if(pg_num_rows($result3)>0) //eintrag gefunden
									{
										if($row3=pg_fetch_object($result3))
										{
											$firma->new=false;	
											$firma->firma_id=$row3->firma_id;	
										}
										else 
										{
											$error_log.="Firma mit adresse_pk: $row->adresse_pk konnte nicht ermittelt werden! Firma wird nicht eingetragen.\n";
										}
									}
									else
									{
										$firma->new=true;
									}
								} 
								if(!$error)
								{
									if(!$firma->save())
									{
										$error_log.=$firma->errormsg."\n";
										$anzahl_fehler2++;
										$error_log.="Firma mit adresse_pk: $row->adresse_pk wurde nicht eingetragen!\n";
									}
									else 
									{
										if($firma->new)
										{
											$ausgabe.="Firma ".$firma->name." eingefügt.\n";
											$anzahl_eingefuegt2++;
										}
										else 
										{
											$ausgabe.="Firma ".$firma->name." geändert.\n";
											$anzahl_update2;
										}
										
									}											
									$adresse->firma_id=$firma->firma_id;
								}
							}	
						}
					}
				}
				else 
				{
					$ausgabe_adresse='';
					$error=true;
					$error_log.="adresse mit adresse_pk: ".$row->adresse_pk." konnte nicht ermittelt werden!\n";
					$anzahl_fehler++;
				}
			}
			else 
			{
				$ausgabe_adresse='';
				$error=true;
				$error_log.="Person mit person_pk '$row->person_fk' für Adresse mit adresse_pk '$row->adresse_pk' konnte in tbl_syncperson nicht gefunden werden!\n";
				$anzahl_fehler++;
			}
		}
		
		if(!$error)
		{
			if($adresse->new || $update)
			{
				if(!$adresse->save())
				{
					$error_log.=$adresse->errormsg."\n";
					$anzahl_fehler++;
				}
				else 
				{
					if($adresse->new)
					{
						$ausgabe.="Adresse '".$adresse->plz."',  '".$adresse->strasse."' eingefügt.\n";
						$anzahl_eingefuegt++;
					}
					else 
					{
						if($update)
						{
							$ausgabe.="Adresse '".$adresse->plz."', '".$adresse->strasse."' geändert: ".$ausgabe_adresse."\n";
							$anzahl_update++;
						}
					}
					//echo "- ";
					//ob_flush();
					//flush();
				}
			}
		}
		//flush();	
	}	
}


//echo nl2br($text);
echo nl2br($error_log);
echo nl2br("\nAdresse\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler");
echo nl2br("\nFirma\nGesamt: $anzahl_quelle2 / Eingefügt: $anzahl_eingefuegt2 / Geändert: $anzahl_update2 / Fehler: $anzahl_fehler2");
$ausgabe="\nAdresse\nGesamt: $anzahl_quelle / Eingefügt: $anzahl_eingefuegt / Geändert: $anzahl_update / Fehler: $anzahl_fehler"
."\nFirma\nGesamt: $anzahl_quelle2 / Eingefügt: $anzahl_eingefuegt2 / Geändert: $anzahl_update2 / Fehler: $anzahl_fehler2\n\n".$ausgabe;
if(strlen(trim($error_log))>0)
{
	mail($adress, 'SYNC-Fehler Adresse von '.$_SERVER['HTTP_HOST'], $error_log,"From: vilesci@technikum-wien.at");
}
mail($adress, 'SYNC Adresse von '.$_SERVER['HTTP_HOST'], $ausgabe,"From: vilesci@technikum-wien.at");
?>
</body>
</html>