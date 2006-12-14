<?php
    require_once('../../config.inc.php');
    require_once('../../../include/functions.inc.php');
    require_once('../../../include/funktion.class.php');
    require_once('../../../include/studiengang.class.php');
    require_once('../../../include/person.class.php');
    require_once('../../../include/benutzer.class.php');
    require_once('../../../include/student.class.php');
    
    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die("Fehler beim �ffnen der Datenbankverbindung");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<body onLoad="document.SearchFormular.txtSearchQuery.focus();">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Personensuche Technikum Wien</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<form method="post" action="psearch.php" name="SearchFormular">
	  	<td nowrap><input type="hidden" name="do_search">
	  	  Suche nach:
	  	  <input type="text" name="txtSearchQuery" size="45"> 
	  	  in Gruppe
	  	  <select name="cmbChoice">
			  <option value="all">Alle Kategorien</option>
			  <?php
				$fkt_obj = new funktion($conn);
				$fkt_obj->getAll();
			  
				//$qry = "SELECT DISTINCT funktion_kurzbz AS kurzbz, bezeichnung FROM public.tbl_funktion WHERE aktiv=TRUE ORDER BY bezeichnung";
				
				//$result = pg_exec($sql_conn, $sql_query);
				//$num_rows = pg_num_rows($result);
				
				//for($i = 0; $i < $num_rows; $i++)
				foreach ($fkt_obj->result as $row)
				{
					//$row = pg_fetch_object($result, $i);
					
					if(isset($cmbChoice) && $cmbChoice == $row->funktion_kurzbz)
					{
						echo "<option value=\"$row->funktion_kurzbz\" selected>$row->beschreibung</option>";
					}
					else
					{
						echo "<option value=\"$row->funktion_kurzbz\">$row->beschreibung</option>";
					}
				}
			  ?>
	  	  </select>
	  	  <input type="submit" name="btnSearch" value="Suchen">
		</td>
		</form>
	  </tr>
	  <tr>
	  	<td>&nbsp;</td>
	  </tr>
	  <tr>
	  	<td nowrap>
			<?php
				if(isset($do_search))
				{
					//To prevent SQL Injection
					$txtSearchQuery=str_replace(')','',$txtSearchQuery);
					$txtSearchQuery=str_replace('\'','',$txtSearchQuery);
					$txtSearchQuery=str_replace('--','',$txtSearchQuery);
					
					if($txtSearchQuery == "" || $txtSearchQuery == "*" || $txtSearchQuery == "*.*")
					{
						if($cmbChoice == "all")
						{
							//$sql_query = "SELECT DISTINCT tbl_person.uid, titel, nachname, vornamen, telefonklappe AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM public.tbl_person, public.tbl_mitarbeiter WHERE tbl_mitarbeiter.uid=tbl_person.uid AND aktiv=TRUE UNION SELECT DISTINCT tbl_person.uid, titel, nachname, vornamen, (''::varchar) AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort FROM public.tbl_person, public.tbl_student WHERE semester<10 AND tbl_person.uid=tbl_student.uid AND tbl_student.uid not like '%dummy%' AND aktiv=TRUE ORDER BY nachname, vornamen";
							$sql_query = "SELECT uid, titelpre, titelpost, nachname, vorname, telefonklappe as teltw,(uid || '@technikum-wien.at') AS emailtw, foto,-1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM campus.vw_mitarbeiter UNION SELECT DISTINCT uid, titelpre, titelpost, nachname, vorname, (''::varchar) AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort FROM campus.vw_student WHERE semester<10 ORDER BY nachname, vorname";
						}
						else
						{
							//$sql_query = "SELECT DISTINCT tbl_person.uid, titel, nachname, vornamen, telefonklappe AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM public.tbl_person, public.tbl_mitarbeiter WHERE tbl_mitarbeiter.uid=tbl_person.uid AND public.tbl_funktion.funktion_kurzbz='$cmbChoice' AND public.tbl_personfunktion.funktion_kurzbz=public.tbl_funktion.funktion_kurzbz AND tbl_person.uid=public.tbl_personfunktion.uid AND aktiv=TRUE UNION SELECT DISTINCT tbl_person.uid, (''::varchar) AS titel, nachname, vornamen, (''::varchar) AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort FROM public.tbl_person, public.tbl_student WHERE semester<10 AND tbl_person.uid=tbl_student.uid AND public.tbl_funktion.funktion_kurzbz='$cmbChoice' AND public.tbl_personfunktion.funktion_kurzbz=public.tbl_funktion.funktion_kurzbz AND tbl_person.uid=public.tbl_personfunktion.uid AND aktiv=TRUE ORDER BY nachname, vornamen";
							$sql_query = "SELECT DISTINCT uid, titelpre, titelpost, nachname, vorname, telefonklappe AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM campus.vw_mitarbeiter JOIN tbl_benutzerfunktion using(uid) WHERE funktion_kurzbz='$cmbChoice' UNION SELECT DISTINCT uid, titelpre,titelpost, nachname, vorname, (''::varchar) AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, vw_student.studiengang_kz, semester, ''::varchar as ort FROM campus.vw_student JOIN tbl_benutzerfunktion using(uid) WHERE semester<10 AND funktion_kurzbz='$cmbChoice' ORDER BY nachname, vorname";
						}
					}
					else
					{
						if($cmbChoice == "all")
						{
							//$sql_query = "SELECT DISTINCT tbl_person.uid, titel, nachname, vornamen, telefonklappe AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM public.tbl_person, public.tbl_mitarbeiter WHERE tbl_mitarbeiter.uid=tbl_person.uid AND (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR tbl_person.uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND aktiv=TRUE UNION SELECT DISTINCT tbl_person.uid, (''::varchar) AS titel, nachname, vornamen, (''::varchar) AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort FROM public.tbl_person, public.tbl_student WHERE semester<10 AND tbl_person.uid=tbl_student.uid AND (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR tbl_person.uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND aktiv=TRUE ORDER BY nachname, vornamen";
							$sql_query = "SELECT DISTINCT uid, titelpre, titelpost, nachname, vorname, telefonklappe AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM campus.vw_mitarbeiter WHERE  (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND aktiv=TRUE UNION SELECT DISTINCT uid, titelpre, titelpost, nachname, vorname, (''::varchar) AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort FROM campus.vw_student WHERE semester<10 AND (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) ORDER BY nachname, vorname";
						}
						else
						{
							//$sql_query = "SELECT DISTINCT tbl_person.uid, titel, nachname, vornamen, telefonklappe AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM public.tbl_person, public.tbl_mitarbeiter WHERE tbl_mitarbeiter.uid=tbl_person.uid AND ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR tbl_person.uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND public.tbl_funktion.funktion_kurzbz='$cmbChoice' AND public.tbl_personfunktion.funktion_kurzbz=public.tbl_funktion.funktion_kurzbz AND tbl_person.uid=public.tbl_personfunktion.uid) AND aktiv=TRUE UNION SELECT DISTINCT tbl_person.uid, (''::varchar) AS titel, nachname, vornamen, (''::varchar) AS teltw, (tbl_person.uid || '@technikum-wien.at') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort FROM public.tbl_person, public.tbl_student WHERE semester <10 AND tbl_person.uid=tbl_student.uid AND ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR tbl_person.uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vornamen) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vornamen || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND public.tbl_funktion.funktion_kurzbz='$cmbChoice' AND public.tbl_personfunktion.funktion_kurzbz=public.tbl_funktion.funktion_kurzbz AND tbl_person.uid=public.tbl_personfunktion.uid) AND aktiv=TRUE ORDER BY nachname, vornamen";
							$sql_query = "SELECT DISTINCT uid, titelpre, titelpost, nachname, vorname, telefonklappe AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort FROM campus.vw_mitarbeiter JOIN tbl_benutzerfunktion USING(uid) WHERE ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND funktion_kurzbz='$cmbChoice') UNION SELECT DISTINCT uid, titelpre, titelpost, nachname, vorname, (''::varchar) AS teltw, (uid || '@technikum-wien.at') AS emailtw, foto, vw_student.studiengang_kz, semester, ''::varchar as ort FROM campus.vw_student JOIN tbl_benutzerfunktion USING(uid) WHERE semester <10 AND ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND funktion_kurzbz='$cmbChoice') ORDER BY nachname, vorname";
						}
					}
					
					$result = pg_exec($conn, $sql_query);
					$num_rows = pg_num_rows($result);
				
					if($num_rows > 0)
					{
						echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"100%\">";
						
						echo "<tr>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Titel</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Vorname</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Nachname</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Telefonnummer</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;E-Mail Adresse</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Raum</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Studiengang</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Semester</font></td>
								<td align=\"left\" class=\"ContentHeader\" nowrap><font class=\"ContentHeader\">&nbsp;Hauptverteiler</font></td>";
								
								
						echo "</tr>
							  <tr>
							  	<td nowrap>&nbsp;</td>
							  </tr>";
						
						for($i = 0; $i < $num_rows; $i++)
						{
							$row = pg_fetch_object($result, $i);
						
							echo "<tr>";
							
							if($row->titelpre != "")
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;$row->titelpre</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;$row->titelpre</td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							if($row->vorname != "")
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;$row->vorname</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;$row->vorname</td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							if($row->nachname != "")
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;<a href='../stdplan/profile/index.php?uid=$row->uid' title='Profil anzeigen'>$row->nachname $row->titelpost</a></td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;<a href='../stdplan/profile/index.php?uid=$row->uid'  title='Profil anzeigen'>$row->nachname $row->titelpost</a></td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							if($row->teltw != "")
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;01 333 40 77 - $row->teltw</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;01 333 40 77 - $row->teltw</td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							if($row->emailtw != "")
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;<a href=\"mailto:$row->emailtw\" class=\"Item\">$row->emailtw</a></td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;<a href=\"mailto:$row->emailtw\" class=\"Item\">$row->emailtw</a></td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							if($row->ort != "")
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;$row->ort</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;$row->ort</td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							$kurzbz='';
							if($row->studiengang_kz != -1)
							{
								$stg_obj = new studiengang($conn, $row->studiengang_kz);
								
								if($i % 2 == 0)
								{
									echo "<td align=\"left\" nowrap>&nbsp;$stg_obj->kurzbzlang</td>";
									$kurzbz=$stg_obj->kurzbz;
								}
								else
								{
									echo "<td align=\"left\" class =\"MarkLine\" nowrap>&nbsp;$stg_obj->kurzbzlang</td>";
									      $kurzbz=$stg_obj->kurzbz;
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"left\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"left\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							if($row->semester != -1)
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"center\" nowrap>&nbsp;$row->semester</td>";
								}
								else
								{
									echo "	<td align=\"center\" class=\"MarkLine\" nowrap>&nbsp;$row->semester</td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"center\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"center\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							if($row->studiengang_kz != -1)
							{
								$std_obj = new student($conn, $row->uid);
								
								$verband=$std_obj->verband;
								$gruppe=$std_obj->gruppe;
								
								$kurzbz=strtolower($kurzbz);
								$verband=strtolower($verband);
								if($i % 2 == 0)
								{
									echo "	<td align=\"center\" nowrap>&nbsp;<a href='mailto:$kurzbz$row->semester$verband$gruppe@technikum-wien.at'>$kurzbz$row->semester$verband$gruppe@technikum-wien.at</td>";
								}
								else
								{
									echo "	<td align=\"center\" class=\"MarkLine\" nowrap>&nbsp;<a href='mailto:$kurzbz$row->semester$verband$gruppe@technikum-wien.at'>$kurzbz$row->semester$verband$gruppe@technikum-wien.at</td>";
								}
							}
							else
							{
								if($i % 2 == 0)
								{
									echo "	<td align=\"center\" nowrap>&nbsp;</td>";
								}
								else
								{
									echo "	<td align=\"center\" class=\"MarkLine\" nowrap>&nbsp;</td>";
								}
							}
							
							
							echo "</tr>";
						}
							  
						echo "<tr>
								<td nowrap>&nbsp;</td>
							  </tr>";
						
						echo "</table>";
					}
				
					if($num_rows > 0)
					{
						echo "Es wurden $num_rows Eintr&auml;ge gefunden.";
					}
					else
					{
						echo "Es wurden keine Eintr&auml;ge gefunden.";
					}
				}
				else
				{
					echo "<br>Bitte geben Sie einen Suchbegriff ein, nach dem gesucht werden soll.";
				}
			?>
		</td>
	  </tr>
    </table></td>
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>