<?php
/**
 *  Copyright (C) 2022 fhcomplete.net
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

/**
 * Simple client to call the signature server
 */
class SignatureLib
{
	// -------------------------------------------------------------------------------------------------
	// Public static methods

	/**
	 * Returns the list of signature inside the given file
	 */
	public static function list($inputFileName)
	{
		try
		{
			// Dont send Document if it is bigger than 30 MB (Limit of Signature Server)
			if (filesize($inputFileName) > 30000000)
			{
				$returnObject = new stdClass();
				$returnObject->code = 1;
				$returnObject->error = 1;
				$returnObject->retval = 'File to big';

				return $returnObject;
			}

			// Get the content of the given file
			$inputFileContent = file_get_contents($inputFileName);
			if ($inputFileContent === false) // if failed
			{
				error_log('An error occurred while getting the content from: '.$inputFileName);
			}
			else
			{
				// Posts the given file content + file name and expects a response in JSON format
				$resultPost = \Httpful\Request::post(SIGNATUR_URL.'/'.SIGNATUR_LIST_API)
					->sendsJson()
					->authenticateWith(SIGNATUR_USER, SIGNATUR_PASSWORD)
					->body('{"filename": "'.basename($inputFileName).'", "content": "'.base64_encode($inputFileContent).'"}')
					->expectsJson()
					->send();
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

		// If the response is fine
		if (isset($resultPost->body) && is_object($resultPost->body)
			&& isset($resultPost->body->retval) && is_array($resultPost->body->retval))
		{
			return $resultPost->body->retval;
		}

		// Otherwise return a null as error
		return null;
	}
}
