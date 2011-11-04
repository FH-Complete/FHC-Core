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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *			Manfred Kindl < manfred.kindl@technikum-wien.at >
 */
 
	/**
	 *	@updated 04.11.2011 (WM)
	 *
	 */
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			


	$sql_query="SELECT studiengang_kz, UPPER(oe_kurzbz) AS oe_kurzbz, bezeichnung FROM public.tbl_studiengang WHERE studiengang_kz>=0 ORDER BY oe_kurzbz";
	//echo $sql_query."<br>";
	$result_stg=$db->db_query($sql_query);
	if(!$result_stg)
		die("studiengang not found! ".$db->db_last_error());
		
	$sql_query="SELECT uid, person_id, kurzbz FROM campus.vw_mitarbeiter WHERE aktiv=true ORDER BY kurzbz";
	$result_lektor=$db->db_query($sql_query);
	if(!$result_lektor)
		die("lehre.lektor not found! ".$db->db_last_error());
		
	$sql_query="SELECT ort_kurzbz FROM tbl_ort WHERE aktiv=true ORDER BY ort_kurzbz";
	$result_ort=$db->db_query($sql_query);
	if(!$result_ort)
		die("ort not found! ".$db->db_last_error());

	    
		
	$stgid=(isset($_REQUEST['stgid'])?$_REQUEST['stgid']:0);	
	$lektorid=(isset($_REQUEST['lektorid'])?$_REQUEST['lektorid']:1);	
	$semester=(isset($_REQUEST['semester'])?$_REQUEST['semester']:'');	
	$verband=(isset($_REQUEST['verband'])?$_REQUEST['verband']:0);	
	$gruppe=(isset($_REQUEST['gruppe'])?$_REQUEST['gruppe']:0);	
	$tag=(isset($_REQUEST['tag'])?$_REQUEST['tag']:date('d'));	
	$monat=(isset($_REQUEST['monat'])?$_REQUEST['monat']:date('m'));	
	$jahr=(isset($_REQUEST['jahr'])?$_REQUEST['jahr']:date('Y'));
	$titel=(isset($_REQUEST['titel'])?$_REQUEST['titel']:'');	
	$beschreibung=(isset($_REQUEST['beschreibung'])?$_REQUEST['beschreibung']:'');
	$type=(isset($_REQUEST['type'])?$_REQUEST['type']:'');

	$stdbegin=(isset($_REQUEST['stdbegin'])?$_REQUEST['stdbegin']:1);
	$stdblock=(isset($_REQUEST['stdblock'])?$_REQUEST['stdblock']:2);
	//$stdsemester=(isset($_REQUEST['stdsemester'])?$_REQUEST['stdsemester']:$stdblock);

?>

<html>
<head>
<title>Insert Reservierungen</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H2>Reservierungen einfügen</H2>
<hr>
<form name="stdplan" method="post" action="reservierung_insert.php">
  <p>Studiengang
    <select name="stgid">
      <?php
		if ($result_stg)
				$num_rows=$db->db_num_rows($result_stg);
		else
			$num_rows=0;
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_stg, $i);
			if ($stgid==$row->studiengang_kz)
				echo "<option value=\"$row->studiengang_kz\" selected>$row->oe_kurzbz - $row->bezeichnung</option>";
			else
				echo "<option value=\"$row->studiengang_kz\">$row->oe_kurzbz - $row->bezeichnung</option>";
		}
		?>
    </select></p>
    <p>Semester
    <select name="semester">
		<option value=NULL>*</option>
      <?php
		for ($i=1;$i<9;$i++)
		{
			if (isset($_POST['semester']) && $_POST['semester']==$i)
				echo "<option value=\"$i\" selected>$i</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
    </select>
    Verband
    <select name="verband">
	  <option value=NULL>*</option>
      <?php $verbaende=array("'A'","'B'","'C'","'D'","'F'","'V'");
		foreach ($verbaende as $i)
		{
			if (isset($_POST['verband']) && $_POST['verband']==$i)
				echo "<option value=\"$i\" selected>$i</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
	</select>
    Gruppe
    <select name="gruppe">
	  <option value=NULL>*</option>
      <?php
		for ($i=1;$i<3;$i++)
		{
			if (isset($_POST['gruppe']) && $_POST['gruppe']==$i)
				echo "<option value=\"$i\" selected>$i</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
    </select>
  </p>
  <p>

	Titel
    <input type="text" name="titel" size="12" maxlength="10" value="<?php echo $titel; ?>">
    Beschreibung
    <input type="text" name="beschreibung" size="35" maxlength="32" value="<?php echo $beschreibung; ?>">
    	
    Lektor
    <SELECT name="lektorid">
      <?php
		$num_rows=$db->db_num_rows($result_lektor);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_lektor, $i);
			if ($_POST['lektorid']==$row->uid)
				echo "<option value=\"$row->uid\" selected>$row->kurzbz</option>";
			else
				echo "<option value=\"$row->uid\">$row->kurzbz</option>";
		}
	  	?>
    </SELECT>
    
  </p>
	<p>
	Ort
    <select name="ortid">
      <?php
		$num_rows=$db->db_num_rows($result_ort);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_ort, $i);
			if ($_POST['ortid']==$row->ort_kurzbz)
				echo "<option value=\"$row->ort_kurzbz\" selected>$row->ort_kurzbz</option>";
			else
				echo "<option value=\"$row->ort_kurzbz\">$row->ort_kurzbz</option>";
		}
	  	?>
    </select>


    Tag
    <input type="text" name="tag" size="2" maxlength="2" value="<?php echo $tag; ?>">
    Monat
    <input type="text" name="monat" size="2" maxlength="2" value="<?php echo $monat; ?>">
    Jahr
    <input type="text" name="jahr" size="4" maxlength="4" value="<?php echo $jahr; ?>">
	</p>
	<p>
    Einheit Beginn
    <input type="text" name="stdbegin" size="2" maxlength="2" value="<?php echo $stdbegin; ?>">
	Anzahl Einheiten
    <input type="text" name="stdblock" size="2" maxlength="2" value="<?php echo $stdblock; ?>">
<!--    Stunden/Semester
    <input type="hidden" name="stdsemester" size="4" maxlength="3" value="<?php echo $stdsemester; ?>">
    Rythmus
    <input type="text" name="rythmus" size="2" maxlength="1" value="<?php echo $_POST['rythmus']; ?>">
    w&ouml;chig-->
  </p>
  <p>
    <input type="hidden" name="type" value="save">
    <input type="submit" name="Save" value="Ausführen">
  </p>
  <hr>
</form>
<?php
if ($type=="save")
{
	$error=false;
	$stunde=$stdbegin;
	echo "Auftrag wird ausgefuehrt!<br>";
//	echo "Kontrolle auf Doppelbelegungen! ... ";

/*	// checken auf Ort
	$date[mday]=$_POST['tag']; $date[mon]=$_POST['monat']; $date[year]=$_POST['jahr'];
	$datum=$tag.".".$monat.".".$jahr;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$_POST['rythmus']));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
   
		$sql_query="SELECT stundenplandev_id FROM tbl_stundenplandev WHERE datum='$datum' AND stunde='$std' AND ort_kurzbz='".$_POST['ortid']."'";
		if ($_POST['unr']=='')
			$sql_query.=" AND unr IS NOT NULL";
		else
			$sql_query.=" AND unr!=".$_POST['unr'];
		echo $sql_query;
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			echo "error!<br>Doppelbelegung gefunden auf Ort=".$_POST['ortid']." Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}
	}

	// checken auf Lektor im Stundenplan
	$date[mday]=$_POST['tag']; $date[mon]=$_POST['monat']; $date[year]=$_POST['jahr'];
	$datum=$tag.".".$monat.".".$jahr;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$_POST['rythmus']));
      $datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT stundenplandev_id FROM tbl_stundenplandev WHERE datum='$datum' AND stunde='$std' AND uid='".$_POST['$lektorid']."'";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			echo "error!<br>Doppelbelegung gefunden auf Lektor=".$_POST['$lektorid']." Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}
	}

	// checken auf Verband
	$date[mday]=$_POST['tag']; $date[mon]=$_POST['monat']; $date[year]=$_POST['jahr'];
	$datum=$tag.".".$monat.".".$jahr;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$_POST['rythmus']));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT semester, verband, gruppe, tbl_stundenplandev.studiengang_kz,tbl_studiengang.kurzbz FROM tbl_stundenplandev JOIN tbl_studiengang using(studiengang_kz) WHERE datum='$datum' AND stunde='$std' AND studiengang_kz='".$_POST['stgid']."' AND semester='$semester' AND (verband='".$_POST['verband']."' OR verband=NULL) AND (gruppe='".$_POST['gruppe']."' OR gruppe=NULL)";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			$row=$db->db_fetch_object($result,0);
			echo "error!<br>Doppelbelegung gefunden auf Datum=$datum - Stunde=$stunde - Studiengang=$row->kurzbz - Semester=$row->semester Verband=$row->verband Gruppe=$row->gruppe!<br>";
			$error=true;
		}
	}
*/
	//Einfuegen in die Datenbank
	if (!$error)
	{
		echo "OK!<br><br>";
		$date['mday']=$_POST['tag']; $date['mon']=$_POST['monat']; $date['year']=$_POST['jahr'];
		$datum=$jahr."-".$monat."-".$tag;
		for ($i=0; ($i<$stdblock)&&!$error; $i++)
		{
			$std=$stdbegin+($i % $stdblock);
			if ( ($std==$stdbegin) && (($i>0)||($stdblock==1)) )
			{
				$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
				$date=getdate($time+(604800*$_POST['rythmus']));
				$datum=$date[year]."-".$date[mon]."-".$date[mday];
			}
			$sql_query="INSERT INTO campus.tbl_reservierung(ort_kurzbz,studiengang_kz,uid,stunde,datum,titel,beschreibung,semester,verband,gruppe,insertamum,insertvon) ".
					   "VALUES (
					   '".$_POST['ortid']."',
					   '".$_POST['stgid']."', 
					   '".$_POST['lektorid']."',	
						$std,				   
					   '$datum',					   					   
					   '".$_POST['titel']."', 
					   '".$_POST['beschreibung']."', 
					   ".$_POST['semester'].", 
					   ".$_POST['verband'].", 
					   ".$_POST['gruppe'].", 
					   now(),
					   '".$_SERVER['PHP_AUTH_USER']."')";
			//echo $sql_query;
			$result=$db->db_query($sql_query);
			if(!$result)
			{
				echo $db->db_last_error()."<br>";
				$error=true;
			}
			else
				echo "<strong>Ort:</strong> ".$_POST['ortid']." - <strong>Studiengang_Kz:</strong> ".$_POST['stgid']." - <strong>Semester:</strong> ".$_POST['semester']." - <strong>Verband:</strong> ".$_POST['verband']." - <strong>Gruppe:</strong> ".$_POST['gruppe']." - <strong>Lektor:</strong> ".$_POST['lektorid']." - <strong>Titel:</strong> ".$_POST['titel']." - <strong>Beschreibung:</strong> ".$_POST['beschreibung']." - <strong>Datum:</strong> $datum - <strong>Stunde:</strong> $std -- <strong>Eingefügt!</strong><br>";

		}
		if (!$error)
			echo "<br><font style='color:green'><strong>Einfügen erfolgreich abgeschlossen!</strong></font><br>";
		else
			echo "<br><font style='color:red'><strong>Es ist ein Fehler aufgetreten!</strong></font><br>";
	}
}
?>
</body>
</html>
