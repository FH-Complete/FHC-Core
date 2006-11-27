<?php
/*
 * Created on 12-06-2006
 *
 * Erstellt ein RDF File mit den Mitarbeitern die eine Funktion
 * als lektor im Studiengang und Fachbereich der Uebergeben wird haben.
 */
// header fuer no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// Klassen inkludieren
include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
include('../../include/fas/benutzer.class.php');
include('../../include/fas/person.class.php');
include('../../include/fas/mitarbeiter.class.php');
include('../../include/fas/funktion.class.php');

error_reporting(E_ALL);
ini_set('display_errors','1');

// Datenbank Verbindung herstellen
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn_fas = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$stg = isset($_GET['stg'])?$_GET['stg']:'';
$fb = isset($_GET['fb'])?$_GET['fb']:'';

$user = get_uid();

//Aktuelles Studiensemester holen
$benutzer = new benutzer($conn);
if(!$benutzer->loadVariables($user))
	die("error:".$benutzer->errormsg);

// Mitarbeiter holen
$fkt_obj = new funktion($conn_fas);
if($stg!='' && $fb !='')
{
	//Alle laden die eine Funktion in diesem Bereich haben
	if(!$fkt_obj->getMitarbeiter($stg,$fb,$benutzer->variable->semester_aktuell))
		die("Error: $fkt_obj->errormsg");

}

$rdf_url='http://www.technikum-wien.at/mitarbeiterlehreinheitenauswahl';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MALEHAW="<?php echo $rdf_url; ?>/rdf#"
>
	<RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  <RDF:li>

		  <RDF:Description  id="2701"  about="<?php echo $rdf_url ?>/2701" >
	      	<MALEHAW:mitarbeiter_id>2701</MALEHAW:mitarbeiter_id>
	      	<MALEHAW:vorname>Dieter</MALEHAW:vorname>
	      	<MALEHAW:nachname>Dummy</MALEHAW:nachname>
	   	</RDF:Description>
<?php
if($stg!='' && $fb!='')
{

	$arr = array();
	foreach ($fkt_obj->result as $elem)
	{
		if($elem->mitarbeiter_id!=2701) //Dummy nicht nochmal in die Liste schreiben
		{
			//Namen der Lektoren holen
			$mitarbeiter = new mitarbeiter($conn_fas);
			$mitarbeiter->load_mitarbeiter($elem->mitarbeiter_id);
			$arr['id'][]=$mitarbeiter->mitarbeiter_id;
			$arr['vn'][]=$mitarbeiter->vorname;
			$arr['nn'][]=$mitarbeiter->familienname;
		}
	}
	//Nach Nachname sortieren
	array_multisort($arr['nn'],$arr['vn'],$arr['id']);
	for($i=0;$i<count($arr['id']);$i++)
	{
		//Ausgabe
		echo "
	   <RDF:Description  id=\"".$arr['id'][$i]."\"  about=\"".$rdf_url.'/'.$arr['id'][$i]."\" >
	      <MALEHAW:mitarbeiter_id>".$arr['id'][$i]."</MALEHAW:mitarbeiter_id>
	      <MALEHAW:vorname>".$arr['vn'][$i]."</MALEHAW:vorname>
	      <MALEHAW:nachname>".$arr['nn'][$i]."</MALEHAW:nachname>
	   </RDF:Description>
	      	";
	}
}
?>
		</RDF:li>
	</RDF:Seq>
</RDF:RDF>
