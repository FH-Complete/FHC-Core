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
 */
 
	/**
	 *	@updated 10.11.2004 (WM)
	 *	todo: unr als string?
	 *
	 */
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			


	$sql_query="SELECT studiengang_kz, kurzbz, bezeichnung FROM public.tbl_studiengang WHERE studiengang_kz>0 ORDER BY kurzbz";
	//echo $sql_query."<br>";
	$result_stg=$db->db_query($sql_query);
	if(!$result_stg)
		die("studiengang not found! ".$db->db_last_error());
	$sql_query="SELECT lehrfach_id, kurzbz,bezeichnung FROM lehre.tbl_lehrfach where aktiv=true or aktiv is null ORDER BY kurzbz";
	$result_lehrf=$db->db_query($sql_query);
	if(!$result_lehrf)
		die("lehrfach not found! ".$db->db_last_error());
	$sql_query="SELECT tbl_person.person_id, kurzbz FROM public.tbl_person, public.tbl_mitarbeiter where person_id=personalnummer and lektor=true ORDER BY kurzbz";
	$result_lektor=$db->db_query($sql_query);
	if(!$result_lektor)
		die("lehre.lektor not found! ".$db->db_last_error());
	$sql_query="SELECT ort_kurzbz FROM tbl_ort ORDER BY ort_kurzbz";
	$result_ort=$db->db_query($sql_query);
	if(!$result_ort)
		die("ort not found! ".$db->db_last_error());

	$sql_query="SELECT * FROM lehre.tbl_lehreinheit ORDER BY lehrform_kurzbz";
	$result_einheit=$db->db_query($sql_query);
	if(!$result_einheit)
		 die("Einheit not found! ".$db->db_last_error());

	
    $sql_query="SELECT lehrform_kurzbz,bezeichnung FROM lehre.tbl_lehrform where verplanen=true ORDER BY lehrform_kurzbz";
	$result_lehrform=$db->db_query($sql_query);
	if(!$result_lehrform) 
			die("Lehrform not found! ".$db->db_last_error());
    
		
	$stgid=(isset($_REQUEST['stgid'])?$_REQUEST['stgid']:1);	
	$lektorid=(isset($_REQUEST['lektorid'])?$_REQUEST['lektorid']:1);	
	$semester=(isset($_REQUEST['semester'])?$_REQUEST['semester']:0);	
	$verband=(isset($_REQUEST['verband'])?$_REQUEST['verband']:0);	
	$gruppe=(isset($_REQUEST['gruppe'])?$_REQUEST['gruppe']:0);	
	$tag=(isset($_REQUEST['tag'])?$_REQUEST['tag']:1);	
	$monat=(isset($_REQUEST['monat'])?$_REQUEST['monat']:1);	
	$jahr=(isset($_REQUEST['jahr'])?$_REQUEST['jahr']:date('Y'));

	$stdbegin=(isset($_REQUEST['stdbegin'])?$_REQUEST['stdbegin']:1);
	$stdblock=(isset($_REQUEST['stdblock'])?$_REQUEST['stdblock']:1);
	$stdsemester=(isset($_REQUEST['stdsemester'])?$_REQUEST['stdsemester']:1);
	$lehrformid=(isset($_REQUEST['lehrformid'])?$_REQUEST['lehrformid']:1);

?>

<html>
<head>
<title>Stundenplan Check</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H2>Eingabe in Stundenplan</H2>
<hr>
<form name="stdplan" method="post" action="stdplan_insert.php">
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
				echo "<option value=\"$row->studiengang_kz\" selected>$row->kurzbz $row->bezeichnung</option>";
			else
				echo "<option value=\"$row->studiengang_kz\">$row->kurzbz  $row->bezeichnung</option>";
		}
		?>
    </select></p>
    <p>Semester
    <select name="semester">
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
	  <option value="0">*</option>
      <?php
		for ($i='A';$i<'E';$i++)
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
	  <option value="0">*</option>
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
  [ Einheit
    <select name="einheit_kurzbz">
      <?php
		$num_rows=$db->db_num_rows($result_einheit);
		echo "<option value=\"-1\" selected>- auswaehlen -</option>";
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_einheit, $i);
			
			if ($stgid==$row->lehreinheit_id)
				echo "<option value=\"$row->lehreinheit_id\" selected>$row->lehrform_kurzbz</option>";
			else
				echo "<option value=\"$row->lehreinheit_id\">$row->lehrform_kurzbz</option>";
		}
		?>
    </select> ]


   Lehrfach
    <select name="lehrfachid">
      <?php
		$num_rows=$db->db_num_rows($result_lehrf);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_lehrf, $i);
			if ($_POST['lehrfachid']==$row->lehrfach_nr)
				echo "<option value=\"$row->lehrfach_nr\" selected>$row->kurzbz $row->bezeichnung</option>";
			else
				echo "<option value=\"$row->lehrfach_nr\">$row->kurzbz $row->bezeichnung</option>";
		}
		?>
    </select>

    <p>
    Unterrichtsnummer
    <input type="text" name="unr" size="15" maxlength="20" value="<?php echo $unr; ?>">
    </p>


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
    
    
    Lehrform
    <SELECT name="lehrformid">
       <?php
        while($row=$db->db_fetch_object ($result_lehrform))
        {
			if ($_POST['lehrformid']==$row->lehrform_kurzbz)
				echo "<option value=\"$row->lehrform_kurzbz\" selected>($row->lehrform_kurzbz) $row->bezeichnung</option>";
			else
				echo "<option value=\"$row->lehrform_kurzbz\">($row->lehrform_kurzbz) $row->bezeichnung</option>";
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
    <input type="text" name="tag" size="3" maxlength="2" value="<?php echo $tag; ?>">
    Monat
    <input type="text" name="monat" size="3" maxlength="2" value="<?php echo $monat; ?>">
    Jahr
    <input type="text" name="jahr" size="5" maxlength="4" value="<?php echo $jahr; ?>">
    1. Stunde
    <input type="text" name="stdbegin" size="3" maxlength="2" value="<?php echo $stdbegin; ?>">
  </p>
  <p>Stunden/Block
    <input type="text" name="stdblock" size="3" maxlength="2" value="<?php echo $stdblock; ?>">
    Stunden/Semester
    <input type="text" name="stdsemester" size="4" maxlength="3" value="<?php echo $stdsemester; ?>">
    Rythmus
    <input type="text" name="rythmus" size="2" maxlength="1" value="<?php echo $_POST['rythmus']; ?>">
    w&ouml;chig
  </p>
  <p>
    <input type="hidden" name="type" value="save">
    <input type="submit" name="Save" value="Ausf&uuml;hren">
  </p>
  <hr>
</form>
<?php
if ($type=="save")
{
	$error=false;
	$stunde=$stdbegin;
	echo "Auftrag wird ausgefuehrt!<br>";
	echo "Kontrolle auf Doppelbelegungen! ... ";

	// checken auf Ort
	$date[mday]=$_POST['tag']; $date[mon]=$_POST['monat']; $date[year]=$_POST['jahr'];
	$datum=$tag.".".$monat.".".$jahr;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$_POST['rythmus']));
			$datum=$date[mday].".".$date[mon].".".$date[year];
		}
		$sql_query="set datestyle to german;SELECT stundenplandev_id FROM tbl_stundenplandev WHERE datum='$datum' AND stunde='$std' AND ort_kurzbz='".$_POST['ortid']."'";
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
			$datum=$date[mday].".".$date[mon].".".$date[year];
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
			$datum=$date[mday].".".$date[mon].".".$date[year];
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

	//Einfuegen in die Datenbank
	if (!$error)
	{
		echo "OK!<br>";
		$date[mday]=$_POST['tag']; $date[mon]=$_POST['monat']; $date[year]=$_POST['jahr'];
		$datum=$tag.".".$monat.".".$jahr;
		for ($i=0; ($i<$stdsemester)&&!$error; $i++)
		{
			$std=$stunde+($i % $stdblock);
			if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
			{
				$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
				$date=getdate($time+(604800*$_POST['rythmus']));
				$datum=$date[mday].".".$date[mon].".".$date[year];
			}
			// todo: unr als string?
			$sql_query="INSERT INTO tbl_stundenplandev (studiengang_kz, semester, verband, gruppe, lehrfach_nr, uid, ort_kurzbz, datum, stunde,einheit_kurzbz,unr,updateamum,updatevon, lehrform_kurzbz) ".
					   "VALUES ('".$_POST['stgid']."', '".$_POST['semester']."', '".$_POST['verband']."', '".$_POST['gruppe']."', '".$_POST['lehrfachid']."', '".$_POST['lektorid']."', '".$_POST['ortid']."', '$datum', $std,".($_POST['einheit_kurzbz']==-1?'NULL':"'".$_POST['einheit_kurzbz']."'").",".($_POST['unr']==-1?'NULL':$_POST['unr']).",now(),'".$_SERVER['PHP_AUTH_USER']."','".$_POST['lehrformid']."')";
			echo $sql_query;
			$result=$db->db_query($sql_query);
			if(!$result)
			{
				echo $db->db_last_error()."<br>";
				$error=true;
			}
			else
				echo "Studiengang_ID: ".$_POST['stgid']." - Semester: ".$_POST['semester']." - Verband: ".$_POST['verband']." - Gruppe: ".$_POST['gruppe']." - Lehrfach_Nr: ".$_POST['lehrfachid']." - Lektor_ID: ".$_POST['lektorid']." - Lehrform: ".$_POST['lehrformid']." - Ort_ID: ".$_POST['ortid']." - Datum: $datum - Stunde: $std -- Eingefuegt!<br>";

		}
		if (!$error)
			echo "Einfuegen erfolgreich abgeschlossen!<br>";
		else
			echo "Es ist ein Fehler aufgetreten!<br>";
	}
}
?>
</body>
</html>
