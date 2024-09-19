<?php
/* Copyright (C) 2022 fhcomplete.net
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

require_once(dirname(__FILE__).'/../../vendor/nategood/httpful/bootstrap.php');

use \ZipArchive as ZipArchive;

/**
 * Simple client to convert documents using Docsbox
 */
class DocsboxLib
{
	const ERROR = 1;
	const SUCCESS = 0;
	const STATUS_FINISHED = 'finished'; // Docsbox status when a document conversion ended
	const STATUS_QUEUED = 'queued'; // Docsbox status when a file has been queued for the conversion
	const STATUS_STARTED = 'started'; // Docsbox status when a file has started being worked
	const STATUS_WORKING = 'working'; // Docsbox status when a file is being converted
	const OUTPUT_FILENAME = 'out.zip.pdf'; // File name used by docsbox to save a converted document

	const DEFAULT_FORMAT = 'pdf'; // Default supported format

	// -------------------------------------------------------------------------------------------------
	// Public static methods

	/**
	 * Static method used to convert a document using a Docsbox installation (local/remote) over the network
	 * It return 0 on success and any other integer on error
	 * NOTE: currently format is not supported
	 */
	public static function convert($inputFileName, $outputFileName, $format)
	{
		// If a format has not been given
		if (($format == null) || ($format != null && ctype_space($format) === true)) $format = self::DEFAULT_FORMAT;

		// Posts the file to docsbox
		$queueId = self::_postFile($inputFileName);
		// If an error occurred
		if ($queueId == null) return self::ERROR;

		// Checks and waits if the file has been converted
		$resultUrl = self::_checkConvertion($queueId);
		// If an error occurred
		if ($resultUrl == null) return self::ERROR;

		// Download and rename the converted file
		$downloaded = self::_downloadFile($resultUrl, $outputFileName);
		// If an error occurred
		if (!$downloaded) return self::ERROR;

		return self::SUCCESS;
	}

	// -------------------------------------------------------------------------------------------------
	// Private static methods

	/**
	 * Posts the given file to a Docsboxserver and checks the response to return a valid queue id
	 * On failure it returns a null value
	 */
	private static function _postFile($inputFileName)
	{
		$queueId = null;

		try
		{
			// Posts the given file and expects a response in JSON format
			$postFileResponse = \Httpful\Request::post(DOCSBOX_SERVER.DOCSBOX_PATH_API)
				->attach(array('file' => $inputFileName))
				->expectsJson()
				->send();

			// Checks that:
			// - the response is not empty
			// - the reponse body has the property id
			// - the property id is a valid string
			// - the reponse body has the property status
			// - docsbox queued the conversion of the posted file
			if (is_object($postFileResponse)
				&& isset($postFileResponse->body)
				&& isset($postFileResponse->body->id)
				&& $postFileResponse->body->id != '' && $postFileResponse->body->id != null
				&& isset($postFileResponse->body->status)
				&& $postFileResponse->body->status == self::STATUS_QUEUED)
			{
				$queueId = $postFileResponse->body->id;
			}
			else
			{
				// If docsbox refused to convert the posted file
				if (isset($postFileResponse->body->status)
					&& $postFileResponse->body->status != self::STATUS_QUEUED)
				{
					error_log(
						'Docsbox did not queue the posted file. Returned status: '.
						$postFileResponse->body->status
					);
				}
				else // any other generic error
				{
					error_log(
						'An error occurred while posting to docsbox. Response: '.
						print_r($postFileResponse, 1)
					);
				}
			}
		}
		catch(\Httpful\Exception\ConnectionErrorException $cee) // Httpful exception
		{
			error_log($cee->getMessage());
		}
		catch (Exception $e) // any other exception
		{
			error_log($e->getMessage());
		}

		return $queueId;
	}

	/**
	 * Check the status of the file convertion identified by the given queue element id
	 * A URL is returned with the path where it is possible to download the converted file
	 * If an error occurred then a null value is returned
	 */
	private static function _checkConvertion($queueId)
	{
		$resultUrl = null;
		$startConvertionsTime = time(); // time when the file conversion has started

		// Until a timeout has occurred
		while (time() - $startConvertionsTime <= DOCSBOX_CONVERSION_TIMEOUT)
		{
			sleep(DOCSBOX_WAITING_SLEEP_TIME); // takes a nap on every round

			try
			{
				// Calls the docsbox server to check the status of the
				// file conversion using the provided queue id
				// it expects a response in JSON format
				$getStatusResponse = \Httpful\Request::get(DOCSBOX_SERVER.DOCSBOX_PATH_API.$queueId)
					->expectsJson()
					->send();

				// Checks that:
				// - the response is not empty
				// - the reponse body has the property id
				// - the property id is a valid string
				// - the reponse body has the property status
				// - docsbox is working the conversion of the posted file
				if (is_object($getStatusResponse)
					&& isset($getStatusResponse->body->id)
					&& $getStatusResponse->body->id != '' && $getStatusResponse->body->id != null
					&& isset($getStatusResponse->body->status))
				{
					// Checks that docsbox has finished working on the file conversion
					// and that there is a valid resultUrl property
					if ($getStatusResponse->body->status == self::STATUS_FINISHED
						&& isset($getStatusResponse->body->result_url)
						&& $getStatusResponse->body->result_url != ''
						&& $getStatusResponse->body->result_url != null)
					{
						$resultUrl = $getStatusResponse->body->result_url;
						break;
					}
					// Just started or still working on it
					elseif ($getStatusResponse->body->status == self::STATUS_WORKING
						|| $getStatusResponse->body->status == self::STATUS_STARTED)
					{
						// go on!
					}
					else // any other status is abnormal
					{
						error_log(
							'Not valid status for queue element: '.$queueId.'. Response: '.
							print_r($getStatusResponse, 1)
						);
						break; // interrupt the loop on error
					}
				}
				else // if the response from the docsbox server is not valid
				{
					error_log(
						'An error occurred while checking the docsbox activity. Response: '.
						print_r($getStatusResponse, 1)
					);
					break; // interrupt the loop on error
				}
			}
			catch(\Httpful\Exception\ConnectionErrorException $cee) // Httpful exception
			{
				error_log($cee->getMessage());
				break; // interrupt the loop on error
			}
			catch (Exception $e) // any other exception
			{
				error_log($e->getMessage());
				break; // interrupt the loop on error
			}
		}

		return $resultUrl;
	}

	/**
	 * Download the converted file using the provided URL, unzip it, and renames it into the provided file name
	 */
	private static function _downloadFile($resultUrl, $outputFileName)
	{
		$downloaded = false; // pessimistic assumption

		try
		{
			// Download the file
			$getFileResponse = \Httpful\Request::get(DOCSBOX_SERVER.$resultUrl)->send();

			// If the downloaded file content is valid and not empty
			if (isset($getFileResponse->body)
				&& $getFileResponse->body != null
				&& $getFileResponse->body != '')
			{
				// Output directory where to unzip the downloaded zip file
				$outputDirectory = dirname($outputFileName);
				// The path and name of the downloaded zip file
				$temporaryDownloadedZip = sys_get_temp_dir().'/'.basename($resultUrl);

				// Write the file content into a temporary directory and file
				if (file_put_contents($temporaryDownloadedZip, $getFileResponse->body) != false)
				{
					$zipArchive = new ZipArchive;

					// Open and extract the dowloaded zip file into the directory of the output file
					if ($zipArchive->open($temporaryDownloadedZip) === true
						&& $zipArchive->extractTo($outputDirectory) === true
						&& $zipArchive->close() === true)
					{
						// Opened, extracted and closed!

						// Rename the extracted file to the given output file name
						if (rename($outputDirectory.'/'.self::OUTPUT_FILENAME, $outputFileName))
						{
							$downloaded = true;
						}
						else
						{
							error_log(
								'An error occurred while renaming the extracted file: '.
								$outputDirectory.'/'.self::OUTPUT_FILENAME.' into: '.
								$outputFileName
							);
						}
					}
					else
					{
						error_log(
							'An error occurred while working the dowloaded zip file: '.
							$temporaryDownloadedZip
						);
					}
				}
				else // if an error occurred while writing
				{
					error_log(
						'An error occurred while writing the file content to: '.
						$temporaryDownloadedZip
					);
				}
			}
			else // if the downloaded file is not valid
			{
				error_log(
					'An error occurred while downloading the file from the docsbox server: '.
					print_r($getFileResponse, 1)
				);
			}
		}
		catch(\Httpful\Exception\ConnectionErrorException $cee)
		{
			error_log($cee->getMessage());
		}
		catch (Exception $e)
		{
			error_log($e->getMessage());
		}

		return $downloaded;
	}
}

