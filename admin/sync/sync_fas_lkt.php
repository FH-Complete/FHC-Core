<?php
	include ('../../vilesci/config.inc.php');
	
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

	$adress='fas_sync@technikum-wien.at';
	//mail($adress,"FAS Synchro mit VILESCI (Lektoren)","BEGIN OF SYNCHRONISATION","From: vilesci@technikum-wien.at");
	$conn=pg_connect(CONN_STRING);
	$conn_fas=pg_connect(CONN_STRING_FAS);

 	// Anzahl der Mitarbeiter in VILESCI
	$sql_query="SELECT count(*) AS anz FROM tbl_mitarbeiter WHERE uid NOT LIKE '\\\\_%'";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$vil_anz_mta=pg_fetch_result($result,0,'anz');

	// Start Studenten Synchro
	$sql_query="SELECT * FROM vw_vilesci_mitarbeiter_aktiv WHERE uid IS NOT NULL AND uid NOT LIKE ''"; // LIMIT 5";
	//echo $sql_query."<br>";
	$result=pg_exec($conn_fas, $sql_query);
	$num_rows=pg_numrows($result);
	$text="Dies ist eine automatische eMail!\r\r";
	$text.="Es wurde eine Synchronisation mit FAS durchgeführt.\r";
	$text.="Anzahl der Mitarbeiter vom FAS-Import: $num_rows \r";
	$text.="Anzahl der Mitarbeiter in der VILESCI: $vil_anz_mta \r\r";
	$plausi_error=0;
	$update_error=0;
	$insert_error=0;
	$double_error=0;
	$anz_update=0;
	$anz_insert=0;
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result,$i);
		$row->gebort=substr($row->gebort,0,30);
		//$row->titel=substr($row->titel,0,15);
		$uid=str_replace(' ','',$row->uid);
		// Plausibilitaetscheck
		if (strlen($row->titel)>64)
			$text.="Der Mitarbeiter $row->titel $row->vornamen $row->nachname ($row->uid) hat einen zu langen Titel.\r";
		if (strlen($row->titel)<=64)
		{
			// SQL vorbereiten (jeden Mitarbeiter vom FAS in VILESCI suchen
			$sql_query="SELECT uid,titel,vornamen,nachname,gebdatum,gebort,";
			$sql_query.="personalnummer";
			$sql_query.=" FROM tbl_person NATURAL JOIN tbl_mitarbeiter WHERE uid LIKE '$uid'";
			//echo $sql_query;
			$res_std=pg_exec($conn, $sql_query);
			$num_rows_std=pg_numrows($res_std);

			// neue Lektoren
			if ($num_rows_std==0)
			{
				$text.="Der Lektor $row->vornamen $row->nachname ($row->uid) wird neu angelegt.\r";
				// person
				$qry="INSERT INTO tbl_person(uid,titel,vornamen, nachname, gebdatum, gebort) ".
					 "VALUES('$uid','$row->titel','$row->vornamen','$row->nachname','$row->gebdatum','$row->gebort')";
				echo $qry.'<BR>';
				if(!$res_insert=pg_exec($conn, $qry))
				{
					$text.=$qry;
					$text.="\rFehler: ".pg_errormessage($conn)."\r";
					$insert_error++;
				}
				
				//Alias erstellen
				$vn = split('[- .,]',strtolower($row->vornamen));
				$vn = clean_string($vn[0]);

				$nn = split('[- .,]',strtolower($row->nachname));
				$nn = clean_string($nn[0]);
				$alias = $vn.".".$nn;
				$qry = "UPDATE tbl_person set alias='$alias' where uid='$uid'";
				if(!$res_insert=pg_exec($conn, $qry))
				{
					$text.=$qry;
					$text.="\rFehler: Alias existiert bereits: $alias";
					$insert_error++;
				}
				// lektor
				$sql_query="INSERT INTO tbl_mitarbeiter (uid,personalnummer,kurzbz,lektor,telefonklappe,fixangestellt) ".
							"VALUES('$row->uid','$row->persnr','$row->kurzbez',true,'$row->teltw',".($row->fixangestellt?'true':'false').")";
				echo $sql_query.'<BR>';
				if(!$res_insert=pg_exec($conn, $sql_query))
				{
					$text.=$sql_query;
					$text.="\rFehler: ".pg_errormessage($conn)."\r";
					$insert_error++;
				}
				else
					$anz_insert++;
			}
			// bestehende Lektoren
			elseif ($num_rows_std==1)
			{
				$update=0;
				$row_std=pg_fetch_object($res_std,0);
				if ($row->gruppe==NULL)
					$row->gruppe=1;
				if ($row->titel!=$row_std->titel)
					$update=1;
				elseif ($row->vornamen!=$row_std->vornamen)
					$update=2;
				elseif ($row->nachname!=$row_std->nachname)
					$update=3;
				elseif ($row->gebdatum!=$row_std->gebdatum)
					$update=4;
				elseif ($row->gebort!=$row_std->gebort)
					$update=5;
				elseif ($row->persnr!=$row_std->personalnummer)
					$update=6;
				if ($update)
				{
					$text.="Der Lektor $row->vornamen $row->nachname ($row->uid) [$update] wird upgedatet.\r";
					// person
					$sql_query="UPDATE tbl_person SET titel='$row->titel', vornamen='$row->vornamen', ".
							   " nachname='$row->nachname', gebdatum='$row->gebdatum', gebort='$row->gebort'".
							   " WHERE uid LIKE '$uid'";
					echo $sql_query.'<BR>';
					if(!$res_update=pg_exec($conn, $sql_query))
					{
						$text.=$sql_query;
	                    $text.="\rFehler: ".pg_errormessage($conn)."\r";
						$update_error++;
					}

					$sql_query="UPDATE tbl_mitarbeiter SET personalnummer='$row->persnr',fixangestellt=".($row->fixangestellt?'TRUE':'FALSE');
					$sql_query.=" WHERE uid LIKE '$uid'";
					echo $sql_query.'<BR>';		// kurzbz='$row->kurzbez',
					if(!$res_update=pg_exec($conn, $sql_query))
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
			elseif ($num_rows_std>1)
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
	if (mail($adress,"FAS Synchro mit VileSci (Lektoren)",$text,"From: vilesci@technikum-wien.at"))
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
echo $text;

?>
</body>
</html>
