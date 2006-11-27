<?php
/**
 * erstellt ein RDF File mit den Studiensemestern
 * Created on 23.3.2006
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
error_reporting(E_ALL);
ini_set('display_errors','1');

require('../../vilesci/config.inc.php');
require('../../include/fas/lehrveranstaltung.class.php');
require('../../include/functions.inc.php');
require('../../include/fas/benutzer.class.php');
require('../../include/fas/functions.inc.php');
require('../../include/fas/fachbereich.class.php');
require('../../include/fas/ausbildungssemester.class.php');

// Datenbank Verbindung
if (!$conn_fas = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn = @pg_pconnect(CONN_STRING))
$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$studiengang_id = isset($_GET['studiengang_id'])?$_GET['studiengang_id']:'';
$ausbildungssemester_id = isset($_GET['ausbildungssemester_id'])?$_GET['ausbildungssemester_id']:null;

$user = get_uid();
$benutzer = new benutzer($conn);
$benutzer->loadVariables($user);
$studiensemester_id = getStudiensemesterIdFromName($conn_fas, $benutzer->variable->semester_aktuell);

$rdf_url='http://www.technikum-wien.at/lehrveranstaltung';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRVERANSTALTUNG="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/liste">
<?php
$lehrveranstaltungDAO=new lehrveranstaltung($conn_fas);
$lehrveranstaltungDAO->load_lva($studiengang_id, $studiensemester_id, $ausbildungssemester_id);

	foreach ($lehrveranstaltungDAO->result as $lehrveranstaltung)
	{
		?>
      <RDF:li>
         <RDF:Description RDF:about="<?php echo $rdf_url.'/'.$lehrveranstaltung->lehrveranstaltung_id; ?>" >
            <LEHRVERANSTALTUNG:lehrveranstaltung_id><?php echo $lehrveranstaltung->lehrveranstaltung_id;  ?></LEHRVERANSTALTUNG:lehrveranstaltung_id>
            <LEHRVERANSTALTUNG:art><?php echo $lehrveranstaltung->art;  ?></LEHRVERANSTALTUNG:art>
            <LEHRVERANSTALTUNG:ausbildungssemester_id><?php echo $lehrveranstaltung->ausbildungssemester_id;  ?></LEHRVERANSTALTUNG:ausbildungssemester_id>
<?php
            $ausbsem = new ausbildungssemester($conn_fas);
            $ausbsem->load($lehrveranstaltung->ausbildungssemester_id);
?>
            <LEHRVERANSTALTUNG:ausbildungssemester_bezeichnung><?php echo $ausbsem->name;  ?></LEHRVERANSTALTUNG:ausbildungssemester_bezeichnung>
            <LEHRVERANSTALTUNG:beschreibung><?php echo $lehrveranstaltung->beschreibung;  ?></LEHRVERANSTALTUNG:beschreibung>
            <LEHRVERANSTALTUNG:ectspunkte><?php echo $lehrveranstaltung->ectspunkte;  ?></LEHRVERANSTALTUNG:ectspunkte>
            <LEHRVERANSTALTUNG:fachbereich_id><?php echo $lehrveranstaltung->fachbereich_id;  ?></LEHRVERANSTALTUNG:fachbereich_id>
<?php
           	$fb = new fachbereich($conn_fas);
           	$fb->load($lehrveranstaltung->fachbereich_id);
?>
            <LEHRVERANSTALTUNG:fachbereich_bezeichnung><?php echo $fb->name;  ?></LEHRVERANSTALTUNG:fachbereich_bezeichnung>
            <LEHRVERANSTALTUNG:kategorie><?php echo $lehrveranstaltung->kategorie;  ?></LEHRVERANSTALTUNG:kategorie>
            <LEHRVERANSTALTUNG:kurzbezeichnung><?php echo $lehrveranstaltung->kurzbezeichnung;  ?></LEHRVERANSTALTUNG:kurzbezeichnung>
            <LEHRVERANSTALTUNG:name><?php echo $lehrveranstaltung->name;  ?></LEHRVERANSTALTUNG:name>
            <LEHRVERANSTALTUNG:notenlektor_id><?php echo $lehrveranstaltung->notenlektor_id;  ?></LEHRVERANSTALTUNG:notenlektor_id>
            <LEHRVERANSTALTUNG:nummer><?php echo $lehrveranstaltung->nummer;  ?></LEHRVERANSTALTUNG:nummer>
            <LEHRVERANSTALTUNG:nummerintern><?php echo $lehrveranstaltung->nummerintern;  ?></LEHRVERANSTALTUNG:nummerintern>
            <LEHRVERANSTALTUNG:sortierung><?php echo $lehrveranstaltung->sortierung;  ?></LEHRVERANSTALTUNG:sortierung>
            <LEHRVERANSTALTUNG:studentenwochenstunden><?php echo $lehrveranstaltung->studentenwochenstunden;  ?></LEHRVERANSTALTUNG:studentenwochenstunden>
            <LEHRVERANSTALTUNG:studiengang_id><?php echo $lehrveranstaltung->studiengang_id;  ?></LEHRVERANSTALTUNG:studiengang_id>
            <LEHRVERANSTALTUNG:studiensemester_id><?php echo $lehrveranstaltung->studiensemester_id;  ?></LEHRVERANSTALTUNG:studiensemester_id>
            <LEHRVERANSTALTUNG:updateamum><?php echo $lehrveranstaltung->updateamum;  ?></LEHRVERANSTALTUNG:updateamum>
            <LEHRVERANSTALTUNG:updatevon><?php echo $lehrveranstaltung->updatevon;  ?></LEHRVERANSTALTUNG:updatevon>
         </RDF:Description>
      </RDF:li>
	<?php
	}
?>
  </RDF:Seq>
</RDF:RDF>