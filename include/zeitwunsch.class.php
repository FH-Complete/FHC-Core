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
require_once(dirname(__FILE__).'/basis_db.class.php');

class zeitwunsch extends basis_db
{
	public $new;      // boolean
	public $zeitwunsch;

	//Tabellenspalten
	public $stunde;				// smalint
	public $mitarbeiter_uid;	// varchar(32)
	public $tag;				// smalint
	public $gewicht;			// smalint
	public $min_stunde;
	public $max_stunde;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;

	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->init();
	}

	/**
	 * Initialisierung
	 *
	 */
	private function init()
	{
		// Stundenraster abfragen
		$sql='SELECT min(stunde) AS min_stunde,max(stunde) AS max_stunde FROM lehre.tbl_stunde;';
		if(!$this->db_query($sql))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		else
		{
			$row=$this->db_fetch_object();
			$this->min_stunde=$row->min_stunde;
			$this->max_stunde=$row->max_stunde;
		}
		return true;
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->mitarbeiter_uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein.';
			return false;
		}
		if($this->mitarbeiter_uid == '')
		{
			$this->errormsg = 'UID muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->stunde))
		{
			$this->errormsg = 'Stunde muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->gewicht))
		{
			$this->errormsg = 'Gewicht muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->tag))
		{
			$this->errormsg = 'Tag muss eine gueltige Zahl sein';
			return false;
		}

		return true;
	}
	
	/**
	 * Speichert einen Zeitwunsch in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_zeitwunsch (mitarbeiter_uid, tag, stunde, gewicht, 
					insertamum, insertvon, updateamum, updatevon) VALUES('.
					$this->db_add_param($this->mitarbeiter_uid).','.
					$this->db_add_param($this->tag, FHC_INTEGER).','.
					$this->db_add_param($this->stunde, FHC_INTEGER).','.
					$this->db_add_param($this->gewicht, FHC_INTEGER).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_zeitwunsch SET'.
			       ' gewicht='.$this->db_add_param($this->gewicht, FHC_INTEGER).', '.
			       ' updateamum='.$this->db_add_param($this->updateamum).', '.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE 
			       		mitarbeiter_uid=".$this->db_add_param($this->mitarbeiter_uid, FHC_STRING, false)." 
			       		AND tag=".$this->db_add_param($this->tag, FHC_INTEGER)." 
			       		AND stunde=".$this->db_add_param($this->stunde, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Zeitwunsches';
			return false;
		}
	}

	/**
	 * Zeitwunsch einer Person laden
	 * @param uid
	 * @param datum
	 * @return boolean Ergebnis steht in Array $zeitwunsch wenn true
	 */
	public function loadPerson($uid,$datum=null)
	{
		// Zeitwuensche abfragen
		if(!$this->db_query("SELECT * FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid=".$this->db_add_param($uid)))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		else
		{
			while ($row = $this->db_fetch_object())
			{
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
			}
		}
		
		if (!is_null($datum))
		{
			$beginn=montag($datum);
			$start=date('Y-m-d',$beginn);
			$ende=date('Y-m-d',jump_day($beginn,7));

			// Zeitsperren abfragen
			$sql="
				SELECT 
					vondatum,vonstunde,bisdatum,bisstunde
				FROM 
					campus.tbl_zeitsperre
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($uid)."
					AND vondatum<=".$this->db_add_param($ende)." 
					AND bisdatum>=".$this->db_add_param($start);
			
			if(!$this->db_query($sql))
			{
				$this->errormsg=$this->db_last_error();
				return false;
			}
			else
			{
				while($row = $this->db_fetch_object())
				{
					$beginn=montag($datum);
					for ($i=1;$i<=7;$i++)
					{
						$date_iso=date('Y-m-d',$beginn);
						//echo "\n".$date_iso."\n".$row->vondatum."\n";
						if ($date_iso>$row->vondatum && $date_iso<$row->bisdatum)
							for ($j=$this->min_stunde;$j<=$this->max_stunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						if ($date_iso==$row->vondatum && $date_iso<$row->bisdatum)
						{
							if (is_null($row->vonstunde))
								$row->vonstunde=$this->min_stunde;
							for ($j=$row->vonstunde;$j<=$this->max_stunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						}
						if ($date_iso>$row->vondatum && $date_iso==$row->bisdatum)
						{
							if (is_null($row->bisstunde))
								$row->bisstunde=$this->max_stunde;
							for ($j=$this->min_stunde;$j<=$row->bisstunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						}
						if ($date_iso==$row->vondatum && $date_iso==$row->bisdatum)
						{
							if (is_null($row->vonstunde))
								$row->vonstunde=$this->min_stunde;
							if (is_null($row->bisstunde))
								$row->bisstunde=$this->max_stunde;
							for ($j=$row->vonstunde;$j<=$row->bisstunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						}
						$beginn=jump_day($beginn,1);
					}
				}
			}
		}
		return true;
	}


	/**
	 * Zeitwunsch der Personen in Lehreinheiten laden
	 * @param $le_id LehreinheitID Array
	 * @param $datum 
	 * @return true oder false
	 */
	public function loadZwLE($le_id,$datum=null)
	{
		//$this->init();
		// SUB-Select fuer LVAs
		$sql_query_leid='';
		$sql_query_le='SELECT DISTINCT mitarbeiter_uid FROM campus.vw_lehreinheit WHERE ';
		for ($i=0;$i<count($le_id);$i++)
			$sql_query_leid.=" OR lehreinheit_id=".$this->db_add_param($le_id[$i], FHC_INTEGER);
		$sql_query_leid=mb_substr($sql_query_leid,3);
		$sql_query_le.=$sql_query_leid;

		// Schlechteste Zeitwuensche holen
		$sql_query='SELECT tag,stunde,min(gewicht) AS gewicht
				FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid IN ('.$sql_query_le.') GROUP BY tag,stunde';

		// Zeitwuensche abfragen
		if(!$this->db_query($sql_query))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		else
			while($row = $this->db_fetch_object())
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;

		// ***********************************************************
		// Zeitsperren fuer die aktuelle Woche holen

		if (!is_null($datum))
		{
			$beginn=montag($datum);
			$start=date('Y-m-d',$beginn);
			$ende=date('Y-m-d',jump_day($beginn,7));

			// Zeitsperren abfragen
			$sql="
				SELECT 
					vondatum,vonstunde,bisdatum,bisstunde
				FROM 
					campus.tbl_zeitsperre
				WHERE 
					mitarbeiter_uid IN ($sql_query_le) 
					AND vondatum<=".$this->db_add_param($ende)." 
					AND bisdatum>=".$this->db_add_param($start);
			
			if(!$this->db_query($sql))
			{
				$this->errormsg = $this->db_last_error();
				return false;
			}
			while($row = $this->db_fetch_object())
			{
				$beginn=montag($datum);
				for ($i=1;$i<=7;$i++)
				{
					$date_iso=date('Y-m-d',$beginn);
					//echo "\n".$date_iso."\n".$row->vondatum."\n";
					if ($date_iso>$row->vondatum && $date_iso<$row->bisdatum)
						for ($j=$this->min_stunde;$j<=$this->max_stunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					if ($date_iso==$row->vondatum && $date_iso<$row->bisdatum)
					{
						if (is_null($row->vonstunde))
							$row->vonstunde=$this->min_stunde;
						for ($j=$row->vonstunde;$j<=$this->max_stunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					}
					if ($date_iso>$row->vondatum && $date_iso==$row->bisdatum)
					{
						if (is_null($row->bisstunde))
							$row->bisstunde=$this->max_stunde;
						for ($j=$this->min_stunde;$j<=$row->bisstunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					}
					if ($date_iso==$row->vondatum && $date_iso==$row->bisdatum)
					{
						if (is_null($row->vonstunde))
							$row->vonstunde=$this->min_stunde;
						if (is_null($row->bisstunde))
							$row->bisstunde=$this->max_stunde;
						for ($j=$row->vonstunde;$j<=$row->bisstunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					}
					$beginn=jump_day($beginn,1);
				}
			}
		}
		return true;
	}
	
	/**
	 * Prueft ob bereits ein Zeitwunsch eingetragen ist
	 *
	 * @param $uid
	 * @param $stunde
	 * @param $tag
	 * @return true wenn vorhanden sonst false
	 */
	function exists($uid, $stunde, $tag)
	{
		$qry = "SELECT 1 FROM campus.tbl_zeitwunsch 
				WHERE 
					mitarbeiter_uid=".$this->db_add_param($uid)." 
					AND stunde=".$this->db_add_param($stunde, FHC_INTEGER)." 
					AND tag=".$this->db_add_param($tag, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else 
				return false;
		}
		else 
		{
			$this->errormsg='Fehler beim Abfragen des Zeitwunsches';
			return false;
		}
	}
}
?>