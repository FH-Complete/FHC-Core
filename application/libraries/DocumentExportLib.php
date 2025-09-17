<?php
/* Copyright (C) 2025 fhcomplete.net
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
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

use stdClass as stdClass;
use DOMDocument as DOMDocument;
use XSLTProcessor as XSLTProcessor;
use SimpleXMLElement as SimpleXMLElement;

/**
 * This library replaces the old document_export.class except for the convert
 * function which is located in the DocumentLib library.
 *
 * The usage differs a little bit from the old library:
 * In the old library you had to call create() then some optional function
 * for adding data (addDataArray()/addDataXML()/addDataURL()/setFilename()),
 * modifiing said data (sign()/setXMLTag_archivierbar()) or adding
 * images (addImage()) and then call output() and close().
 * Now the create, output and close functions are combined into one function and adding data and images is done via parameters.
 * Instead of calling addDataArray, addDataXML or addDataURL just call
 * getDataArray, getDataXML or getDataURL respectevily and use the return
 * value as $xml_data parameter in the getContent call.
 * Instead of calling addImages just create an array and pass it as $images
 * parameter to the getContent function.
 * To get/show a signed document just pass a valid uid as $sign_user
 * parameter.
 *
 * Example:
 * Old:
 * $doc = new document_export($vorlage->vorlage_kurzbz, $oe_kurzbz, $version);
 * $doc->setFilename($filename);
 * $doc->addDataXML($data);
 * $doc->addImage($imagepath, $imagename, $imagecontenttype);
 * $doc->create($outputformat);
 * $doc->output(true);
 * $doc->close();
 *
 * New:
 * $xml_data = $this->documentexportlib->getDataXML($data);
 * $images = [[
 * 	'path' => $imagepath,
 * 	'name' => $imagename,
 * 	'contenttype' => $imagecontenttype
 * ]];
 */
class DocumentExportLib
{
	private $_ci;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Gets CI instance
		$this->_ci =& get_instance();

		// Load Phrases
		$this->_ci->load->library('DocumentLib', ['document_export', null], 'DocumentExportPhrases');
	}

	/**
	 * Laedt die XML Daten fuer die XSL Transformation anhand eines Arrays
	 *
	 * @param array					$data Array mit Daten
	 * @param string	 			$root Bezeichnung des Root Nodes
	 *
	 * @return DOMDocument
	 */
	public function getDataArray($data, $root)
	{
		$xml_data = new DOMDocument();
		$xml_data->loadXML($this->convertArrayToXML($data, $root));
		return $xml_data;
	}

	/**
	 * XML Daten fuer die XSL Transformation
	 *
	 * @param string				$xml
	 *
	 * @return DOMDocument
	 */
	public function getDataXML($xml)
	{
		$xml_data = new DOMDocument();
		$xml_data->loadXML($xml);
		return $xml_data;
	}

	/**
	 * URL zu XML Datei die fuer XSLTransformation verwendet werden soll
	 *
	 * @param string				$xml URL to XML
	 * @param string				$params GET parameter
	 *
	 * @return stdClass
	 */
	public function getDataURL($xml, $params)
	{
		$xml_found = false;

		$aktive_addons = array_filter(array_map('trim', explode(";", ACTIVE_ADDONS)));
		foreach($aktive_addons as $addon) {
			$xmlfile = DOC_ROOT . 'addons/' . $addon . '/rdf/' . $xml;
			if (file_exists($xmlfile)) {
				$xml_found = true;
				$xml_url = XML_ROOT . '../addons/' . $addon . '/rdf/' . $xml . '?' . $params;
				break;
			}
		}
		if (!$xml_found)
			$xml_url = XML_ROOT . $xml . '?' . $params;


		// Load the XML source
		$xml_data = new DOMDocument;

		if (!$xml_data->load($xml_url))
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_xml_load", [
				"url" => $xml_url,
				"xml" => $xml,
				"params" => $params
			]));

		return success($xml_data);
	}

	/**
	 * Adds a XML Tag for signatur to the document
	 *
	 * @param DomDocument			$xml_data
	 *
	 * @return void
	 */
	protected function addSignToData($xml_data)
	{
		$signblock = $xml_data->createElement("signed", "true");
		$xml_data->documentElement->appendChild($signblock);
	}

	/**
	 * Adds a XML Tag for archive to the document
	 *
	 * @param DomDocument			$xml_data
	 *
	 * @return void
	 */
	public function addArchiveToData($xml_data)
	{
		$archiv = $xml_data->createElement("archivierbar", "true");
		$xml_data->documentElement->appendChild($archiv);
	}

	/**
	 * Get the contents of a Document
	 *
	 * @param stdClass				$vorlage A db entry from tbl_vorlage
	 * @param DomDocument			$xml_data
	 * @param string				$oe_kurzbz
	 * @param integer|null			$version (optional)
	 * @param string				$outputformat (optional)
	 * @param string				$sign_user (optional) Must be a valid uid
	 * @param string				$sign_profile (optional) Signatureprofile for signing
	 * @param array					$images (optional) Each element should have a property path, name & contenttype which are all strings
	 *
	 * @return stdClass
	 */
	public function getContent(
		$vorlage,
		$xml_data,
		$oe_kurzbz,
		$version = null,
		$outputformat = null,
		$sign_user = null,
		$sign_profile = null,
		$images = []
	) {
		$source_folder = getcwd();
		$temp_folder = sys_get_temp_dir() . '/fhcunoconv-' . uniqid();

		$outputformat = $this->getDefaultOutputFormat($outputformat, $vorlage->mimetype);

		$createResult = $this->createAndSignContent(
			$temp_folder,
			$outputformat,
			$vorlage,
			$oe_kurzbz,
			$version,
			$xml_data,
			$images,
			$sign_user,
			$sign_profile
		);
		if (isError($createResult)) {
			$this->close($temp_folder, $source_folder);
			return $createResult;
		}
		$temp_filename = getData($createResult);

		$fsize = filesize($temp_filename);
		$handle = fopen($temp_filename, 'r');
		if (!$handle)
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_file_load"));
		$fileContentResult = fread($handle, $fsize);
		fclose($handle);

		$this->close($temp_folder, $source_folder);

		return success($fileContentResult);
	}

	/**
	 * Helper function for getContent
	 * Creates the temp folder and calls create and sign functions.
	 *
	 * @param string				$temp_folder
	 * @param string				$outputformat
	 * @param stdClass				$vorlage
	 * @param string				$oe_kurzbz
	 * @param integer				$version
	 * @param DomDocument			$xml_data
	 * @param array					$images Each element should have a property path, name and contenttype which are all strings
	 * @param string				$sign_user Must be a valid uid
	 * @param string				$sign_profile Signatureprofile for signing
	 *
	 * @return stdClass
	 */
	protected function createAndSignContent(
		$temp_folder,
		$outputformat,
		$vorlage,
		$oe_kurzbz,
		$version,
		$xml_data,
		$images,
		$sign_user,
		$sign_profile
	) {
		mkdir($temp_folder);
		chdir($temp_folder);

		$this->_ci->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');

		$result = $this->_ci->VorlagestudiengangModel->getCurrent($vorlage->vorlage_kurzbz, $oe_kurzbz, $version);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_template_missing"));
		$vorlage_stg = current(getData($result));
		foreach ($vorlage_stg as $k => $v)
			$vorlage->$k = $v;

		if ($sign_user)
		{
			$this->addSignToData($xml_data);
		}

		$result = $this->create($temp_folder, $outputformat, $vorlage, $xml_data, $images);
		if (isError($result))
			return $result;

		$temp_filename = getData($result);

		if ($sign_user)
		{
			$result = $this->sign($temp_folder, $temp_filename, $outputformat, $sign_user, $sign_profile);
			if (isError($result))
				return $result;

			$temp_filename = getData($result);
		}

		return success($temp_filename);
	}

	/**
	 * Helper function for createAndSignContent.
	 * Creates the files in the temp folder.
	 *
	 * @param string				$temp_folder
	 * @param string				$outputformat
	 * @param stdClass				$vorlage
	 * @param DomDocument			$xml_data
	 * @param array					$images Each element should have a property path, name and contenttype which are all strings
	 *
	 * @return stdClass
	 */
	protected function create($temp_folder, $outputformat, $vorlage, $xml_data, $images)
	{
		$content_xsl = new DOMDocument();
		if (!$content_xsl->loadXML($vorlage->text))
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_xsl_load"));

		$proc = new XSLTProcessor();
		$proc->importStyleSheet($content_xsl);

		$contentbuffer = $proc->transformToXml($xml_data);

		file_put_contents($temp_folder . '/content.xml', $contentbuffer);

		if ($xml_data->firstChild->tagName == 'error')
			return error($xml_data->firstChild->textContent);

		$styles_xsl = null;
		// styles.xml erstellen
		if ($vorlage->style) {
			$styles_xsl = new DOMDocument();
			if (!$styles_xsl->loadXML($vorlage->style))
				return error($this->_ci->DocumentExportPhrases->t("document_export", "error_styles_load"));
			$style_proc = new XSLTProcessor();
			$style_proc->importStyleSheet($styles_xsl);

			$stylesbuffer = $style_proc->transformToXml($xml_data);

			file_put_contents($temp_folder . '/styles.xml', $stylesbuffer);
		}

		// Template holen
		$vorlage_found = false;
		$vorlage_filename = $vorlage->vorlage_kurzbz . ($vorlage->mimetype == 'application/vnd.oasis.opendocument.spreadsheet' ? '.ods' : '.odt');

		$aktive_addons = array_filter(array_map('trim', explode(";", ACTIVE_ADDONS)));
		foreach($aktive_addons as $addon) {
			$zipfile = DOC_ROOT . 'addons/' . $addon . '/system/vorlage_zip/' . $vorlage_filename;

			if (file_exists($zipfile)) {
				$vorlage_found = true;
				break;
			}
		}
		if (!$vorlage_found)
			$zipfile = DOC_ROOT . 'system/vorlage_zip/' . $vorlage_filename;

		$tempname_zip = $temp_folder . '/out.zip';

		if (!copy($zipfile, $tempname_zip))
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_file_copy"));

		exec("zip $tempname_zip content.xml");
		if (!is_null($styles_xsl))
			exec("zip $tempname_zip styles.xml");

		// bilder hinzufuegen
		if (count($images) > 0)
		{
			// Unterordner fuer die Bilder erstellen
			mkdir('Pictures');

			// Manifest Datei holen
			exec('unzip ' . $tempname_zip . ' META-INF/manifest.xml');

			// Bild zur Manifest Datei hinzufuegen
			$manifest = file_get_contents('META-INF/manifest.xml');

			$manifest_xml = new DOMDocument;
			if (!$manifest_xml->loadXML($manifest))
				return error($this->_ci->DocumentExportPhrases->t("document_export", "error_manifest"));

			//root-node holen
			$root = $manifest_xml->getElementsByTagName('manifest')->item(0);

			foreach ($images as $bild) {
				copy($bild['path'], 'Pictures/' . $bild['name']);

				//Neues Element unterhalb des Root Nodes anlegen
				$node = $manifest_xml->createElement("manifest:file-entry");
				$node->setAttribute("manifest:full-path", 'Pictures/' . $bild['name']);
				$node->setAttribute("manifest:media-type", $bild['contenttype']);
				$root->appendChild($node);
			}

			$out = $manifest_xml->saveXML();

			//geaenderte Manifest Datei speichern und wieder ins Zip packen
			file_put_contents('META-INF/manifest.xml', $out);
			exec('zip ' . $tempname_zip . ' META-INF/*');

			// Bilder zum ZIP-File hinzufuegen
			exec('zip ' . $tempname_zip . ' Pictures/*');
		}

		clearstatcache();

		switch ($outputformat) {
			case 'pdf':
			case 'doc':
				$ret = 0;
				$temp_filename = $temp_folder . '/out.' . $outputformat;

				if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true) {
					// Use docsbox
					require_once('DocsboxLib.php');

					$ret = DocsboxLib::convert($tempname_zip, $temp_filename, $outputformat);
				} else {
					// Use unoconv

					// Unoconv Version 0.6 hat eine Bug wodurch die Berechtigungen des PDF/Doc nicht korrekt gesetzt
					// werden. Deshalb wird dies hier speziell behandelt.
					// Die 2. Variante hat den Vorteil dass hier eine bessere Fehlerbehandlung moeglich ist
					if ($this->unoconv_version == '0.6')
						$command = 'unoconv -e IsSkipEmptyPages=false -f ' . $outputformat . '  %2$s > %1$s';
					else
						$command = 'unoconv -e IsSkipEmptyPages=false -f ' . $outputformat . ' --output %s %s 2>&1';

					$command = sprintf($command, $temp_filename, $tempname_zip);

					exec($command, $out, $ret);
				}

				if ($ret)
					return error($this->_ci->DocumentExportPhrases->t("document_export", "error_conv_timeout"));
				break;
			case 'odt':
			default:
				$temp_filename = $tempname_zip;
		}

		return success($temp_filename);
	}

	/**
	 * Helper function for createAndSignContent.
	 * Signs the main file in the temp folder.
	 *
	 * @param string				$temp_folder
	 * @param string				$temp_filename
	 * @param string				$outputformat
	 * @param string				$user Must be a valid uid
	 * @param string				$profile Signatureprofile for signing
	 *
	 * @return stdClass
	 */
	protected function sign($temp_folder, $temp_filename, $outputformat, $user, $profile)
	{
		if ($outputformat != 'pdf')
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_sign_pdf"));

		// Load the File
		$file_data = file_get_contents($temp_filename);

		$data = new stdClass();
		$data->document = base64_encode($file_data);

		// Signatur Profil
		if (!is_null($profile))
			$data->profile = $profile;
		else
			$data->profile = SIGNATUR_DEFAULT_PROFILE;

		// Username des Endusers der die Signatur angefordert hat
		$data->user = $user;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, SIGNATUR_URL . '/' . SIGNATUR_SIGN_API);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_USERAGENT, "FH-Complete");

		// SSL Zertifikatsprüfung deaktivieren
		// Besser ist es das Zertifikat am Server zu installieren!
		//curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$data_string = json_encode($data, JSON_FORCE_OBJECT);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Content-Length:' . mb_strlen($data_string),
			'Authorization: Basic ' . base64_encode(SIGNATUR_USER . ":" . SIGNATUR_PASSWORD)
		]);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
			curl_close($ch);
			return error($this->_ci->DocumentExportPhrases->t("document_export", "error_sign_timeout"));
		}
		curl_close($ch);
		$resultdata = json_decode($result);

		// If it is success
		if (isset($resultdata->error) && $resultdata->error == 0) {
			$signed_filename = $temp_folder . '/signed.pdf';
			file_put_contents($signed_filename, base64_decode($resultdata->retval));
			return success($signed_filename);
		}

		// otherwise if it is an error
		return error($resultdata->retval ?? $this->_ci->DocumentExportPhrases->t("global", "unknown_error", ["error" => $result]));
	}

	/**
	 * Deletes all files in the $temp_folder and changes back to the source_folder
	 *
	 * @param string				$temp_folder
	 * @param string				$source_folder
	 *
	 * @return void
	 */
	protected function close($temp_folder, $source_folder)
	{
		$files = glob($temp_folder . '/*'); // get all file names
		foreach ($files as $file)
			if (is_file($file))
				unlink($file);

		chdir($source_folder);
		rmdir($temp_folder);
	}

	/**
	 * Convert an array to XML
	 *
	 * @param array					$data
	 * @param string				$root
	 * @param SimpleXMLElement		$xml_data
	 *
	 * @return string|boolean
	 */
	private function convertArrayToXML($data, $root = null, $xml_data = null)
	{
		$_xml_data = $xml_data;
		if ($_xml_data === null)
			$_xml_data = new SimpleXMLElement($root !== null ? '<' . $root . ' />' : '<root/>');

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				if (is_numeric($key)) {
					$key = 'item' . $key; // dealing with <0/>..<n/> issues
					$this->convertArrayToXML($value, null, $_xml_data);
				} else {
					$subnode = $_xml_data->addChild($key);
					$this->convertArrayToXML($value, null, $subnode);
				}
			} else {
				// Remove UTF8 Control Characters (breaking XML)
				$value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
				$_xml_data->addChild((string)$key, htmlspecialchars("$value"));
			}
		}

		return $_xml_data->asXML();
	}

	/**
	 * Get default outputformat from mimetype if its not set
	 *
	 * @param string				$outputformat
	 * @param string				$mimetype
	 *
	 * @return string
	 */
	private function getDefaultOutputFormat($outputformat, $mimetype)
	{
		if ($outputformat)
			return $outputformat;

		if ($mimetype == 'application/vnd.oasis.opendocument.spreadsheet')
			return 'ods';
		if ($mimetype == 'application/vnd.oasis.opendocument.text')
			return 'odt';

		return 'pdf';
	}
}

