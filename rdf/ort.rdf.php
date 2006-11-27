<?php
/*
 * Created on 02.12.2004
 *
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
include('../vilesci/config.inc.php');

if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
// Orte holen
$sql_query="SELECT * FROM (tbl_ort NATURAL JOIN tbl_ortraumtyp) JOIN tbl_raumtyp USING (raumtyp_kurzbz)
				WHERE aktiv	AND raumtyp_kurzbz!='LM' ORDER BY raumtyp_kurzbz, hierarchie,ort_kurzbz";
if(!$result=pg_query($conn, $sql_query))
	$error_msg.=pg_errormessage($conn);
else
	$num_rows=@pg_numrows($result);

$rdf_url='http://www.technikum-wien.at/tempus/ort';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ORT="<?php echo $rdf_url; ?>/rdf#"
>

<?php
$descr='';
$sequenz='';

for ($i=0;$i<$num_rows;$i++)
{
    $ortLAST=($i>0?pg_fetch_object($result,$i-1):null);
	$ort=pg_fetch_object($result,$i);
	$ortNEXT=(($i<$num_rows-1)?pg_fetch_object($result,$i+1):null);
	$currentTYP=$ort->raumtyp_kurzbz;
	$lastTYP=$ortLAST->raumtyp_kurzbz;
	$nextTYP=$ortNEXT->raumtyp_kurzbz;
	//echo "current:$currentTYP last:$lastTYP next:$nextTYP";
	if ($lastTYP!=$currentTYP || $i==0)
		$descr.='<RDF:Description RDF:about="'.$rdf_url.'/'.$ort->raumtyp_kurzbz.'" >
        			<ORT:raumtyp>'.$ort->raumtyp_kurzbz.'</ORT:raumtyp>
    				<ORT:hierarchie></ORT:hierarchie>
    				<ORT:ort_kurzbz></ORT:ort_kurzbz>
    				<ORT:ort_bezeichnung></ORT:ort_bezeichnung>
    				<ORT:max_person></ORT:max_person>
      			</RDF:Description>';
	$descr.='<RDF:Description RDF:about="'.$rdf_url.'/'.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz.'" >
        			<ORT:raumtyp>'.$ort->ort_kurzbz."</ORT:raumtyp>
    				<ORT:hierarchie>".$ort->hierarchie."</ORT:hierarchie>
    				<ORT:ort_kurzbz>".$ort->ort_kurzbz."</ORT:ort_kurzbz>
    				<ORT:ort_bezeichnung>".$ort->bezeichnung."</ORT:ort_bezeichnung>
    				<ORT:max_person>".$ort->max_person.'</ORT:max_person>
      			</RDF:Description>'."\n";

	if ($lastTYP!=$currentTYP)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.'/'.$ort->raumtyp_kurzbz.'" />
					<RDF:li>
      					<RDF:Seq RDF:about="'.$rdf_url.'/'.$ort->raumtyp_kurzbz.'" >'."\n";
	if ($nextTYP!=$currentTYP || $i==$num_rows-1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.'/'.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz.'" />
					</RDF:Seq>
      			</RDF:li>'."\n";
	elseif ($lastTYP==$currentTYP || $nextTYP==$currentTYP || $num_rows==1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.'/'.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz.'" />'."\n";
}
$sequenz='<RDF:Seq about="'.$rdf_url.'/alle-orte">'."\n".$sequenz.'
  	</RDF:Seq>';
echo $descr;
echo $sequenz;
?>
</RDF:RDF>