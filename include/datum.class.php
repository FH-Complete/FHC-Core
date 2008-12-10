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
	 * ISO-Format "2007-01-31"
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

	/**
	 * Springt von einen UNIX Timestamp ($datum) $wochen nach vor bzw. hinten
	 */
	function jump_week($datum, $wochen)
	{
		$stunde_vor=date("G",$datum);
		// Eine Woche sind 604800 Sekunden
		$datum+=604800*$wochen;
		$stunde_nach=date("G",$datum);
		if ($stunde_nach!=$stunde_vor)
			$datum+=3600;
		return $datum;
	}

	/**
	 * Springt von einen UNIX Timestamp ($datum) $days nach vor bzw. hinten
	 */
	function jump_day($datum, $days)
	{
		$stunde_vor=date("G",$datum);
		// Ein Tag sind 86400 Sekunden
		$datum+=86400*$days;
		$stunde_nach=date("G",$datum);
		if ($stunde_nach!=$stunde_vor)
			$datum+=3600;
		return $datum;
	}

	/**
	 * Konvertiert das ISO Datumsformat (YYYY-MM-DD)
	 * nach (DD.MM.YYYY)
	 */
	function convertISODate($datum)
	{
		return (strlen($datum)>0?date('d.m.Y',strtotime($datum)):'');
	}


	/**
	 * Prueft Uhrzeit auf Gueltigkeit (HH:MM:SS)
	 * @return true wenn ok, false wenn falsches Format
	 */
	function checkUhrzeit($uhrzeit)
	{
		if(ereg("([0-9]{2}):([0-9]{2})(:([0-9]{2}))?$",$uhrzeit))
			return true;
		else
			return false;
	}

	/**
	 * Prueft ob das Datum im Format dd.mm.YYYY oder YYYY-mm-dd ist
	 * @return true wenn ok, false wenn falsches Format
	 */
	function checkDatum($datum)
	{
		if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})$",$datum) || ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})$",$datum))
			return true;
		else
			return false;
	}
	
	/**
	 * Liefert ein Datum im angegeben Format
	 * ToDo: Liefert aktuellen Timestamp wenn Sonderzeichen uebergeben werden
	 *       zB '---'
	 * @param $datum
	 * @param $format
	 * @param $strict wenn das Datum aus einem Suchfeld komment, dann strict auf TRUE setzen da sonst
	 * 				  Eintraege wie zB 'last Monday' oder 'a' auch in ein Datum umgewandelt werden.
	 * @return Formatierten Timestamp wenn ok, false im Fehlerfall
	 */
	function formatDatum($datum, $format='Y-m-d H:i:s', $strict=false)
	{
		if(trim($datum)=='')
			return '';
		
		$ts='';
		$error=false;
		
		//2008-12-31
		if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$datum, $regs))
			$ts = mktime(0,0,0,$regs[2],$regs[3],$regs[1]);
		
		//2008-12-31 12:30
		if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2})",$datum, $regs))
			$ts = mktime($regs[4],$regs[5],0,$regs[2],$regs[3],$regs[1]);
		
		//2008-12-31 12:30:15
		if(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$datum, $regs))
			$ts = mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]);
			
		//1.12.2008
		if(ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})",$datum, $regs))
			$ts = mktime(0,0,0,$regs[2],$regs[1],$regs[3]);
			
		//1.12.2008 12:30
		if(ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4}) ([0-9]{2}):([0-9]{2})",$datum, $regs))
			$ts = mktime($regs[4],$regs[5],0,$regs[2],$regs[1],$regs[3]);
				
		//1.12.2008 12:30:15
		if(ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$datum, $regs))
			$ts = mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[1],$regs[3]);
		
		if($ts=='' && !$strict)
		{
			$ts = strtotime($datum);
			if(!$ts || $ts==-1)
			{
				//wenn strtotime fehlschlaegt liefert diese -1 zurueck, ab php5.1.0 jedoch false
				$error = true;
			}
		}

		if($ts!='' && !$error)
			return date($format, $ts);
			
		return false;
	}
}
?>