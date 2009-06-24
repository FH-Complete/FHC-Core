<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Erstellt ein PDF mithilfe Apache FOP
 * 
 * $xml = file_get_contents('/path/to/your/xmlfile.xml');
 * $xsl = file_get_contenst('/path/to/your/xslfile.xsl');
 * $fop = new fop();
 * $pdf_filename = $fop->create_pdf($xml, $xsl);
*/
class fop
{
	var $xml;
	var $xsl;

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		//Apache FOP
	}

	public function generatePdf($xml, $xsl, $filename, $destination)
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