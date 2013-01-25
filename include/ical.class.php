<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/**
 * Klasse ical
 * @create 27-01-2012
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class ical extends basis_db
{
	public $new;
	public $result = array();
	public $dtresult = array();

	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Importiert ein FreeBusy File
	 * 
	 * @param $ical
	 * @param $typ
	 */
	public function importFreeBusy($ical, $typ)
	{
		$rows = explode("\n",$ical);
		
		$idx = count($this->result);
		$status=0;
		$dtstart='';
		$dtend='';
		foreach($rows as $row)
		{
			if(mb_strstr($row,'BEGIN:VFREEBUSY'))
			{
				$status=1;
				if(!isset($this->result[$idx]))
					$this->result[$idx]='';
				$this->result[$idx].=$row."\n";				
			}
			elseif(mb_strstr($row,'END:VFREEBUSY'))
			{
				$status=0;
				$this->result[$idx].=$row."\n";
				$idx++;
			}
			elseif($status==1)
			{
				if($typ=='Google')
				{
					// VEVENT mit UTC Timestamps 
					if(mb_strstr($row,'DTSTART:'))
					{
						$dtstart = $this->ConvertTimezone(mb_substr($row,8,-1));
					} 
					elseif(mb_strstr($row,'DTEND:'))
					{
						$dtend = $this->ConvertTimezone(mb_substr($row,6));
						$this->result[$idx].='FREEBUSY:'.$dtstart.'/'.$dtend."\n";
						$dtstart='';
						$dtend='';
					}
				}
				elseif($typ=='Webmail')
				{
					// Freebusy Eintrag mit UTC Timestamps
					if(mb_strstr($row,'FREEBUSY:'))
					{
						$dtstart = $this->ConvertTimezone(mb_substr($row,9,16));
						$dtend = $this->ConvertTimezone(mb_substr($row,26));
						$this->result[$idx].='FREEBUSY:'.$dtstart.'/'.$dtend."\n";
						$dtstart='';
						$dtend='';
					}
				}
				elseif($typ=='SoGo')
				{
					if(mb_strstr($row,'FREEBUSY'))
					{
						$len = mb_strlen($row);
						$doppelpunktpos = mb_strpos($row, ':');
						$row = mb_substr($row, $doppelpunktpos+1);
						$slashpos = mb_strpos($row, '/');
						$dtstart = mb_substr($row, 0, $slashpos);
						$dtend = mb_substr($row, $slashpos+1);
						$this->dtresult[]=array('dtstart'=>trim($dtstart),'dtend'=>trim($dtend));
						
						$dtstart = $this->ConvertTimezone($dtstart);
						$dtend = $this->ConvertTimezone($dtend);
						$this->result[$idx].='FREEBUSY:'.$dtstart.'/'.$dtend."\n";
						$dtstart='';
						$dtend='';
					}
				}
				elseif(mb_strpos($row,'FREEBUSY')===0)
					$this->result[$idx].=$row."\n";
			}
		}
	}
	
	/**
	 * Liefert die FreeBusy Eintraege
	 */
	public function getFreeBusy()
	{
		return implode($this->result);
	}
	
	/**
	 * Importiert ein FreeBusy File
	 * 
	 * @param $ical
	 * @param $typ
	 */
	public function parseFreeBusy($ical)
	{
		$rows = explode("\n",$ical);
		
		$idx = count($this->result);
		$status=0;
		$dtstart='';
		$dtend='';

		foreach($rows as $row)
		{
			if(mb_strpos($row,'FREEBUSY')===0)
			{

				$len = mb_strlen($row);
				$doppelpunktpos = mb_strpos($row, ':');
				$row = mb_substr($row, $doppelpunktpos+1);
				$slashpos = mb_strpos($row, '/');
				$dtstart = mb_substr($row, 0, $len-$slashpos);
				$dtend = mb_substr($row, $slashpos+1);
				$this->dtresult[]=array('dtstart'=>trim($dtstart),'dtend'=>trim($dtend));
			}
		}
	}
	
	/**
	 * 
	 * Konvertiert die Zeitzone eines XMLRPC (Compact) datetimes von UTC auf Europe/Vienna 
	 * @param $datetime (zB 20080701T093807Z)
	 */
	function ConvertTimezone($datetime)
	{
		if(mb_strpos($datetime,'Z')!==0)
		{
			//UTC
			$timezone = new DateTimeZone('UTC');
		}
		else
		{
			//Default
			$timezone = new DateTimeZone('Europe/Vienna');
		}
		
		$date = new DateTime($datetime, $timezone);
		$date->setTimezone(new DateTimeZone('Europe/Vienna'));
		return $date->format('Ymd\THis');
	}
}
?>
