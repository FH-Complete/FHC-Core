<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class DocumentLib
{
	private $unoconv_version;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Gets CI instance
		$this->ci =& get_instance();

		// Which document converter has to be used
		if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true)
		{
			// Use docsbox!!
		}
		else
		{
			exec('unoconv --version', $ret_arr);

			if(isset($ret_arr[0]))
			{
				$hlp = explode(' ', $ret_arr[0]);
				if(isset($hlp[1]))
				{
					$this->unoconv_version = $hlp[1];
				}
				else
					show_error('Could not get Unoconv Version');
			}
			else
				show_error('Unoconv not found - Please install Unoconv');
		}
	}

	/**
	 * Converts a File to PDF
	 * @param string $filename Full path to the file.
	 * @return success or error object
	 */
	public function convertToPDF($filename)
	{
		if (!file_exists($filename))
			return error('Unable to Convert to PDF. File not found:'.$filename);

		$mimetype = mime_content_type($filename);
		$outFile = sys_get_temp_dir().'/FHC_'.uniqid().'.pdf';

		switch ($mimetype)
		{
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$this->_jpegtopdf($filename, $outFile);
				return success($outFile);
			case 'application/vnd.oasis.opendocument.spreadsheet':
			case 'application/msword':
			case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
			case 'application/haansoftdocx':
			case 'application/vnd.ms-word':
			case 'application/vnd.oasis.opendocument.text':
			case 'text/plain':
				if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true)
				{
					// Use docsbox
				}
				else
				{
					// Unoconv Version 0.6 seems to fail on converting TXT Files
					if ($this->unoconv_version == '0.6')
						return error();
				}

				$ret = $this->convert($filename, $outFile, 'pdf');
				if(isSuccess($ret))
				{
					return success($outFile);
				}
				else
				{
					return error(getError($ret));
				}
			case 'application/pdf':
				return success($filename);
			default:
				return error('Unknown Mimetype:'.$mimetype);
		}
	}

	/**
	 * Combines multiple single PDFs to one PDF
	 *
	 * @param array $files Array of Files to merge (full path to file).
	 * @param string $outFile Path to the Output File.
	 * @return success or error object
	 */
	public function mergePDF($files, $outFile)
	{
		$cmd = "gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=$outFile ";
		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		// add all pdf files to the command
		foreach ($files as $f)
		{
			$cmd .= $f." ";
			if (!file_exists($f))
			{
				return error("File not found: '$f'");
			}
			if (finfo_file($finfo, $f) != "application/pdf")
			{
				return error("Wrong format(".finfo_file($finfo, $f)."): '$f'");
			}
		}

		finfo_close($finfo);

		$out = null;
		exec($cmd, $out, $ret);
		if ($ret != 0)
		{
			return error('PDF-zusammenfuegung ist derzeit nicht möglich. Bitte informieren Sie den Administrator');
		}
		return success(true);
	}

	/**
	 * Converts a Document to another format with unoconv
	 *
	 * @param string $inFile File that should be convertet.
	 * @param string $outFile Name of the Output File.
	 * @param string $format Outputformat (PDF, DOC, ...).
	 * @return success or error Object
	 */
	public function convert($inFile, $outFile, $format)
	{
		$ret = 0;

		// If it is set to use docsbox
		if (defined('DOCSBOX_ENABLED') && DOCSBOX_ENABLED === true)
		{
			require_once(dirname(__FILE__).'/../application/libraries/DocsboxLib.php');

			$ret = DocsboxLib::convert($inFile, $outFile, $format);
		}
		else // otherwise use unoconv
		{
			if ($this->unoconv_version == '0.6')
				$command = 'unoconv -f %1$s  %3$s > %2$s';
			else
				$command = 'unoconv -f %s --output %s %s 2>&1';
			$command = sprintf($command, $format, $outFile, $inFile);

			$out = null;
			exec($command, $out, $ret);
		}

		if ($ret != 0)
		{
			return error('Dokumentenkonvertierung ist derzeit nicht möglich. Bitte informieren Sie den Administrator');
		}

		return success(true);
	}

	/**
	 * Converts a JPG to PDF
	 *
	 * @param string $filename Path to JPG.
	 * @param string $outfile Path to Output (pdf) File.
	 * @return success or error object
	 */
	private function _jpegtopdf($filename, $outfile)
	{
		if (!file_exists($filename))
			return error('File does not exists');

		$size = getimagesize($filename);

		$margin_left_right = 18;
		$margin_bottom = 18;

		/*
		 * längere Seite ermitteln
		 * Hochformat wenn die Seiten gleich lang sind oder das Bild schmäler ist als die Seitenbreite
		 */
		if ($size[0] > $size[1] && $size[0] > 595)
		{
			$page_height = 595;
			$page_width = 842;
			//Wenn Bild kleiner oder gleich Seitenbreite, dann margin erhoehen
			if ($size[0] <= $page_width)
			{
				$margin_left_right = ($page_width - $size[0]) / 2;
				$margin_bottom = ($page_height - $size[1]);
			}
		}
		else
		{
			$page_height = 842;
			$page_width = 595;
			//Wenn Bild kleiner oder gleich Seitenbreite, dann margin erhoehen
			if ($size[0] <= $page_width)
			{
				$margin_left_right = ($page_width - $size[0]) / 2;
				$margin_bottom = ($page_height - $size[1]);
			}
		}

		// -r300 = 300 ppi
		$cmd = 'gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -r100 ';
		$cmd .= '-o '.$outfile.' viewjpeg.ps -c "('.$filename.') ';
		$cmd .= '<< /PageSize ['.$page_width.' '.$page_height.'] ';
		$cmd .= '/.HWMargins ['.$margin_left_right.' '.$margin_bottom.' '.$margin_left_right.' 18] ';
		$cmd .= '/countspaces {  [ exch { dup 32 ne { pop } if  } forall ] length } bind def  >> ';
		$cmd .= 'setpagedevice viewJPEG"';

		$out = null;
		exec($cmd, $out, $ret);
		if ($ret != 0)
		{
			$this->errormsg = 'jpegToPdf ist derzeit nicht möglich. Bitte informieren Sie den Administrator';
			return false;
		}
		return true;
	}
}
