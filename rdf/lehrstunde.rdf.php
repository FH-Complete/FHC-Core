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

require_once('../cis/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/lehrstunde.class.php');

function checkID($needle)
{
	global $id_list;
	//echo "checkID $needle \n";
	reset($id_list);
	foreach($id_list as $v)
		if ($v==$needle)
			return true;
	return false;
}

if (isset($stundenplan_id0))
{
	$idList=array();
	while(list($k,$v)=each($_GET))
		if (strpos($k,'stundenplan_id')!==false)
			$idList[]=$v;
}

//print_r($idList);

$uid=get_uid();

if (isset($_GET['datum']))
	$datum=$_GET['datum'];
else
	$datum=date('Y-m-d',(mktime()));
if (isset($_GET['datum_bis']))
	$datum_bis=$_GET['datum_bis'];
else
	$datum_bis=null;
if (isset($_GET['stunde']))
	$stunde=$_GET['stunde'];
else
	$stunde=null;
if (isset($_GET['type']))
	$type=$_GET['type'];
else
	$type='lektor';
if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;
if (isset($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=null;
if (isset($_GET['ver']))
	$ver=$_GET['ver'];
else
	$ver=null;
if (isset($_GET['grp']))
	$grp=$_GET['grp'];
else
	$grp=null;
if (isset($_GET['einheit']))
	$einheit=$_GET['einheit'];
else
	$einheit=null;
if (isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];
else
	$pers_uid=$uid;
if (isset($_GET['ort_kurzbz']))
	$ort_kurzbz=$_GET['ort_kurzbz'];
else
	$ort_kurzbz=null;

if (isset($idList))
	$type='idList';
else
	$idList=null;

$error_msg='';
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg.='Es konnte keine Verbindung zum Server aufgebaut werden!';
$error_msg.=loadVariables($conn,$REMOTE_USER);

//if (!isset($datum_bis))
//	$datum_bis=date('Y-m-d',(mktime(0,0,1,substr($datum,5,2),substr($datum,8),substr($datum,0,4))+86400));

$lehrstunden=new lehrstunde($conn);
$anz=$lehrstunden->load_lehrstunden($type,$datum,$datum_bis,$pers_uid,$ort_kurzbz,$stg_kz,$sem,$ver,$grp,$einheit,$db_stpl_table,$idList);
if ($anz<0)
{
	$errormsg=$lehrstunden->errormsg;
	echo "Fehler: ".$errormsg;
	exit();
}

$rdf_url='http://www.technikum-wien.at/lehrstunde';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LEHRSTUNDE="<?php echo $rdf_url; ?>/rdf#"
	>
	<RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if (is_array($lehrstunden->lehrstunden))
	foreach ($lehrstunden->lehrstunden as $ls)
	{
		//var_dump($ls);
		//echo $ls->stunde.";";
		//if ($ls->stunde == $stunde  && checkID($ls->stundenplan_id))
		//{
			?>
  			<RDF:li>
  	    	<RDF:Description  id="<?php echo $ls->stundenplan_id; ?>"  about="<?php echo $rdf_url.'/'. $ls->stundenplan_id; ?>" >
  		      	<LEHRSTUNDE:id><?php echo $ls->stundenplan_id  ?></LEHRSTUNDE:id>
				<LEHRSTUNDE:lehreinheit_id><?php echo $ls->lehreinheit_id  ?></LEHRSTUNDE:lehreinheit_id>
				<LEHRSTUNDE:datum><?php echo $ls->datum  ?></LEHRSTUNDE:datum>
				<LEHRSTUNDE:stunde><?php echo $ls->stunde  ?></LEHRSTUNDE:stunde>
  		  		<LEHRSTUNDE:unr><?php echo $ls->unr  ?></LEHRSTUNDE:unr>
				<LEHRSTUNDE:ort_kurzbz><?php echo $ls->ort_kurzbz  ?></LEHRSTUNDE:ort_kurzbz>
				<LEHRSTUNDE:lehrfach><?php echo $ls->lehrfach  ?></LEHRSTUNDE:lehrfach>
				<LEHRSTUNDE:lehrfach_bez><![CDATA[<?php echo $ls->lehrfach_bez  ?>]]></LEHRSTUNDE:lehrfach_bez>
				<LEHRSTUNDE:lehrform><?php echo $ls->lehrform  ?></LEHRSTUNDE:lehrform>
				<LEHRSTUNDE:lektor><?php echo $ls->lektor_kurzbz  ?></LEHRSTUNDE:lektor>
				<LEHRSTUNDE:sem><?php echo $ls->sem  ?></LEHRSTUNDE:sem>
				<LEHRSTUNDE:ver><?php echo $ls->ver  ?></LEHRSTUNDE:ver>
				<LEHRSTUNDE:grp><?php echo $ls->grp  ?></LEHRSTUNDE:grp>
				<LEHRSTUNDE:gruppe><?php echo $ls->gruppe_kurzbz  ?></LEHRSTUNDE:gruppe>
				<LEHRSTUNDE:lehrform><?php echo $ls->lehrform  ?></LEHRSTUNDE:lehrform>
				<LEHRSTUNDE:studiengang><?php echo $ls->studiengang  ?></LEHRSTUNDE:studiengang>
				<LEHRSTUNDE:farbe><?php echo $ls->farbe  ?></LEHRSTUNDE:farbe>
				<LEHRSTUNDE:anmerkung><![CDATA[<?php echo $ls->anmerkung;  ?>]]></LEHRSTUNDE:anmerkung>
  	    	</RDF:Description>
  			</RDF:li>
			<?php
		//}
	}
?>

  </RDF:Seq>
</RDF:RDF>