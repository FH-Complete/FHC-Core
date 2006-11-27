<?php
/*
 * Created on 22.3.2006
 *
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");

error_reporting(E_ALL);
ini_set('display_errors','1');
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
include('../../vilesci/config.inc.php');
include_once('../../include/fas/person.class.php');
include_once('../../include/fas/mitarbeiter.class.php');
require('../../include/fas/benutzer.class.php');
require('../../include/fas/functions.inc.php');
require('../../include/functions.inc.php');
$error_msg='';
// Datenbank Verbindung
	if (!$conn = @pg_pconnect(CONN_STRING_FAS))
	   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

	if(!$conn_vilesci = @pg_pconnect(CONN_STRING))
		$error_msg.='Es konnte keine Verbindung zum Server aufgebaut werden!';
/**
 * Fuegt CDATA String hinzu falls String nicht leerzeichencodiert ist
 */
function addCDATA($str)
{
	return ($str=='&#xA0;'?'&#xA0;':'<![CDATA['.$str.']]>');
}

function convdate($date)
{
	list($y,$m,$d) = explode('-',$date);
	return $d.'.'.$m.'.'.$y;
}

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

if (isset($_GET['leerzeichencodierung']))
	$leerzeichencodierung=true;
else 
	$leerzeichencodierung=false;
	
$user = get_uid();
$benutzer = new benutzer($conn_vilesci);
if(!$benutzer->loadVariables($user))
	die($benutzer->errormsg);
	
// Mitarbeiter holen
$mitarbeiterDAO=new mitarbeiter($conn);
$mitarbeiterDAO->getMitarbeiter($mitarbeiter_id, $fix, $stgl, $fbl, $aktiv, $karenziert, $ausgeschieden,false,getStudiensemesterIdFromName($conn, $benutzer->variable->semester_aktuell));

$rdf_url='http://www.technikum-wien.at/mitarbeiter';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:MITARBEITER="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php

foreach ($mitarbeiterDAO->result as $mitarbeiter)
{
	//if ($mitarbeiter->titelpre=='') $mitarbeiter->titelpre='&#x200B;';
	//Konvertierung der Leerzeichen damit die Sortierung funktioniert
	//Wird nur konvertier wenn die Daten in den tree geladen werden
	//Nicht wenn die Details fuer einen Mitarbeiter geladen werden
	//Funktioniert NICHT zusammen mit CDATA -> addCDATA() verwenden
	if($leerzeichencodierung)
	{
		if ($mitarbeiter->familienname=='') $mitarbeiter->familienname='&#xA0;';
		if ($mitarbeiter->vorname=='') $mitarbeiter->vorname='&#xA0;';
		if ($mitarbeiter->vornamen=='') $mitarbeiter->vornamen='&#xA0;';
		if ($mitarbeiter->anrede=='') $mitarbeiter->anrede='&#xA0;';
		if ($mitarbeiter->geschlecht=='') $mitarbeiter->geschlecht='&#xA0;';
		if ($mitarbeiter->gebort=='') $mitarbeiter->gebort='&#xA0;';
		if ($mitarbeiter->staatsbuergerschaft=='') $mitarbeiter->staatsbuergerschaft='&#xA0;';
		if ($mitarbeiter->familienstand=='') $mitarbeiter->familienstand='&#xA0;';
		if ($mitarbeiter->svnr=='') $mitarbeiter->svnr='&#xA0;';
		if ($mitarbeiter->anzahlderkinder=='') $mitarbeiter->anzahlderkinder='&#xA0;';
		if ($mitarbeiter->ersatzkennzeichen=='') $mitarbeiter->ersatzkennzeichen='&#xA0;';
		if ($mitarbeiter->bemerkung=='') $mitarbeiter->bemerkung='&#xA0;';
		if ($mitarbeiter->aktstatus=='') $mitarbeiter->aktstatus='&#xA0;';
		if ($mitarbeiter->titelpost=='') $mitarbeiter->titelpost='&#xA0;';
		if ($mitarbeiter->titelpre=='') $mitarbeiter->titelpre='&#xA0;';
		if ($mitarbeiter->uid=='') $mitarbeiter->uid='&#xA0;';
		if ($mitarbeiter->gebnation=='') $mitarbeiter->gebnation='&#xA0;';
		if ($mitarbeiter->qualifikation=='') $mitarbeiter->qualifikation='&#xA0;';
		if ($mitarbeiter->hauptberuf=='') $mitarbeiter->hauptberuf='&#xA0;';
		if ($mitarbeiter->persnr=='') $mitarbeiter->persnr='&#xA0;';
		if ($mitarbeiter->kurzbez=='') $mitarbeiter->kurzbez='&#xA0;';
		if ($mitarbeiter->stundensatz=='') $mitarbeiter->stundensatz='&#xA0;';
		if ($mitarbeiter->ausbildung=='') $mitarbeiter->ausbildung='&#xA0;';
	}
	
	?>
	  <RDF:li>
      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$mitarbeiter->mitarbeiter_id; ?>" >
        	<MITARBEITER:person_id NC:parseType="Integer"><?php echo $mitarbeiter->person_id;  ?></MITARBEITER:person_id>
    		<MITARBEITER:nachname><?php echo addCDATA($mitarbeiter->familienname);  ?></MITARBEITER:nachname>
    		<MITARBEITER:vorname><?php echo $mitarbeiter->vorname;  ?></MITARBEITER:vorname>
    		<MITARBEITER:vornamen><?php echo $mitarbeiter->vornamen;  ?></MITARBEITER:vornamen>
    		<MITARBEITER:anrede><?php echo $mitarbeiter->anrede;  ?></MITARBEITER:anrede>
    		<MITARBEITER:geschlecht><?php echo $mitarbeiter->geschlecht;  ?></MITARBEITER:geschlecht>
    		<MITARBEITER:geburtsdatum ><?php echo ($mitarbeiter->gebdat!=''?convdate($mitarbeiter->gebdat):'');  ?></MITARBEITER:geburtsdatum>
    		<MITARBEITER:geburtsdatum_iso ><?php echo $mitarbeiter->gebdat;  ?></MITARBEITER:geburtsdatum_iso>
    		<MITARBEITER:geburtsort ><?php echo $mitarbeiter->gebort;  ?></MITARBEITER:geburtsort>
    		<MITARBEITER:staatsbuergerschaft><?php echo $mitarbeiter->staatsbuergerschaft;  ?></MITARBEITER:staatsbuergerschaft>
    		<MITARBEITER:familienstand><?php echo $mitarbeiter->familienstand;  ?></MITARBEITER:familienstand>
    		<MITARBEITER:familienstand_bezeichnung><?php echo $mitarbeiter->familienstand_bezeichnung;  ?></MITARBEITER:familienstand_bezeichnung>
    		<MITARBEITER:svnr><?php echo $mitarbeiter->svnr;  ?></MITARBEITER:svnr>
    		<MITARBEITER:anzahlderkinder NC:parseType="Integer"><?php echo $mitarbeiter->anzahlderkinder;  ?></MITARBEITER:anzahlderkinder>
    		<MITARBEITER:ersatzkennzeichen><?php echo $mitarbeiter->ersatzkennzeichen;  ?></MITARBEITER:ersatzkennzeichen>
    		<MITARBEITER:bemerkung><?php echo addCDATA($mitarbeiter->bemerkung); ?></MITARBEITER:bemerkung>
    		<MITARBEITER:aktstatus><?php echo $mitarbeiter->aktstatus;  ?></MITARBEITER:aktstatus>
    		<MITARBEITER:aktstatus_bezeichnung><?php echo $mitarbeiter->aktstatus_bezeichnung;  ?></MITARBEITER:aktstatus_bezeichnung>
    		<MITARBEITER:bismelden><?php echo ($mitarbeiter->bismelden?'Ja':'Nein');  ?></MITARBEITER:bismelden>
    		<MITARBEITER:titelpre><?php echo addCDATA($mitarbeiter->titelpre);  ?></MITARBEITER:titelpre>
    		<MITARBEITER:titelpost><?php echo addCDATA($mitarbeiter->titelpost);  ?></MITARBEITER:titelpost>
    		<MITARBEITER:uid><?php echo $mitarbeiter->uid;  ?></MITARBEITER:uid>
    		<MITARBEITER:geburtsnation><?php echo $mitarbeiter->gebnation;  ?></MITARBEITER:geburtsnation>
    		<MITARBEITER:mitarbeiter_id NC:parseType="Integer"><?php echo $mitarbeiter->mitarbeiter_id; ?></MITARBEITER:mitarbeiter_id>
    		<MITARBEITER:beginndatum><?php echo ($mitarbeiter->beginndatum!=''?date('d.m.Y',strtotime($mitarbeiter->beginndatum)):''); ?></MITARBEITER:beginndatum>
    		<MITARBEITER:beginndatum_iso><?php echo $mitarbeiter->beginndatum; ?></MITARBEITER:beginndatum_iso>
    		<MITARBEITER:akademischergrad><?php echo $mitarbeiter->akadgrad_bezeichnung; ?></MITARBEITER:akademischergrad>
    		<MITARBEITER:habilitation><?php echo $mitarbeiter->habilitation_bezeichnung; ?></MITARBEITER:habilitation>
    		<MITARBEITER:mitgliedentwicklungsteam><?php echo ($mitarbeiter->mitgliedentwicklungsteam?'Ja':'Nein'); ?></MITARBEITER:mitgliedentwicklungsteam>
    		<MITARBEITER:qualifikation><?php echo $mitarbeiter->qualifikation; ?></MITARBEITER:qualifikation>
    		<MITARBEITER:hauptberuflich><?php echo ($mitarbeiter->hauptberuflich?'Ja':'Nein'); ?></MITARBEITER:hauptberuflich>
    		<MITARBEITER:hauptberuf><?php echo $mitarbeiter->hauptberuf; ?></MITARBEITER:hauptberuf>
    		<MITARBEITER:sws><?php echo $mitarbeiter->semesterwochenstunden; ?></MITARBEITER:sws>
    		<MITARBEITER:personal_nr NC:parseType="Integer"><?php echo $mitarbeiter->persnr; ?></MITARBEITER:personal_nr>
    		<MITARBEITER:beendigungsdatum><?php echo (strlen($mitarbeiter->beendigungsdatum)>0?date('d.m.Y',strtotime($mitarbeiter->beendigungsdatum)):''); ?></MITARBEITER:beendigungsdatum>
    		<MITARBEITER:beendigungsdatum_iso><?php echo $mitarbeiter->beendigungsdatum; ?></MITARBEITER:beendigungsdatum_iso>
    		<MITARBEITER:ausgeschieden><?php echo $mitarbeiter->ausgeschieden_bezeichnung; ?></MITARBEITER:ausgeschieden>
    		<MITARBEITER:kurzbezeichnung><?php echo addCDATA($mitarbeiter->kurzbez); ?></MITARBEITER:kurzbezeichnung>
    		<MITARBEITER:stundensatz NC:parseType="Integer"><?php echo $mitarbeiter->stundensatz ?></MITARBEITER:stundensatz>
    		<MITARBEITER:ausbildung><?php echo $mitarbeiter->ausbildung ?></MITARBEITER:ausbildung>
    		<MITARBEITER:ausbildung_bezeichnung><?php echo $mitarbeiter->ausbildung_bezeichnung ?></MITARBEITER:ausbildung_bezeichnung>
    		<MITARBEITER:aktiv><?php echo $mitarbeiter->aktiv_bezeichnung; ?></MITARBEITER:aktiv>
    		<MITARBEITER:deluser><?php 
    			//Wenn der Datensatz juenger als eine Woche
    			if((time()-strtotime($mitarbeiter->updateamum))<7*24*60*60)
    			{    				
    				$qry = "Select uid from tbl_variable where name='fas_id' AND wert='$mitarbeiter->updatevon'";
    				if($result=pg_query($conn_vilesci,$qry))
	    				if($row=pg_fetch_object($result))
    						echo $row->uid;
    			}
    		?></MITARBEITER:deluser>
      	</RDF:Description>
      </RDF:li>
<?php

}
?>

  </RDF:Seq>


</RDF:RDF>