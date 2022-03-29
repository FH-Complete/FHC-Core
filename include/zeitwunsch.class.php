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
	public $zeitwunsch_id;
	public $zeitwunsch_gueltigkeit_id;

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
					insertamum, insertvon, updateamum, updatevon, zeitwunsch_gueltigkeit_id) VALUES('.
					$this->db_add_param($this->mitarbeiter_uid).','.
					$this->db_add_param($this->tag, FHC_INTEGER).','.
					$this->db_add_param($this->stunde, FHC_INTEGER).','.
					$this->db_add_param($this->gewicht, FHC_INTEGER).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->zeitwunsch_gueltigkeit_id).');';
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
			       		AND stunde=".$this->db_add_param($this->stunde, FHC_INTEGER). "
			       		AND zeitwunsch_gueltigkeit_id=".$this->db_add_param($this->zeitwunsch_gueltigkeit_id, FHC_INTEGER);
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
     * Zeitwunsch einer Person zu bestimmter Zeitwunschgueltigkeit laden
     * @param $uid
     * @param $zeitwunsch_gueltigkeit_id
     * @return boolean
     */
    public function loadByZWG($uid, $zeitwunsch_gueltigkeit_id)
    {
        $qry = '
            SELECT *
            FROM campus.tbl_zeitwunsch
            JOIN campus.tbl_zeitwunsch_gueltigkeit zwg USING (zeitwunsch_gueltigkeit_id)
            WHERE zwg.mitarbeiter_uid = ' . $this->db_add_param($uid) . '
            AND zeitwunsch_gueltigkeit_id = ' . $this->db_add_param($zeitwunsch_gueltigkeit_id) . '
            ORDER BY tag, stunde
        ';

        if ($this->db_query($qry))
        {
            while ($row = $this->db_fetch_object())
            {
                $this->zeitwunsch[$row->tag][$row->stunde] = $row->gewicht;
                $this->insertamum = $row->insertamum;
                $this->insertvon = $row->insertvon;
                $this->updateamum = $row->updateamum;
                $this->updatevon = $row->updatevon;
                $this->zeitwunsch_id = $row->zeitwunsch_id;
                $this->zeitwunsch_gueltigkeit_id = $row->zeitwunsch_gueltigkeit_id;
            }
            return true;
        }
        else
        {
            $this->errormsg = $this->db_last_error();
            return false;
        }
    }

    /**
	 * Alle Zeitwuensche einer Person laden.
     * Um auf einen Zeitwunsch einer bestimmten Zeit, also innerhalb einer bestimmten Zeitwunschgueltigkeit
     * zu beschraenken, kann ein Datum mitgegeben werden.
	 * @param uid
	 * @param datum UNIX timestamp, um Zeitwunsch mit richtiger Zeitwunschgueltigkeit zu ermitteln
	 * @return boolean Ergebnis steht in Array $zeitwunsch wenn true
	 */
	public function loadPerson($uid,$datum=null)
	{
        // Default datum: jetzt
        if (is_null($datum))
        {
            $datum = time();
        }

        $qry = "
            SELECT *
            FROM campus.tbl_zeitwunsch
            JOIN campus.tbl_zeitwunsch_gueltigkeit zwg USING (zeitwunsch_gueltigkeit_id)
            WHERE zwg.mitarbeiter_uid=". $this->db_add_param($uid). "
            AND ". $this->db_add_param(date('Y-m-d', $datum)). " BETWEEN von AND COALESCE(bis,'2999-01-01');
        ";


		// Zeitwuensche abfragen
		if(!$this->db_query($qry))
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
                $this->zeitwunsch_id = $row->zeitwunsch_id;
                $this->zeitwunsch_gueltigkeit_id = $row->zeitwunsch_gueltigkeit_id;
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
					zeitsperretyp_kurzbz, vondatum,vonstunde,bisdatum,bisstunde
				FROM
					campus.tbl_zeitsperre
				WHERE
					mitarbeiter_uid=".$this->db_add_param($uid)."
					AND vondatum<=".$this->db_add_param($ende)."
					AND bisdatum>=".$this->db_add_param($start). "
					-- Negative Zeitsperren sollen im Plan eine positive Zeitsperre 'ZVerfueg' overrulen
					ORDER BY
					  CASE 
					  WHEN zeitsperretyp_kurzbz = 'ZVerfueg' THEN 1
					  ELSE 2
					  END;";

			if(!$this->db_query($sql))
			{
				$this->errormsg=$this->db_last_error();
				return false;
			}
			else
			{
                // Zeitsperren negativ (-3) gewichten.
                // Ausnahme: positive Zeitsperren: diese positiv (4) gewichten.
				while($row = $this->db_fetch_object())
				{
					$beginn=montag($datum);
					for ($i=1;$i<=7;$i++)
					{
						$date_iso=date('Y-m-d',$beginn);
						//echo "\n".$date_iso."\n".$row->vondatum."\n";
						if ($date_iso>$row->vondatum && $date_iso<$row->bisdatum)
							for ($j=$this->min_stunde;$j<=$this->max_stunde;$j++)
								$this->zeitwunsch[$i][$j] = $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;

						if ($date_iso==$row->vondatum && $date_iso<$row->bisdatum)
						{
							if (is_null($row->vonstunde))
								$row->vonstunde=$this->min_stunde;
							for ($j=$row->vonstunde;$j<=$this->max_stunde;$j++)
								$this->zeitwunsch[$i][$j] = $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
						}
						if ($date_iso>$row->vondatum && $date_iso==$row->bisdatum)
						{
							if (is_null($row->bisstunde))
								$row->bisstunde=$this->max_stunde;
							for ($j=$this->min_stunde;$j<=$row->bisstunde;$j++)
								$this->zeitwunsch[$i][$j] = $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
						}
						if ($date_iso==$row->vondatum && $date_iso==$row->bisdatum)
						{
							if (is_null($row->vonstunde))
								$row->vonstunde=$this->min_stunde;
							if (is_null($row->bisstunde))
								$row->bisstunde=$this->max_stunde;
							for ($j=$row->vonstunde;$j<=$row->bisstunde;$j++)
								$this->zeitwunsch[$i][$j] = $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
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
	 * @param $datum UNIX timestamp Datum, um Zeitwunsch mit richtiger Zeitwunschgueltigkeit zu ermitteln
	 * @return true oder false
	 */
	public function loadZwLE($le_id, $datum = null)
	{
        // Default datum: jetzt
        if (is_null($datum))
        {
            $datum = time();
        }
		// SUB-Select fuer LVAs
		$sql_query_leid='';
		$sql_query_le='SELECT DISTINCT mitarbeiter_uid FROM campus.vw_lehreinheit WHERE ';
		for ($i=0;$i<count($le_id);$i++)
			$sql_query_leid.=" OR lehreinheit_id=".$this->db_add_param($le_id[$i], FHC_INTEGER);
		$sql_query_leid=mb_substr($sql_query_leid,3);
		$sql_query_le.=$sql_query_leid;

		// Schlechteste Zeitwuensche holen
		$sql_query='SELECT tag,stunde,min(gewicht) AS gewicht
				FROM campus.tbl_zeitwunsch
                JOIN campus.tbl_zeitwunsch_gueltigkeit zwg USING (zeitwunsch_gueltigkeit_id)
                WHERE zwg.mitarbeiter_uid IN ('.$sql_query_le.')
                AND '. $this->db_add_param(date('Y-m-d', $datum)). ' BETWEEN von AND COALESCE(bis,\'2999-01-01\')
                GROUP BY tag,stunde;';

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
					zeitsperretyp_kurzbz, vondatum,vonstunde,bisdatum,bisstunde
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
							$this->zeitwunsch[$i][$j]= $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
					if ($date_iso==$row->vondatum && $date_iso<$row->bisdatum)
					{
						if (is_null($row->vonstunde))
							$row->vonstunde=$this->min_stunde;
						for ($j=$row->vonstunde;$j<=$this->max_stunde;$j++)
							$this->zeitwunsch[$i][$j]= $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
					}
					if ($date_iso>$row->vondatum && $date_iso==$row->bisdatum)
					{
						if (is_null($row->bisstunde))
							$row->bisstunde=$this->max_stunde;
						for ($j=$this->min_stunde;$j<=$row->bisstunde;$j++)
							$this->zeitwunsch[$i][$j]= $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
					}
					if ($date_iso==$row->vondatum && $date_iso==$row->bisdatum)
					{
						if (is_null($row->vonstunde))
							$row->vonstunde=$this->min_stunde;
						if (is_null($row->bisstunde))
							$row->bisstunde=$this->max_stunde;
						for ($j=$row->vonstunde;$j<=$row->bisstunde;$j++)
							$this->zeitwunsch[$i][$j]= $row->zeitsperretyp_kurzbz == 'ZVerfueg' ? 4 : -3;
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
     * @param $zwg_id
     * @param $stunde
     * @param $tag
     * @return true wenn vorhanden sonst false
     */
	function exists($uid, $zwg_id, $stunde, $tag)
	{
		$qry = "SELECT 1 FROM campus.tbl_zeitwunsch
				WHERE
					mitarbeiter_uid=".$this->db_add_param($uid)."
					AND stunde=".$this->db_add_param($stunde, FHC_INTEGER)."
					AND tag=".$this->db_add_param($tag, FHC_INTEGER). "
                    AND zeitwunsch_gueltigkeit_id = ".$this->db_add_param($zwg_id, FHC_INTEGER);
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
