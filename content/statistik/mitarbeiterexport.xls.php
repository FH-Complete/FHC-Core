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
/**
 * Exportiert die Mitarbeiterdaten in ein Excel File.
 * Der Mitarbeiterfilter und die zu exportierenden Spalten werden per GET uebergeben.
 * Die Adressen der Mitarbeiter werden immer dazugehaengt
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/Excel/excel.php');

$db = new basis_db();
$user = get_uid();
loadVariables($user);

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

//die Spalten die Exportiert werden sollen, werden per GET uebergeben
//spalte1=nachname, spalte2=vorname, spalte3=gebdatum, ...
$anzSpalten=0;
$varname='spalte'.(string)$anzSpalten;
while (isset($_GET[$varname]))
{
	$spalte[$anzSpalten]=$_GET[$varname];
	$anzSpalten++;
	$varname='spalte'.(string)$anzSpalten;
}
$zustelladresse=true;

// Mitarbeiter holen
$mitarbeiterDAO=new mitarbeiter();
$mitarbeiterDAO->getPersonal($fix, $stgl, $fbl, $aktiv, $karenziert, $ausgeschieden, $semester_aktuell);

//Sortieren der Eintraege nach Nachname, Vorname
//Umlaute werden ersetzt damit diese nicht unten angereiht werden
//sondern richtig mitsortiert
$vorname=array();
$nachname=array();

$umlaute = array('ö','Ö','ü','Ü','ä','Ä');
$umlauterep = array('o','O','u','U','a','A');
foreach ($mitarbeiterDAO->result as $key=>$foo)
{
	$vorname[$key]=str_replace($umlaute, $umlauterep, $foo->vorname);
	$nachname[$key]=str_replace($umlaute, $umlauterep, $foo->nachname);
}
array_multisort($nachname, SORT_ASC, $vorname, SORT_ASC, $mitarbeiterDAO->result);

	// Creating a workbook
	$workbook = new Spreadsheet_Excel_Writer();
	$workbook->setVersion(8);
	// sending HTTP headers
	$workbook->send("Mitarbeiter". "_" . date("d_m_Y") . ".xls");

	// Creating a worksheet
	$worksheet =& $workbook->addWorksheet("Mitarbeiter");
	$worksheet->setInputEncoding('utf-8');

	$format_bold =& $workbook->addFormat();
	$format_bold->setBold();

	$format_title =& $workbook->addFormat();
	$format_title->setBold();
	// let's merge
	$format_title->setAlign('merge');

	//Zeilenueberschriften ausgeben
	for ($i=0;$i<$anzSpalten;$i++)
		$worksheet->write(0,$i,mb_strtoupper(str_replace('_bezeichnung','',$spalte[$i])), $format_bold);
	$worksheet->write(0,$i,"STRASSE", $format_bold);
	$worksheet->write(0,$i+1,"PLZ", $format_bold);
	$worksheet->write(0,$i+2,"ORT", $format_bold);
	$worksheet->write(0,$i+3,"FIRMENNAME", $format_bold);

	//Maximale Spaltenbreite ermitteln damit sie am Schluss gesetzt werden kann
	$j=1;
	$maxlength = array();
	for ($i=0;$i<$anzSpalten;$i++)
		$maxlength[$i]=mb_strlen(str_replace('_bezeichnung','',$spalte[$i]));
	$maxlength[$i]=mb_strlen('STRASSE');
	$maxlength[$i+1]=mb_strlen('PLZ');
	$maxlength[$i+2]=mb_strlen('ORT');
	$maxlength[$i+3]=mb_strlen('FIRMENNAME');

	//Zeilen (Mitarbeiter) ausgeben
	foreach ($mitarbeiterDAO->result as $mitarbeiter)
	{
		//Spalten ausgeben
		for ($i=0;$i<$anzSpalten;$i++)
		{
			if(is_bool($mitarbeiter->{$spalte[$i]}))
				$mitarbeiter->{$spalte[$i]} = ($mitarbeiter->{$spalte[$i]}?'Ja':'Nein');

			if(mb_strlen($mitarbeiter->{$spalte[$i]})>$maxlength[$i])
				$maxlength[$i] = mb_strlen($mitarbeiter->{$spalte[$i]});
			$worksheet->write($j,$i, $mitarbeiter->{$spalte[$i]});
		}

		//Zustelladresse aus der Datenbank holen und dazuhaengen
		$qry = "SELECT * FROM public.tbl_adresse WHERE person_id='$mitarbeiter->person_id' AND zustelladresse=true LIMIT 1";
		if($result = $db->db_query($qry))
		{
			if($row = $db->db_fetch_object($result))
			{
				if(mb_strlen($row->strasse)>$maxlength[$i])
					$maxlength[$i]=mb_strlen($row->strasse);
				$worksheet->write($j,$i, $row->strasse);
				if(mb_strlen($row->plz)>$maxlength[$i+1])
					$maxlength[$i+1]=mb_strlen($row->plz);
				$worksheet->write($j,$i+1, $row->plz);
				if(mb_strlen($row->ort)>$maxlength[$i+2])
					$maxlength[$i+2]=mb_strlen($row->ort);
				$worksheet->write($j,$i+2, $row->ort);

				if($row->firma_id!='')
				{
					$qry = "SELECT * FROM public.tbl_firma WHERE firma_id='$row->firma_id'";
					if($result = $db->db_query($qry))
					{
						if($row = $db->db_fetch_object($result))
						{
							if(mb_strlen($row->name)>$maxlength[$i+3])
								$maxlength[$i+3]=mb_strlen($row->name);
							$worksheet->write($j,$i+3, $row->name);
						}
					}
				}
			}
		}

		$j++;
	}

	//Die Breite der Spalten setzen
	for ($i=0;$i<$anzSpalten;$i++)
		$worksheet->setColumn($i, $i, $maxlength[$i]+2);
    $worksheet->setColumn($i, $i, $maxlength[$i]+2);
    $worksheet->setColumn($i+1, $i+1, $maxlength[$i+1]+2);
    $worksheet->setColumn($i+2, $i+2, $maxlength[$i+2]+2);

	$workbook->close();

?>
