<?php
/*
 *  author: Christian Paminger <pam@technikum-wien.at>
 *  date: 2006-04-22
*/

//error_reporting(E_ALL);
//ini_set('display_errors','1');


include('../vilesci/config.inc.php');
include_once('../include/functions.inc.php');
include_once('../include/fas/functions.inc.php');
include_once('../include/fas/person.class.php');
include_once('../include/fas/mitarbeiter.class.php');
include_once('../include/Excel/PEAR.php');
include_once('../include/Excel/BIFFwriter.php');
include_once('../include/Excel/Workbook.php');
include_once('../include/Excel/Format.php');
include_once('../include/Excel/Worksheet.php');
include_once('../include/Excel/Parser.php');
include_once('../include/Excel/OLE.php');
include_once('../include/Excel/PPS.php');
include_once('../include/Excel/Root.php');
include_once('../include/Excel/File.php');
include_once('../include/Excel/Writer.php');
include_once('../include/fas/benutzer.class.php');


// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn_vilesci = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

//Parameter holen
if (isset($_GET['mitarbeiter_id']))
	$mitarbeiter_id = $_GET['mitarbeiter_id'];
else
	$mitarbeiter_id=null;

if (isset($_GET['fix']))
	$fix = $_GET['fix'];
else
	$fix=null;

if (isset($_GET['stgl']))
	$stgl = $_GET['stgl'];
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
$benutzer = new benutzer($conn_vilesci);
$benutzer->loadVariables($user);
// Mitarbeiter holen
$mitarbeiterDAO=new mitarbeiter($conn);
$mitarbeiterDAO->getMitarbeiter($mitarbeiter_id, $fix, $stgl, $fbl, $aktiv, $karenziert, $ausgeschieden, $zustelladresse,getStudiensemesterIdFromName($conn, $benutzer->variable->semester_aktuell));

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
			$worksheet->write($j,$i, utf8_decode($mitarbeiter->$spalte[$i]));
		}
		if(strlen($mitarbeiter->zustelladresse_strasse)>$maxlength[$i])
			$maxlength[$i]=strlen($mitarbeiter->zustelladresse_strasse);
		$worksheet->write($j,$i, utf8_decode($mitarbeiter->zustelladresse_strasse));
		if(strlen($mitarbeiter->zustelladresse_plz)>$maxlength[$i+1])
			$maxlength[$i+1]=strlen($mitarbeiter->zustelladresse_plz);
		$worksheet->write($j,$i+1, utf8_decode($mitarbeiter->zustelladresse_plz));
		if(strlen($mitarbeiter->zustelladresse_ort)>$maxlength[$i+2])
			$maxlength[$i+2]=strlen($mitarbeiter->zustelladresse_ort);
		$worksheet->write($j,$i+2, utf8_decode($mitarbeiter->zustelladresse_ort));
		$j++;
	}

	for ($i=0;$i<$anzSpalten;$i++)
		$worksheet->setColumn($i, $i, $maxlength[$i]+2);
    $worksheet->setColumn($i, $i, $maxlength[$i]+2);
    $worksheet->setColumn($i+1, $i+1, $maxlength[$i+1]+2);
    $worksheet->setColumn($i+2, $i+2, $maxlength[$i+2]+2);

	$workbook->close();

?>
