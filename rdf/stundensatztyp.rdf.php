<?php

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/xhtml+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

require_once('../config/vilesci.config.inc.php');
require_once('../include/stundensatztyp.class.php');

$rdf_url='http://www.technikum-wien.at/stundensatztyp';
?>

<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:STUNDENSATZTYP="<?php echo $rdf_url; ?>/rdf#"
>

	<RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
		
	$stundensatztyp = new stundensatzzyp();
	if (!$stundensatztyp->getAll())
		die($stundensatztyp->errormsg);

	foreach ($stundensatztyp->result as $row)
	{
		?>
		<RDF:li>
			<RDF:Description  id="<?php echo $row->stundensatztyp; ?>"  about="<?php echo $rdf_url.'/'.$row->stundensatztyp; ?>" >
				<STUNDENSATZTYP:typ><![CDATA[<?php echo $row->stundensatztyp  ?>]]></STUNDENSATZTYP:typ>
				<STUNDENSATZTYP:bezeichnung><![CDATA[<?php echo $row->bezeichnung  ?>]]></STUNDENSATZTYP:bezeichnung>
			</RDF:Description>
		</RDF:li>
		<?php
	}
?>
	</RDF:Seq>
</RDF:RDF>