<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehreinheit.class.php');

$uid=get_uid();
$error_msg='';

if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
$error_msg.=loadVariables($conn,$uid);

//$semester_aktuell='WS2007';
if (isset($semester_aktuell))
	$studiensemester=$semester_aktuell;
else
	echo $error_msg='studiensemester is not set!';
if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type='lektor';
if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=0;
if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=0;
if (isset($_GET['lektor']))
	$lektor=$_GET['lektor'];
else
	$lektor=$uid;
if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;
if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;
if (isset($_GET['gruppe']))
	$gruppe_kurzbz=$_GET['gruppe'];
else
	$gruppe_kurzbz=null;

// LVA holen
$lva=array();
$lehreinheit=new lehreinheit($conn);
if (!$error_msg)
	if (!$lehreinheit->getLehreinheitLVPL($db_stpl_table,$studiensemester,$type,$stg_kz,$sem,$lektor,$ver,$grp,$gruppe_kurzbz))
		die ('Fehler bei Methode getLehreinheitLVPL(): '.$lehreinheit->errormsg);
$lva=$lehreinheit->lehreinheiten;
$rdf_url='http://www.technikum-wien.at/lehreinheit-lvplan/';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="<?php echo $rdf_url; ?>rdf#">

<RDF:Seq about="<?php echo $rdf_url.'alle'; ?>">

<?php
//var_dump($lva);
//echo $lehreinheit->errormsg;
$anz=count($lva);
//echo $anz;
if ($anz>0)
foreach ($lva as $l)
{
	//var_dump($l);
	$lva_ids='';
	$lehrverband='';
	$lvnr='';
	$lektor='';
	$gruppe_kurzbz='';
	$i=0;
	// IDs der Lehreinheiten
	foreach($l->lehreinheit_id as $lva_id)
		$lva_ids.='&amp;lva_id'.$i++.'='.$lva_id;
	// Lektoren
	$lektor='';
	$l->lektor=array_unique($l->lektor);
	sort($l->lektor);
	foreach($l->lektor as $lv)
		$lektor.=$lv.' ';
	// Lehrverbaende
	$l->lehrverband=array_unique($l->lehrverband);
	sort($l->lehrverband);
	foreach($l->lehrverband as $lv)
		$lehrverband.=$lv.' ';
	// LVNRs
	foreach($l->lvnr as $lv)
		$lvnr.=$lv.' ';
	foreach($l->gruppe_kurzbz as $lv)
		$gruppe_kurzbz.=$lv.' ';
	// Stundenblockung
	$stundenblockung='';
	$l->stundenblockung=array_unique($l->stundenblockung);
	sort($l->stundenblockung);
	foreach($l->stundenblockung as $sb)
		$stundenblockung.=$sb.' ';
	if (count($l->stundenblockung)>1)
		$stundenblockung.=' ?';
	// Start KW
	$start_kw='';
	$l->start_kw=array_unique($l->start_kw);
	sort($l->start_kw);
	foreach($l->start_kw as $kw)
		$start_kw.=$kw.' ';
	if (count($l->start_kw)>1)
		$start_kw.=' ?';
	// Wochenrythmus
	$wochenrythmus='';
	$l->wochenrythmus=array_unique($l->wochenrythmus);
	sort($l->wochenrythmus);
	foreach($l->wochenrythmus as $wr)
		$wochenrythmus.=$wr.' ';
	if (count($l->wochenrythmus)>1)
		$wochenrythmus.=' ?';
	// Lehrfach
	$lehrfach='';
	$l->lehrfach=array_unique($l->lehrfach);
	sort($l->lehrfach);
	foreach($l->lehrfach as $lf)
		$lehrfach.=$lf.' ';
	if (count($l->lehrfach)>1)
		$lehrfach.=' ?';
	// Lehrform
	$lehrform='';
	$l->lehrform=array_unique($l->lehrform);
	sort($l->lehrform);
	foreach($l->lehrform as $lf)
		$lehrform.=$lf.' ';
	if (count($l->lehrform)>1)
		$lehrform.=' ?';
	// Semesterstunden
	$semesterstunden='';
	$l->semesterstunden=array_unique($l->semesterstunden);
	sort($l->semesterstunden);
	foreach($l->semesterstunden as $lf)
		$semesterstunden.=$lf.' ';
	if (count($l->semesterstunden)>1)
		$semesterstunden.=' ?';

	// Planstunden
	$planstunden='';
	$l->planstunden=array_unique($l->planstunden);
	sort($l->planstunden);
	foreach($l->planstunden as $lf)
		$planstunden.=$lf.' ';
	if (count($l->planstunden)>1)
		$planstunden.=' ?';

	// Verplant
	$verplant='';
	$l->verplant=array_unique($l->verplant);
	sort($l->verplant);
	foreach($l->verplant as $lf)
		$verplant.=$lf.' ';
	if (count($l->verplant)>1)
		$verplant.=' ?';
	// Offene Stunden
	$offenestunden='';
	$l->offenestunden=array_unique($l->offenestunden);
	sort($l->offenestunden);
	foreach($l->offenestunden as $os)
		$offenestunden.=$os.' ';
	if (count($l->offenestunden)>1)
		$offenestunden.=' ?';

	echo'<RDF:li>
      		<RDF:Description  id="lva'.($anz--).'" about="'.$rdf_url.$l->unr.'">
		   		<LVA:lvnr>'.$lvnr.'</LVA:lvnr>
				<LVA:unr>'.$l->unr.'</LVA:unr>
				<LVA:lektor>'.$lektor.'</LVA:lektor>
				<LVA:lehrfach_id>'.$l->lehrfach_id.'</LVA:lehrfach_id>
				<LVA:studiengang_kz>'.$l->stg_kz[0].'</LVA:studiengang_kz>
				<LVA:fachbereich_kurzbz>'.$l->fachbereich.'</LVA:fachbereich_kurzbz>
				<LVA:semester>'.$l->semester[0].'</LVA:semester>
				<LVA:verband>'.$l->verband[0].'</LVA:verband>
				<LVA:gruppe>'.$l->gruppe[0].'</LVA:gruppe>
				<LVA:gruppe_kurzbz>'.$l->gruppe_kurzbz[0].'</LVA:gruppe_kurzbz>
				<LVA:raumtyp>'.$l->raumtyp.'</LVA:raumtyp>
				<LVA:raumtypalternativ>'.$l->raumtypalternativ.'</LVA:raumtypalternativ>
				<LVA:semesterstunden>'.$planstunden.'</LVA:semesterstunden>
				<LVA:stundenblockung>'.$stundenblockung.'</LVA:stundenblockung>
				<LVA:wochenrythmus>'.$wochenrythmus.'</LVA:wochenrythmus>
				<LVA:verplant>'.$verplant.'</LVA:verplant>
				<LVA:offenestunden>'.$offenestunden.'</LVA:offenestunden>
				<LVA:start_kw>'.$start_kw.'</LVA:start_kw>
				<LVA:anmerkung>'.$l->anmerkung[0].'</LVA:anmerkung>
				<LVA:studiensemester_kurzbz>'.$l->studiensemester_kurzbz.'</LVA:studiensemester_kurzbz>
				<LVA:lehrfach>'.$lehrfach.'</LVA:lehrfach>
				<LVA:lehrform>'.$lehrform.'</LVA:lehrform>
				<LVA:lehrfach_bez><![CDATA['.$l->lehrfach_bez[0].']]></LVA:lehrfach_bez>
				<LVA:lehrfach_farbe>#'.$l->lehrfach_farbe[0].'</LVA:lehrfach_farbe>
				<LVA:lva_ids>'.$lva_ids.'</LVA:lva_ids>
				<LVA:lehrverband>'.$lehrverband.'</LVA:lehrverband>
      		</RDF:Description>
		</RDF:li>';
}
?>
</RDF:Seq>
</RDF:RDF>