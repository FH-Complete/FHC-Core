<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/vorlage.class.php');
require_once(dirname(__FILE__).'/addon.class.php');
require_once(dirname(__FILE__).'/studiengang.class.php');

class dokument_export
{
	private $content_xsl; // XSL Vorlage fuer content.xml
	private $styles_xsl; // XSL Vorlage fuer styles.xml
	private $xml_data; // XML Daten
	private $vorlage; // Vorlage Objekt
	private $vorlage_file; // Vorlage ODT/ODS in das hineingezippt wird
	private $outputformat; // Datentyp des Ausgabefiles
	private $filename; // Dateiname des Ausgabefiles
	private $temp_filename; // Dateinamen des Temp. Ausgabefiles
	private $temp_folder; // Ordner in dem die Temp Dateien abgelegt werden
	private $signed_filename; // Dateiname der signierten Datei
	private $images=array();
	private $sourceDir;
	public $errormsg;
	private $unoconv_version;
	private $sign;
	private $sign_user;
	private $sign_profile;

	/**
	 * Konstruktor
	 */
	public function __construct($vorlage = null, $oe_kurzbz=0, $version=null)
	{
		if(!isset($vorlage))
			return;

		if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true)
		{
			// Use docsbox!!
		}
		else
		{
			exec('unoconv --version',$ret_arr);
			if(isset($ret_arr[0]))
			{
				$hlp = explode(' ',$ret_arr[0]);
				if(isset($hlp[1]))
					$this->unoconv_version = $hlp[1];
				else
					die('Could not get Unoconv Version');
			}
			else
				die('Unoconv not found');
		}

		//Vorlage aus der Datenbank holen
		$this->vorlage = new vorlage();
		if(!$this->vorlage->getAktuelleVorlage($oe_kurzbz, $vorlage, $version))
			die('Keine Dokumentenvorlage gefunden');

		$this->content_xsl = new DOMDocument;
		if(!$this->content_xsl->loadXML($this->vorlage->text))
			die('unable to load xsl');

		// Style Vorlage laden falls vorhanden
		if($this->vorlage->style!='')
		{
			$this->styles_xsl = new DOMDocument;
			if(!$this->styles_xsl->loadXML($this->vorlage->style))
				die('unable to load styles xsl');
		}

		switch($this->vorlage->mimetype)
		{
			case 'application/vnd.oasis.opendocument.text':
					$this->outputformat = 'odt';
					$this->vorlage_file = $this->vorlage->vorlage_kurzbz.'.odt';
					break;
			case 'application/vnd.oasis.opendocument.spreadsheet':
					$this->outputformat = 'ods';
					$this->vorlage_file = $this->vorlage->vorlage_kurzbz.'.ods';
					break;
			default:
					$this->outputformat = 'pdf';
					$this->vorlage_file = $this->vorlage->vorlage_kurzbz.'.odt';
		}

		if($this->vorlage->bezeichnung!='')
			$this->filename = $this->vorlage->bezeichnung;
		else
			$this->filename = $this->vorlage->vorlage_kurzbz;

	}

	/**
	 * Laedt die XML Daten fuer die XSL Transformation anhand eines Arrays
	 * @param $data Array mit Daten
	 * @param $root Bezeichnung des Root Nodes
	 * @return boolean true
	 */
	public function addDataArray($data, $root)
	{
		$this->xml_data = new DOMDocument;
		$this->xml_data->loadXML($this->ConvertArrayToXML($data,$root));
		return true;
	}

	/**
	 * XML Daten fuer die XSL Transformation
	 * @param $xml
	 * @return boolean true
	 */
	public function addDataXML($xml)
	{
		$this->xml_data = new DOMDocument;
		$this->xml_data->loadXML($xml);
		return true;
	}

	/**
	 * URL zu XML Datei die fuer XSLTransformation verwendet werden soll
	 * @param $xml URL zu XML
	 * @param $params GET Parameter die an XML URL uebergeben werden
	 * @return boolean true
	 */
	public function addDataURL($xml, $params)
	{
		$xml_found = false;
		$addons = new addon();

		foreach($addons->aktive_addons as $addon)
		{
			$xmlfile = DOC_ROOT.'addons/'.$addon.'/rdf/'.$xml;
			if(file_exists($xmlfile))
			{
				$xml_found = true;
				$xml_url = XML_ROOT.'../addons/'.$addon.'/rdf/'.$xml.'?'.$params;
				break;
			}
		}
		if(!$xml_found)
			$xml_url=XML_ROOT.$xml.'?'.$params;


		// Load the XML source
		$this->xml_data = new DOMDocument;

		if(!$this->xml_data->load($xml_url))
			die('unable to load xml: '.$xml_url.' XML:'.$xml.' PARAMs:'.$params);

		return true;
	}

	/**
	 * Fuegt ein Bild zum Dokument hinzu
	 * @param $path Pfad zum Bild im Filesystem
	 * @param $name Name des Bildes das es im Dokument haben soll ohne Pfad (zB 1.png)
	 * @param $contenttype Contenttype des Bilds (zB image/png)
	 */
	public function addImage($path, $name, $contenttype)
	{
		$this->images[]=array('path'=>$path,'name'=>$name,'contenttype'=>$contenttype);
	}

	/**
	 * Erstellt das ODT Dokument inklusive Bilder und konvertiert es ins gewuenschte Format
	 * @param $outputformat ODT, PDF, DOC
	 * @return true wenn ok
	 */
	public function create($outputformat=null)
	{
		if(!is_null($outputformat))
			$this->outputformat=$outputformat;

		// content.xml erstellen
		$proc = new XSLTProcessor;
		$proc->importStyleSheet($this->content_xsl);

		$contentbuffer = $proc->transformToXml($this->xml_data);

		$this->temp_folder = sys_get_temp_dir().'/fhcunoconv-'.uniqid();
		mkdir($this->temp_folder);
		$this->sourceDir = getcwd();
		chdir($this->temp_folder);
		file_put_contents($this->temp_folder . '/content.xml', $contentbuffer);

		// styles.xml erstellen
		if(!is_null($this->styles_xsl))
		{
			$style_proc = new XSLTProcessor;
			$style_proc->importStyleSheet($this->styles_xsl);

			$stylesbuffer = $style_proc->transformToXml($this->xml_data);

			file_put_contents($this->temp_folder . '/styles.xml', $stylesbuffer);
		}

		// Template holen
		$vorlage_found=false;
		$addons = new addon();

		foreach($addons->aktive_addons as $addon)
		{
			$zipfile = DOC_ROOT.'addons/'.$addon.'/system/vorlage_zip/'.$this->vorlage_file;

			if(file_exists($zipfile))
			{
				$vorlage_found=true;
				break;
			}
		}
		if(!$vorlage_found)
			$zipfile = DOC_ROOT.'system/vorlage_zip/'.$this->vorlage_file;

		$tempname_zip = $this->temp_folder . '/out.zip';

		if(!copy($zipfile, $tempname_zip))
			die('copy failed');

		exec("zip $tempname_zip content.xml");
		if(!is_null($this->styles_xsl))
			exec("zip $tempname_zip styles.xml");

		// bilder hinzufuegen
		if(count($this->images)>0)
		{
			// Unterordner fuer die Bilder erstellen
			mkdir('Pictures');

			// Manifest Datei holen
			exec('unzip '.$tempname_zip.' META-INF/manifest.xml');

			// Bild zur Manifest Datei hinzufuegen
			$manifest = file_get_contents('META-INF/manifest.xml');

			$manifest_xml = new DOMDocument;
			if(!$manifest_xml->loadXML($manifest))
				die('Manifest File ungueltig');

			//root-node holen
			$root = $manifest_xml->getElementsByTagName('manifest')->item(0);

			foreach($this->images as $bild)
			{
				copy($bild['path'], 'Pictures/'.$bild['name']);

				//Neues Element unterhalb des Root Nodes anlegen
				$node = $manifest_xml->createElement("manifest:file-entry");
				$node->setAttribute("manifest:full-path",'Pictures/'.$bild['name']);
				$node->setAttribute("manifest:media-type",$bild['contenttype']);
				$root->appendChild($node);
			}

			$out = $manifest_xml->saveXML();

			//geaenderte Manifest Datei speichern und wieder ins Zip packen
			file_put_contents('META-INF/manifest.xml', $out);
			exec('zip '.$tempname_zip.' META-INF/*');

			// Bilder zum ZIP-File hinzufuegen
			exec("zip $tempname_zip Pictures/*");
		}

		clearstatcache();

		switch($this->outputformat)
		{
			case 'pdf':
			case 'doc':
				$ret = 0;
				$this->temp_filename = $this->temp_folder . '/out.' . $this->outputformat;

				// If it is set to use docsbox
				if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true)
				{
					require_once(dirname(__FILE__).'/../application/libraries/DocsboxLib.php');

					$ret = DocsboxLib::convert($tempname_zip, $this->temp_filename, $this->outputformat);
				}
				else // otherwise use unoconv
				{
					// Unoconv Version 0.6 hat eine Bug wodurch die Berechtigungen des PDF/Doc nicht korrekt gesetzt
					// werden. Deshalb wird dies hier speziell behandelt.
					// Die 2. Variante hat den Vorteil dass hier eine bessere Fehlerbehandlung moeglich ist
					if ($this->unoconv_version == '0.6')
						$command = 'unoconv -e IsSkipEmptyPages=false -f ' . $this->outputformat . '  %2$s > %1$s';
					else
						$command = 'unoconv -e IsSkipEmptyPages=false -f ' . $this->outputformat . ' --output %s %s 2>&1';

					$command = sprintf($command, $this->temp_filename, $tempname_zip);

					exec($command, $out, $ret);
				}

				if ($ret != 0)
				{
					$this->errormsg = 'Dokumentenkonvertierung ist derzeit nicht möglich. Bitte versuchen Sie es in einer Minute erneut oder kontaktieren Sie einen Administrator';
					return false;
				}
				break;
			case 'odt':
			default:
				$this->temp_filename = $tempname_zip;

		}

		if($this->sign)
			return $this->_sign();

		return true;
	}

	/**
	 * Liefert das Dokument mit den passenden Headern zum Download oder als ReturnValue
	 * @param $download wenn true werden Header gesendet und das Dokument ausgeliefert
	 * 					wenn false wird es als Returnwert zurueckgeliefert
	 * @return boolean true oder Dokument
	 */
	public function output($download=true)
	{
		if($this->signed_filename!='')
		{
			$fsize = filesize($this->signed_filename);
			if(!$handle = fopen($this->signed_filename,'r'))
				die('load failed');
		}
		else
		{
			$fsize = filesize($this->temp_filename);
			if(!$handle = fopen($this->temp_filename,'r'))
				die('load failed');
		}

		if($download)
		{
			if(headers_sent())
				exit('Header wurden bereits gesendet -> Abbruch');

			switch($this->outputformat)
			{
				case 'pdf':
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'.$this->filename.'.pdf"');
					header('Content-Length: '.$fsize);
					break;

				case 'doc':
					header('Content-type: application/vnd.ms-word');
					header('Content-Disposition: attachment; filename="'.$this->filename.'.doc"');
					header('Content-Length: '.$fsize);
					break;

				case 'odt':
					header('Content-type: application/vnd.oasis.opendocument.text');
					header('Content-Disposition: attachment; filename="'.$this->filename.'.odt"');
					header('Content-Length: '.$fsize);
					break;
				default:
					exit('Outputformat is not defined');
			}

			while (!feof($handle))
			{
				echo fread($handle, 8192);
			}
			fclose($handle);
			return true;
		}
		else
		{
			$data = fread($handle, $fsize);
			fclose($handle);
			return $data;
		}
	}

	/**
	 * Loescht die Temporaeren Dateien die angelegt wurden
	 * @return boolean true
	 */
	public function close()
	{
		unlink('content.xml');
		if($this->styles_xsl!='')
			unlink('styles.xml');

		if(file_exists($this->temp_filename))
			unlink($this->temp_filename);

		if($this->signed_filename != '')
			unlink($this->signed_filename);

		if(file_exists("out.zip"))
			unlink('out.zip');

		if(count($this->images)>0)
		{
			unlink('META-INF/manifest.xml');

			foreach($this->images as $bild)
				unlink('Pictures/'.$bild['name']);
			rmdir('Pictures');
			rmdir('META-INF');
		}


		rmdir($this->temp_folder);
		chdir($this->sourceDir);

		return true;
	}

	/**
	 * Konvertiert das Array in ein XML
	 * @param $data PHP Array mit den Daten
	 * @param $rootElement Bezeichnung des XML Wurzelelements
	 * @param $xml_data SimpleXMLElement fuer Rekursionsaufloesung
	 * @return xml
	 */
	private function ConvertArrayToXML($data, $rootElement=null, $xml_data=null )
	{
		$_xml_data = $xml_data;
		if ($_xml_data === null)
			$_xml_data = new SimpleXMLElement($rootElement !== null ? '<'.$rootElement.' />' : '<root/>');

		foreach( $data as $key => $value )
		{
			if( is_array($value) )
			{
				if( is_numeric($key) )
				{
					$key = 'item'.$key; //dealing with <0/>..<n/> issues
					$this->ConvertArrayToXML($value, null, $_xml_data);
				}
				else
				{
					$subnode = $_xml_data->addChild($key);
					$this->ConvertArrayToXML($value, null, $subnode);
				}
			}
			else
			{
				// Remove UTF8 Control Characters (breaking XML)
				$value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
				$_xml_data->addChild("$key",htmlspecialchars("$value"));
			}
		}
		return $_xml_data->asXML();
	}

	/**
	* Konvertiert ein Dokument in ein anderes Format
	* @param string $inFile Origin File Path
	* @param string $outFile Output file
	* @param string $format Format to export To
	* @return boolean
	*/
	public function convert($inFile, $outFile, $format = "pdf")
	{
		$ret = 0;

		// If it is set to use DOCSBOX
		if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true)
		{
			require_once(dirname(__FILE__).'/../application/libraries/DocsboxLib.php');

			$ret = DocsboxLib::convert($inFile, $outFile, $format);
		}
		else // fallback to unoconv
		{
			if($this->unoconv_version=='0.6')
				$command = 'unoconv -f %1$s  %3$s > %2$s';
			else
				$command = 'unoconv -f %s --output %s %s 2>&1';
			$command = sprintf($command, $format, $outFile, $inFile);

			exec($command, $out, $ret);
		}

		if ($ret != 0)
		{
			$this->errormsg = 'Dokumentenkonvertierung ist derzeit nicht möglich. Bitte versuchen Sie es in einer Minute erneut oder kontaktieren Sie einen Administrator';
			return false;
		}

		return true;
	}

	/**
	 * Set the Filename
	 * @param string $filename Filename without Extension.
	 * @return void
	 */
	public function setFilename($filename)
	{
		$this->filename = $filename;
	}

	/**
	 * Markiert das Dokument zur Signatur
	 * Fuegt automatisch einen XML Tag fuer Signatur zun Dokument hinzu
	 * @param $user User der die Signatur erstellen will
	 * @param $profile Signaturprofil mit der das Dokument signiert werden soll (Optional)
	 */
	public function sign($user, $profile = null)
	{
		$this->sign = true;
		$this->sign_user = $user;
		$this->sign_profile = $profile;

		$signblock = $this->xml_data->createElement("signed","true");
		$this->xml_data->documentElement->appendChild($signblock);
	}

	/**
	 * Schickt das Dokument an den Signaturserver um dieses mit einer Amtssignatur zu versehen
	 * Es koennen nur PDFs signiert werden
	 */
	private function _sign()
	{
		if($this->outputformat != 'pdf')
		{
			$this->errormsg = 'Derzeit koennen nur PDFs signiert werden';
			return false;
		}

		// Load the File
		$file_data = file_get_contents($this->temp_filename);

		$data = new stdClass();
		$data->document = base64_encode($file_data);

		// Signatur Profil
		if(!is_null($this->sign_profile))
			$data->profile = $this->sign_profile;
		else
			$data->profile = SIGNATUR_DEFAULT_PROFILE;

		// Username des Endusers der die Signatur angefordert hat
		$data->user = $this->sign_user;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, SIGNATUR_URL);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_USERAGENT, "FH-Complete");

		// SSL Zertifikatsprüfung deaktivieren
		// Besser ist es das Zertifikat am Server zu installieren!
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data_string = json_encode($data,JSON_FORCE_OBJECT);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length:'.mb_strlen($data_string),
			'Authorization: Basic '.base64_encode(SIGNATUR_USER.":".SIGNATUR_PASSWORD)
			)
		);

		$result = curl_exec($ch);
		if (curl_errno($ch))
		{
			curl_close($ch);
			$this->errormsg = 'Signaturserver ist derzeit nicht erreichbar';
		}
		else
		{
			curl_close($ch);
			$resultdata = json_decode($result);

			if (isset($resultdata->success) && $resultdata->success == 'true')
			{
				$this->signed_filename = $this->temp_folder .'/signed.pdf';
				file_put_contents($this->signed_filename, base64_decode($resultdata->document));
				return true;
			}
			else
			{
				if(isset($resultdata->errormsg))
					$this->errormsg = $resultdata->errormsg;
				else
					$this->errormsg = 'Unknown Error:'.print_r($resultdata,true);
				return false;
			}
		}
	}
	
	public function setXMLTag_archivierbar()
	{
		$archivierbar = $this->xml_data->createElement("archivierbar", "true");
		$this->xml_data->documentElement->appendChild($archivierbar);
	}
}
?>
