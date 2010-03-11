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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/**
 * Klasse betriebsmittelstatus (FAS-Online)
 * @create 13-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class betriebsmittel_betriebsmittelstatus extends basis_db
{
	private $schema_inventar='wawi';

	public $new;
	public $debug=false;	
	public $result = array();
	
	/*
  betriebsmittelbetriebsmittelstatus_id integer NOT NULL DEFAULT nextval('wawi.tbl_betriebsmittel_betriebsmi_betriebsmittelbetriebsmittels_seq'::regclass),
  betriebsmittel_id integer NOT NULL,
  betriebsmittelstatus_kurzbz character varying(16) NOT NULL,
  datum bigint,
  updateamum timestamp without time zone,
  updatevon character varying(32),
  insertamum timestamp without time zone,
  insertvon character varying(32),	
	*/
	
	//Tabellenspalten
	public $betriebsmittelbetriebsmittelstatus_id; // Integer
	public $betriebsmittel_id; // Integer
	public $betriebsmittelstatus_kurzbz;	//string
	public $anmerkung;  //String	
	public $datum;   	//Int
	public $updateamum; // timestamp without time zone,
	public $updatevon; //  character varying(32),
	public $insertamum; //  timestamp without time zone,
	public $insertvon; //  character varying(32),	
	
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
					
		$qry='';
		$where='';

		$qry.=' select * from '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus';
		// Bedingungen hinzufuegen
		$where.=" where betriebsmittelbetriebsmittelstatus_id=".$this->addslashes(trim($betriebsmittelbetriebsmittelstatus_id)) ;
		$qry.=$where;

		// Sortierung
		$qry.=' order by datum ';

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
			if (count($this->result)==1)
			{
				$this->betriebsmittelbetriebsmittelstatus_id = $this->result[0]->betriebsmittelbetriebsmittelstatus_id;
				$this->betriebsmittel_id = $this->result[0]->betriebsmittel_id;
				$this->betriebsmittelstatus_kurzbz = $this->result[0]->betriebsmittelstatus_kurzbz;
				$this->anmerkung = $this->result[0]->anmerkung;
				$this->datum = $this->result[0]->datum;
				$this->updateamum = $this->result[0]->updateamum;
				$this->updatevon = $this->result[0]->updatevon;
				$this->insertamum = $this->result[0]->insertamum;
				$this->insertvon = $this->result[0]->insertvon;
			}
			return $this->result;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
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
					
		$qry='';
		$where='';
		$qry.=' select * from '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus';
		// Bedingungen hinzufuegen
		$where.=" where betriebsmittel_id=".$this->addslashes(trim($betriebsmittel_id)) ;
		if (!is_null($betriebsmittelstatus_kurzbz) && !empty($betriebsmittelstatus_kurzbz))
			$where.=" and trim(betriebsmittelstatus_kurzbz)=".$this->addslashes(trim($betriebsmittelstatus_kurzbz)) ;
		$qry.=$where;
		// Sortierung
		$qry.=' order by datum desc,updateamum desc,insertamum desc';

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
			if (count($this->result)==1)
			{
				$this->betriebsmittelbetriebsmittelstatus_id = $this->result[0]->betriebsmittelbetriebsmittelstatus_id;
				$this->betriebsmittel_id = $this->result[0]->betriebsmittel_id;
				$this->betriebsmittelstatus_kurzbz = $this->result[0]->betriebsmittelstatus_kurzbz;
				$this->anmerkung = $this->result[0]->anmerkung;
				$this->datum = $this->result[0]->datum;
				$this->updateamum = $this->result[0]->updateamum;
				$this->updatevon = $this->result[0]->updatevon;
				$this->insertamum = $this->result[0]->insertamum;
				$this->insertvon = $this->result[0]->insertvon;
			}
			return $this->result;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}
	
	/**
	 * Laedt die Funktion mit der ID $betriebsmittel und Optional einen Status
	 * @param  $betriebsmittel_id
	 * @param  $betriebsmittelstatus_kurzbz	 
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
					
		$qry='';
		$where='';

		$qry.=' select * from '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus';
		// Bedingungen hinzufuegen
		$where.=" where betriebsmittel_id=".$this->addslashes(trim($this->betriebsmittel_id)) ;

		$qry.=$where;
		// Sortierung
		$qry.=' order by betriebsmittelbetriebsmittelstatus_id desc  limit 1';
		
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
			if (count($this->result)==1)
			{
				$this->betriebsmittelbetriebsmittelstatus_id = $this->result[0]->betriebsmittelbetriebsmittelstatus_id;
				$this->betriebsmittel_id = $this->result[0]->betriebsmittel_id;
				$this->betriebsmittelstatus_kurzbz = $this->result[0]->betriebsmittelstatus_kurzbz;
				$this->anmerkung = $this->result[0]->anmerkung;
				$this->datum = $this->result[0]->datum;
				$this->updateamum = $this->result[0]->updateamum;
				$this->updatevon = $this->result[0]->updatevon;
				$this->insertamum = $this->result[0]->insertamum;
				$this->insertvon = $this->result[0]->insertvon;
			}
			return $this->result;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
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
			
		$qry='';
		$where='';

		$qry.=' select * FROM '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus';
		$qry.="	where betriebsmittelstatus_kurzbz >'' ";

		// Bedingungen hinzufuegen
		$qry.=$where;

		// Sortierung
		$qry.=' order by betriebsmittel_id,datum desc,insertamum desc ';
		
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
			if (count($this->result)==1)
			{
				$this->betriebsmittelbetriebsmittelstatus_id = $this->result[0]->betriebsmittelbetriebsmittelstatus_id;
				$this->betriebsmittel_id = $this->result[0]->betriebsmittel_id;
				$this->betriebsmittelstatus_kurzbz = $this->result[0]->betriebsmittelstatus_kurzbz;
				$this->anmerkung = $this->result[0]->anmerkung;
				$this->datum = $this->result[0]->datum;
				$this->updateamum = $this->result[0]->updateamum;
				$this->updatevon = $this->result[0]->updatevon;
				$this->insertamum = $this->result[0]->insertamum;
				$this->insertvon = $this->result[0]->insertvon;
			}
			return $this->result;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
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
			$qry='INSERT INTO '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus 
			(betriebsmittel_id,betriebsmittelstatus_kurzbz,anmerkung,datum,insertamum,insertvon,updateamum,updatevon ) VALUES('.
						$this->addslashes($this->betriebsmittel_id).','.
						$this->addslashes($this->betriebsmittelstatus_kurzbz).','.
						$this->addslashes($this->anmerkung).','.
						($this->datum?$this->addslashes($this->datum):'now()').', '.
					    ($this->insertamum?$this->addslashes($this->insertamum):'now()').', '.
					    $this->addslashes($this->insertvon).', '.
			    		($this->updateamum?$this->addslashes($this->updateamum):'now()').', '.
					     $this->addslashes((empty($this->updatevon)?$this->updatevon:$this->insertvon))	.'); ';
		}
		else 
		{
			$qry='UPDATE '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus SET '.
					"betriebsmittel_id =".$this->addslashes($this->betriebsmittel_id).', '.
					"betriebsmittelstatus_kurzbz =".$this->addslashes($this->betriebsmittelstatus_kurzbz).', '.
					"anmerkung =".$this->addslashes($this->anmerkung).', '.					
					"datum =".($this->datum?$this->addslashes($this->datum):'now()').', '.
					"updateamum =".($this->updateamum?$this->addslashes($this->updateamum):'now()').', '.
					"updatevon =".$this->addslashes((empty($this->updatevon)?$this->updatevon:$this->insertvon)).' '.
					" WHERE betriebsmittelbetriebsmittelstatus_id=".$this->addslashes($this->betriebsmittelbetriebsmittelstatus_id);
		}

#		echo "<br> $qry <br>";
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('".$this->schema_inventar.".tbl_betriebsmittel_betriebsmi_betriebsmittelbetriebsmittels_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->betriebsmittelbetriebsmittelstatus_id= $row->id;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Lesen der Sequence '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
					return false;
				}
			}
			return $this->betriebsmittelbetriebsmittelstatus_id;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Betriebsmittel Betriebsmittelstatus-Datensatzes '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
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

		$qry='DELETE from '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus '.
			' WHERE betriebsmittelbetriebsmittelstatus_id='.$this->addslashes($this->betriebsmittelbetriebsmittelstatus_id);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim entfernen des Betriebsmittel Betriebsmittelstatus-Datensatzes '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}	
	}	


	/**
	 * Entfernt die alle Daten zu einem Betriebsmittel in die Datenbank
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

		$qry='DELETE from '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus '.
			' WHERE betriebsmittel_id='.$this->addslashes($betriebsmittel_id);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{			
			$this->errormsg = 'Fehler beim entfernen des Betriebsmittel Betriebsmittelstatus-Datensatzes '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}	
	}	
}
?>