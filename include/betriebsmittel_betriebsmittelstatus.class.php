<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/**
 * Klasse Betriebsmittelstatus
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class betriebsmittel_betriebsmittelstatus extends basis_db
{
	public $new;
	public $debug=false;	
	public $result = array();
	
	//Tabellenspalten
	public $betriebsmittelbetriebsmittelstatus_id; // Integer
	public $betriebsmittel_id; // Integer
	public $betriebsmittelstatus_kurzbz; // character varying(16)
	public $anmerkung;  // text
	public $datum;   	// date
	public $updateamum; // timestamp without time zone
	public $updatevon; 	// character varying(32)
	public $insertamum; // timestamp without time zone
	public $insertvon; 	// character varying(32)
	
	/**
	 * Konstruktor
	 * @param $betriebsmittelstatus
	 */
	public function __construct($betriebsmittelbetriebsmittelstatus_id=null)
	{

		parent::__construct();
		if($betriebsmittelbetriebsmittelstatus_id!=null)
			$this->load($betriebsmittelbetriebsmittelstatus_id);
	}
		
	/**
	 * Laedt die Funktion mit der ID $betriebsmittelstatus
	 * @param  $betriebsmittelstatus
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($betriebsmittelbetriebsmittelstatus_id)
	{	
		$this->result=array();
		$this->errormsg='';
		
		$qry='SELECT * FROM wawi.tbl_betriebsmittel_betriebsmittelstatus
			WHERE betriebsmittelbetriebsmittelstatus_id='.$this->db_add_param(trim($betriebsmittelbetriebsmittelstatus_id), FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->betriebsmittelbetriebsmittelstatus_id = $row->betriebsmittelbetriebsmittelstatus_id;
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->betriebsmittelstatus_kurzbz = $row->betriebsmittelstatus_kurzbz;
				$this->anmerkung = $row->anmerkung;
				$this->datum = $row->datum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	/**
	 * Laedt die Funktion mit der ID $betriebsmittel und Optional einen Status
	 * @param  $betriebsmittel_id
	 * @param  $betriebsmittelstatus_kurzbz	 
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_betriebsmittel_id($betriebsmittel_id,$betriebsmittelstatus_kurzbz=null)
	{	
		$this->result=array();
		$this->errormsg='';
					
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		
		$qry='SELECT * FROM wawi.tbl_betriebsmittel_betriebsmittelstatus
			WHERE betriebsmittel_id='.$this->db_add_param(trim($betriebsmittel_id), FHC_INTEGER);
		
		if (!is_null($betriebsmittelstatus_kurzbz) && !empty($betriebsmittelstatus_kurzbz))
			$qry.=" and trim(betriebsmittelstatus_kurzbz)=".$this->db_add_param(trim($betriebsmittelstatus_kurzbz)) ;
		
		// Sortierung
		$qry.=' ORDER BY datum desc,updateamum desc,insertamum desc';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmittel_betriebsmittelstatus();
				$bmt->betriebsmittelbetriebsmittelstatus_id = $row->betriebsmittelbetriebsmittelstatus_id;
				$bmt->betriebsmittel_id = $row->betriebsmittel_id;
				$bmt->betriebsmittelstatus_kurzbz = $row->betriebsmittelstatus_kurzbz;
				$bmt->anmerkung = $row->anmerkung;
				$bmt->datum = $row->datum;
				$bmt->updateamum = $row->updateamum;
				$bmt->updatevon = $row->updatevon;
				$bmt->insertamum = $row->insertamum;
				$bmt->insertvon = $row->insertvon;
				$this->result[] = $bmt;
			}
			
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt den letzten Stauts eines Betriebsmittels
	 * @param  $betriebsmittel_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_last_betriebsmittel_id($betriebsmittel_id)
	{	
		$this->result=array();
		$this->errormsg='';
		if ($betriebsmittel_id)
			$this->betriebsmittel_id=$betriebsmittel_id;
					
		if(!is_numeric($this->betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		
		$qry=' SELECT * FROM wawi.tbl_betriebsmittel_betriebsmittelstatus
			WHERE betriebsmittel_id='.$this->db_add_param(trim($this->betriebsmittel_id), FHC_INTEGER).'
			ORDER BY betriebsmittelbetriebsmittelstatus_id DESC LIMIT 1';
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->betriebsmittelbetriebsmittelstatus_id = $row->betriebsmittelbetriebsmittelstatus_id;
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->betriebsmittelstatus_kurzbz = $row->betriebsmittelstatus_kurzbz;
				$this->anmerkung = $row->anmerkung;
				$this->datum = $row->datum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg='Es wurde kein Eintrag gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	/**
	 * Laedt alle betriebsmittelstatus
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAll()
	{
		$this->result=array();
		$this->errormsg='';
		
		$qry='SELECT * FROM wawi.tbl_betriebsmittel_betriebsmittelstatus
			ORDER BY betriebsmittel_id,datum desc,insertamum desc ';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bmt = new betriebsmittel_betriebsmittelstatus();
				$bmt->betriebsmittelbetriebsmittelstatus_id = $row->betriebsmittelbetriebsmittelstatus_id;
				$bmt->betriebsmittel_id = $row->betriebsmittel_id;
				$bmt->betriebsmittelstatus_kurzbz = $row->betriebsmittelstatus_kurzbz;
				$bmt->anmerkung = $row->anmerkung;
				$bmt->datum = $row->datum;
				$bmt->updateamum = $row->updateamum;
				$bmt->updatevon = $row->updatevon;
				$bmt->insertamum = $row->insertamum;
				$bmt->insertvon = $row->insertvon;
				$this->result[] = $bmt;
			}
			
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
		
	/**
	 * Speichert die Daten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{		
		$this->result=array();
		$this->errormsg='';
		if(!is_numeric($this->betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}	
		$qry='';
		if($this->new)
		{
			$this->betriebsmittelbetriebsmittelstatus_id='';
			$qry='BEGIN;INSERT INTO wawi.tbl_betriebsmittel_betriebsmittelstatus 
			(betriebsmittel_id,betriebsmittelstatus_kurzbz,anmerkung,datum,insertamum,insertvon,updateamum,updatevon ) VALUES('.
						$this->db_add_param($this->betriebsmittel_id, FHC_INTEGER).','.
						$this->db_add_param($this->betriebsmittelstatus_kurzbz).','.
						$this->db_add_param($this->anmerkung).','.
						($this->datum?$this->db_add_param($this->datum):'now()').', '.
					    ($this->insertamum?$this->db_add_param($this->insertamum):'now()').', '.
					    $this->db_add_param($this->insertvon).', '.
			    		($this->updateamum?$this->db_add_param($this->updateamum):'now()').', '.
					     $this->db_add_param((empty($this->updatevon)?$this->updatevon:$this->insertvon))	.'); ';
		}
		else 
		{
			$qry='UPDATE wawi.tbl_betriebsmittel_betriebsmittelstatus SET '.
					"betriebsmittel_id =".$this->db_add_param($this->betriebsmittel_id, FHC_INTEGER).', '.
					"betriebsmittelstatus_kurzbz =".$this->db_add_param($this->betriebsmittelstatus_kurzbz).', '.
					"anmerkung =".$this->db_add_param($this->anmerkung).', '.					
					"datum =".($this->datum?$this->db_add_param($this->datum):'now()').', '.
					"updateamum =".($this->updateamum?$this->db_add_param($this->updateamum):'now()').', '.
					"updatevon =".$this->db_add_param((empty($this->updatevon)?$this->updatevon:$this->insertvon)).' '.
					" WHERE betriebsmittelbetriebsmittelstatus_id=".$this->db_add_param($this->betriebsmittelbetriebsmittelstatus_id, FHC_INTEGER, false);
		}

		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('wawi.tbl_betriebsmittel_betriebsmi_betriebsmittelbetriebsmittels_seq') as id;";
				
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->db_query('COMMIT;');
						$this->betriebsmittelbetriebsmittelstatus_id= $row->id;
					}
					else 
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						return false;
					}
				}
				else 
				{
					$this->db_query('ROLLBACK;');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
				}
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Betriebsmittel Betriebsmittelstatus-Datensatzes';
			return false;
		}
	}

	/**
	 * Entfernt die Daten in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function delete($betriebsmittelbetriebsmittelstatus_id=null)
	{		
		$this->result=array();
		$this->errormsg='';
		if (!is_null($betriebsmittelbetriebsmittelstatus_id))
			$this->betriebsmittelbetriebsmittelstatus_id=$betriebsmittelbetriebsmittelstatus_id;
		if(!is_numeric($this->betriebsmittelbetriebsmittelstatus_id))
		{
			$this->errormsg = 'Betriebsmittelbetriebsmittelstatus_id ist ungueltig';
			return false;
		}

		$qry='DELETE from wawi.tbl_betriebsmittel_betriebsmittelstatus '.
			' WHERE betriebsmittelbetriebsmittelstatus_id='.$this->db_add_param($this->betriebsmittelbetriebsmittelstatus_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim Entfernen des Betriebsmittel Betriebsmittelstatus-Datensatzes';
			return false;
		}	
	}	


	/**
	 * Entfernt die alle Stati zu einem Betriebsmittel in der Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function delete_betriebsmittel($betriebsmittel_id)
	{		
		$this->result=array();
		$this->errormsg='';

		
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}

		$qry='DELETE from wawi.tbl_betriebsmittel_betriebsmittelstatus '.
			' WHERE betriebsmittel_id='.$this->db_add_param($betriebsmittel_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim Entfernen des Betriebsmittel Betriebsmittelstatus-Datensatzes';
			return false;
		}	
	}

	/**
	 * Laedt den letzten Stauts eines Betriebsmittels
	 * @param  $betriebsmittel_id
	 * @return betriebsmittelstatus_kurzbz wenn ok, false im Fehlerfall
	 */
	public function load_last_status_by_betriebsmittel_id($betriebsmittel_id)
	{
		$this->result=array();
		$this->errormsg='';
		if ($betriebsmittel_id)
			$this->betriebsmittel_id=$betriebsmittel_id;

		if(!is_numeric($this->betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}

		$qry=' SELECT * FROM wawi.tbl_betriebsmittel_betriebsmittelstatus
			WHERE betriebsmittel_id='.$this->db_add_param(trim($this->betriebsmittel_id), FHC_INTEGER).'
			ORDER BY betriebsmittelbetriebsmittelstatus_id DESC LIMIT 1';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->betriebsmittelstatus_kurzbz;
			}
			else
			{
				$this->errormsg='Es wurde kein Eintrag gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
