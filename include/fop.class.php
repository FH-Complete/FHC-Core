<?php
/*
$xml = file_get_contents('/path/to/your/xmlfile.xml');
$xsl = file_get_contenst('/path/to/your/xslfile.xsl');

$fop = new fop();
$pdf_filename = $fop->create_pdf($xml, $xsl);
*/
class fop
{
	var $xml;
	var $xsl;

	function fop()
	{
		//Apache FOP
	}

	function generatePdf($xml, $xsl, $filename, $destination)
	{
		$tmppdf = tempnam('/tmp', 'FAS_FOP');

		$tmpxml = tempnam('/tmp', 'FAS_FOP');
		$tmpxsl = tempnam('/tmp', 'FAS_FOP');
		
		file_put_contents($tmpxml, $xml);
		file_put_contents($tmpxsl, $xsl);

		
		exec("fop -xml {$tmpxml} -xsl {$tmpxsl} -pdf {$tmppdf} 2>&1", $output);
		
		@unlink($tmpxml);
		@unlink($tmpxsl);
        
		switch($destination)
		{
			case "D": // Download
						$buffer = file_get_contents($tmppdf);
						if(headers_sent())
						{
	      					echo 'Some data has already been output to browser, can\'t send PDF file';
	      					break;
						}
						
						if(isset($_SERVER['HTTP_USER_AGENT']) && mb_strpos($_SERVER['HTTP_USER_AGENT'],'MSIE'))
	      					header('Content-Type: application/force-download');
	    				else
	      					header('Content-Type: application/octet-stream');
	    				
	    				header('Content-Length: '.mb_strlen($buffer));
	    				header('Content-disposition: attachment; filename="'.$filename.'.pdf"');
	    				
						
	    				echo $buffer;
	    				unlink($tmppdf);
	    				break;
			case "F": // im Filesystem speichern
						break;
						
			case "I": //auf Stdout ausgeben
						echo file_get_contents($tmppdf);
						break;
		}

		return($tmppdf);
	}
}
?>