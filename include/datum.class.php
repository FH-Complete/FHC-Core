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
	public $ts_day=86400;	// Timestamp eines Tages

	/**
	 * Konstruktor
	 *
	 */
	public function __construct()
	{
	}

	/**
	 * Liefert einen UNIX Timestamp von einem String im
	 * Format "31.12.2007 14:30"
	 */
	public function mktime_datumundzeit($datumundzeit)
	{
		if(mb_ereg("([0-9]{2}).([0-9]{2}).([0-9]{4}) ([0-9]{2}):([0-9]{2})",$datumundzeit, $regs))
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
	public function mktime_datum($datum)
	{
		if(mb_ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})",$datum, $regs))
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
	public function mktime_fromdate($datum)
	{
		if(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$datum, $regs))
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
	public function mktime_fromtimestamp($timestamp)
	{
		if(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$timestamp, $regs))
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
	public function jump_week($datum, $wochen)
	{
		$days = $wochen * 7;

		$datetime=new DateTime();
		$datetime->setTimestamp($datum);
		$datetime->modify($days.' day');
		return $datetime->format("U");
	}

	/**
	 * Springt von einen UNIX Timestamp ($datum) $days nach vor bzw. hinten
	 */
	public function jump_day($datum, $days)
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
	public function convertISODate($datum)
	{
		return (mb_strlen($datum)>0?date('d.m.Y',strtotime($datum)):'');
	}


	/**
	 * Prueft Uhrzeit auf Gueltigkeit (HH:MM:SS)
	 * @return true wenn ok, false wenn falsches Format
	 */
	public function checkUhrzeit($uhrzeit)
	{
		if(mb_ereg("([0-9]{2}):([0-9]{2})(:([0-9]{2}))?$",$uhrzeit))
			return true;
		else
			return false;
	}

	/**
	 * Prueft ob das Datum im Format dd.mm.YYYY oder YYYY-mm-dd ist UND ob es sich um ein gültiges Datum handelt
	 * @return true wenn ok, false wenn falsches Format und/oder nicht gültig
	 */
	public function checkDatum($datum)
	{
		//Format dd.mm.yyyy
		if(mb_ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})$", $datum))
		{

			$year = substr($datum, 6, 4);
			$month = substr($datum, 3, 2);
			$day = substr($datum, 0, 2);
		}

		//Format yyyy-mm-dd
		elseif(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})$", $datum))
		{
			$year = substr($datum, 0, 4);
			$month = substr($datum, 5, 2);
			$day = substr($datum, 8, 2);
		}
		else
		{
			return false;
		}

		return	checkdate($month, $day, $year);
	}



	/**
	 * Zieht ein Datum von einem anderen ab, und gibt die differenz in Tagen zurueck (mit Vorzeichen)
	 * @param $datum1
	 * @param $datum2
	 */
	public function DateDiff($datum1, $datum2)
	{
		$datetime1 = new DateTime($datum1);
		$datetime2 = new DateTime($datum2);
		$interval = $datetime1->diff($datetime2);
		return $interval->format('%R%a');
	}

	/**
	 * Prueft ob ein Datum / Datum und Uhrzeit zwischen 2 anderen liegt
	 * Unterstuetzt auch offenes (leeres) Start und Ende Datum
	 *
	 * @param $start Startdatum
	 * @param $ende Endedatum
	 * @param $datum Datum das geprueft wird
	 * @return true wenn dazwischen sonst false
	 */
	public function between($start, $ende, $datum)
	{
		$datestart = new DateTime($start);
		$dateende = new DateTime($ende);
		$dateref = new DateTime($datum);

		// Start und Ende nicht gesetzt
		if($start=='' && $ende=='')
			return true;

		// Start nicht gesetzt; Ende gesetzt
		if($start=='' && $ende!='' && $dateende>=$dateref)
			return true;

		// Ende nicht gesetzt; Start gesetzt
		if($ende=='' && $start!='' && $datestart<=$dateref)
			return true;

		// Start und Ende gesetzt
		if($ende!='' && $start!='' && $datestart<=$dateref && $dateende>=$dateref)
			return true;

		return false;
	}

	/**
	 * Summiert 2 Zeiten Stunde:Minute
	 * Es liefert keine Uhrzeit zurueck sondern Stunden und Minuten
	 * zB 12:10 + 23:15 = 35:25
	 *
	 * @param $zeit1
	 * @param $zeit2
	 * @return summe der beiden zeiten im Format Stunden:Minuten
	 */
	public function sumZeit($zeit1, $zeit2)
	{
		list($h1, $m1) = explode(':', $zeit1);
		list($h2, $m2) = explode(':', $zeit2);

		$m1 +=$m2;

		if($m1>=60)
		{
			$uebertrag = (int)($m1/60);
			$h1+= $uebertrag;
		}
		$m1=$m1%60;
		$h1+=$h2;
		if($m1<10)
			$m1='0'.$m1;
		if($h1<10)
			$h1='0'.$h1;

		return $h1.':'.$m1;
	}

	/**
	 * Subtrahiert 2 Zeiten ($zeit1-$zeit2) Stunde:Minute
	 * Es liefert keine Uhrzeit zurueck sondern Stunden und Minuten
	 * zB 23:15 - 12:10 = 11:05
	 *
	 * @param $zeit1
	 * @param $zeit2
	 * @return subtraktion der beiden zeiten im Format Stunden:Minuten, null wenn zeit 1 kleiner als zeit2 ist
	 */
	public function subZeit($zeit1, $zeit2)
	{
		list($h1, $m1) = explode(':', $zeit1);
		list($h2, $m2) = explode(':', $zeit2);

		if($h1<$h2)
			return null;
		else if($h1 == $h2 && $m1<$m2)
			return null;

		$m1 -=$m2;
//echo $h1.','.$m1.','.$h2.','.$m2;
		if($m1<0)
		{
			$m1 = $m1 + 60;
			$h1 = (int)$h1-1;
		}
		$m1=$m1%60;
		$h1-=$h2;
		if($m1<10)
			$m1='0'.$m1;
		if($h1<10)
			$h1='0'.$h1;

		return $h1.':'.$m1;
	}

	/**
	 * Prueft und Liefert ein Datum im angegeben Format
	 *   		fuer die Formatierung wird die Funktion formatDatum verwendet
	 * @param $datum
	 * @param $format
	 * @param $strict wenn das Datum aus einem Suchfeld komment, dann strict auf TRUE setzen da sonst
	 * 				  Eintraege wie zB 'last Monday' oder 'a' auch in ein Datum umgewandelt werden.
	 * @return Formatierten Timestamp wenn ok, false im Fehlerfall
	 */
	function checkformatDatum($datum, $format='Y-m-d H:i:s', $strict=false)
	{

			@list($day, $month, $year) = @explode(".", $datum);
			if (@checkdate($month, $day, $year))
				return $this->formatDatum($datum, $format, $strict);
			@list($day, $month, $year) = @explode("-", $datum);
			if (@checkdate($month, $day, $year))
				return $this->formatDatum($datum, $format, $strict);
			@list($year, $month, $day) = @explode(".", $datum);
			if (@checkdate($month, $day, $year))
				return $this->formatDatum($datum, $format, $strict);
			@list($year, $month, $day) = @explode("-", $datum);
			if (@checkdate($month, $day, $year))
				return $this->formatDatum($datum, $format, $strict);

			if (strlen($datum)==6)
			{
				$year="20".substr($datum,0,2);
				$month=substr($datum,2,2);
				$day=substr($datum,4,2);
				if (@checkdate($month, $day, $year))
					return $this->formatDatum($datum, $format, $strict);
			}
			else if (strlen($datum)==8)
			{
				$year=substr($datum,0,4);
				$month=substr($datum,4,2);
				$day=substr($datum,	6,2);
				if (@checkdate($month, $day, $year))
					return $this->formatDatum($datum, $format, $strict);

				$year=substr($datum,5,4);
				$month=substr($datum,3,2);
				$day=substr($datum,	0,2);
				if (@checkdate($month, $day, $year))
					return $this->formatDatum($datum, $format, $strict);
			}
			return false;
		}


	/**
	 * Liefert ein Datum im angegeben Format
	 * ToDo: Liefert aktuellen Timestamp wenn Sonderzeichen uebergeben werden
	 *       zB '---'
	 * @param $datum
	 * @param $format
	 * @param $strict wenn das Datum aus einem Suchfeld kommt, dann strict auf TRUE setzen da sonst
	 * 				  Eintraege wie zB 'last Monday' oder 'a' auch in ein Datum umgewandelt werden.
	 * @return Formatierten Timestamp wenn ok, false im Fehlerfall
	 */
	public function formatDatum($datum, $format='Y-m-d H:i:s', $strict=false)
	{
		if(trim($datum)=='')
			return '';

		$ts='';
		$error=false;

		//2008-12-31
		if(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$datum, $regs))
			$ts = mktime(0,0,0,$regs[2],$regs[3],$regs[1]);

		//2008-12-31 12:30
		if(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2})",$datum, $regs))
			$ts = mktime($regs[4],$regs[5],0,$regs[2],$regs[3],$regs[1]);

		//2008-12-31 12:30:15
		if(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$datum, $regs))
			$ts = mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[3],$regs[1]);

		if($ts=='')
		{
			//1.12.2008
			if(mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})",$datum, $regs))
				$ts = mktime(0,0,0,$regs[2],$regs[1],$regs[3]);

			//1.12.2008 12:30
			if(mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4}) ([0-9]{2}):([0-9]{2})",$datum, $regs))
				$ts = mktime($regs[4],$regs[5],0,$regs[2],$regs[1],$regs[3]);

			//1.12.2008 12:30:15
			if(mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})",$datum, $regs))
				$ts = mktime($regs[4],$regs[5],$regs[6],$regs[2],$regs[1],$regs[3]);
		}

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

	/**
	 * konvertiert Zeit in format stunden:minuten in Stunden als Dezimalahl
	 * @param $timestring in Form stunden:minuten
	 * @return int Stundenzahl als Dezimalzahl
	 */
	public function convertTimeStringToHours($timestring)
	{
		return intval(substr($timestring, 0, 2)) + intval(substr($timestring, 3, 2)) / 60;
	}

}
?>
