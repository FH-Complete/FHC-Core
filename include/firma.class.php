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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and.
 *          Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at>.
 */
/**
 * Klasse firma
 * @create 18-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/organisationseinheit.class.php');

class firma extends basis_db
{
	public $new;       			// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $firma_id;		// integer
	public $name;			// string
	public $anmerkung;		// string
	public $ext_id;			// integer
	public $insertamum;		// timestamp
	public $insertvon;		// bigint
	public $updateamum;		// timestamp
	public $updatevon;		// bigint
	public $firmentyp_kurzbz;	
	public $schule; 		// boolean
	public $steuernummer; 	// string	
	public $gesperrt; 		// boolean
	public $aktiv; 			// boolean	
	public $finanzamt; 		// string	
	
	// firma_organisationseinheit
	public $oe_kurzbz; 		// string
	public $oe_parent_kurzbz; 	// string
	public $firma_organisationseinheit_id;	// integer
	public $organisationseinheittyp_kurzbz; // string

	public $bezeichnung; 		// string
	public $kundennummer; 		// integer
	public $oe_aktiv; 			// boolean	
	public $mailverteiler; 		// string
	public $tags = array();

			
	/**
	 * Konstruktor
	 * @param $firma_id ID der Firma die geladen werden soll (Default=null)
	 */
	public function __construct($firma_id=null)
	{
		parent::__construct();
		
		if(!is_null($firma_id))
			$this->load($firma_id);
	}

	/**
	 * Laedt die Firma mit der ID $firma_id
	 * @param  $firma_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($firma_id)
	{
		if(!is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}
		
		$qry = "SElECT * FROM public.tbl_firma WHERE firma_id='$firma_id'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->firma_id = $row->firma_id;
				$this->name = $row->name;
				$this->anmerkung = $row->anmerkung;
				$this->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->schule = ($row->schule=='t'?true:false);
				$this->steuernummer = $row->steuernummer;				
				$this->gesperrt = ($row->gesperrt=='t'?true:false);
				$this->aktiv = ($row->aktiv=='t'?true:false);		
				$this->finanzamt = $row->finanzamt;				
				
				$qry = "SELECT tag FROM public.tbl_firmatag WHERE firma_id='$firma_id'";
				if($resulttag = $this->db_query($qry))
				{
					while($rowtag = $this->db_fetch_object($resulttag))
					{
						$this->tags[]=$rowtag->tag;
					}
				}
				return true;
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Gesamtlaenge pruefen
		if(mb_strlen($this->name)>128)
		{
			$this->errormsg = 'Name darf nicht länger als 128 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 256 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO public.tbl_firma (name,  anmerkung, 
					firmentyp_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id, schule,steuernummer,
					gesperrt,aktiv,finanzamt) VALUES('.
			     $this->addslashes($this->name).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->firmentyp_kurzbz).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).','.
			     ($this->schule?'true':'false').','.
			     $this->addslashes($this->steuernummer).', '.				 
			     ($this->gesperrt?'true':'false').','.
			     ($this->aktiv?'true':'false').','.				 				 
			     ($this->finanzamt?$this->addslashes($this->finanzamt):'null').' ); '; 			 
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob firma_id eine gueltige Zahl ist
			if(!is_numeric($this->firma_id))
			{
				$this->errormsg = 'firma_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_firma SET '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'name='.$this->addslashes($this->name).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'updateamum= now(), '.
		     	'updatevon='.$this->addslashes($this->updatevon).', '.
		     	'firmentyp_kurzbz='.$this->addslashes($this->firmentyp_kurzbz).', '.
		     	'schule='.($this->schule?'true':'false').', '.
		     	'steuernummer='.$this->addslashes($this->steuernummer).', '.
		     	'gesperrt='.($this->gesperrt?'true':'false').', '.
		     	'aktiv='.($this->aktiv?'true':'false').', '.
		     	'finanzamt='.($this->finanzamt?addslashes($this->finanzamt):'null').' '.
				'WHERE firma_id='.$this->addslashes($this->firma_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('public.tbl_firma_firma_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->firma_id = $row->id;
						$this->db_query('COMMIT');
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Firma-Datensatzes';
			return false;
		}
		return $this->firma_id;
	}

	/**
	 * Speichert die Tags in $tags zur Firma
	 * 
	 */
	public function savetags()
	{
		if(!is_numeric($this->firma_id) || $this->firma_id=='')
		{
			$this->errormsg = 'FirmaID ist ungueltig';
			return false;
		}
		
		foreach($this->tags as $tag)
		{
			if($tag!='')
			{
				$qry = "
					SELECT 
						(SELECT true FROM public.tbl_firmatag WHERE tag='".addslashes($tag)."' AND firma_id='$this->firma_id') as zugewiesen,
						(SELECT true FROM public.tbl_tag WHERE tag='".addslashes($tag)."') as vorhanden";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						if($row->vorhanden!='t')
						{
							//Tag neu anlegen
							$qry = "INSERT INTO public.tbl_tag(tag) VALUES('".addslashes($tag)."');";
							if(!$this->db_query($qry))
							{
								$this->errormsg='Fehler beim Anlegen des Tags';
								return false;
							}
						}
						
						if($row->zugewiesen!='t')
						{
							//Tag zuweisen
							$qry = "INSERT INTO public.tbl_firmatag(firma_id, tag, insertamum, insertvon) 
									VALUES(".$this->addslashes($this->firma_id).",".
										$this->addslashes($tag).",".
										$this->addslashes($this->insertamum).",".
										$this->addslashes($this->insertvon).");";
							if(!$this->db_query($qry))
							{
								$this->errormsg='Fehler beim Anlegen des Tags';
								return false;
							}
						}
					}
					else 
					{
						$this->errormsg='Fehler beim Laden der Tags';
						return false;
					}
				}
				else 
				{
					$this->errormsg='Fehler beim Laden der Tags';
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Loescht die Tag-Zuordnung zur Firma
	 *
	 * @param $firma_id
	 * @param $tag
	 * @return boolean
	 */
	public function deletetag($firma_id, $tag)
	{
		if(!is_numeric($firma_id) || $firma_id=='')
		{
			$this->errormsg = 'FirmaID ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_firmatag WHERE firma_id='".addslashes($firma_id)."' AND tag='".addslashes($tag)."'";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Löschen des Tags';
			return false;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $firma_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($firma_id)
	{
		$qry = "DELETE FROM public.tbl_firma WHERE firma_id='$firma_id'";
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Firmen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($firma_search = null)
	{
		$qry = "SElECT * FROM public.tbl_firma";
		if (!empty($firma_search))
		{
			$qry.= " WHERE ";
			$matchcode=mb_strtoupper(addslashes(str_replace(array('<','>',' ',';','*','_','-',',',"'",'"'),"%",$firma_search)));		
			$qry.="  UPPER(trim(public.tbl_firma.name)) like '%".$matchcode."%'";
		}
		
		$qry.= " ORDER BY NAME"; 
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$fa->steuernummer = $row->steuernummer;				
				$fa->gesperrt = ($row->gesperrt=='t'?true:false);
				$fa->aktiv = ($row->aktiv=='t'?true:false);		
				$fa->finanzamt = $row->finanzamt;				
			
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Liefert alle vorhandenen Firmentypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmenTypen()
	{
		$qry = "SELECT * FROM public.tbl_firmentyp ORDER BY firmentyp_kurzbz";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->beschreibung = $row->beschreibung;
				
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Auslesen der Firmentypen';
			return false;
		}
	}

	/**
	 * Laedt alle Firmen eines bestimmen Firmentyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmen($firmentyp_kurzbz='')
	{
		$qry = "SElECT * FROM public.tbl_firma";
		
		if($firmentyp_kurzbz!='')
			$qry.=" WHERE firmentyp_kurzbz='".addslashes($firmentyp_kurzbz)."'";
		$qry.=" ORDER BY name";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$fa->steuernummer = $row->steuernummer;				
				$fa->gesperrt = ($row->gesperrt=='t'?true:false);
				$fa->aktiv = ($row->aktiv=='t'?true:false);		
				$fa->finanzamt = $row->finanzamt;				
				
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt alle Firmen Standorte, und Adressen nach Suchstring und/oder eines bestimmen Firmentyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function searchFirma($filter='',$firmentyp_kurzbz='', $standorte=false)
	{
		$this->result = array();
		$this->errormsg = '';
	
		$qry ="SELECT * FROM (SElECT ";
		if(!$standorte)
			$qry.=" distinct on(firma_id)";
		$qry.=" tbl_firma.firma_id,tbl_firma.* ,tbl_standort.kurzbz,tbl_standort.adresse_id,tbl_standort.standort_id,tbl_standort.bezeichnung  ";
		$qry.=" ,person_id,	tbl_adresse.name as adress_name, strasse, plz, ort, gemeinde,nation,typ,heimatadresse,zustelladresse  ";		
		$qry.=" FROM public.tbl_firma";
		$qry.=" LEFT JOIN public.tbl_standort USING(firma_id) ";
		$qry.=" LEFT JOIN public.tbl_adresse  on ( tbl_adresse.adresse_id=tbl_standort.adresse_id ) ";
		$qry.=" WHERE 1=1";

		if($filter!='')
			$qry.= " and ( lower(tbl_firma.name) like lower('%$filter%') 
					OR lower(kurzbz) like lower('%$filter%') 			
					
					OR lower(tbl_adresse.name) like lower('%$filter%') 
					OR lower(plz) like lower('%$filter%') 
					OR lower(ort) like lower('%$filter%') 
					OR lower(strasse) like lower('%$filter%') 
					
					OR lower(bezeichnung) like lower('%$filter%') 
					OR lower(anmerkung) like lower('%$filter%')
					".(is_numeric($filter)?" OR tbl_firma.firma_id='$filter'":'')."
					OR tbl_firma.firma_id IN (SELECT firma_id FROM public.tbl_firmatag 
											  WHERE firma_id=tbl_firma.firma_id AND lower(tag) like lower('%$filter%'))
					 ) ";
		
		if($firmentyp_kurzbz!='')
			$qry.=" and firmentyp_kurzbz='".addslashes($firmentyp_kurzbz)."'";
		
		//if($filter=='' && $firmentyp_kurzbz=='')
		//	$qry.=" limit 500 ";
		$qry.=") as a ORDER BY name ";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$fa->steuernummer = $row->steuernummer;				
				$fa->gesperrt = ($row->gesperrt=='t'?true:false);
				$fa->aktiv = ($row->aktiv=='t'?true:false);		
				$fa->finanzamt = $row->finanzamt;		
				$fa->kurzbz = $row->kurzbz;		
				$fa->adresse_id = $row->adresse_id;		
				$fa->standort_id = $row->standort_id;		
				$fa->bezeichnung = $row->bezeichnung;	
				$fa->person_id = $row->person_id;		
				$fa->adresse_id = $row->adresse_id;		
				$fa->strasse = $row->strasse;		
				$fa->plz = $row->plz;	
				$fa->ort = $row->ort;	
				$fa->gemeinde = $row->gemeinde;		
				$fa->nation = $row->nation;		
				$fa->typ = $row->typ;		
				$fa->adress_name = $row->adress_name;						
				$fa->heimatadresse = ($row->heimatadresse=='t'?true:false);	
				$fa->zustelladresse = ($row->zustelladresse=='t'?true:false);
				
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}	

	/**
	 * Liefert die Kundennummer einer Firma zu einer Organisationseinheit
	 * Wenn fuer diese Organisationseinheit kein Eintrag vorhanden ist, wird
	 * in den uebergeordneten OEs gesucht
	 *
	 * @param firma_id
	 * @param oe_kurzbz
	 * @return kundennummer oder false wenn nicht vorhanden 
	 */
	public function get_kundennummer($firma_id, $oe_kurzbz)
	{
		$qry = "SELECT kundennummer FROM public.tbl_firma_organisationseinheit 
				WHERE firma_id='".addslashes($firma_id)."' AND oe_kurzbz='".addslashes($oe_kurzbz)."';";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->kundennummer;
			}
			else
			{
				$oe = new organisationseinheit();
				if($oe->load($oe_kurzbz))
				{
					if($oe->oe_parent_kurzbz!='')
						return $this->get_kundennummer($firma_id, $oe->oe_parent_kurzbz);
					else
						return false;
				}
				else
					return false;
			}
		}
	}

	/**
	 * Laedt alle Firmen -  Organisationseinheiten nach Firmen ID und/oder OE Kurzbz 
	 * @param $firma_id ID die gelesen werden soll
	 * @param $oe_kurzbz Organisationskurzbezeichnung 
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function get_firmaorganisationseinheit($firma_id='',$oe_kurzbz='')
	{
		$this->result = array();
		$this->errormsg = '';
		if($firma_id && !is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}

		$qry =" select tbl_firma.*  ";
		$qry.=" ,tbl_firma_organisationseinheit.firma_organisationseinheit_id ,tbl_firma_organisationseinheit.kundennummer ,tbl_firma_organisationseinheit.oe_kurzbz ";
		$qry.=" ,tbl_organisationseinheit.oe_parent_kurzbz,tbl_organisationseinheit.bezeichnung, tbl_firma_organisationseinheit.bezeichnung as fobezeichnung, ";
		$qry.=" tbl_organisationseinheit.organisationseinheittyp_kurzbz,tbl_organisationseinheit.aktiv as oe_aktiv,tbl_organisationseinheit.mailverteiler   ";		
		$qry.=" FROM public.tbl_firma";
		$qry.=" JOIN public.tbl_firma_organisationseinheit USING(firma_id)";
		$qry.=" left outer join public.tbl_organisationseinheit  on ( tbl_organisationseinheit.oe_kurzbz=tbl_firma_organisationseinheit.oe_kurzbz ) ";
		$qry.=" WHERE true ";

		if($firma_id!='')
			$qry.=" and tbl_firma_organisationseinheit.firma_id='".addslashes($firma_id)."'";
		if($oe_kurzbz!='')
			$qry.=" and tbl_firma_organisationseinheit.oe_kurzbz='".addslashes($oe_kurzbz)."'";
			
		$qry.=" ORDER BY tbl_firma.name, tbl_firma_organisationseinheit.oe_kurzbz ";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$fa->steuernummer = $row->steuernummer;				
				$fa->gesperrt = ($row->gesperrt=='t'?true:false);
				$fa->aktiv = ($row->aktiv=='t'?true:false);		
				$fa->finanzamt = $row->finanzamt;		
				$fa->oe_kurzbz = $row->oe_kurzbz;	
				$fa->firma_organisationseinheit_id = $row->firma_organisationseinheit_id;		
				$fa->oe_parent_kurzbz = $row->oe_parent_kurzbz;		
				$fa->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;	
				$fa->bezeichnung = $row->bezeichnung;	
				$fa->fobezeichnung = $row->fobezeichnung;		
				$fa->kundennummer = $row->kundennummer;						

				$fa->oe_aktiv = ($row->oe_aktiv=='t'?true:false);	
				$fa->mailverteiler = ($row->mailverteiler=='t'?true:false);	
				
				$this->result[]=$fa;
			}
			return $this->result;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}	
	
	/**
	 * Laedt Firma -  Organisationseinheiten nach Zwischentabellen ID 
	 * @param $firma_organisationseinheit_id  Zwischentabellen ID 
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_firmaorganisationseinheit($firma_organisationseinheit_id='')
	{
		$this->result = array();
		$this->errormsg = '';
		

		$qry =" select *  ";
		$qry.=" FROM public.tbl_firma_organisationseinheit ";
		$qry.=" WHERE tbl_firma_organisationseinheit.firma_organisationseinheit_id='".addslashes($firma_organisationseinheit_id)."'";	
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{				
				$this->firma_id = $row->firma_id;

				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->oe_kurzbz = $row->oe_kurzbz;	
				$this->firma_organisationseinheit_id = $row->firma_organisationseinheit_id;		
				$this->bezeichnung = $row->bezeichnung;		
				$this->kundennummer = $row->kundennummer;							
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}	
	
	
	/**
	 * Loescht den Firma/Organisations Datenensatz mit der ID die uebergeben wird
	 * @param $firma_organisationseinheit_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function deleteorganisationseinheit($firma_organisationseinheit_id)
	{
		if(!is_numeric($firma_organisationseinheit_id))
		{
			$this->errormsg = 'Organisationseinheit/Firma_id ist ungueltig';
			return false;
		}
		$qry = "delete from public.tbl_firma_organisationseinheit WHERE firma_organisationseinheit_id>0";
		if ($firma_organisationseinheit_id)
			$qry.=" and firma_organisationseinheit_id='".addslashes($firma_organisationseinheit_id)."'";
 			
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveorganisationseinheit()
	{
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO public.tbl_firma_organisationseinheit (firma_id,oe_kurzbz, 
					bezeichnung,kundennummer, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES('.
			     $this->addslashes($this->firma_id).', '.
			     $this->addslashes($this->oe_kurzbz).', '.
			     $this->addslashes($this->bezeichnung).', '.
			     $this->addslashes($this->kundennummer).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).' ); '; 			 
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob firma_id eine gueltige Zahl ist
			if(!is_numeric($this->firma_id))
			{
				$this->errormsg = 'firma_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_firma_organisationseinheit SET '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'oe_kurzbz='.$this->addslashes($this->oe_kurzbz).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'kundennummer='.$this->addslashes($this->kundennummer).', '.
				'updateamum= now(), '.
		     	'updatevon='.$this->addslashes($this->updatevon).', '.
		     	'ext_id='.$this->addslashes($this->ext_id).' '.
				'WHERE firma_organisationseinheit_id='.$this->addslashes($this->firma_organisationseinheit_id).';';
		}
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('public.tbl_firma_organisationseinhei_firma_organisationseinheit_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->firma_organisationseinheit_id = $row->id;
						$this->db_query('COMMIT');
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Firma-Datensatzes';
			return false;
		}
		return $this->firma_organisationseinheit_id;
	}

}
?>
