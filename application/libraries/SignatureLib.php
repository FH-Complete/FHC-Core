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
			if (filesize($inputFileName) > 30000000) return $this->_returnObject(1, 1, 'File too big');

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

	/**
	 * 
	 */
	public static function sign($inputFileName, $user, $profile)
	{
		// Load the File
                $file_data = file_get_contents($inputFileName);

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
                        'Authorization: Basic ' . base64_encode(SIGNATUR_USER . ':' . SIGNATUR_PASSWORD)
                ]);

                $result = curl_exec($ch);
                if (curl_errno($ch)) {
                        curl_close($ch);
                        return $this->_returnObject(1, 1, 'CURL error');
                }
                curl_close($ch);
		$resultdata = json_decode($result);

		// If it is success
                if (isset($resultdata->error) && $resultdata->error == 0) {
                        $signed_filename = $temp_folder . '/signed.pdf';
                        file_put_contents($signed_filename, base64_decode($resultdata->retval));
                        return $this->_returnObject(0, 0, $signed_filename);
                }

                // Otherwise it is an error
                return $this->_returnObject(1, 1, 'Error while signing the given document');
	}

	/**
	 *
	 */
	private function _returnObject($code, $error, $retval)
	{
		$returnObject = new stdClass();
		$returnObject->code = $code;
		$returnObject->error = $error;
		$returnObject->retval = $retval;
		
		return $returnObject;
	}
}

