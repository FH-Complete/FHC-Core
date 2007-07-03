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
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/Excel/excel.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

loadVariables($conn, $user);

//Parameter holen

if (isset($_GET['fix']))
	$fix = $_GET['fix'];
else
	$fix=null;

if (isset($_GET['stgl']))
	$stgl = ($_GET['stgl']=='true'?true:false);
else
	$stgl=null;

if (isset($_GET['fbl']))
	$fbl = $_GET['fbl'];
else
	$fbl=null;

if (isset($_GET['aktiv']))
	$aktiv = $_GET['aktiv'];
else
	$aktiv=null;

if (isset($_GET['karenziert']))
	$karenziert = $_GET['karenziert'];
else
	$karenziert=null;

if (isset($_GET['ausgeschieden']))
	$ausgeschieden = $_GET['ausgeschieden'];
else
	$ausgeschieden=null;

if (isset($_GET['zustelladresse']))
	$zustelladresse = $_GET['zustelladresse'];
else
	$zustelladresse = null;

//Spalten
$anzSpalten=0;
$varname='spalte'.(string)$anzSpalten;
while (isset($_GET[$varname]))
{
	$spalte[$anzSpalten]=$_GET[$varname];
	//echo $spalte[$anzSpalten];
	$anzSpalten++;
	$varname='spalte'.(string)$anzSpalten;
}
$zustelladresse=true;

// Mitarbeiter holen
$mitarbeiterDAO=new mitarbeiter($conn);
$mitarbeiterDAO->getPersonal($fix, $stgl, $fbl, $aktiv, $karenziert, $ausgeschieden, $semester_aktuell);
//echo 'fix:'.$fix.' stgl:'.$stgl.' fbl:'.$fbl.' aktiv:'.$aktiv.' karenziert:'.$karenziert.' ausgeschieden:'.$ausgeschieden.' semester_aktuell:'.$semester_aktuell;
	/*
	 * Create Excel File with Content from Students Examples solved
	 */

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();

	// sending HTTP headers
	$workbook->send("Mitarbeiter". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Mitarbeiter");

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
//	$format_title->setColor('yellow');
//	$format_title->setPattern(1);
//	$format_title->setFgColor('blue');
	// let's merge
	$format_title->setAlign('merge');

	for ($i=0;$i<$anzSpalten;$i++)
		$worksheet->write(0,$i,strtoupper(str_replace('_bezeichnung','',$spalte[$i])), $format_bold);
	$worksheet->write(0,$i,"STRASSE", $format_bold);
	$worksheet->write(0,$i+1,"PLZ", $format_bold);
	$worksheet->write(0,$i+2,"ORT", $format_bold);

	// set width of columns

	//$worksheet->setColumn(1,4,20); // ersten 3 Spalten auf width=17
	//$worksheet->setColumn(0,0,22);

	$j=1;
	$maxlength = array();
	for ($i=0;$i<$anzSpalten;$i++)
		$maxlength[$i]=strlen(str_replace('_bezeichnung','',$spalte[$i]));
	$maxlength[$i]=strlen('STRASSE');
	$maxlength[$i+1]=strlen('PLZ');
	$maxlength[$i+2]=strlen('ORT');

	foreach ($mitarbeiterDAO->result as $mitarbeiter)
	{
		for ($i=0;$i<$anzSpalten;$i++)
		{
			if(strlen($mitarbeiter->$spalte[$i])>$maxlength[$i])
				$maxlength[$i] = strlen($mitarbeiter->$spalte[$i]);
			$worksheet->write($j,$i, $mitarbeiter->$spalte[$i]);
		}
		$qry = "SELECT * FROM public.tbl_adresse WHERE person_id='$mitarbeiter->person_id' ORDER BY zustelladresse LIMIT 1";
		if($result = pg_query($conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{	
				if(strlen($row->strasse)>$maxlength[$i])
					$maxlength[$i]=strlen($row->strasse);
				$worksheet->write($j,$i, $row->strasse);
				if(strlen($row->plz)>$maxlength[$i+1])
					$maxlength[$i+1]=strlen($row->plz);
				$worksheet->write($j,$i+1, $row->plz);
				if(strlen($row->ort)>$maxlength[$i+2])
					$maxlength[$i+2]=strlen($row->ort);
				$worksheet->write($j,$i+2, $row->ort);
			}
		}
		
		$j++;
	}

	for ($i=0;$i<$anzSpalten;$i++)
		$worksheet->setColumn($i, $i, $maxlength[$i]+2);
    $worksheet->setColumn($i, $i, $maxlength[$i]+2);
    $worksheet->setColumn($i+1, $i+1, $maxlength[$i+1]+2);
    $worksheet->setColumn($i+2, $i+2, $maxlength[$i+2]+2);

	$workbook->close();

?>
