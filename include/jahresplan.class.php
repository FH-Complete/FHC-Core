<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
/**
 *
 * @classe Jahresplan 
 *
 * @param connectSQL Datenbankverbindung
 * @param veranstaltungskategorie_kurzbz Veranstaltungskategorie Key	
 * @param veranstaltung_id Veranstaltung Key 
 * @param reservierung_id Reservierung Key
 *
 */
require_once(dirname(__FILE__).'/basis_db.class.php'); 

class jahresplan extends basis_db 
{
	public $new;     			// @boolean
	public $result = array(); 	// jahresplan Objekt 

	// Veranstaltungskategorie	
	public $veranstaltungskategorie_kurzbz; // @string (16) KEY 
	public $bezeichnung; 		// @string (64)
	public $farbe; 				// @string (6)
	public $bild; 				// @string (255)
		
	// Veranstaltungen		
   	public $veranstaltung_id; 	// @int serial (Key)
   	
	public $titel; 				// @string (64)
	public $beschreibung; 		// @string (255)
	public $inhalt; 			// @string (255)
	
	public $start; 				// @timestamp oder @date
   	public $ende; 				// @timestamp oder @date

	public $insertamum; 		// @timestamp
	public $insertvon; 			// @string (16)
	public $updateamum; 		// @timestamp
	public $updatevon; 			// @string (16)
	public $freigabeamum; 		// @timestamp oder @date
	public $freigabevon; 		// @string (16)
		
	// Reservierung
   	public $reservierung_id;	// @int serial (key)  
	public $startDatum;			// @int
	public $endeDatum;			// @int
	public $startZeit;			// @int
	public $endeZeit;			// @int			
	
   	// Suchbedingungen
	public $show_only_public_kategorie=true;	// @boolean  - Public Kategorien sollen gelesen werden = false

   	public $start_jahr;			// @int
   	public $ende_jahr;			// @int
	
   	public $start_jahr_monat; 	// @int
   	public $ende_jahr_monat; 	// @int	
	
   	public $start_jahr_woche; 	// @int
   	public $ende_jahr_woche; 	// @int	
	
   	public $suchtext; 			// @int
	public $freigabe; 			// @boolean
	
	public $schemaSQL="campus"; // string Datenbankschema
		
	/**
	 * Konstruktor
	 * @param $db Connection zur DB
	 *        $veranstaltungskategorie_kurzbz  zum ladenden der Kategorie Funktion
	 *        $veranstaltung_id  zum ladenden der Veranstaltung Funktion
	 *        $reservierung_id  zum ladenden der Reservierung Funktion
	 *        $show_only_public_kategorie  Boolean welche Kategorien Public oder Alle fuer Lektoren und Mitarbeiter
	 */
	public function __construct($veranstaltungskategorie_kurzbz="",$veranstaltung_id="",$reservierung_id="",$show_only_public_kategorie=true) 
	{
		parent::__construct();

		// Init alle Funktionen und Variablen
		$this->InitJahresplan();
			
		// Berechtigungen beim Lesen
		$this->show_only_public_kategorie=$show_only_public_kategorie;
			
		// Veranstaltungskategorie
		$this->veranstaltungskategorie_kurzbz=$veranstaltungskategorie_kurzbz;
		if (!empty($this->veranstaltungskategorie_kurzbz))
			$this->loadVeranstaltungskategorie($this->veranstaltungskategorie_kurzbz);
	
		// Veranstaltungen
		$this->veranstaltung_id=$veranstaltung_id;
		if (!empty($this->veranstaltung_id))
			$this->loadVeranstaltung($this->veranstaltung_id,$this->veranstaltungskategorie_kurzbz);
	
		$this->reservierung_id=$reservierung_id;
		if (!empty($this->reservierung_id))
			$this->loadReservierung($this->reservierung_id,$this->veranstaltung_id);

	}

	/**
	 * Initialisierung
	 *
	 */
	public function InitJahresplan() 
	{
		$this->errormsg='';
		$this->new=false;	
			
		$this->InitVeranstaltungskategorie();
		$this->InitVeranstaltung();
		$this->InitReservierung();	
	}
	   
	/**
	 * Initialisierung der Kategorien
	 *
	 */
	public function InitVeranstaltungskategorie()
   	{
		// Veranstaltungskategorie	
			$this->veranstaltungskategorie_kurzbz='';
			$this->bezeichnung='';
			$this->farbe='';
			$this->bild='';			
			$this->result=array();
	}

	/**
	 * Speichert bzw. Aendert eine Veranstaltungskategorie
	 * @return true wenn ok, false im Fehlerfall
	 */	
	public function saveVeranstaltungskategorie()
   	{
		// Initialisieren
		$this->errormsg='';
		$qry='';
			
		$fildsList='';
		$fildsValue='';
		
		if (empty($this->veranstaltungskategorie_kurzbz) || $this->veranstaltungskategorie_kurzbz==null )
		{
			$this->errormsg='Veranstaltungskategorie - Kurzbz. fehlt!';
			return false;
		}

		if (empty($this->bezeichnung))
		{
			$this->errormsg='Veranstaltungskategorie - Bezeichnung fehlt!';
			return false;
		}
		
		if($this->new)
		{

			$fildsList.='veranstaltungskategorie_kurzbz,';
			$fildsList.='bezeichnung,';
			$fildsList.='farbe,';
			$fildsList.='bild';

			$fildsValue.="'".addslashes($this->veranstaltungskategorie_kurzbz)."',"; 
			$fildsValue.="'".addslashes($this->bezeichnung)."',";
			$fildsValue.="'".addslashes($this->farbe)."',";
			$fildsValue.="'".addslashes($this->bild)."'";
	
	   		$qry=" insert into ".$this->schemaSQL.".tbl_veranstaltungskategorie (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			$fildsValue.=(!empty($fildsValue)?',':'')."bezeichnung='".addslashes($this->bezeichnung)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."farbe='".addslashes($this->farbe)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."bild='".addslashes($this->bild)."'";

			$qry.=" update ".$this->schemaSQL.".tbl_veranstaltungskategorie set ";
			$qry.=$fildsValue;
			$qry.=" where veranstaltungskategorie_kurzbz='".addslashes($this->veranstaltungskategorie_kurzbz)."' ";
		}	
		
		if($this->db_query($qry))
			return true;
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim speichern des Datensatzes';
			return false;
		}	
	}

	/**
	 * Loescht eine Veranstaltungskategorie
	 * @return true wenn ok, false im Fehlerfall
	 */	      
	public function deleteVeranstaltungskategorie($veranstaltungskategorie_kurzbz="")
	{
		// Initialisieren
		$qry="";
		$this->errormsg='';
	
		// Parameter
		if (!empty($veranstaltungskategorie_kurzbz))
			$this->veranstaltungskategorie_kurzbz=$veranstaltungskategorie_kurzbz;
	
		// Plausib
		if (empty($this->veranstaltungskategorie_kurzbz) || $this->veranstaltungskategorie_kurzbz==null )
		{
			$this->errormsg='Veranstaltungskategorie - Kurzbz. fehlt!';
			return false;
		}
		
		// Abfrage
		$qry.=" delete from ".$this->schemaSQL.".tbl_veranstaltungskategorie ";
		if (is_array($this->veranstaltungskategorie_kurzbz))
			$qry.=" where veranstaltungskategorie_kurzbz in ('".implode("','",$this->veranstaltungskategorie_kurzbz)."') ";
		else	
			$qry.=" where veranstaltungskategorie_kurzbz='".addslashes($this->veranstaltungskategorie_kurzbz)."' ";

		if($this->db_query($qry))
			return true;
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim Veranstaltungskategorie löschen';
			return false;
		}
	}

	/**
	 * Lesen der Veranstaltungskategorien 
	 * @return Array mit Veranstaltungs-Objekt wenn ok, false im Fehlerfall
	 */	    
	public function loadVeranstaltungskategorie($veranstaltungskategorie_kurzbz="")
	{
		// Initialisieren
		$qry="";
		$this->errormsg='';
		$this->result=array();

		if (!empty($veranstaltungskategorie_kurzbz))
			$this->veranstaltungskategorie_kurzbz=$veranstaltungskategorie_kurzbz;
		
		// Abfrage		
	     	$qry.="SELECT * FROM ".$this->schemaSQL.".tbl_veranstaltungskategorie ";
	    $qry.=" WHERE veranstaltungskategorie_kurzbz IS NOT NULL ";

       	// Suche nach einer einzigen Veranstaltungskategorie_kurzbz
	    if (!is_array($this->veranstaltungskategorie_kurzbz) && !empty($this->veranstaltungskategorie_kurzbz) )
    		{
       		$qry.=" and veranstaltungskategorie_kurzbz='".addslashes($this->veranstaltungskategorie_kurzbz)."' ";	
	    }
    	elseif (is_array($this->veranstaltungskategorie_kurzbz) && count($this->veranstaltungskategorie_kurzbz)>0 )
	    {
   	   		$qry.=" and veranstaltungskategorie_kurzbz in ('".implode("','",$this->veranstaltungskategorie_kurzbz)."') ";	
		}

		// Entscheiden welche Daten angezeigt werden Public oder fuer Mitarbeiter alles		
		if ($this->show_only_public_kategorie)
    	   	$qry.=" and veranstaltungskategorie_kurzbz not like '*%' ";	

       $qry.=" ORDER BY veranstaltungskategorie_kurzbz ";	

		if($this->db_query($qry))
		{
			$this->result=array();
			while($row = $this->db_fetch_object())
			{
				$this->result[]=$row;
			}
			return $this->result;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler Veranstaltungskategorien lesen!';
			return false;
		}	
	}


	/**
	 *	Initialisierung Veranstaltung
	 *
	 */
	public function InitVeranstaltung() 
	{
		$this->InitVeranstaltungskategorie();
	
		$this->veranstaltung_id=0;
		$this->veranstaltungskategorie_kurzbz='';

		$this->titel='';
		$this->beschreibung='';

		$this->inhalt='';
		
		$this->start=''; 
		$this->ende='';

		$this->insertamum='';
		$this->insertvon='';
		$this->updateamum='';
		$this->updatevon='';
		$this->freigabeamum='';
		$this->freigabevon='';
		
		// Suchfelder
		$this->start_jahr='';
		$this->ende_jahr='';

		$this->start_jahr_monat='';
		$this->ende_jahr_monat='';

		$this->start_jahr_woche='';
		$this->ende_jahr_woche='';

		$this->suchtext='';
		$this->freigabe=false;
			
		$this->result=array();
	}

	/**
	 * Speichert bzw. Aendert eine Veranstaltung
	 * @return true wenn ok, false im Fehlerfall
	 * ToDo: angleichen an die anderen Save Funktionen
	 */	
	public function saveVeranstaltung()
	{
		// Initialisieren
		$this->errormsg='';
		$qry='';
		$fildsList='';
		$fildsValue='';
		
		if (!$this->new && ( empty($this->veranstaltung_id) || $this->veranstaltung_id==null) )
		{
			$this->errormsg='Veranstaltungs - ID fehlt!';
			return false;
		}

		if (empty($this->veranstaltungskategorie_kurzbz) || $this->veranstaltungskategorie_kurzbz==null )
		{
			$this->errormsg='Veranstaltungs - Kategoriekurzbz. fehlt!';
			return false;
		}

		if (empty($this->beschreibung))
		{
			$this->errormsg='Veranstaltungs - Beschreibung fehlt!';
			return false;
		}		
		
		if($this->new)
		{
			$fildsList.='veranstaltungskategorie_kurzbz,';
			$fildsList.='beschreibung,';
			$fildsList.='inhalt,';			
			$fildsList.='start,';
			$fildsList.='ende,';
			$fildsList.='insertamum,';
			$fildsList.='insertvon,';
			$fildsList.='updateamum,';
			$fildsList.='updatevon,';
			$fildsList.='freigabeamum,';
			$fildsList.='freigabevon';

			$fildsValue.="'".addslashes($this->veranstaltungskategorie_kurzbz)."',"; 
			$fildsValue.="'".addslashes($this->beschreibung)."',";
			$fildsValue.="'".addslashes($this->inhalt)."',";
			$fildsValue.="'".addslashes($this->start)."',";
			$fildsValue.="'".addslashes($this->ende)."',";
			$fildsValue.="'".addslashes($this->insertamum)."',";
			$fildsValue.="'".addslashes($this->insertvon)."',";
			$fildsValue.="'".addslashes($this->updateamum)."',";
			$fildsValue.="'".addslashes($this->updatevon)."',";
			$fildsValue.=(is_null($this->freigabeamum) || empty($this->freigabeamum)?'null':"'".addslashes($this->freigabeamum)."'").",";
			$fildsValue.=(is_null($this->freigabevon) || empty($this->freigabevon)?'null':"'".addslashes($this->freigabevon)."'");
			$qry.=" insert into ".$this->schemaSQL.".tbl_veranstaltung (".$fildsList.") values (".$fildsValue.") ";
		}
		else
		{

			$fildsValue.=(!empty($fildsValue)?',':'')."veranstaltungskategorie_kurzbz='".addslashes($this->veranstaltungskategorie_kurzbz)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."beschreibung='".addslashes($this->beschreibung)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."inhalt='".addslashes($this->inhalt)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."start='".addslashes($this->start)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."ende='".addslashes($this->ende)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."updateamum='".addslashes($this->updateamum)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."updatevon='".addslashes($this->updatevon)."'";
			if (is_null($this->freigabeamum))
				$fildsValue.=(!empty($fildsValue)?',':'')."freigabeamum=null";
			else
				$fildsValue.=(!empty($fildsValue)?',':'')."freigabeamum='".addslashes($this->freigabeamum)."'";
			$fildsValue.=(!empty($fildsValue)?',':'').(is_null($this->freigabevon) || empty($this->freigabevon)?"freigabevon=null":"freigabevon='".addslashes($this->freigabevon)."'");
			$qry.=" update ".$this->schemaSQL.".tbl_veranstaltung set ";
			$qry.=$fildsValue;
			$qry.=" where veranstaltung_id='".addslashes($this->veranstaltung_id)."' ";
		}

		if(!$this->db_query($qry))
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		
		if($this->new)
		{
			$qry=" select max(".$this->schemaSQL.".tbl_veranstaltung.veranstaltung_id) from ".$this->schemaSQL.".tbl_veranstaltung; "; 
			if(!$this->db_query($qry))
			{
				if (empty($this->errormsg))
					$this->errormsg = 'Fehler beim lesen des neuen Datensatzes';
				return false;
			}

			if (!$row = $this->db_fetch_object())
			{
				if (empty($this->errormsg))
					$this->errormsg = 'Fehler beim ermitteln des neuen Datensatzes';
				return false;
			}
			$this->veranstaltung_id=$row->max;
		}
		// Beim Lesen ist ein Fehler aufgetreten
		$this->freigabe=false;
		$veranstaltung_id=$this->veranstaltung_id;
		$this->InitVeranstaltung();
		$this->veranstaltung_id=$veranstaltung_id;
		if (!$this->loadVeranstaltung()) 
			return false;
		return $this->result;
	}

	/**
	 * Loescht eine Veranstaltung
	 * @return true wenn ok, false im Fehlerfall
	 */	
	public function deleteVeranstaltung($veranstaltung_id="")
	{
		// Initialisieren
		$qry='';
		$this->errormsg='';
	
		if (!empty($veranstaltung_id))
			$this->veranstaltung_id=$veranstaltung_id;
	
		if (empty($this->veranstaltung_id) || $this->veranstaltung_id==null )
		{
			$this->errormsg='Veranstaltung - ID fehlt!';
			return false;
		}
		
		// Abfrage
		$qry.="BEGIN;  ";
		
		// Reservierung 
		$qry.="update  ".$this->schemaSQL.".tbl_reservierung set veranstaltung_id=null ";
		if (is_array($this->veranstaltung_id))
			$qry.=" WHERE veranstaltung_id in (".implode(",",$this->veranstaltung_id)."); ";
		else 
			$qry.=" WHERE veranstaltung_id =".$this->veranstaltung_id."; ";
	
		// Veranstaltung	
		$qry.=" delete from ".$this->schemaSQL.".tbl_veranstaltung ";
		if (is_array($this->veranstaltung_id))
			$qry.=" WHERE veranstaltung_id in (".implode(",",$this->veranstaltung_id)."); ";
		else 
			$qry.=" WHERE veranstaltung_id =".$this->veranstaltung_id."; ";
	
		$qry.=" COMMIT; ";

		if($this->db_query($qry))
			return true;
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim Veranstaltung löschen ';
			return false;
		}	
	}

	/**
	 * Lesen der Veranstaltung 
	 * @return Array mit Veranstaltungs-Objekt wenn ok, false im Fehlerfall
	 */
	public function loadVeranstaltung($veranstaltungskategorie_kurzbz="",$veranstaltung_id="",$freigabe="",$show_only_public_kategorie="")
	{
		//Init
		$qry='';
	
		$this->errormsg='';
		$this->result=array();

		if ($veranstaltung_id!='')
			$this->veranstaltung_id=$veranstaltung_id;

		if (!empty($veranstaltungskategorie_kurzbz))
			$this->veranstaltungskategorie_kurzbz=$veranstaltungskategorie_kurzbz;
			
		if ($freigabe!='')
			$this->freigabe=$freigabe;

		if ($show_only_public_kategorie!='')
			$this->show_only_public_kategorie=$show_only_public_kategorie;
		
		$qry.="SELECT tbl_veranstaltung.* ";

		$qry.=", to_char(tbl_veranstaltung.start, 'YYYYMMDD') as \"start_jjjjmmtt\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'YYYYMMDD') as \"ende_jjjjmmtt\" ";
		$qry.=", to_char(tbl_veranstaltung.start, 'YYYYMM') as \"start_jahr_monat\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'YYYYMM') as \"ende_jahr_monat\" ";
		$qry.=", to_char(tbl_veranstaltung.start, 'YYYY') as \"start_jahr\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'YYYY') as \"ende_jahr\" ";

		$qry.=", to_char(tbl_veranstaltung.start, 'MM') as \"start_monat\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'MM') as \"ende_monat\" ";

		$qry.=", to_char(tbl_veranstaltung.start, 'DD') as \"start_tag\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'DD') as \"ende_tag\" ";
		
		$qry.=", to_char(tbl_veranstaltung.start, 'Day') as \"start_tagname\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'Day') as \"ende_tagname\" ";

		$qry.=", to_char(tbl_veranstaltung.start, 'IW') as \"start_woche\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'IW') as \"ende_woche\" ";

		$qry.=", to_char(tbl_veranstaltung.start, 'Q') as \"start_quartal\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'Q') as \"ende_quartal\" ";
		
		$qry.=", EXTRACT(EPOCH FROM tbl_veranstaltung.start::TIMESTAMP WITHOUT TIME
ZONE at time zone 'CEST' ) as \"start_timestamp\" ";
		$qry.=", EXTRACT(EPOCH FROM tbl_veranstaltung.ende::TIMESTAMP WITHOUT TIME
ZONE at time zone 'CEST' ) as \"ende_timestamp\" ";


		$qry.=", to_char(tbl_veranstaltung.start, 'DD.MM.YYYY') as \"start_datum\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'DD.MM.YYYY') as \"ende_datum\" ";

		$qry.=", to_char(tbl_veranstaltung.start, 'HH24:MI') as \"start_zeit\" ";
		$qry.=", to_char(tbl_veranstaltung.ende, 'HH24:MI') as \"ende_zeit\" ";

		$qry.=", to_char(tbl_veranstaltung.insertamum, 'DD.MM.YYYY') as \"insertamum_datum\" ";
		$qry.=", to_char(tbl_veranstaltung.insertamum, 'HH24:MI') as \"insertamum_zeit\" ";
		$qry.=", EXTRACT(EPOCH FROM tbl_veranstaltung.insertamum) as \"insertamum_timestamp\" ";

		$qry.=", to_char(tbl_veranstaltung.updateamum, 'DD.MM.YYYY') as \"updateamum_datum\" ";
		$qry.=", to_char(tbl_veranstaltung.updateamum, 'HH24:MI') as \"updateamum_zeit\" ";
		$qry.=", EXTRACT(EPOCH FROM tbl_veranstaltung.updateamum) as \"updateamum_timestamp\" ";
	
		$qry.=", to_char(tbl_veranstaltung.freigabeamum, 'DD.MM.YYYY') as \"freigabeamum_datum\" ";
		$qry.=", to_char(tbl_veranstaltung.freigabeamum, 'HH24:MI') as \"freigabeamum_zeit\" ";
		$qry.=", EXTRACT(EPOCH FROM tbl_veranstaltung.freigabeamum) as \"freigabeamum_timestamp\" ";

		$qry.=",tbl_veranstaltungskategorie.*,tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz as kategorie_kurzbz  ";

			$qry.=" FROM ".$this->schemaSQL.".tbl_veranstaltungskategorie  ";
			$qry.=" LEFT JOIN ".$this->schemaSQL.".tbl_veranstaltung ON ".$this->schemaSQL.".tbl_veranstaltung.veranstaltungskategorie_kurzbz=".$this->schemaSQL.".tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz ";
		$qry.=" WHERE ".$this->schemaSQL.".tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz>'' ";
	
		if ($this->freigabe)
		{
			$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltung.freigabevon>'' ";
		}
			// Suche nach einer einzigen Veranstaltung_id
			if (!is_array($this->veranstaltung_id) && !empty($this->veranstaltung_id) )
		{
			if(!is_numeric($this->veranstaltung_id))
			{
				$this->errormsg = 'Veranstaltung_id ist ungueltig';
				return false;
			}
			$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltung.veranstaltung_id='".addslashes($this->veranstaltung_id)."' ";
		}
		elseif (is_array($this->veranstaltung_id) && count($this->veranstaltung_id)>0 )
		{
			$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltung.veranstaltung_id in (".addslashes(implode(",",$this->veranstaltung_id)).") ";
		}

			// Suche nach einer einzigen Veranstaltungskategorie_kurzbz
			if (!is_array($this->veranstaltungskategorie_kurzbz) && $this->veranstaltungskategorie_kurzbz!='' )
			{
				$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz='".addslashes($this->veranstaltungskategorie_kurzbz)."' ";
			}
			elseif (is_array($this->veranstaltungskategorie_kurzbz) && count($this->veranstaltungskategorie_kurzbz)>0 )
			{
				$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz in ('".implode("','",$this->veranstaltungskategorie_kurzbz)."') ";
			}


		if (!empty($this->start) && empty($this->ende) )
			$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltung.start=to_timestamp(".$this->start.") ";
		else if (empty($this->start) && !empty($this->ende) )
			$qry.=" AND ".$this->schemaSQL.".tbl_veranstaltung.ende=to_timestamp(".$this->ende.") ";
		else if (!empty($this->start) && !empty($this->ende) )
		{
			$qry.=" AND to_timestamp(".$this->start.") >=to_timestamp(".$this->schemaSQL.".tbl_veranstaltung.start) ";
			$qry.=" AND to_timestamp(".$this->ende.") <= to_timestamp(".$this->schemaSQL.".tbl_veranstaltung.ende) ";
		}

		if (!empty($this->start_jahr) && empty($this->ende_jahr))
			$qry.=" AND to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYY')='".addslashes($this->start_jahr)."'";
		elseif (empty($this->start_jahr) && !empty($this->ende_jahr) )
			$qry.=" AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYY')='".addslashes($this->ende_jahr)."'";
		elseif (empty($this->start_jahr) && !empty($this->ende_jahr) )
		{
			$qry.=" AND '".addslashes($this->start_jahr)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYY') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYY') ";
			$qry.=" AND '".addslashes($this->ende_jahr)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYY') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYY') ";
		}


		if (!empty($this->start_jahr_monat) && empty($this->ende_jahr_monat) )
			$qry.=" AND '".addslashes($this->start_jahr_monat)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMM') and  to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMM')";
		elseif (empty($this->start_jahr_monat) && !empty($this->ende_jahr_monat) )
			$qry.=" AND '".addslashes($this->start_jahr_monat)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMM') and  to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMM')";
		elseif (!empty($this->start_jahr_monat) && !empty($this->ende_jahr_monat) )
		{
			$qry.=" AND '".addslashes($this->start_jahr_monat)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMM') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMM') ";
			$qry.=" AND '".addslashes($this->ende_jahr_monat)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMM') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMM') ";
		}
			
		if (!empty($this->start_jahr_woche) && empty($this->ende_jahr_woche) )
	   		$qry.=" AND '".addslashes($this->start_jahr_woche)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYIW'') and  to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYIW'')";	
		elseif (empty($this->start_jahr_woche) && !empty($this->ende_jahr_woche) )
	   		$qry.=" AND '".addslashes($this->start_jahr_woche)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYIW'') and  to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYIW'')";	
		elseif (!empty($this->start_jahr_woche) && !empty($this->ende_jahr_woche) )
		{
	   		$qry.=" AND '".addslashes($this->start_jahr_woche)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYIW'') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYIW'') ";	
	   		$qry.=" AND '".addslashes($this->ende_jahr_woche)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYIW'') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYIW'') ";	
		}			
					
		if (!empty($this->start_jahr_monat_tag) && empty($this->ende_jahr_monat_tag) )
	   		$qry.=" AND to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMMDD')>='".addslashes($this->start_jahr_monat_tag)."'";
		elseif (empty($this->start_jahr_monat_tag) && !empty($this->ende_jahr_monat_tag) )
	   		$qry.=" AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMMDD')<='".addslashes($this->ende_jahr_monat_tag)."'";
		elseif (!empty($this->start_jahr_monat_tag) && !empty($this->ende_jahr_monat_tag) )
		{
	   		$qry.=" AND '".addslashes($this->start_jahr_monat_tag)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMMDD') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMMDD') ";	
	   		$qry.=" AND '".addslashes($this->ende_jahr_monat_tag)."' between to_char(".$this->schemaSQL.".tbl_veranstaltung.start, 'YYYYMMDD') AND to_char(".$this->schemaSQL.".tbl_veranstaltung.ende, 'YYYYMMDD') ";	
		}

		if ($this->suchtext)
		{
			$this->suchtext='%'.$this->suchtext.'%';
			$this->suchtext=str_replace(' ','%',$this->suchtext);
			$this->suchtext=str_replace('%%','%',addslashes($this->suchtext));
	   		$qry.=" AND ( ".$this->schemaSQL.".tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz like '".addslashes($this->suchtext)."'
						OR ".$this->schemaSQL.".tbl_veranstaltungskategorie.bezeichnung like '".addslashes($this->suchtext)."'
						OR ".$this->schemaSQL.".tbl_veranstaltung.beschreibung like '".addslashes($this->suchtext)."'
						OR ".$this->schemaSQL.".tbl_veranstaltung.inhalt like '".addslashes($this->suchtext)."' ) ";	
		}		

		// Entscheiden welche Daten angezeigt werden Public oder fuer Mitarbeiter alles		
		if ($this->show_only_public_kategorie)
			$qry.=" AND NOT ".$this->schemaSQL.".tbl_veranstaltung.veranstaltungskategorie_kurzbz like '*%' ";	
	
		if (!empty($this->start) || !empty($this->ende) || !empty($this->start_jahr) || !empty($this->ende_jahr) || !empty($this->start_jahr_monat) || !empty($this->ende_jahr_monat)  || !empty($this->start_jahr_monat_tag) || !empty($this->ende_jahr_monat_tag) )
			$qry.=" ORDER BY ".$this->schemaSQL.".tbl_veranstaltung.start, ".$this->schemaSQL.".tbl_veranstaltungskategorie.bezeichnung  ";	
		else
			$qry.=" ORDER BY ".$this->schemaSQL.".tbl_veranstaltungskategorie.bezeichnung, ".$this->schemaSQL.".tbl_veranstaltung.start  ";	
			
		if($this->db_query($qry))
		{
			$veranstaltungkategorie=array();
			while($row = $this->db_fetch_object())
			{
				$veranstaltungkategorie[]=$row;
			}
			
			return $this->result=$veranstaltungkategorie;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler Veranstaltungen lesen';
			return false;
		}	
    }


	/**
	 * Reservierung Initialisieren
	 *
	 */
	public function InitReservierung() 
	{
		$this->reservierung_id=0; 
		$this->veranstaltung_id=null;

		$this->startDatum='';
		$this->endeDatum='';
		$this->startZeit='';
		$this->endeZeit='';			

		$this->result=array();
	}
	
	/**
	 * Reservierung Speichern
	 *
	 * @return unknown
	 * 
	 * TODO: eventuelle auslagerung in reservierung.class.php ???
	 */
	public function saveReservierung()
	{
		// Initialisieren
		$this->errormsg='';
		$qry="";
		
		// Plausib
		if ( empty($this->reservierung_id) ) 
		{
			$this->errormsg='Keine Reservierung ID gefunden !';
			return false;
		}	
		$qry.=" update ".$this->schemaSQL.".tbl_reservierung set veranstaltung_id=".(!empty($this->veranstaltung_id) && !is_null($this->veranstaltung_id)?$this->veranstaltung_id:"null")." WHERE reservierung_id=".$this->reservierung_id."; " ;
		if($this->db_query($qry))
			return true;
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt Reservierungen
	 *
	 * @param unknown_type $reservierung_id
	 * @param unknown_type $veranstaltung_id
	 * @param unknown_type $startDatum
	 * @param unknown_type $endeDatum
	 * @param unknown_type $startZeit
	 * @param unknown_type $endeZeit
	 * @return unknown
	 * 
	 * TODO: eventuelle auslagerung in reservierung.class.php ???
	 */
     public function loadReservierung($reservierung_id="",$veranstaltung_id="",$startDatum="",$endeDatum="",$startZeit="",$endeZeit="")
     {
		//Init
		$this->errormsg='';
	
     	if ($reservierung_id!='')
			$this->reservierung_id=$reservierung_id;
     	if ($veranstaltung_id!='')
			$this->veranstaltung_id=$veranstaltung_id;
	
     	if ($startDatum!='')
			$this->startDatum=$startDatum;
		
		if (!empty($this->startDatum) && is_numeric($this->startDatum))
			$this->startDatum=strftime('%Y%m%d',$this->startDatum);
		
		if ($endeDatum!='')
			$this->endeDatum=$endeDatum;
		
		if (!empty($this->endeDatum) && is_numeric($this->endeDatum))
			$this->endeDatum=strftime('%Y%m%d',$this->endeDatum);
	
		if ($startZeit!='')
			$this->startZeit=$startZeit;
		
		if (!empty($this->startZeit) && is_numeric($this->startZeit))
			$this->startZeit=date('Hi',$this->startZeit);
		
		if ($endeZeit!='')
			$this->endeZeit=$endeZeit;
		if (!empty($this->endeZeit) && is_numeric($this->endeZeit))
			$this->endeZeit=date('Hi',$this->endeZeit);			
		
		$qry='';
   		$qry.="SELECT tbl_reservierung.* ";
	   		
		$qry.=", to_char(tbl_reservierung.datum, 'YYYYMMDD') as \"datum_jjjjmmtt\" ";
		$qry.=", to_char(tbl_reservierung.datum, 'YYYYMM') as \"datum_jahr_monat\" ";
		$qry.=", to_char(tbl_reservierung.datum, 'YYYY') as \"datum_jahr\" ";
	
		$qry.=", to_char(tbl_reservierung.datum, 'WW') as \"datum_woche\" ";
	
		$qry.=", to_char(tbl_reservierung.datum, 'Q') as \"datum_quartal\" ";
		
		$qry.=", to_char(tbl_reservierung.datum, 'DD.MM.YYYY') as \"datum_anzeige\" ";
	
		$qry.=", lehre.tbl_stunde.beginn, lehre.tbl_stunde.ende ";	
		$qry.=", to_char(lehre.tbl_stunde.beginn, 'HH24:MI') as \"beginn_anzeige\" ";
		$qry.=", to_char(lehre.tbl_stunde.ende, 'HH24:MI') as \"ende_anzeige\" ";
	
		$qry.=", EXTRACT(EPOCH FROM tbl_reservierung.datum) as \"datum_timestamp\" ";
	
		
		$qry.=" FROM ".$this->schemaSQL.".tbl_reservierung  ";
		$qry.=" RIGHT JOIN lehre.tbl_stunde ON lehre.tbl_stunde.stunde=".$this->schemaSQL.".tbl_reservierung.stunde ";
		
		// Wird nur fuer Lesen alle - benoetigt
		if (empty($this->reservierung_id) && empty($this->veranstaltung_id))
		{
			if (!empty($this->startZeit) && empty($this->endeZeit) )
	   			$qry.=" AND to_char(lehre.tbl_stunde.beginn, 'HH24MI')='".$this->startZeit."' ";	
			else if (empty($this->startZeit) && !empty($this->endeZeit) )
	   			$qry.=" AND to_char(lehre.tbl_stunde.ende, 'HH24MI')='".$this->endeZeit."' ";	
			else if (!empty($this->startZeit) && !empty($this->endeZeit) )
			{
		   		$qry.=" AND to_char(lehre.tbl_stunde.beginn, 'HH24MI') >='".$this->startZeit."' ";	
  				$qry.=" AND to_char(lehre.tbl_stunde.ende, 'HH24MI') <= '".$this->endeZeit.",' ";	
			}	
		}		
		
    	$qry.=" WHERE ".$this->schemaSQL.".tbl_reservierung.titel>'' ";
	
	    // Suche nach einer einzigen reservierung_id
		if (!is_array($this->reservierung_id) && !empty($this->reservierung_id) )
      		$qry.=" AND ".$this->schemaSQL.".tbl_reservierung.reservierung_id=".$this->reservierung_id." ";	
		elseif (is_array($this->reservierung_id) && count($this->reservierung_id)>0 )
   	   		$qry.=" AND ".$this->schemaSQL.".tbl_reservierung.reservierung_id in (".implode(",",$this->reservierung_id).") ";	
	
     	// Suche nach einer einzigen Veranstaltung_id
		if (!is_array($this->veranstaltung_id) && !empty($this->veranstaltung_id) )
   			$qry.=" AND ".$this->schemaSQL.".tbl_reservierung.veranstaltung_id=".$this->veranstaltung_id." ";	
  	   	elseif (is_array($this->veranstaltung_id) && count($this->veranstaltung_id)>0 )
   	   		$qry.=" AND ".$this->schemaSQL.".tbl_reservierung.veranstaltung_id in (".implode(",",$this->veranstaltung_id).") ";	
	
		if (!empty($this->startDatum) && empty($this->endeDatum) )
	   		$qry.=" AND to_char(".$this->schemaSQL.".tbl_reservierung.datum, 'YYYYMMDD')='".$this->start."' ";	
		else if (empty($this->startDatum) && !empty($this->endeDatum) )
  			$qry.=" AND to_char(".$this->schemaSQL.".tbl_reservierung.datum, 'YYYYMMDD')='".$this->ende."' ";	
		else if (!empty($this->startDatum) && !empty($this->endeDatum) )
		{
	   		$qry.=" AND '".$this->startDatum."' between to_char(".$this->schemaSQL.".tbl_reservierung.datum, 'YYYYMMDD') AND to_char(".$this->schemaSQL.".tbl_reservierung.datum, 'YYYYMMDD') ";	
  			$qry.=" AND '".$this->endeDatum."' between to_char(".$this->schemaSQL.".tbl_reservierung.datum, 'YYYYMMDD') AND to_char(".$this->schemaSQL.".tbl_reservierung.datum, 'YYYYMMDD') ";	
		}	
		$qry.=" ORDER BY ".$this->schemaSQL.".tbl_reservierung.datum,tbl_reservierung.stunde  ";	
			
		if($this->db_query($qry))
		{
			$this->result=array();
			while($row = $this->db_fetch_object())
			{
				$this->result[]=$row;
			}
			return $this->result;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler Reservierung lesen';
			return false;
		}	
	}	
}
?>
