<?php
/*
 * Created on 16.11.2005
 *
 */
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");


// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
include('../vilesci/config.inc.php');

if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
// Orte holen
$sql_query="Select tbl_mailgrp.mailgrp_kurzbz,tbl_mailgrp.studiengang_kz,tbl_mailgrp.beschreibung,tbl_mailgrp.sichtbar,tbl_mailgrp.generiert,tbl_mailgrp.aktiv, tbl_person.vornamen, tbl_person.nachname, (tbl_person.uid || '@technikum-wien.at') as email from tbl_mailgrp, tbl_personmailgrp, tbl_person where tbl_mailgrp.mailgrp_kurzbz=tbl_personmailgrp.mailgrp_kurzbz AND tbl_personmailgrp.uid=tbl_person.uid ORDER by mailgrp_kurzbz, nachname,vornamen";
if(!$result=pg_query($conn, $sql_query))
	$error_msg.=pg_errormessage($conn);
else
	$num_rows=@pg_numrows($result);

$rdf_url='http://www.technikum-wien.at/vilesci/mailgrp';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:GRP="<?php echo $rdf_url; ?>/rdf#"
>

<?php
$descr='';
$sequenz='';

for ($i=0;$i<$num_rows;$i++)
{
    $grpLAST=($i>0?pg_fetch_object($result,$i-1):null);
	$grp=pg_fetch_object($result,$i);
	$grpNEXT=(($i<$num_rows-1)?pg_fetch_object($result,$i+1):null);
	$currentgrp=$grp->mailgrp_kurzbz;
	$lastgrp=$grpLAST->mailgrp_kurzbz;
	$nextGRP=$grpNEXT->mailgrp_kurzbz;

	if ($lastgrp!=$currentgrp || $i==0)
		$descr.='<RDF:Description RDF:about="'.$rdf_url.'/'.$grp->mailgrp_kurzbz.'" >
        			<GRP:mailgrp>'.$grp->mailgrp_kurzbz.'</GRP:mailgrp>
    				<GRP:vorname></GRP:vorname>
    				<GRP:nachname></GRP:nachname>
                    </RDF:Description>'."\n";
	$descr.='                    <RDF:Description RDF:about="'.$rdf_url.'/'.$grp->mailgrp_kurzbz.'/'.$grp->email.'" >
				<GRP:mailgrp>'.$grp->email.'</GRP:mailgrp>
    				<GRP:vorname>'.$grp->vornamen.'</GRP:vorname>
    				<GRP:nachname>'.$grp->nachname.'</GRP:nachname>
      			</RDF:Description>'."\n";

	if ($lastgrp!=$currentgrp)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.'/'.$grp->mailgrp_kurzbz.'" />
					<RDF:li>
      					<RDF:Seq RDF:about="'.$rdf_url.'/'.$grp->mailgrp_kurzbz.'" >'."\n";
	if ($nextGRP!=$currentgrp || $i==$num_rows-1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.'/'.$grp->mailgrp_kurzbz.'/'.$grp->email.'" />
					</RDF:Seq>
      			</RDF:li>'."\n";
	elseif ($lastgrp==$currentgrp || $nextGRP==$currentgrp || $num_rows==1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.'/'.$grp->mailgrp_kurzbz.'/'.$grp->email.'" />'."\n";
}
$sequenz='<RDF:Seq about="'.$rdf_url.'/alle-grp">'."\n".$sequenz.'
  	</RDF:Seq>';

echo $descr;
echo $sequenz;

?>
</RDF:RDF>