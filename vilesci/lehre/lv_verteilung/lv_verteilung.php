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
 
 
 
// *************************************
// * Zeigt alle Lehreinheiten und die
// * zugehoerigen Gruppen/Lektoren an
// * Filtermoeglichkeit nach Studiengang,
// * Semester, Lektor, Studiensemester
// *************************************

	require_once('../../../config/vilesci.config.inc.php');
    	require_once('../../../include/functions.inc.php');
    	require_once('../../../include/studiensemester.class.php');
    	require_once('../../../include/studiengang.class.php');
    	require_once('../../../include/lehreinheit.class.php');    
    	require_once('../../../include/lehrform.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		

	$user=get_uid();

	

	$stg=(isset($_REQUEST['stg']) ? $_REQUEST['stg'] :-1 );
	$stsem=(isset($_REQUEST['stsem']) ? $_REQUEST['stsem'] :-1 );
	if (!isset($_REQUEST['stsem']))
	{
		$stsem_obj = new studiensemester();
		if (!$stsem = $stsem_obj->getakt())
			$stsem = $stsem_obj->getaktorNext();
	}

	$lektor=(isset($_REQUEST['lektor']) ? $_REQUEST['lektor'] :-1 );
	$stg_kz=(isset($_REQUEST['studiengang']) ? $_REQUEST['studiengang'] :-1 );
	$sem=(isset($_REQUEST['semester']) ? $_REQUEST['semester'] :(isset($_REQUEST['sem']) ? $_REQUEST['sem'] :-1 ) );	
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
<script language="JavaScript" type="text/javascript">

function conf_del()
{
	return confirm('Wollen Sie diesen Datensatz wirklich loeschen?');
}
</script>
<title>Lehreinheiten-Uebersicht</title>
</head>
<body>

<H1>Lehreinheiten Uebersicht</h1>
<?php
//Daten Speichern
if(isset($_GET['edit']) && (isset($_POST['btn_submit']) || (isset($_POST['status']) && $_POST['status']==3)))
{
	$le_obj = new lehreinheit();
	if(isset($_GET['le_id']) && $_GET['le_id']!='') //Update eines bestehenden Datensatzes
	{
		if(!is_numeric($_GET['le_id']))
			die('Fehler bei Parameteruebergabe: le_id ist ungueltig');
		$le_obj->load($_GET['le_id']);
		$le_obj->new=false;
		$le_obj->lehreinheit_id = $_GET['le_id'];
	}
	else //Neue Lehreinheit anlegen
	{
		$le_obj->new=true;
		$le_obj->insertamum=date('Y-m-d H:i:s');
		$le_obj->insertvon=$user;
	}
	
	//Daten Pruefen
	if(!is_numeric($_POST['lv_id']))
		die('Fehler bei Parameteruebergabe: lv_id ist ungueltig');
	
	$le_obj->lehrveranstaltung_id=$_POST['lv_id'];
	$le_obj->studiensemester_kurzbz=$_POST['studiensemester'];
	if(!is_numeric($_POST['lehrfach']))
		die('Fehler bei Parameteruebergabe: lehrfach ist ungueltig');
	$le_obj->lehrfach_id=$_POST['lehrfach'];
	$le_obj->lehrform_kurzbz = $_POST['lehrform'];
	if(!is_numeric($_POST['stundenblockung'])&& $_POST['stundenblockung']!='')
		die('Fehler bei Parameteruebergabe: stundenblockung ist ungueltig!');
	$le_obj->stundenblockung = $_POST['stundenblockung'];
	if(!is_numeric($_POST['wochenrythmus'])&& $_POST['wochenrythmus']!='')
		die('Fehler bei Parameteruebergabe: wochenrythmus ist ungueltig');
	$le_obj->wochenrythmus = $_POST['wochenrythmus'];
	if(!is_numeric($_POST['startkw']) && $_POST['startkw']!='')
		die('Fehler bei Parameteruebergabe: startkw ist ungueltig');
	$le_obj->start_kw = $_POST['startkw'];
	$le_obj->raumtyp = $_POST['raumtyp'];
	$le_obj->raumtypalternativ = $_POST['raumtypalternativ'];
	$le_obj->sprache=$_POST['sprache'];
	$le_obj->lehre=isset($_POST['lehre']);
	$le_obj->anmerkung=$_POST['anmerkung'];
	$le_obj->unr=$_POST['unr'];
	$le_obj->lvnr=$_POST['lvnr'];
	$le_obj->updateamum=date('Y-m-d H:i:s');
	$le_obj->updatevon=$user;
	
	if(!isset($_POST['status']) || $_POST['status']!=2)
	{
		//Datensatz Speichern
		if(!$le_obj->save())
		{
			echo "Fehler beim Speichern: $le_obj->errormsg";
		}
		else 
		{
			//Status 3 = Speichern und Einfuegen
			//Bei status != 3 die Uebersichtsseite wieder anzeigen
			//ansonsten das Formular zum Neu anlegen erneut anzeigen	
			if(!isset($_POST['status']) || $_POST['status']!=3)
			{	
				unset($_GET['edit']);
				unset($_GET['new']);
				$stg = $_POST['studiengang'];
				$sem = $_POST['semester'];
				$stsem = $_POST['studiensemester'];
			}
			else 
				echo "<br><h2>Daten wurden gespeichert</h2><br>";
		}
	}
	
}

//Formular anzeigen
if(isset($_GET['edit']) || isset($_GET['new']))
{
	//Editier / Neu Ansicht
	if(isset($_GET['edit']))
	{
		//Datensatz laden
		$new=false;
		$le_obj = new lehreinheit($_GET['le_id']);
	}
	else 
	{
		$le_obj = new lehreinheit();
		$new=true;
	}
	
	//Formular anzeigen
	echo "<a href='lv_verteilung.php?stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?'&order='.$order:'')."' class='linkgreen'><- Zur&uuml;ck zur &Uuml;bersicht</a><br>";
	echo '<table width="100%"  border="0" cellspacing="2" cellpadding="1">';
	echo "\n";
	echo "<tr><td><form name='form1' action='lv_verteilung.php?edit=true&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&le_id=$le_obj->lehreinheit_id".(isset($order)?'&order='.$order:'').(isset($_GET['new'])?'&new=true':'')."' method='POST'><input type='hidden' name='status' value='1'><input type='hidden' name='new' value='$new'></td></tr>";
	echo "\n";
	echo "<tr><td>Lvnr</td><td><input type='text' name='lvnr' value='$le_obj->lvnr'></td></tr>";
	echo "\n";


	
	
	//Wenn kein Studiengang/Semester angegeben ist
	if(!isset($stg_kz) || !isset($sem) || $stg_kz=='' || $stg_kz==-1 || $sem=='' || $sem==-1)
	{
		if($le_obj->lehrveranstaltung_id!='') //Bei Edit-Mode Studiengang und Semester der Lehreinheit laden
		{
			$qry = "SELECT studiengang_kz, semester FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$le_obj->lehrveranstaltung_id'";
			if ($result = $db->db_query($qry))
			{
				$row=$db->db_fetch_object($result);
				$stg_kz = $row->studiengang_kz;
				$semester=$row->semester;
			}	
		}
		else 
		{
			$stg_kz=254;
			$semester=1;
		}
	}
	
	//Studiengang Drop Down anzeigen
	echo "\n";
	echo "<tr><td>Studiengang</td><td><select name='studiengang' onChange='javascript: document.form1.status.value=\"2\"; document.form1.submit();'>";
	$sql_query = "SELECT studiengang_kz, UPPER(typ::varchar(1) || kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz";
	$result = $db->db_query($sql_query);

	while($row=$db->db_fetch_object($result))
	{
		if($stg_kz==$row->studiengang_kz)
			echo "<option value='$row->studiengang_kz' selected>$row->kurzbz</option>";
		else
			echo "<option value='$row->studiengang_kz'>$row->kurzbz</option>";
	}

	echo "</select></td></tr>";
	echo "\n";
	//Semester Drop Down anzeigen
	echo "<tr><td>Semester</td><td><select name='semester' onChange='javascript: document.form1.status.value=\"2\";document.form1.submit();'>";
	$sql_query = "SELECT max_semester FROM public.tbl_studiengang where studiengang_kz='$stg_kz'";
	$result = $db->db_query($sql_query);
	$row = $db->db_fetch_object($result);
	echo "<option value='0'>0</option>";
	for($i=0;$i<$row->max_semester;$i++)
	{
		if($semester==$i+1)
			echo "<option value='".($i+1)."' selected>".($i+1)."</option>";
		else
			echo "<option value='".($i+1)."'>".($i+1)."</option>";
	}

	echo "</select></td></tr>";
	echo "\n";
	
	//Lehrveranstaltung Drop Down anzeigen
	echo "<tr><td>Lehrveranstaltung</td><td><SELECT name='lv_id'>";

	$sql_query="SELECT * FROM lehre.tbl_lehrveranstaltung WHERE studiengang_kz='$stg_kz' AND semester='$semester'";
	$result = $db->db_query($sql_query);

	while($row=$db->db_fetch_object($result))
	{
		if($row->lehrveranstaltung_id==$le_obj->lehrveranstaltung_id)
			echo "<OPTION value='$row->lehrveranstaltung_id' selected>$row->bezeichnung</OPTION>";
		else
			echo "<OPTION value='$row->lehrveranstaltung_id'>$row->bezeichnung</OPTION>";
	}   
   
	echo "</SELECT></td></tr>";
	if(!isset($_GET['new']))
	{
		echo "<tr><td>Lektor</td><td>";
		$sql_query = "SELECT uid, vorname, nachname FROM campus.vw_mitarbeiter WHERE uid in(SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$le_obj->lehreinheit_id') ORDER BY nachname";
		$result = $db->db_query($sql_query);

		while($row=$db->db_fetch_object($result))
			echo "$row->nachname $row->vorname ($row->uid)<br>";

		echo "</td></tr>";
	}

	//Lehrfach Drop Down anzeigen
	echo "\n";
	echo "<tr><td>Lehrfach</td><td><select name='lehrfach'>";

	$sql_query = "SELECT lehrfach_id, bezeichnung, kurzbz FROM lehre.tbl_lehrfach WHERE studiengang_kz='$stg_kz' AND semester='$semester' ORDER BY bezeichnung";
	$result = $db->db_query($sql_query);
	//echo "<option value='0'>--keine Auswahl--</option>";
	while($row=$db->db_fetch_object($result))
	{
		if($le_obj->lehrfach_id==$row->lehrfach_id)
			echo "<option value='$row->lehrfach_id' selected>$row->kurzbz - $row->bezeichnung</option>";
		else 
			echo "<option value='$row->lehrfach_id'>$row->kurzbz - $row->bezeichnung</option>";
	}

	echo "</select></td></tr>";
	echo "\n";
	
	//Lehrform Drop Down anzeigen
	echo "<tr><td>Lehrform</td><td><select name='lehrform'>";

	$form_obj=new lehrform();

	if(!$form_obj->getAll())
		echo "Fehler beim laden der Lehrform: $form_obj->errormsg";

	foreach($form_obj->lehrform as $row)
	{
		if($le_obj->lehrform_kurzbz==$row->lehrform_kurzbz)
			echo "<option value='$row->lehrform_kurzbz' selected>$row->lehrform_kurzbz - $row->bezeichnung</option>";
		else
			echo "<option value='$row->lehrform_kurzbz'>$row->lehrform_kurzbz - $row->bezeichnung</option>";
	}

	echo "</select></td></tr>";
	echo "\n";
	
	//Raumtyp Drop Down anzeigen
	echo "<tr><td>Raumtyp</td><td><select name='raumtyp'>";
	$sql_query = "SELECT raumtyp_kurzbz, beschreibung FROM public.tbl_raumtyp ORDER BY raumtyp_kurzbz";
	if (!$result = $db->db_query($sql_query))
		die($db->db_last_error());
	
	while ($row = $db->db_fetch_object($result))
	{
		if($le_obj->raumtyp==$row->raumtyp_kurzbz)
			echo "<option value='$row->raumtyp_kurzbz' selected>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
		else
			echo "<option value='$row->raumtyp_kurzbz'>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
	}

	echo "</select></td></tr>";
	echo "\n";
	
	//RaumtypAlternativ Drop Down anzeigen
	echo "<tr><td>Raumtyp Alternativ</td><td><select name='raumtypalternativ'>";
	$sql_query = "SELECT raumtyp_kurzbz, beschreibung FROM public.tbl_raumtyp ORDER BY raumtyp_kurzbz";
	if (!$result = $db->db_query($sql_query))
		die($db->db_last_error());

	while ($row = $db->db_fetch_object($result))
	{
		if($le_obj->raumtypalternativ==$row->raumtyp_kurzbz)
			echo "<option value='$row->raumtyp_kurzbz' selected>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
		else
			echo "<option value='$row->raumtyp_kurzbz'>$row->raumtyp_kurzbz ($row->beschreibung)</option>";
	}

	echo "</select></td></tr>";
	echo "\n";

	echo "<tr><td>Stundenblockung</td><td><input type='text' value='$le_obj->stundenblockung' name='stundenblockung' size='5'></td></tr>";
	echo "\n";
	echo "<tr><td>Wochenrythmus</td><td><input type='text' value='$le_obj->wochenrythmus' name='wochenrythmus' size='5'></td></tr>";
	echo "\n";
	echo "<tr><td>StartKW</td><td><input type='text' value='$le_obj->start_kw' name='startkw' size='5'></td></tr>";
	echo "\n";
	echo "<tr><td>Anmerkung</td><td><input type='text' value='$le_obj->anmerkung' name='anmerkung'></td></tr>";
	echo "\n";
	
	//Studiensemester Drop Down anzeigen
	echo "<tr><td>Studiensemester</td><td><select name='studiensemester'>";
	$sql_query = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester";
	if (!$result = $db->db_query($sql_query))
		die($db->db_last_error());

	while ($row = $db->db_fetch_object($result))
	{
		if($le_obj->studiensemester_kurzbz==$row->studiensemester_kurzbz)
			echo "<option value='$row->studiensemester_kurzbz' selected>$row->studiensemester_kurzbz</option>";
		else
			echo "<option value='$row->studiensemester_kurzbz'>$row->studiensemester_kurzbz</option>";
	}
   
	echo "</select></td></tr>";
	echo "\n";
	
	//Sprache Drop Down anzeigen
    echo "<tr><td>Sprache</td><td><select name='sprache'>";
	$sql_query = "SELECT sprache FROM public.tbl_sprache";
	if (!$result = $db->db_query($sql_query))
		die($db->db_last_error());

	while ($row = $db->db_fetch_object($result))
	{
		if($le_obj->sprache==$row->sprache)
			echo "<option value='$row->sprache' selected>$row->sprache</option>";
		else 
			echo "<option value='$row->sprache'>$row->sprache</option>";
	}
   
	echo "</select></td></tr>";
	echo "\n";
	
	echo "<tr><td>UNr</td><td><input type='text' value='$le_obj->unr' name='unr'></td></tr>";
	echo "\n";
	if($le_obj->lehre=='t')
		$le_obj->lehre='on';
	echo "<tr><td>Lehre</td><td><input type='checkbox' name='lehre'". ($le_obj->lehre=='on'?'checked':'')."></td></tr>";
	echo "<tr><td>&nbsp;</td></tr>";
	echo "\n";
	echo "<tr><td>&nbsp;</td><td><input type='submit' name='btn_submit' value='Speichern'>";
	//Beim neu anlegen einen zusaetzlichen Button anzeigen zum Speichern und erneutem Einfuegen
	if(isset($_GET['new']))
		echo "<input name='submit1' type='button' value='Speichern und Einf&uuml;gen' OnClick='javascript:document.form1.status.value=\"3\";document.form1.submit();'";
	echo "</td></tr>";
	echo "</table>";
	echo "</form>";
}
else 
{
	//Normalansicht (Gesamtuebersicht der Lehreinheiten)
	if(!isset($order))
		$order="tbl_lehreinheit.lehrveranstaltung_id";
		
	if(!isset($stsem))
	{
		$stsem_obj = new studiensemester();
		$stsem = $stsem_obj->getaktorNext();
	}
	if(!isset($stg))
	{
		$stg=227;
	}
	if(!isset($lektor))
	{
		$lektor=-1; 
	}
	if(!isset($sem))
	{
		$sem=-1;
	}
	
	if(isset($saved))
	{
		echo "<br><h2>Daten wurden gespeichert</h2><br>";
	}
	
	//Aendern der Stundenblockung
	if(isset($_GET['leid']) && isset($_POST['stb']) && is_numeric($_GET['leid']) && is_numeric($_POST['stb'])) 
	{
		$sql_query = "UPDATE lehre.tbl_lehreinheit SET 
					  stundenblockung='". $_POST['stb']."',
					  updateamum=now(), updatevon='".$user."' 
					  WHERE lehreinheit_id='". $_GET['leid']."'";
		if($db->db_query($sql_query))
			echo "<br><h2>Update durchgeführt</h2><br>";
		else 
			echo "<br><h2>Update Fehlgeschlagen, Bitte erneut versuchen</h2><br>";
	}
	
	//Aendern des Lehre Feldes
	if(isset($_GET['leid']) && isset($_GET['lehre']) && is_numeric($_GET['leid']))
	{
		$sql_query = "UPDATE lehre.tbl_lehreinheit SET lehre=not lehre, updateamum=now(), updatevon='$user' WHERE lehreinheit_id ='".$_GET['leid']."'";
		if($db->db_query($sql_query))
			echo "<br><h2>Update durchgeführt</h2><br>";
	    else
	    	echo "<br><h2><font color='#FF0000'>Fehler beim Update ".$db->db_last_error()."</font></h2><br>";
	}

	//Loeschen einer Lehreinheit
	if(isset($del) && isset($_GET['le_id']))
	{
		$le_obj = new lehreinheit();
		if($le_obj->delete($_GET['le_id']))
		{
			echo "<br><h2>DELETE durchgeführt</h2><br>";
		}
		else 
			echo $le_obj->errormsg;
		
	}
			
	echo '<table width="600" border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">';
	//Ausgeben der Studiensemester zb WS2005, SS2006 etc
	echo "<a href='lv_verteilung.php?stsem=-1&stg=$stg&sem=$sem&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'>Alle </a>";
	$stsem_obj = new studiensemester();
	$stsem_obj->getAll();
	foreach($stsem_obj->studiensemester as $row)
		echo "- <a href='lv_verteilung.php?stsem=$row->studiensemester_kurzbz&stg=$stg&sem=$sem&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'> $row->studiensemester_kurzbz </a>";

	echo "</td><td align='center'>";
	echo "<form action='lv_verteilung.php?new=true&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&order=$order' method='POST'><input type='submit' value='NEU'></form>";
	echo "</td></tr></table>";
	
	$stg_obj = new studiengang();
	$stg_obj->getAll();
	
	echo "\n";
	echo '<table border="0" cellspacing="0" cellpadding="0"><tr>';
	//Studiengang Drop Down Anzeigen
	echo "<td>Studiengang:</td><td>Lektor:</td></tr>";
	echo "<tr><td><form name='f_stg' action='lv_verteilung.php?stsem=$stsem&lektor=$lektor".(isset($order)?"&order=$order":"")."' method='POST'>";
	echo "<SELECT name='stg' onChange='javascript:document.f_stg.submit();'>";
	
	if($stg==-1 || $lektor==-1)
		echo "<option value='-1' selected>--Alle anzeigen--</option>";
	else 
		echo "<option value='-1'>--Alle anzeigen--</option>";
		
	//Ausgeben der Studiengänge zb BEL, DVT etc
	foreach($stg_obj->result as $row)
	{
		if($row->studiengang_kz==$stg)
		   echo "<option value='$row->studiengang_kz' selected>$row->kuerzel</option>";
		else 
		   echo "<option value='$row->studiengang_kz'>$row->kuerzel</option>";
	}
	echo "</SELECT></form></td>";
	echo "\n";
	
	$sql_query = "SELECT uid, nachname, vorname FROM campus.vw_mitarbeiter WHERE lektor=true ORDER BY nachname, vorname"; 
	$result = $db->db_query($sql_query);
	if (!$result = $db->db_query($sql_query))
		die($db->db_last_error());
	
	echo "\n";
	echo "<td><form name='f_lek' action='lv_verteilung.php?stsem=$stsem&stg=$stg&sem=$sem".(isset($order)?"&order=$order":"")."' method='POST'>";
	//Lektor Drop Down anzeigen
	echo "<SELECT name='lektor' onChange='javascript:document.f_lek.submit();'>";
	if($lektor!='-1')
	   echo "<option value='-1' selected>--Alle anzeigen--</option>";
	else 
	   echo "<option value='-1'>--Alle anzeigen--</option>";
	   
	//Ausgeben der Lektoren
	
	while($row=$db->db_fetch_object($result))
	{		
		if($lektor==$row->uid)
		   echo "<option value='$row->uid' selected>$row->nachname $row->vorname ($row->uid)</option>";
		else 
		   echo "<option value='$row->uid'>$row->nachname $row->vorname ($row->uid)</option>";
	}
	echo "</SELECT></form></td></tr><tr><td>";
	echo "\n";
	
	if($stg!=-1) //Wenn ein Studiengang ausgewählt wurde
	{
		//Anzeigen der Semester
		echo "Semester:</td>";
		echo "<td><a href='lv_verteilung.php?stsem=$stsem&stg=$stg&sem=-1&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'>Alle </a>";
		$stg_obj = new studiengang($stg);
		
		for($i=1;$i<($stg_obj->max_semester+1);$i++)
		{
			echo "-<a href='lv_verteilung.php?stsem=$stsem&stg=$stg&sem=$i&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'> $i </a>";
		}
	}
	echo "&nbsp;</td></tr></table><br>";
	
	echo "Aktuelle Auswahl:";
	if($stsem!=-1)
	   echo " Studiensemester: $stsem";
	if($stg!=-1)
	{		
	    echo " Studiengang: $stg_obj->kuerzel";
	}
	if($sem!=-1)
	   echo " Semester: $sem";
	if($lektor!=-1)
	   echo " Lektor: $lektor";
	   
	echo "<br>";
	//Tabelle aufbauen wenn nicht erster aufruf

		//Daten holen
		$qry = "SELECT tbl_lehreinheit.lehre as le_lehre, * FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE true";
		if($lektor!=-1)
			$qry = "SELECT tbl_lehreinheit.lehre as le_lehre,* FROM lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheitmitarbeiter WHERE
					tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
					tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					mitarbeiter_uid='$lektor'";
			
		if($stsem!=-1)
			$qry.=" AND studiensemester_kurzbz='$stsem'";
		if($sem!=-1)
			$qry.=" AND semester='$sem'";
		if($stg!=-1)
			$qry.=" AND studiengang_kz='$stg'";
		
		$qry.=" ORDER BY $order";	
		//echo $qry;
		if(!$result = $db->db_query($qry))
			die($db->db_last_error());
		if ($db->db_num_rows($result))
		{
			echo "\n";
			echo '<table class="liste">';
			echo "\n";
			echo '  <tr class="liste">';
			//Kopfzeile der Tabelle
			echo "<td>&nbsp;</td><td>&nbsp;</td><td>Gruppen</td>";
			//echo "<td><a href='lv_verteilung.php?stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&order=lektor'>Lektor</a></td>";
			echo "<td>Lektor</td>";
			echo "<td>Raumtyp</td><td>Blockung</td><td>WR</td><td>LF</td><td>Lehre</td>";
			echo "<td>LVbezeichnung</tr>";
			echo "\n";
					
			//Tabellenelemente rausschreiben
			for($i=0;$row = $db->db_fetch_object($result);$i++)
			{			
				echo "\n";
				echo '  <tr class="liste'.($i%2).'">';
				echo "<td><a href='lv_verteilung.php?edit=true&le_id=$row->lehreinheit_id&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?'&order='.$order:'')."' class='linkgreen'>edit</a></td>";
				echo "<td><a href='lv_verteilung.php?le_id=$row->lehreinheit_id&del=1&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?"&order=$order":"")."' onClick='javascript:return conf_del();' class='linkgreen'>delete</a></td>";
				echo "<td>";
				$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row->lehreinheit_id'";
				if($result_grp = $db->db_query($qry))
				{
					$i=0;
					while($row_grp=$db->db_fetch_object($result_grp))
					{
						if($i!=0)
							echo ', ';
						$i=1;
						if($row_grp->gruppe_kurzbz!='')
							echo $row_grp->gruppe_kurzbz;
						else 
						{
							$stg_obj1 = new studiengang($row_grp->studiengang_kz);
							echo $stg_obj1->kuerzel.$row_grp->semester.$row_grp->verband.$row_grp->gruppe;
						}
						
					}
				}
				echo '</td>';
				$qry = "SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id='$row->lehreinheit_id'";
				echo '<td>';
				if($result_ma = $db->db_query($qry))
				{
					$i=0;
					while($row_ma = $db->db_fetch_object($result_ma))
					{
						if($i!=0)
							echo ", ";
						echo $row_ma->mitarbeiter_uid;
						$i=1;
					}
				}
				echo '</td>';
				echo "<td nowrap>$row->raumtyp / $row->raumtypalternativ</td>";
				//echo "<td>$row->stundenblockung</td>";
				echo "<td nowrap><form action='lv_verteilung.php?leid=$row->lehreinheit_id&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&lvnr=$row->lvnr".(isset($order)?"&order=$order":"")."' method='POST'><input type='text' value='$row->stundenblockung' size='2' name='stb'><input type='submit' value='ok'></form></td>";
				echo "<td>$row->wochenrythmus</td>";
				$qry = "SELECT kurzbz FROM lehre.tbl_lehrfach WHERE lehrfach_id='$row->lehrfach_id'";
				$result_lf = $db->db_query($qry);
				$row_lf=$db->db_fetch_object($result_lf);
				echo "<td>$row_lf->kurzbz</td>";
				echo "<td><form action='lv_verteilung.php?leid=$row->lehreinheit_id&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&lehre=$row->le_lehre".(isset($order)?"&order=$order":"")."' method='POST'><input type='image' src='../../../skin/images/".($row->le_lehre=='t'?'true.gif':'false.gif')."'></form></td>";
				//echo "<td nowrap><form action='lv_verteilung.php?lfnr=$row->lehrfach_id&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?"&order=$order":"")."' method='POST'><input type='text' value='$row->lehrevz' size='5' name='lvz'><input type='submit' value='ok'></form></td>";
				echo "<td>$row->bezeichnung</td>";
				echo "</tr>";
			}
		}
		else 
		{
			echo "<br>Keine Daten mit diesen Kriterien Vorhanden";
		}
		
}
?>
</body>
</html>