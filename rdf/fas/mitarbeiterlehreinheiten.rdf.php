<?php
/*
 * Created on 31-03-2006
 *
 * RDF fuer die Lehrfachverteilungen
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
// DAO
include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
include('../../include/fas/benutzer.class.php');
include('../../include/fas/lehreinheit.class.php');
include('../../include/fas/person.class.php');
include('../../include/fas/mitarbeiter.class.php');

error_reporting(E_ALL);
ini_set('display_errors','1');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn_fas = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$lehreinheit_id = isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:'';
$mitarbeiter_id = isset($_GET['mitarbeiter_id'])?$_GET['mitarbeiter_id']:'';
$mitarbeiter_lehreinheit_id = isset($_GET['mitarbeiter_lehreinheit_id'])?$_GET['mitarbeiter_lehreinheit_id']:'';

$user = get_uid();
$benutzer = new benutzer($conn);
if(!$benutzer->loadVariables($user))
	die("error:".$benutzer->errormsg);

// LVAs holen
$lvaDAO=new lehreinheit($conn_fas);

if($lehreinheit_id!='')
{
	if($mitarbeiter_id!='')
	{
		//Lade einen bestimmten zugeteilten Mitarbeiter
		if(!$lvaDAO->load_zuteilung($lehreinheit_id,$mitarbeiter_id))
			die($lvaDAO->errormsg);
	}
	else
	{
		//Lade alle zugeteilten Mitarbeiter
		if(!$lvaDAO->load_zuteilung($lehreinheit_id))
			die($lvaDAO->errormsg);
	}
}
elseif($mitarbeiter_lehreinheit_id!='')
{
	if(!$lvaDAO->load_mitarbeiterzuteilung($mitarbeiter_lehreinheit_id))
		die($lvaDAO->errormsg);
}
$malehreinheiten = $lvaDAO->result;


$rdf_url='http://www.technikum-wien.at/mitarbeiterlehreinheiten';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MALEH="<?php echo $rdf_url; ?>/rdf#"
>
<RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
		  <RDF:li>
<?php
foreach ($malehreinheiten as $maleh)
{
	$mitarbeiter = new mitarbeiter($conn_fas);
	$mitarbeiter->load_mitarbeiter($maleh->mitarbeiter_id);

	echo "
   <RDF:Description  id=\"".$maleh->mitarbeiter_lehreinheit_id."\"  about=\"".$rdf_url.'/'.$maleh->mitarbeiter_lehreinheit_id."\" >
      <MALEH:mitarbeiter_lehreinheit_id>".$maleh->mitarbeiter_lehreinheit_id."</MALEH:mitarbeiter_lehreinheit_id>
      <MALEH:mitarbeiter_id>".$maleh->mitarbeiter_id."</MALEH:mitarbeiter_id>
      <MALEH:lehreinheit_id>".$maleh->lehreinheit_fk."</MALEH:lehreinheit_id>
      <MALEH:lehrfunktion_id>".$maleh->lehrfunktion_id."</MALEH:lehrfunktion_id>
      <MALEH:kosten>".$maleh->kosten."</MALEH:kosten>
      <MALEH:faktor>".$maleh->faktor."</MALEH:faktor>
      <MALEH:gesamtstunden>".$maleh->gesamtstunden_mitarbeiter."</MALEH:gesamtstunden>
      <MALEH:nachname>".$mitarbeiter->familienname."</MALEH:nachname>
      <MALEH:vorname>".$mitarbeiter->vorname."</MALEH:vorname>
   </RDF:Description>
      	";
}
?>
		  </RDF:li>
	</RDF:Seq>

</RDF:RDF>
