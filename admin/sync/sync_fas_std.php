<?php
	/**
	 * FAS-Synchronisation
	 */

	include ('../../vilesci/config.inc.php');
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
	$sql_query="SELECT count(*) AS anz FROM tbl_student";
	//echo $sql_query."<br>";
	$result=pg_exec($conn, $sql_query);
	$vilesci_anz_std=pg_fetch_result($result,0,'anz');

	// Start Studenten Synchro
	$sql_query="SELECT DISTINCT * FROM fas_view_student_vilesci WHERE semester >0 AND semester <9 AND";
	$sql_query.=" verband IS NOT NULL AND uid IS NOT NULL AND uid NOT LIKE ''"; // LIMIT 5";
	echo $sql_query."<br>";
	flush();
	$result=pg_exec($conn_fas, $sql_query);
	$num_rows=pg_numrows($result);
	$text="Dies ist eine automatische eMail!\r\r";
	$text.="Es wurde eine Synchronisation mit FAS durchgeführt.\r";
	$text.="Anzahl der Studenten vom FAS-Import: $num_rows \r";
	$text.="Anzahl der Studenten in VileSci: $vilesci_anz_std \r\r";
	echo $text.'<BR>';
	flush();
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
		$row->titel=substr($row->titel,0,15);
		$uid=str_replace(' ','',$row->uid);
		// Plausibilitaetscheck
		if ($row->gruppe==null)
			$row->gruppe='1';
		if ($row->verband>='A' && $row->verband<='D' && $row->semester<=8 && $row->gruppe>'0' && $row->gruppe<='2')
		{
			// SQL vorbereiten (jeden Studenten vom FAS im VileSci suchen
			$sql_query="SELECT uid,titel,vornamen,nachname,gebdatum,gebort,";
			$sql_query.="trim(both ' ' from matrikelnr) AS matrikelnr,";
			$sql_query.=" studiengang_kz,semester,verband,gruppe";
			$sql_query.=" FROM tbl_person NATURAL JOIN tbl_student WHERE uid LIKE '$uid'";
			// echo $sql_query;
			$res_std=pg_exec($conn, $sql_query);
			$num_rows_std=pg_numrows($res_std);

			// neue Studenten
			if ($num_rows_std==0)
			{
				$text.="Der Student $row->vornamen $row->nachname ($row->uid) wird neu angelegt.\r";
				// tbl_person
				$sql_query="INSERT INTO tbl_person(uid,titel,vornamen, nachname, gebdatum, gebort) ".
					 "VALUES('$row->uid','$row->titel','$row->vornamen','$row->nachname','$row->gebdatum','$row->gebort')";
				echo $sql_query.'<BR>';
				flush();
				
				//Alias erstellen
				$vn = split('[- .,]',strtolower($row->vornamen));
				$vn = clean_string($vn[0]);

				$nn = split('[- .,]',strtolower($row->nachname));
				$nn = clean_string($nn[0]);
				$alias = $vn.".".$nn;
				$qry = "UPDATE tbl_person set alias='$alias' where uid='$row->uid'";
				if(!$res_insert=pg_exec($conn, $qry))
				{
					$text.=$qry;
					$text.="\rFehler: Alias existiert bereits: $alias";
					$insert_error++;
				}
				
				// tbl_student
				if(!$res_insert=pg_exec($conn, $sql_query))
				{
					$text.=$qry;
					$text.="\rFehler: ".pg_errormessage($conn)."\r";
					$insert_error++;
				}
				$sql_query="INSERT INTO tbl_student (uid,matrikelnr, studiengang_kz, semester, verband, gruppe) ".
						   "VALUES('$row->uid','$row->perskz',$row->kennzahl,$row->semester,'$row->verband','$row->gruppe')";
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
			// bestehende Studenten
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
					$text.="Der Student $row->vornamen $row->nachname ($row->uid) wird upgedatet.\r";
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
					// student
					$sql_query="UPDATE tbl_student SET matrikelnr='$row->perskz', semester=$row->semester";
					if ($row->verband==NULL)
						$sql_query.=", verband=NULL";
					else
						$sql_query.=", verband='$row->verband'";
					if ($row->gruppe==NULL)
						$sql_query.=", gruppe=NULL";
					else
						$sql_query.=", gruppe='$row->gruppe'";
					$sql_query.=", studiengang_kz=".$row->kennzahl;
					$sql_query.=", updateamum=now(), updatevon='auto' WHERE uid LIKE '$uid'";
					echo $sql_query.'<BR>';
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
			// Student kommt mehrmals vor ->Warnung
			elseif ($num_rows_std>1)
			{
				$text.="\r!!! Der Student $row->vornamen $row->nachname ($row->uid) kommt mehrfach vor!\r";
				$double_error++;
			}
		}
		else
		{
			$plausi_error++;
			$text.="\r!!! Der Student $row->vornamen $row->nachname ($row->uid) STG:$row->kennzahl S:$row->semester V:$row->verband G:$row->gruppe hat nicht plausible Daten!";
		}
	}
	$text.="\r$plausi_error Fehler beim Plausibilitaetscheck!\r";
	$text.="$update_error Fehler bei Student-Update!\r";
	$text.="$insert_error Fehler bei Student-Insert!\r";
	$text.="$double_error Studenten kommen in VileSci doppelt vor!\r\r";
	$text.="$anz_update Studenten wurden upgedatet.\r";
	$text.="$anz_insert Studenten wurden neu angelegt.\r\r";
	$text.="\rEND OF SYNCHRONISATION\r";
	if (mail($adress,"FAS Synchro mit VileSci (Studenten)",$text,"From: vilesci@technikum-wien.at"))
		$sendmail=true;
	else
		$sendmail=false;
?>

<html>
<head>
	<title>FAS-Synchro mit VileSci (Studenten)</title>
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
