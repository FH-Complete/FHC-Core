<?php
/* Copyright (C) 2018 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
 /**
  * This class provides static functions for handling Result values
  */

class ErrorHandler
{
	/**
	 * Returns a Success Object
	 * @param $retval object
	 * @return result object
	 */
	public static function success($retval = '')
	{
		$data = new stdClass();
		$data->error = false;
		$data->errormsg = '';
		$data->retval = $retval;
		return $data;
	}

	/**
	 * Returns an Error Object
	 * @param $retval object (optional)
	 * @return result object
	 */
	public static function error($errormsg = '', $retval = '')
	{
		$data = new stdClass();
		$data->error = true;
		$data->errormsg = $errormsg;
		$data->retval = $retval;
		return $data;
	}

	/**
	 * Checks if the Result object is Successfull
	 * @param result object
	 * @return boolean
	 */
	public static function isSuccess($result)
	{
		if (is_object($result) && isset($result->error) && $result->error === false)
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks if the Result object is an error
	 * @param result object
	 * @return boolean
	 */
	public static function isError($result)
	{
		return !ErrorHandler::isSuccess($result);
	}

	/*
	 * Checks if the Result object contains additional data
	 * @param result object
	 * @return boolean
	 */
	public static function hasData($result)
	{
		if (is_object($result) && isset($result->retval) && $result->retval!='')
		{
			return true;
		}

		return false;
	}
}
