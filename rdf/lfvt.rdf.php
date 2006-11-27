<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header f�r no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
include('../vilesci/config.inc.php');
include_once('../include/lfvt.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';


// test

//$einheit_kurzbz='';
//$grp='1';
//$ver='A';
//$sem=5;
//$stg_kz=145;



$einheit_kurzbz=$_GET['einheit'];
$grp=$_GET['grp'];
$ver=$_GET['ver'];
$sem=$_GET['sem'];
$stg_kz=$_GET['stg_kz'];
$lektor=$_GET['lektor'];

// LVAs holen
$lvaDAO=new lfvt($conn);
$lvas=$lvaDAO->getLVAs($einheit_kurzbz, $grp, $ver, $sem,
	$stg_kz,$lektor);



$rdf_url='http://www.technikum-wien.at/tempus/lva';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="<?php echo $rdf_url; ?>/rdf#"
>

<?php
if (is_array($lvas)) {
	$currentUNR='';
	$lastUNR='';
	$nextUNR='';
	$inSub=false;
	for ($i=0;$i<count($lvas);$i++)
	{
		$lva=$lvas[$i];
		$currentUNR=$lva->unr;
		$lastUNR=($i>0?$lvas[$i-1]->unr:'');
		$nextUNR=($i<(count($lvas)-1)?$lvas[$i+1]->unr:'');
		$descr.="
      		<RDF:Description  id=\"".$lva->lehrveranstaltung_id."\"  about=\"".$rdf_url.'/'.$lva->lehrveranstaltung_id."\" >
			<LVA:lvnr>".$lva->lvnr."</LVA:lvnr>
    			<LVA:unr>".$lva->unr."</LVA:unr>
    			<LVA:einheit_kurzbz>".$lva->einheit_kurzbz."</LVA:einheit_kurzbz>
    			<LVA:lektor>".$lva->lektor."</LVA:lektor>
				<LVA:lektorPrettyPrint>".utf8_encode($lva->lektorPrettyPrint)."</LVA:lektorPrettyPrint>
    			<LVA:lehrfach_nr>".$lva->lehrfach_nr."</LVA:lehrfach_nr>
    			<LVA:studiengang_kz>".$lva->studiengang_kz."</LVA:studiengang_kz>
    			<LVA:fachbereich_id>".$lva->fachbereich_id."</LVA:fachbereich_id>
    			<LVA:semester>".$lva->semester."</LVA:semester>".
    			(strlen(trim($lva->verband))>0?"                  <LVA:verband>".$lva->verband."</LVA:verband>":"").
			($lva->gruppe>0?"		 			<LVA:gruppe>".$lva->gruppe."</LVA:gruppe>":"")."
    			<LVA:raumtyp>".$lva->raumtyp."</LVA:raumtyp>
    			<LVA:raumtypalternativ>".$lva->raumtypalternativ."</LVA:raumtypalternativ>
    			<LVA:semesterstunden>".$lva->semesterstunden."</LVA:semesterstunden>
    			<LVA:stundenblockung>".$lva->stundenblockung."</LVA:stundenblockung>
    			<LVA:wochenrythmus>".$lva->wochenrythmus."</LVA:wochenrythmus>
    			<LVA:start_kw>".$lva->start_kw."</LVA:start_kw>
    			<LVA:anmerkung>".$lva->anmerkung."</LVA:anmerkung>
    			<LVA:studiensemester_kurzbz>".$lva->studiensemester_kurzbz."</LVA:studiensemester_kurzbz>
    			<LVA:lehrfach><![CDATA[".utf8_encode($lva->lehrfach)."]]></LVA:lehrfach>
			<LVA:dbID>".$lva->lehrveranstaltung_id."</LVA:dbID>
      		</RDF:Description>";

		$subClose=false;
		if (($lastUNR!=$currentUNR && $currentUNR==$nextUNR) || count($lvas)==$i) {
			$inSub=true;
			$hier.="
      	<!-- ********************************* -->
      	<RDF:li>
      		<RDF:Seq about=\"".$rdf_url.'/'.$lva->lehrveranstaltung_id."\" >";
		}

		if ($nextUNR!=$currentUNR && $inSub) {
			$inSub=false;
			$subClose=true;
			$hier.="
			<RDF:li resource=\"".$rdf_url.'/'.$lva->lehrveranstaltung_id."\" />
      		</RDF:Seq>
      	</RDF:li>
      	<!-- ********************************* -->";
		}

		if (($inSub && $lastUNR==$currentUNR) || (count($lvas)==1) || (!$inSub && $currentUNR!=nextUNR && !$subClose)) {
			$hier.="
			<RDF:li resource=\"".$rdf_url.'/'.$lva->lehrveranstaltung_id."\" /> <!-- insub -->";

		}



	}

	$hier="
  	<RDF:Seq about=\"".$rdf_url."/liste\">".$hier."
  	</RDF:Seq>";
	echo $descr;
	echo $hier;
	//print_r($lvas);
}
?>


</RDF:RDF>
