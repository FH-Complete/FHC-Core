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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
	require_once('../../vilesci/config.inc.php');
	
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

	//$adress='fas_sync@technikum-wien.at';
	$adress='tw_tester@technikum-wien.at';
	
	//mail($adress,"FAS Synchro mit VILESCI (Lektoren)","BEGIN OF SYNCHRONISATION","From: vilesci@technikum-wien.at");
	$conn=pg_connect(CONN_STRING);
	$conn_fas=pg_connect(CONN_STRING_FAS);

 	// Anzahl der Mitarbeiter in PORTAL
	$sql_query="SELECT count(*) AS anz FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid NOT LIKE '\\\\_%'";
	//echo $sql_query."<br>";
	$result=pg_query($conn, $sql_query);
	$vil_anz_mta=pg_fetch_result($result,0,'anz');

	// Start Studenten Synchro
	$sql_query="SELECT * FROM vw_vilesci_mitarbeiter_aktiv WHERE uid IS NOT NULL AND uid NOT LIKE ''"; // LIMIT 5";
	//echo $sql_query."<br>";
	$result=pg_query($conn_fas, $sql_query);
	$num_rows=pg_num_rows($result);
	$text="Dies ist eine automatische eMail!\r\r";
	$text.="Es wurde eine Synchronisation mit FAS durchgeführt.\r";
	$text.="Anzahl der Mitarbeiter vom FAS-Import: $num_rows \r";
	$text.="Anzahl der Mitarbeiter in PORTAL: $vil_anz_mta \r\r";
	$plausi_error=0;
	$update_error=0;
	$insert_error=0;
	$double_error=0;
	$anz_update=0;
	$anz_insert=0;
	while($row=pg_fetch_object($result))
	{
		$row->gebort=substr($row->gebort,0,30);
		//$row->titel=substr($row->titel,0,15);
		$uid=str_replace(' ','',$row->uid);
		// Plausibilitaetscheck
		if (strlen($row->titel)>32)
			$text.="Der Mitarbeiter $row->titel $row->vornamen $row->nachname ($row->uid) hat einen zu langen Titel.\r";
		if (strlen($row->titel)<=32)
		{
			// SQL vorbereiten (jeden Mitarbeiter vom FAS in VILESCI suchen
			$sql_query="SELECT uid,titelpre,vorname,nachname,gebdatum,gebort,";
			$sql_query.="personalnummer";
			$sql_query.=" FROM campus.vw_mitarbeiter WHERE uid = '$uid'";
			//echo $sql_query;
			$res_lkt=pg_query($conn, $sql_query);
			$num_rows_lkt=pg_num_rows($res_lkt);

			// neue Lektoren
			if ($num_rows_lkt==0)
			{
				$text.="Der Lektor $row->vornamen $row->nachname ($row->uid) wird neu angelegt.\r";
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
				$qry = "INSERT INTO public.tbl_person(titelpre, nachname, vorname, vornamen, gebdatum, gebort, aktiv) 
				VALUES('$row->titel','$row->nachname','$vorname','$vornamen','$row->gebdatum','$row->gebort',true);"; 
				echo $qry.'<BR>';
				
				if(!$res_insert=pg_query($conn, $qry))
				{
					$text.=$qry;
					$text.="\rFehler: ".pg_errormessage($conn)."\r";
					$insert_error++;
					pg_query($conn, 'ROLLBACK');
				}
				else 
				{
					$qry = "SELECT currval('tbl_person_person_id_seq') AS id;";
					
					if(!$row_seq=pg_fetch_object(pg_query($this->conn,$qry)))
					{
						pg_query($conn, 'ROLLBACK');
						$text = 'Sequence konnte nicht ausgelesen werden\n';
						$insert_error++;
					}
					else 
					{
						$person_id = $row_seq->id;
						
						//Benutzer Datensatzt anlegen
						$qry = "INSERT INTO public.tbl_benutzer(uid, person_id, aktiv, insertamum, insertvon, updateamum, updatevon)
						        VALUES('$row->uid','$person_id','true',now(),'auto',now(),'auto');";
	
						if(!pg_query($conn, $qry))
						{
							pg_query($conn, 'ROLLBACK');
							$text.="\rFehler: ".pg_errormessage($conn)."\r";
							$insert_error++;
						}
						else 
						{						
							//Alias erstellen
							$vn = split('[- .,]',strtolower($row->vornamen));
							$vn = clean_string($vn[0]);
			
							$nn = split('[- .,]',strtolower($row->nachname));
							$nn = clean_string($nn[0]);
							$alias = $vn.".".$nn;
							$qry = "UPDATE public.tbl_benutzer set alias='$alias' WHERE uid='$uid'";
							if(!$res_insert=pg_exec($conn, $qry))
							{
								$text.=$qry;
								$text.="\rFehler: Alias existiert bereits: $alias";
								$insert_error++;
							}
							
							// Mitarbeiterdatensatz
							$sql_query="INSERT INTO tbl_mitarbeiter (mitarbeiter_uid,personalnummer,telefonklappe,kurzbz,lektor,fixangestellt, insertamum, insertvon, updateamum, updatevon) ".
										"VALUES('$row->uid','$row->persnr','$row->teltw','$row->kurzbez',true,".($row->fixangestellt?'true':'false').",now(),'auto',now(),'auto')";
							echo $sql_query.'<BR>';
							if(!$res_insert=pg_query($conn, $sql_query))
							{
								$text.=$sql_query;
								$text.="\rFehler: ".pg_errormessage($conn)."\r";
								pg_query($conn, 'ROLLBACK');
								$insert_error++;
							}
							else
							{
								$anz_insert++;
								pg_query($conn, 'COMMIT');
							}
						}
					}
				}
				
			}
			// bestehende Lektoren
			elseif ($num_rows_lkt==1)
			{				
				$update=0;
				$row_lkt=pg_fetch_object($res_lkt,0);
				
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
				//if ($row->gruppe==NULL)
				//	$row->gruppe=1;
				if ($row->titel!=$row_lkt->titelpre)
					$update=1;
				elseif ($vorname!=$row_lkt->vorname)
					$update=2;
				elseif ($row->nachname!=$row_lkt->nachname)
					$update=3;
				elseif ($row->gebdatum!=$row_lkt->gebdatum)
					$update=4;
				elseif ($row->gebort!=$row_lkt->gebort)
					$update=5;
				elseif ($row->persnr!=$row_lkt->personalnummer)
					$update=6;
				if ($update)
				{
					$text.="Der Lektor $row->vornamen $row->nachname ($row->uid) [$update] wird upgedatet.\r";
					
					// person
					$sql_query="UPDATE public.tbl_person SET titelpre='$row->titel', vorname='$vorname', vornamen='$vornamen', ".
							   " nachname='$row->nachname', gebdatum='$row->gebdatum', gebort='$row->gebort'".
							   " WHERE person_id=(SELECT person_id FROM public.tbl_benutzer WHERE uid='$uid')";
					echo $sql_query.'<BR>';
					if(!$res_update=pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\rFehler: ".pg_errormessage($conn)."\r";
						$update_error++;
					}

					$sql_query="UPDATE public.tbl_mitarbeiter SET personalnummer='$row->persnr',fixangestellt=".($row->fixangestellt?'TRUE':'FALSE');
					$sql_query.=" WHERE mitarbeiter_uid LIKE '$uid'";
					echo $sql_query.'<BR>';		// kurzbz='$row->kurzbez',
					if(!$res_update=pg_query($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\rFehler: ".pg_errormessage($conn)."\r";
						$update_error++;
					}
					else
						$anz_update++;
				}
			}
			// Lektor kommt mehrmals vor ->Warnung
			elseif ($num_rows_lkt>1)
			{
				$text.="\r!!! Der Lektor $row->vornamen $row->nachname ($row->uid) kommt mehrfach vor!\r";
				$double_error++;
			}
		}
		else
			$plausi_error++;
	}
	$text.="\r$plausi_error Fehler beim Plausibilitaetscheck!\r";
	$text.="$update_error Fehler bei Lektor-Update!\r";
	$text.="$insert_error Fehler bei Lektor-Insert!\r";
	$text.="$double_error Lektoren kommen in VileSci doppelt vor!\r\r";
	$text.="$anz_update Lektoren wurden upgedatet.\r";
	$text.="$anz_insert Lektoren wurden neu angelegt.\r\r";
	$text.="\rEND OF SYNCHRONISATION\r";
	if (mail($adress,"FAS Synchro mit PORTAL (Lektoren)",$text,"From: vilesci@technikum-wien.at"))
		$sendmail=true;
	else
		$sendmail=false;
?>

<html>
<head>
<title>FAS-Synchro mit VileSci (Lektoren)</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
