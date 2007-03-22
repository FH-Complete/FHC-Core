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

class datum
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $ts_day=86400;	// Timestamp eines Tages
	var $result = array(); // studiensemester Objekt

	function datum()
	{
	}

	/**
	 * Liefert einen UNIX Timestamp von einem String im
	 * Format "31.12.2007 14:30"
	 */
	function mktime_datumundzeit($datumundzeit)
	{
		if(ereg("([0-9]{2}).([0-9]{2}).([0-9]{4}) ([0-9]{2}):([0-9]{2})",$datumundzeit, $regs))
			return mktime($regs[4],$regs[5],0,$regs[2],$regs[1],$regs[3]);
		else
		{
			$this->errormsg = 'Falsches Datumsformat';
			return false;
		}
	}

	/**
	 * Liefert einen UNIX Timestamp von einem String im
	 * Format "31.12.2007"
	 */
	function mktime_datum($datum)
	{
		if(ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})",$datum, $regs))
		{
			return mktime(0,0,0,$regs[2],$regs[1],$regs[3]);
		}
		else
		{
			$this->errormsg = 'Falsches Datumsformat';
			return false;
		}
	}

	/**
	 * Liefert einen UNIX Timestamp von einem Datum im
	 * Format "2007-01-31"
	 */
	function mktime_fromdate($datum)
	{
		if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$datum, $regs))
		{
			return mktime(0,0,0,$regs[2],$regs[3],$regs[1]);
		}
		else
		{
			$this->errormsg = 'Falsches Datumsformat';
			return false;
		}
	}

	/**
	 * Liefert einen UNIX Timestamp von einem String im
	 * Format "2007-01-31 14:30:12"
	 */
	function mktime_fromtimestamp($timestamp)
	{
		if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$timestamp, $regs))
		{
			return mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]);
		}
		else
		{
			$this->errormsg = 'Falsches Datumsformat';
			return false;
		}
	}
}
?>