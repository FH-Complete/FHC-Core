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
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			

		$sql_query="SELECT * FROM tbl_studiengang WHERE studiengang_kz>0 ORDER BY kurzbz";
		$result_stg=$db->db_query($sql_query);
		if(!$result_stg)
					die("studiengang not found! ".$db->db_last_error());
				

	$type=(isset($_REQUEST['type'])?$_REQUEST['type']:'');				

	$mode=(isset($_REQUEST['mode'])?$_REQUEST['mode']:'');				
	$stg_kz=(isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'0');				
	
	$tagv=(isset($_REQUEST['tagv'])?$_REQUEST['tagv']:'1');				
	$monatv=(isset($_REQUEST['monatv'])?$_REQUEST['monatv']:'1');				
	$jahrv=(isset($_REQUEST['jahrv'])?$_REQUEST['jahrv']:date('Y'));				


	$tagb=(isset($_REQUEST['tagb'])?$_REQUEST['tagb']:'31');				
	$monatb=(isset($_REQUEST['monatb'])?$_REQUEST['monatb']:'12');				
	$jahrb=(isset($_REQUEST['jahrb'])?$_REQUEST['jahrb']:date('Y'));				

	if ($mode=='del')
	{
		$sql_query="DELETE FROM lehre.tbl_stundenplan WHERE studiengang_kz=$stg_kz AND datum>='$jahrv-$monatv-$tagv' AND datum<='$jahrb-$monatb-$tagb'";
		//echo $sql_query.'<BR>';
		
		if ($result=$db->db_query($sql_query))
				$anz=$db->db_affected_rows($result);
		else
				$anz=0;
		echo $anz.' Records deleted! '.$db->db_last_error().'<hr>';
	}
	?>
<html>
<head>
<title>Delete Stundenplan</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<H1>Delete from Stundenplan</H1>
<hr>
<form name="stdplan" method="post" action="stdplan_delete.php">
  <p>Studiengang
    <select name="stg_kz">
      <?php
		$num_rows=$db->db_num_rows($result_stg);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_stg, $i);
			echo "<option ". ($stg_kz==$row->studiengang_kz?'  selected=\" selected\" ' :'')." value=\"$row->studiengang_kz\">$row->kurzbz</option>";
		}
		?>
    </select>
  </p>
  <p> Von (inkl): Tag
	  <input type="text" name="tagv" size="3" maxlength="2" value="<?php echo $tagv; ?>">
    Monat
    <input type="text" name="monatv" size="3" maxlength="2" value="<?php echo $monatv; ?>">
    Jahr
    <input type="text" name="jahrv" size="5" maxlength="4" value="<?php echo $jahrv; ?>">
  </p>
  <p> Bis (inkl): Tag
    <input type="text" name="tagb" size="3" maxlength="2" value="<?php echo $tagb; ?>">
    Monat
    <input type="text" name="monatb" size="3" maxlength="2" value="<?php echo $monatb; ?>">
    Jahr
    <input type="text" name="jahrb" size="5" maxlength="4" value="<?php echo $jahrb; ?>">
  </p>
  <p>
    <input type="hidden" name="mode" value="del">
    <input type="submit" name="Save" value="Ausf&uuml;hren">
  </p>
  <hr>
</form>
<?php
if ($type=="save")
{
	$error=false;
	$stunde=$stdbegin;
	echo "Auftrag wird ausgeführt!<br>";
	echo "Kontrolle auf Doppelbelegungen! ... ";

	// checken auf Ort
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM stundenplan WHERE datum='$datum' AND stunde_id='$std' AND ort_id='$ortid'";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			echo "error!<br>Doppelbelegung gefunden auf Ort=$ortid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}
	}
	// checken auf Lehrfach
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM stundenplan WHERE datum='$datum' AND stunde_id='$std' AND lehrfach_id='$lehrfachid'";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			echo "error!<br>Doppelbelegung gefunden auf Lehrfach=$lehrfachid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}
	}
	// checken auf Verband
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT semester, verband, gruppe, studiengang_kz FROM tbl_stundenplan WHERE datum='$datum' AND stunde_id='$std' AND studiengang_kz='$stg_kz' AND semester='$semester' AND (verband='$verband' OR verband=NULL) AND (gruppe='$gruppe' OR gruppe=NULL)";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			$row=$db->db_fetch_object($result,0);
			echo "error!<br>Doppelbelegung gefunden auf Datum=$datum - Stunde=$stunde - StudiengangID=$row->studiengang_id - Semester=$row->semester Verband=$row->verband Gruppe=$row->gruppe!<br>";
			$error=true;
		}
	}

	// checken auf Ort im Einheitenplan
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM einheitenplan WHERE datum='$datum' AND stunde_id='$std' AND ort_id='$ortid'";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			echo "error!<br>Doppelbelegung gefunden im Einheitenplan auf Ort=$ortid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}
	}
	// checken auf Lehrfach im Einheitenplan
	$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
	$datum=$jahr."-".$monat."-".$tag;
	for ($i=0; ($i<$stdsemester)&&!$error; $i++)
	{
		$std=$stunde+($i % $stdblock);
		if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
		{
			$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
			$date=getdate($time+(604800*$rythmus));
			$datum=$date[year]."-".$date[mon]."-".$date[mday];
		}
		$sql_query="SELECT id FROM einheitenplan WHERE datum='$datum' AND stunde_id='$std' AND lehrfach_id='$lehrfachid'";
		$result=$db->db_query($sql_query);
		if($result && ($db->db_num_rows($result)>0))
		{
			echo "error!<br>Doppelbelegung gefunden im Einheitenplan auf Lehrfach=$lehrfachid Datum=$datum Stunde=$stunde!<br>";
			$error=true;
		}
	}

	//Einfügen in die Datenbank
	if (!$error)
	{
		echo "OK!<br>";
		$date[mday]=$tag; $date[mon]=$monat; $date[year]=$jahr;
		$datum=$jahr."-".$monat."-".$tag;
		for ($i=0; ($i<$stdsemester)&&!$error; $i++)
		{
			$std=$stunde+($i % $stdblock);
			if ( ($std==$stunde) && (($i>0)||($stdblock==1)) )
			{
				$time=mktime(0, 0, 0, $date[mon], $date[mday], $date[year]);
				$date=getdate($time+(604800*$rythmus));
				$datum=$date[year]."-".$date[mon]."-".$date[mday];
			}
			if (($verband=='0') && ($gruppe==0))
				$sql_query="INSERT INTO stundenplan (studiengang_id, semester, verband, gruppe, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$stgid', '$semester', NULL, NULL, '$lehrfachid', '$ortid', '$datum', '$std')";
			elseif ($gruppe=0)
				$sql_query="INSERT INTO stundenplan (studiengang_id, semester, verband, gruppe, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$stgid', '$semester', '$verband', NULL, '$lehrfachid', '$ortid', '$datum', '$std')";
			else
				$sql_query="INSERT INTO stundenplan (studiengang_id, semester, verband, gruppe, lehrfach_id, ort_id, datum, stunde_id) VALUES ('$stgid', '$semester', '$verband', '$gruppe', '$lehrfachid', '$ortid', '$datum', '$std')";
			//echo $sql_query;
			$result=$db->db_query($sql_query);
			if(!$result)
			{
				echo $db->db_last_error()."<br>";
				$error=true;
			}
			else
				echo "Studiengang_ID: $stgid - Semester: $semester - Verband: $verband - Gruppe: $gruppe - Lehrfach_ID: $lehrfachid - Ort_ID: $ortid - Datum: $datum - Stunde: $std -- Eingefuegt!<br>";

		}
		if (!$error)
			echo "Einfügen erfolgreich abgeschlossen!<br>";
		else
			echo "Es ist ein Fehler aufgetreten!<br>";
	}
}
?>
</body>
</html>
