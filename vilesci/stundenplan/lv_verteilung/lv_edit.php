<?php
/*
$Header: /Pfad/Kodierrichtlinien.tex,v 1.2 2004/02/29 17:05:38 pam Exp $
$Log: Kodierrichtlinien.tex,v $
Revision 1.2 2004/02/29 17:05:38 pam
Fehler in Umlauten beseitigt.
*/

/*
	Übergabewerte: $new Wenn ein Datensatz neu angelegt werden soll
	               $status   1 wenn gespeichert werdene soll
	               			 2 wenn Refresh der DropDown Menüs notwendig ist.
	               			 3 bei speichern und einfügen
	               &stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&order=$order Für Rückgabe
*/
    include('../../config.inc.php');
	include('../../../include/lv_verteilung.class.php');
	include('../../../include/lehrform.class.php');
	if (!$conn = @pg_pconnect(CONN_STRING)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
<title>LV-Verteilung</title>

<?php 

   
   //Datensatz laden
   $lv = new lv_verteilung($conn);
   if(isset($status))
   {
   		$lv->lehrveranstaltung_id = $lv_id;
   		$lv->lvnr = $lvnr;
   		$lv->lehrform = $lehrform;
   		$lv->einheit_kurzbz = $einheit_kurzbz;
   		$lv->lektor = $lektor1;
   		$lv->lehrfach_nr = $lehrfach;
   		$lv->studiengang_kz=$studiengang;
   		$lv->fachbereich_id = $fachbereich;
   		$lv->semester = $semester;
   		$lv->verband= $verband;
   		$lv->gruppe = $gruppe;
   		$lv->raumtyp = $raumtyp;
   		$lv->raumtypalternativ = $raumtypalternativ;
   		$lv->semesterstunden = $semesterstunden;
   		$lv->stundenblockung = $stundenblockung;
   		$lv->wochenrythmus = $wochenrythmus;
   		$lv->start_kw = $startkw;
   		$lv->anmerkung = $anmerkung;
   		$lv->studiensemester_kurzbz = $studiensemester;
   		$lv->unr = $unr;
   		$lv->fas_id = $fasid;
   		$lv->lehre= $lehre;
   		$lv->new = $new;
   		if($status==1)
   		{
   			
   			if($lv->save())
   			   echo "<script language='JavaScript'>window.location.href='lv_verteilung.php?saved=true&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?'&order='.$order:'')."'</script></head><body>";
   			else
   			{ 
   			   echo "</head>";
			   echo "<body>";
			   if(isset($new) && $new)
   					echo "<H1>LV Verteilung - NEW</H1><br>";
			   else
   					echo "<H1>LV Verteilung - EDIT</H1><br>";
   			   echo "\n";
   			   echo "<h2>$lv->errormsg</h2><br>";   
   			}
   			
   		}
   		else if($status==3)
   		{
	   		echo "</head>";
			echo "<body>";
			if(isset($new) && $new)
				echo "<H1>LV Verteilung - NEW</H1><br>";
			else
				echo "<H1>LV Verteilung - EDIT</H1><br>";
			echo "\n";
			if($lv->save())
		    	echo "<h2>Daten wurden gespeichert</h2>";
			else
		    	echo "<h2>$lv->errormsg</h2><br>";   
   		}
   		else 
   		{
   		   echo "</head>";
		   echo "<body>";
		   if(isset($new) && $new)
				echo "<H1>LV Verteilung - NEW</H1><br>";
		   else
				echo "<H1>LV Verteilung - EDIT</H1><br>";
		   echo "\n";
		   
   		}
   }
   else 
   {
   	   echo "</head>";
	   echo "<body>";
	   if(isset($new) && $new)
   			echo "<H1>LV Verteilung - NEW</H1><br>";
	   else
   			echo "<H1>LV Verteilung - EDIT</H1><br>";
   	   echo "\n";
   	   
	   if(!isset($new))
	   {
	      if(isset($lvid))
	         $lv->load($lvid);
	      else 
	         die("Fehler bei der Parameterübergabe");
	   }
	   else 
	   {
	   	  if($new)
	   	  {
	         $lv->new=true;
	         
	         $lv->studiengang_kz = ($stg!=-1?$stg:'227');
	         $lv->semester=$sem;
	         $lv->lvnr=0;
	         $lv->semesterstunden = 0;
	         $lv->stundenblockung = 0;
	         $lv->wochenrythmus = 1;
	         $lv->start_kw = 1;
	         $lv->unr = 0;
	         $lv->lehre = "on";
	         
	         $sql_query="select studiensemester_kurzbz from tbl_studiensemester where start<now() ORDER BY start DESC LIMIT 1";
			 $result=pg_exec($conn,$sql_query);
			 if($row=pg_fetch_object($result))
				$lv->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			 else 
				$lv->studiensemester_kurzbz=-1;
	          
	   	  }	   	  
	   }
   }
   //Formular anzeigen
   echo "<a href='lv_verteilung.php?stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?'&order='.$order:'')."' class='linkgreen'><- Zurück zur Übersicht</a><br>";
   echo '<table width="100%"  border="0" cellspacing="0" cellpadding="0">';
   echo "\n";
   echo "<tr><td><form name='form1' action='lv_edit.php?stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?'&order='.$order:'')."' method='POST'><input type='hidden' name='status' value='1'><input type='hidden' name='new' value='$new'></td></tr>";
   echo "\n";
   //echo "<tr><td>Lehrveranstaltungs ID</td><td><input type='text' name='lv_id' value='$lv->lehrveranstaltung_id' readonly></td></tr>";
   echo "<tr><td><input type='hidden' name='lv_id' value='$lv->lehrveranstaltung_id' readonly></td></tr>";
   echo "\n";
   echo "<tr><td>Lvnr</td><td><input type='text' name='lvnr' value='$lv->lvnr'></td></tr>";
   echo "\n";
   echo "<tr><td>Lektor</td><td><select name='lektor1'>";
   $sql_query = "SELECT a.uid, vornamen, nachname FROM tbl_mitarbeiter as a, tbl_person b WHERE lektor AND a.uid=b.uid ORDER BY nachname";
   $result = pg_exec($conn, $sql_query);
   
   while($row=pg_fetch_object($result))
   {
      if($lv->lektor==$row->uid)
   		echo "<option value='$row->uid' selected>$row->nachname $row->vornamen ($row->uid)</option>";
      else 
        echo "<option value='$row->uid'>$row->nachname $row->vornamen ($row->uid)</option>";
   }
   
   echo "</select></td></tr>";
   echo "\n";
   echo "<tr><td>Studiengang</td><td><select name='studiengang' onChange='javascript:document.form1.status.value=\"2\";document.form1.submit();'>";
   $sql_query = "SELECT studiengang_kz, kurzbz FROM tbl_studiengang ORDER BY kurzbz";
   $result = pg_exec($conn, $sql_query);
   
   while($row=pg_fetch_object($result))
   {
      if($lv->studiengang_kz==$row->studiengang_kz)
   		echo "<option value='$row->studiengang_kz' selected>$row->kurzbz</option>";
      else 
        echo "<option value='$row->studiengang_kz'>$row->kurzbz</option>";
   }
   
   echo "</select></td></tr>";
   echo "\n";
   echo "<tr><td>Semester</td><td><select name='semester' onChange='javascript:document.form1.status.value=\"2\";document.form1.submit();'>";
   $sql_query = "SELECT max_semester FROM tbl_studiengang where studiengang_kz='$lv->studiengang_kz'";
   $result = pg_exec($conn, $sql_query);
   $row = pg_fetch_object($result);
   echo "<option value='0'>0</option>";
   for($i=0;$i<$row->max_semester;$i++)
   {
      if($lv->semester==$i+1)
   		echo "<option value='".($i+1)."' selected>".($i+1)."</option>";
      else 
        echo "<option value='".($i+1)."'>".($i+1)."</option>";
   }
   
   echo "</select></td></tr>";
   echo "\n";
   echo "<tr><td>Verband</td><td><input type='text' value='$lv->verband' name='verband' size='5'></td></tr>";
   echo "\n";
   echo "<tr><td>Gruppe</td><td><input type='text' value='$lv->gruppe' name='gruppe' size='5'></td></tr>";
   
   echo "\n";
   echo "<tr><td>Einheit kurzbz</td><td><select name='einheit_kurzbz'>";
   echo "<option value=''>--keine Auswahl--</option>";
   $sql_query = "SELECT einheit_kurzbz FROM tbl_einheit WHERE studiengang_kz='$lv->studiengang_kz' AND semester='$lv->semester'";
   $result = pg_exec($conn, $sql_query);
   
   while($row=pg_fetch_object($result))
   {
      if($lv->einheit_kurzbz==$row->einheit_kurzbz)
   		echo "<option value='$row->einheit_kurzbz' selected>$row->einheit_kurzbz</option>";
      else 
        echo "<option value='$row->einheit_kurzbz'>$row->einheit_kurzbz</option>";
   }

   echo "</select></td></tr>";
    
    // echo "<tr><td>einheit_kurzbz</td><td><input type='text' name='einheit_kurzbz' value='$lv->einheit_kurzbz'></td></tr>";
   echo "\n";
   echo "<tr><td>Lehrfach</td><td><select name='lehrfach'>";

   $sql_query = "SELECT lehrfach_nr, bezeichnung, kurzbz FROM tbl_lehrfach WHERE studiengang_kz='$lv->studiengang_kz' AND semester='$lv->semester' ORDER BY bezeichnung";
   $result = pg_exec($conn, $sql_query);
   echo "<option value='0'>--keine Auswahl--</option>";
   while($row=pg_fetch_object($result))
   {
      if($lv->lehrfach_nr==$row->lehrfach_nr)
   		echo "<option value='$row->lehrfach_nr' selected>$row->kurzbz - $row->bezeichnung</option>";
      else 
        echo "<option value='$row->lehrfach_nr'>$row->kurzbz - $row->bezeichnung</option>";
   }

   echo "</select></td></tr>";
   
   echo "\n";
   echo "<tr><td>Lehrform</td><td><select name='lehrform'>";
	
   $form_obj=new lehrform($conn);
   
   if(!$form_result=$form_obj->getAll())
   		echo "Fehler beim laden der Lehrform: $form_obj->errormsg";

  
   foreach($form_result as $row)
   {
      if($lv->lehrform==$row->kurzbz)
   		echo "<option value='$row->kurzbz' selected>$row->kurzbz - $row->bezeichnung</option>";
      else 
        echo "<option value='$row->kurzbz'>$row->kurzbz - $row->bezeichnung</option>";
   }

   echo "</select></td></tr>";
   
   echo "\n";
   echo "<tr><td>Fachbereich</td><td><select name='fachbereich'>";
   $sql_query = "SELECT bezeichnung, fachbereich_id FROM tbl_fachbereich WHERE NOT bezeichnung='' ORDER BY bezeichnung";
   $result = pg_exec($conn, $sql_query);
   echo "<option value='0'>--keine Auswahl--</option>";
   while($row=pg_fetch_object($result))
   {
      if($lv->fachbereich_id==$row->fachbereich_id)
   		echo "<option value='$row->fachbereich_id' selected>$row->bezeichnung</option>";
      else 
        echo "<option value='$row->fachbereich_id'>$row->bezeichnung</option>";
   }
   
   echo "</select></td></tr>";
   
   
   echo "\n";
   echo "<tr><td>Raumtyp</td><td><select name='raumtyp'>";
   $sql_query = "SELECT raumtyp_kurzbz, beschreibung FROM tbl_raumtyp ORDER BY raumtyp_kurzbz";
   $result = pg_exec($conn, $sql_query);
   while ($row = pg_fetch_object($result))
   {
      if($lv->raumtyp==$row->raumtyp_kurzbz)
   		echo "<option value='$row->raumtyp_kurzbz' selected>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
      else 
        echo "<option value='$row->raumtyp_kurzbz'>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
   }
   
   echo "</select></td></tr>";
   echo "\n";
   echo "<tr><td>Raumtyp Alternativ</td><td><select name='raumtypalternativ'>";
   $sql_query = "SELECT raumtyp_kurzbz, beschreibung FROM tbl_raumtyp ORDER BY raumtyp_kurzbz";
   $result = pg_exec($conn, $sql_query);
   while ($row = pg_fetch_object($result))
   {
      if($lv->raumtypalternativ==$row->raumtyp_kurzbz)
   		echo "<option value='$row->raumtyp_kurzbz' selected>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
      else 
        echo "<option value='$row->raumtyp_kurzbz'>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
   }
   
   echo "</select></td></tr>";
   echo "\n";
   echo "<tr><td>Semesterstunden</td><td><input type='text' value='$lv->semesterstunden' name='semesterstunden' size='5'></td></tr>";
   echo "\n";
   echo "<tr><td>Stundenblockung</td><td><input type='text' value='$lv->stundenblockung' name='stundenblockung' size='5'></td></tr>";
   echo "\n";
   echo "<tr><td>Wochenrythmus</td><td><input type='text' value='$lv->wochenrythmus' name='wochenrythmus' size='5'></td></tr>";
   echo "\n";
   echo "<tr><td>StartKW</td><td><input type='text' value='$lv->start_kw' name='startkw' size='5'></td></tr>";
   echo "\n";
   echo "<tr><td>Anmerkung</td><td><input type='text' value='$lv->anmerkung' name='anmerkung'></td></tr>";
   echo "\n";
   echo "<tr><td>Studiensemester</td><td><select name='studiensemester'>";
   $sql_query = "SELECT studiensemester_kurzbz from tbl_studiensemester";
   $result = pg_exec($conn, $sql_query);
   while ($row = pg_fetch_object($result))
   {
      if($lv->studiensemester_kurzbz==$row->studiensemester_kurzbz)
   		echo "<option value='$row->studiensemester_kurzbz' selected>$row->studiensemester_kurzbz</option>";
      else 
        echo "<option value='$row->studiensemester_kurzbz'>$row->studiensemester_kurzbz</option>";
   }
   
   echo "</select></td></tr>";
   echo "\n";
   //echo "<tr><td>FasId</td><td><input type='text' value='$lv->fas_id' name='fasid'></td></tr>";
   echo "\n";
   echo "<tr><td>UNr</td><td><input type='text' value='$lv->unr' name='unr'></td></tr>";
   echo "\n";
   if($lv->lehre=='t')
   		$lv->lehre='on';
   echo "<tr><td>Lehre</td><td><input type='checkbox' name='lehre'". ($lv->lehre=='on'?'checked':'')."></td></tr>";
   echo "<tr><td>&nbsp;</td></tr>";
   echo "\n";
   echo "<tr><td>&nbsp;</td><td><input type='submit' value='Speichern'>";
   if(isset($new) && $new)
      echo "<input type='button' value='Speichern und Einf&uuml;gen' OnClick='javascript:document.form1.status.value=\"3\";document.form1.submit();'";
   echo "</td></tr>";
   echo "</table>";
   echo "</form>";
?>
</body></html>