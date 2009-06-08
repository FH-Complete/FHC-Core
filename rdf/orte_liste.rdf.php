<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header f?r no cache
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
// ORT holen
if(!($result=pg_query($conn, 'SELECT * FROM (tbl_ort NATURAL JOIN tbl_ortraumtyp)
							 	JOIN tbl_raumtyp USING (raumtyp_kurzbz) WHERE aktiv
								ORDER BY raumtyp_kurzbz, hierarchie,ort_kurzbz')))
	$error_msg.=pg_errormessage($conn);
else
	$num_rows=@pg_numrows($result);

$rdf_url='http://www.technikum-wien.at/tempus/ort/';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ORT="<?php echo $rdf_url; ?>rdf#"
>

<RDF:Seq about="<?php echo $rdf_url ?>alle">

<?php
for ($i=0;$i<$num_rows;$i++)
{
	$ort=pg_fetch_object($result,$i);
	?>
	  <RDF:li>
      	<RDF:Description about="<?php echo $rdf_url.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz; ?>" >
        	<ORT:raumtyp><?php echo $ort->raumtyp_kurzbz; ?></ORT:raumtyp>
    		<ORT:hierarchie><?php echo $ort->hierarchie; ?></ORT:hierarchie>
    		<ORT:ort_kurzbz><?php echo $ort->ort_kurzbz; ?></ORT:ort_kurzbz>
    		<ORT:ort_bezeichnung><?php echo $ort->bezeichnung; ?></ORT:ort_bezeichnung>
    		<ORT:max_person><?php echo $ort->max_person; ?></ORT:max_person>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>