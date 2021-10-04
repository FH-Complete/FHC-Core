<?php
/* Copyright (C) 2021 fhcomplete.net
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

require_once(dirname(__FILE__).'/../vendor/nategood/httpful/bootstrap.php');

class Docsbox
{
	/**
	 *
	 */
	public static function convert($inputFileName, $outputFileName)
	{
		try
		{
			$response = \Httpful\Request::post('http://docconverter.technikum-wien.at/api/v1/')
				->attach(array('file' => $inputFileName))
				->expectsJson()
				->send();

			var_dump($response);exit;
		}
		catch(\Httpful\Exception\ConnectionErrorException $cee)
		{
			// Error
		}
		catch (Exception $e)
		{
			// Error
		}

		if (is_object($response) && isset($response->id) && isset($response->status))
		{
			$status = null;
			$result_url = null;

			while ($status == null)
			{
				try
				{
					$response = \Httpful\Request::get('http://docconverter.technikum-wien.at/api/v1/'.$response->id)
						->expectsJson()
						->send();

					var_dump($response);
				}
				catch(\Httpful\Exception\ConnectionErrorException $cee)
				{
					// Error
				}
				catch (Exception $e)
				{
					// Error
				}

				if (is_object($response) && isset($response->id) && isset($response->status))
				{
					if ($response->status == 'finished' && isset($response->result_url))
					{
						$status = $response->status;
						$result_url = $response->result_url;
					}
					else
					{
						// Error
					}
				}
				else
				{
					// Error
				}
			}

			try
			{
				$response = \Httpful\Request::get($result_url)->send();

				var_dump($response);
			}
			catch(\Httpful\Exception\ConnectionErrorException $cee)
			{
				// Error
			}
			catch (Exception $e)
			{
				// Error
			}

			var_dump($response);exit;
		}
		else
		{
			// Error
		}
	}
}

