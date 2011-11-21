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
	 *	@updated 11.11.2011 kindl
	 *
	 */
		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			

	$sql_query="SELECT studiengang_kz, UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) AS kuerzel, bezeichnung FROM public.tbl_studiengang WHERE studiengang_kz>=0 ORDER BY kuerzel";
	//echo $sql_query."<br>";
	$result_stg=$db->db_query($sql_query);
	if(!$result_stg)
		die("studiengang not found! ".$db->db_last_error());
		
		
	$stgid=(isset($_REQUEST['stgid'])?$_REQUEST['stgid']:'');	
	$semester=(isset($_REQUEST['semester'])?$_REQUEST['semester']:0);	
	$verband=(isset($_REQUEST['verband'])?$_REQUEST['verband']:0);	
	$gruppe=(isset($_REQUEST['gruppe'])?$_REQUEST['gruppe']:0);	
	$lehreinheit_id=(isset($_REQUEST['lehreinheit_id'])?$_REQUEST['lehreinheit_id']:'');	
	$type=(isset($_REQUEST['type'])?$_REQUEST['type']:'');

?>

<html>
<head>
<title>Incoming löschen</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>
<H2>Incoming löschen</H2>
<hr>
<form name="stdplan" method="post" action="incoming_delete.php">

	<p>
	Löscht den entsprechenden Incoming aus <strong>beiden</strong> LV-Plan Tabellen und auch die Gruppenzuteilung im FAS.<br/><br/>
		
	Lehreinheit aus der der Incoming gelöscht werden soll:
    <input type="text" name="lehreinheit_id" size="6" maxlength="10" value="<?php echo $lehreinheit_id; ?>"><br/>
	</p>
	<p>Gruppe des Incomings, die gelöscht werden soll (zB: BME0I1)<br/>
	Studiengang
    <select name="stgid">
		<option value=NULL>*</option>
      <?php
		if ($result_stg)
				$num_rows=$db->db_num_rows($result_stg);
		else
			$num_rows=0;
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_stg, $i);
			if ($stgid==$row->studiengang_kz)
				echo "<option value=\"$row->studiengang_kz\" selected>$row->kuerzel</option>";
			else
				echo "<option value=\"$row->studiengang_kz\">$row->kuerzel</option>";
		}
		?>
    </select>
    Semester
    <select name="semester">
		
      <?php
		for ($i=0;$i<9;$i++)
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
	  <option value=NULL>I</option>
	  
     <?php /* $verbaende=array("'A'","'B'","'C'","'D'","'F'","'V'");
		foreach ($verbaende as $i)
		{
			if (isset($_POST['verband']) && $_POST['verband']==$i)
				echo "<option value=\"$i\" selected>$i</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		} */
		?>
	</select>
    Gruppe
    <select name="gruppe">
	  <option value=NULL>*</option>
      <?php
		for ($i=1;$i<10;$i++)
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
    <input type="hidden" name="type" value="save">
    <input type="submit" name="Save" value="Löschen">
  </p>
  <hr>
</form>
<?php
if ($type=="save")
{
	$error=false;
	echo "Auftrag wird ausgefuehrt...<br>";
	if (!$error)
	{
			$sql_query="DELETE FROM lehre.tbl_stundenplandev 
						WHERE lehreinheit_id=".$_POST['lehreinheit_id']."
						AND studiengang_kz='".$_POST['stgid']."' 
						AND semester=".$_POST['semester']." 
						AND verband='I' 
						AND gruppe='".$_POST['gruppe']."';
						
						DELETE FROM lehre.tbl_stundenplan 
						WHERE lehreinheit_id=".$_POST['lehreinheit_id']."
						AND studiengang_kz='".$_POST['stgid']."' 
						AND semester=".$_POST['semester']." 
						AND verband='I' 
						AND gruppe='".$_POST['gruppe']."';
						
						DELETE FROM lehre.tbl_lehreinheitgruppe 
						WHERE lehreinheit_id=".$_POST['lehreinheit_id']."
						AND studiengang_kz='".$_POST['stgid']."' 
						AND semester=".$_POST['semester']." 
						AND verband='I' 
						AND gruppe='".$_POST['gruppe']."'";
			//echo $sql_query;
			$result=$db->db_query($sql_query);
			if(!$result)
			{
				echo $db->db_last_error()."<br>";
				$error=true;
			}
			else
				echo "<strong>Lehreinheit:</strong> ".$_POST['lehreinheit_id']." - <strong>Studiengang_Kz:</strong> ".$_POST['stgid']." - <strong>Semester:</strong> ".$_POST['semester']." - <strong>Verband:</strong> I - <strong>Gruppe:</strong> ".$_POST['gruppe']." -- <strong>Gelöscht!</strong><br>";

		if (!$error)
			echo "<br><font style='color:green'><strong>Gruppe erfolgreich gelöscht</strong></font><br>";
		else
			echo "<br><font style='color:red'><strong>Es ist ein Fehler aufgetreten!</strong></font><br>";
	}
}
?>
</body>
</html>
