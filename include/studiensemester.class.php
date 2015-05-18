<?php
/* Copyright (C) 2009 fhcomplete.org
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
require_once('basis_db.class.php');

class studiensemester extends basis_db
{
	public $new;      // boolean
	public $studiensemester = array(); // studiensemester Objekt

	//Tabellenspalten
	public $studiensemester_kurzbz;// varchar(16)
	public $start; 					// date
	public $ende; 					// date
	public $bezeichnung;			// varchar(32)
	public $studienjahr_kurzbz;			// varchar(16)
	public $beschreibung;			// varchar(16)

	/**
	 * Konstruktor - Laedt optional ein StSem
	 * 
	 * @param $studiensemester_kurzbz StSem das geladen werden soll (default=null)
	 */
	public function __construct($studiensemester_kurzbz=null)
	{
		parent::__construct();
		
		if($studiensemester_kurzbz != null)
			$this->load($studiensemester_kurzbz);
	}
	
	/**
	 * Laedt das Studiensemester mit der uebergebenen Kurzbz
	 * 
	 * @param $studiensemester_kurzbz Stsem das geladen werden soll
	 */
	public function load($studiensemester_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen des Studiensemesters';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
			$this->bezeichnung = $row->bezeichnung;
			$this->studienjahr_kurzbz = $row->studienjahr_kurzbz;
			$this->beschreibung = $row->beschreibung;
		}
		else
		{
			$this->errormsg = "Es ist kein Studiensemester mit dieser Kurzbezeichung vorhanden";
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * 
	 * @return true wenn ok, false im Fehlerfall
	 */	
	private function validate()
	{
		if(mb_strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'Studiensemester Kurzbezeichnung darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>32)
		{
			$this->errormsg = 'Studiensemester Bezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->studiensemester_kurzbz=='')
		{
			$this->errormsg = 'Es muss eine Kurzbezeichnung eingegeben werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert das Studiensemester in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * 
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = "INSERT INTO public.tbl_studiensemester (studiensemester_kurzbz, start, ende)
			        VALUES(".$this->db_add_param($this->studiensemester_kurzbz).",".
					$this->db_add_param($this->start).','.
					$this->db_add_param($this->ende).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_studiensemester SET'.
			       ' start='.$this->db_add_param($this->start).','.
			       ' ende='.$this->db_add_param($this->ende).
			       " WHERE studiensemester_kurzbz=".$this->db_add_param($this->studiensemester_kurzbz);
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Studiensemesters';
			return false;
		}
	}

	/**
	 * Liefert das aktuelle Studiensemester
	 * 
	 * @return aktuelles Studiensemester oder false wenn es keines gibt
	 */
	public function getakt()
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE start <= now() AND ende >= now()";

		if(!$this->db_query($qry))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}

		if($this->db_num_rows()>0)
		{
		   $erg = $this->db_fetch_object();
		   return $erg->studiensemester_kurzbz;
		}
		else
		{
			$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
			return false;
		}
	}

	/**
	 * Liefert ein Studiensemester mit Startdatum vom naechstgelegenen Studiensemester und
	 * dem Startdatum vom folgenden Studiensemester als Endedatum
	 * 
	 * @return boolean
	 */
	public function getNearestTillNext()
	{
		if(!$this->getNearest())
			return false;
		
		$start=$this->start;
		$studiensemester_kurzbz=$this->studiensemester_kurzbz;
		
		if (!$this->getNextFrom($this->studiensemester_kurzbz))
			return false;
		$ende=$this->start;

		$this->studiensemester_kurzbz=$studiensemester_kurzbz;
		$this->start=$start;
		$this->ende=$ende;

		return true;
	}

	/**
	 * Liefert das Aktuelle Studiensemester oder das darauffolgende
	 * 
	 * @param $semester wenn das semester uebergeben wird, dann werden nur die studiensemester
	 *                  geliefert die in dieses semester fallen (Bei geradem semester nur SS sonst WS)
	 * @return Studiensemester oder false wenn es keines gibt
	 */
	public function getaktorNext($semester='')
	{
		if(($stsem=$this->getakt()) && $semester=='')
			return $stsem;
		else
		{
			$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE true";
			
			if($semester!='')
			{
				if($semester%2==0)
					$ss='SS';
				else
					$ss='WS';

				$qry.= " AND substring(studiensemester_kurzbz from 1 for 2)='$ss' ";
			}
			$qry.= " AND ende >= now() ORDER BY ende LIMIT 1";

			if(!$this->db_query($qry))
			{
				$this->errormsg = $this->db_last_error();
				return false;
			}

			if($erg = $this->db_fetch_object())
			{
				return $erg->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
				return false;
			}
		}
	}

	/**
	 * Liefert das naechstgelegenste Studiensemester
	 * 
	 * @param semester  wenn das semester uebergeben wird, dann werden nur die studiensemester
	 *                  geliefert die in dieses semester fallen (Bei geradem semester nur SS sonst WS)
	 * @return Studiensemester oder false wenn es keines gibt
	 */
	public function getNearest($semester='')
	{
		$qry = "SELECT studiensemester_kurzbz, start, ende FROM public.vw_studiensemester ";
		if($semester!='')
		{
			if($semester%2==0)
				$ss='SS';
			else
				$ss='WS';

			$qry.= " WHERE substring(studiensemester_kurzbz from 1 for 2)='$ss' ";
		}
		$qry.=' ORDER BY delta LIMIT 1';

		if(!$this->db_query($qry))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}

		if($erg = $this->db_fetch_object())
		{
			$this->studiensemester_kurzbz=$erg->studiensemester_kurzbz;
			$this->start=$erg->start;
			$this->ende=$erg->ende;
			return $erg->studiensemester_kurzbz;
		}
		else
		{
			$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
			return false;
		}
	}

	/**
	 * Liefert alle Studiensemester
	 *
	 * @return true wenn ok, sonst false
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_studiensemester ORDER BY ende";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$stsem_obj = new studiensemester();

				$stsem_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$stsem_obj->start = $row->start;
				$stsem_obj->ende = $row->ende;
				$stsem_obj->bezeichnung = $row->bezeichnung;
				$stsem_obj->studienjahr_kurzbz = $row->studienjahr_kurzbz;
				$stsem_obj->beschreibung = $row->beschreibung;

				$this->studiensemester[] = $stsem_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Studiensemester';
			return false;
		}
	}

	/**
	 * Liefert das naechste Studiensemester
	 * 
	 * @param $art Wenn art=WS dann wird das naechste Wintersemester geliefert
	 *             Wenn art=SS dann wird das naechste Sommersemester geliefert
	 * @return true wenn ok, false wenn kein entsprechendes vorhanden ist
	 */
	public function getNextStudiensemester($art='')
	{
		$qry = "SELECT * FROM public.tbl_studiensemester WHERE start>now() ";

		if($art!='')
			$qry.= " AND substring(studiensemester_kurzbz from 1 for 2)=".$this->db_add_param($art);

		$qry.=" ORDER BY start LIMIT 1";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen des Studiensemesters';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
		}
		else
		{
			$this->errormsg = "Es wurde kein entsprechendes Studiensemester gefunden";
			return false;
		}

		return true;
	}
	
	/**
	 * Liefert die naechsten Studiensemester bis zum eingestellten Limit
	 * 
	 * @param $art Wenn art=WS dann wird das naechste Wintersemester geliefert
	 *             Wenn art=SS dann wird das naechste Sommersemester geliefert
	 *        $limit Wie viele kommende Studiensemester sollen geliefert werden?
	 *     			 Wenn leer, dann 1.
	 * @return true wenn ok, sonst false
	 */
	public function getFutureStudiensemester($art='', $limit=NULL)
	{
		$qry = "SELECT * FROM public.tbl_studiensemester WHERE start>now() ";

		if($art!='')
			$qry.= " AND substring(studiensemester_kurzbz from 1 for 2)=".$this->db_add_param($art);

		$qry.=" ORDER BY start";
		
		if(!is_null($limit) && is_numeric($limit))
			$qry.=" LIMIT ".$limit;
		else 
			$qry.=" LIMIT 1";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$stsem_obj = new studiensemester();
				
				$stsem_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$stsem_obj->start = $row->start;
				$stsem_obj->ende = $row->ende;
				$stsem_obj->bezeichnung = $row->bezeichnung;
				
				$this->studiensemester[] = $stsem_obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Lesen des Studiensemesters';
			return false;
		}
	}

	/**
	 * Liefert das vorige Studiensemester
	 * 
	 * @return studiensemester_kurzbz oder false wenn keines vorhanden
	 */
	public function getPrevious()
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende<now() ORDER BY ende DESC LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorangegangenes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorangegangenen Studiensemesters';
			return false;
		}
	}
	
	/**
	 * Liefert das vorvorige Studiensemester
	 * 
	 * @return studiensemester_kurzbz oder false wenn keines vorhanden
	 */
	public function getBeforePrevious()
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende<now() ORDER BY ende DESC LIMIT 2";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row = $this->db_fetch_object())
				{
					return $row->studiensemester_kurzbz;
				}
				else
				{
					$this->errormsg = 'Es wurde kein vorjaehriges Studiensemester gefunden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorjaehriges Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorjaehrigen Studiensemesters';
			return false;
		}
	}

	/**
	 * Liefert das Studiensemester vor $studiensemester_kurzbz
	 * 
	 * @param $studiensemester_kurzbz
	 * @return $studiensemester_kurzbz oder false wenn Fehler
	 */
	public function getPreviousFrom($studiensemester_kurzbz)
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester
				WHERE ende<(SELECT start FROM public.tbl_studiensemester
				            WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")
		        ORDER BY ende DESC LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorangegangenes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorangegangenen Studiensemesters';
			return false;
		}
	}

	/**
	 * Liefert das Studiensemester nach $studiensemester_kurzbz
	 * 
	 * @param $studiensemester_kurzbz
	 * @return $studiensemester_kurzbz oder false wenn Fehler
	 */
	public function getNextFrom($studiensemester_kurzbz)
	{
		$qry = "SELECT studiensemester_kurzbz, start, ende FROM public.tbl_studiensemester
				WHERE start>(SELECT ende FROM public.tbl_studiensemester
				            WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")
		        ORDER BY start LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->start = $row->start;
				$this->ende = $row->ende;
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein folgendes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des folgenden Studiensemesters';
			return false;
		}
	}

	/**
	 * Liefert das Studiensemester das aktuell am naehesten zu $studiensemester_kurzbz liegt
	 * 
	 * @param $studiensemester_kurzbz
	 * @return $studiensemester_kurzbz oder false wenn Fehler
	 */
	public function getNearestFrom($studiensemester_kurzbz)
	{
		$qry = "SELECT studiensemester_kurzbz, start, ende FROM public.vw_studiensemester
				WHERE studiensemester_kurzbz<>".$this->db_add_param($studiensemester_kurzbz)."
		        ORDER BY delta LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->start = $row->start;
				$this->ende = $row->ende;
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein folgendes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des folgenden Studiensemesters';
			return false;
		}
	}

	/**
	 * Springt von Studiensemester $studiensemester_kurzbz um $wert Studiensemester vor/zurueck
	 * 
	 * @param $studiensemester_kurzbz
	 * @param $wert
	 * @return studiensemester_kurzbz
	 */
	public function jump($studiensemester_kurzbz, $wert)
	{
		if($wert>0)
		{
			$op='>';
			$sort='ASC';
			$sort2='DESC';
		}
		elseif($wert<0)
		{
			$op='<';
			$sort='DESC';
			$sort2='ASC';
		}
		else
		{
			$op='=';
			$sort='';
			$sort2='';
		}

		$qry = "SELECT studiensemester_kurzbz
				FROM
					(
					SELECT studiensemester_kurzbz, start
					FROM public.tbl_studiensemester
					WHERE start$op(SELECT start FROM public.tbl_studiensemester
					               WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz).")
					ORDER BY start $sort
					LIMIT ".abs($wert)."
					) as foo
				ORDER BY start $sort2 LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->studiensemester_kurzbz;
			}
			else
				return $studiensemester_kurzbz;
		}
		else 
		{
			$this->errormsg='Fehler bei einer Abfrage';
			return false;
		}
	}
	
	/**
	 * Laedt die vergangenen Studiensemester und das aktuelle
	 *
	 * @param limit maximale Anzahl
	 * @return true wenn ok, sonst false
	 */
	public function getFinished($limit=null)
	{
		$qry = "SELECT * FROM public.tbl_studiensemester where start<=now() ORDER BY ende DESC";
		if(!is_null($limit) && is_numeric($limit))
			$qry.=' LIMIT '.$limit;

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$stsem_obj = new studiensemester();

				$stsem_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$stsem_obj->start = $row->start;
				$stsem_obj->ende = $row->ende;

				$this->studiensemester[] = $stsem_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Studiensemester';
			return false;
		}
	}
	
	/**
	 * Liefert $days (Default 60) Tage nach dem start des neuen Semesters noch das vorherige Studiensemester 
	 * zurueck, danach das aktuelle.
	 * 
	 * 
	 * @return studiensemester_kurzbz oder false wenn keines vorhanden
	 */
	public function getLastOrAktSemester($days=60)
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE start<now()-'".$days." days'::interval ORDER BY start desc limit 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein LastOrAkt Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des LastOrAkt Studiensemesters';
			return false;
		}
	}
	
	/**
	 * Liefert $days (Default 60) Tage nach dem start des neuen Semesters noch das vorherige Studiensemester 
	 * zurueck, danach das aktuelle.
	 * 
	 * 
	 * @return studiensemester_kurzbz oder false wenn keines vorhanden
	 */
	public function getNextOrAktSemester($days=60)
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE start>now()-'".$days." days'::interval ORDER BY start limit 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein NextOrAkt Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des NextOrAkt Studiensemesters';
			return false;
		}
	}
	/**
	 * Liefert den UNIX Timestamp (Beginn,Ende) eines Studiensemesters
	 * 
	 * @param $studiensemester_kurzbz
	 * @return Beginn und Ende eines Studiensemesters als Timestamp
	 */
	public function getTimestamp($studiensemester_kurzbz)
	{
		$qry = "SELECT start,ende,studiensemester_kurzbz FROM public.tbl_studiensemester
				WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{	
				if(!isset($this->begin))
					$this->begin=new stdclass();
				$this->begin->start=mktime(0,0,0,mb_substr($row->start,5,2),mb_substr($row->start,8,2),mb_substr($row->start,0,4));
				if(!isset($this->ende))
					$this->ende=new stdclass();
				$this->ende->ende=mktime(0,0,0,mb_substr($row->ende,5,2),mb_substr($row->ende,8,2),mb_substr($row->ende,0,4));
				return true;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des Studiensemesters';
			return false;
		}
	} 
    /**
     * untersucht das uebergebene datum in welchem semester es sich befindet
     * @param type $datum
     * @return boolean 
     */
    public function getSemesterFromDatum($datum)
    {
        if($datum == '')
        {
            $this->errormsg = "Ungueltiges Datum uebergeben"; 
            return false; 
        }
        $qry = "SELECT * FROM public.tbl_studiensemester WHERE start <=".$this->db_add_param($datum, FHC_STRING)." AND ende >= ".$this->db_add_param($datum).';'; 
        
        if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_object())
            {
                return $row->studiensemester_kurzbz; 
            }
            else
            {
                $this->errormsg = "Es wurde kein passendes Studiensemester gefunden"; 
                return false; 
            }
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten."; 
            return false; 
        }
    }
    
    /**
     * Liefert das dazupassende Studiensemester im Studienjahr 
     * @param $studiensemester_kurzbz
     * @return $studiensemester_kurzbz
     */
    public function getStudienjahrStudiensemester($studiensemester_kurzbz)
    {
    	if(mb_substr($studiensemester_kurzbz,0,2)=='WS')
    		return $this->getNextFrom($studiensemester_kurzbz);
    	else
    		return $this->getPreviousFrom($studiensemester_kurzbz);
    }
}
?>
