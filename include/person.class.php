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
/*
 * Benoetigt functions.inc.php
 */
require_once('basis_db.class.php');

class person extends basis_db
{
	public $errormsg;			// string
	public $new;      			// boolean
	public $personen = array(); // person Objekt
	public $done=false;			// boolean

	//Tabellenspalten
	public $person_id;        	// integer
	public $sprache;			// varchar(16)
	public $anrede;				// varchar(16)
	public $titelpost;         	// varchar(32)
	public $titelpre;          	// varchar(64)
	public $nachname;          	// varchar(64)
	public $vorname;           	// varchar(32)
	public $vornamen;          	// varchar(128)
	public $gebdatum;          	// date
	public $gebort;            	// varchar(128)
	public $gebzeit;           	// time
	public $foto;              	// text
	public $anmerkungen;       	// varchar(256)
	public $homepage;          	// varchar(256)
	public $svnr;				// char(10)
	public $ersatzkennzeichen; 	// char(10)
	public $familienstand;     	// char(1)
	public $anzahlkinder;      	// smalint
	public $aktiv;             	// boolean
	public $insertamum;			// timestamp
	public $insertvon;			// varchar(16)
	public $updateamum;			// timestamp
	public $updatevon;			// varchar(16)
	public $geschlecht;			// varchar(1)
	public $staatsbuergerschaft;// varchar(3)
	public $geburtsnation;		// varchar(3);
	public $ext_id;				// bigint
	public $kurzbeschreibung; 	// text

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Person
	// * @param $person_id      Person die geladen werden soll (default=null)
	// *************************************************************************
	public function __construct($person_id=null)
	{
		parent::__construct();

		if($person_id != null)
			$this->load($person_id);
	}

	// *********************************************************
	// * Laedt Person mit der uebergebenen ID
	// * @param $person_id ID der Person die geladen werden soll
	// *********************************************************
	public function load($person_id)
	{
		//person_id auf gueltigkeit pruefen
		if(is_numeric($person_id) && $person_id!='')
		{
			$qry = "SELECT person_id, sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
				gebdatum, gebort, gebzeit, foto, anmerkung, homepage, svnr, ersatzkennzeichen,
				familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon, ext_id,
				geschlecht, staatsbuergerschaft, geburtsnation, kurzbeschreibung
				FROM public.tbl_person WHERE person_id='$person_id'";

			if(!$this->db_query($qry))
			{
				$this->errormsg = "Fehler beim Lesen der Personendaten\n";
				return false;
			}

			if($row = $this->db_fetch_object())
			{
				$this->person_id = $row->person_id;
				$this->sprache = $row->sprache;
				$this->anrede = $row->anrede;
				$this->titelpost = $row->titelpost;
				$this->titelpre = $row->titelpre;
				$this->nachname = $row->nachname;
				$this->vorname = $row->vorname;
				$this->vornamen = $row->vornamen;
				$this->gebdatum = $row->gebdatum;
				$this->gebort = $row->gebort;
				$this->gebzeit = $row->gebzeit;
				$this->foto = $row->foto;
				$this->anmerkungen = $row->anmerkung;
				$this->homepage = $row->homepage;
				$this->svnr = $row->svnr;
				$this->ersatzkennzeichen = $row->ersatzkennzeichen;
				$this->familienstand = $row->familienstand;
				$this->anzahlkinder = $row->anzahlkinder;
				$this->aktiv = ($row->aktiv=='t'?true:false);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;
				$this->geschlecht = $row->geschlecht;
				$this->staatsbuergerschaft = $row->staatsbuergerschaft;
				$this->geburtsnation = $row->geburtsnation;
				$this->kurzbeschreibung = $row->kurzbeschreibung;
			}
			else
			{
				$this->errormsg = "Es ist kein Personendatensatz mit der ID ".$person_id." vorhanden\n";
				return false;
			}

			return true;
		}
		else
		{
			$this->errormsg = "Die person_id muss eine gueltige Zahl sein\n";
			return false;
		}
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	protected function validate()
	{
		$this->nachname = trim($this->nachname);
		$this->vorname = trim($this->vorname);
		$this->vornamen = trim($this->vornamen);
		$this->anrede = trim($this->anrede);
		$this->titelpost = trim($this->titelpost);
		$this->titelpre = trim($this->titelpre);
		
		if(utf8_strlen($this->sprache)>16)
		{
			$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->anrede)>16)
		{
			$this->errormsg = 'Anrede darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->titelpost)>32)
		{
			$this->errormsg = 'Titelpost darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->titelpre)>64)
		{
			$this->errormsg = 'Titelpre darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->nachname)>64)
		{
			$this->errormsg = 'Nachname darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($this->nachname=='' || is_null($this->nachname))
		{
			$this->errormsg = 'Nachname muss eingegeben werden';
			return false;
		}

		if(utf8_strlen($this->vorname)>32)
		{
			$this->errormsg = 'Vorname darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->vornamen)>128)
		{
			$this->errormsg = 'Vornamen darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		//ToDo Gebdatum pruefen -> laut bis muss student aelter als 10 Jahre sein
		/*if(strlen($this->gebdatum)==0 || is_null($this->gebdatum))
		{
			$this->errormsg = "Geburtsdatum muss eingegeben werden\n";
			return false;
		}*/
		if(utf8_strlen($this->gebort)>128)
		{
			$this->errormsg = 'Geburtsort darf nicht laenger als 128 Zeichen sein';
			return false;
		}

		if(utf8_strlen($this->homepage)>256)
		{
			$this->errormsg = 'Homepage darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->svnr)>10)
		{
			$this->errormsg = 'SVNR darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if($this->svnr!='')
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3,7,9,0,5,8,4,2,1,6);
			$erg=0;
			//Quersumme bilden
			for($i=0;$i<10;$i++)
				$erg += $gewichtung[$i] * $this->svnr{$i};

			if($this->svnr{3}!=($erg%11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				$this->errormsg = 'SVNR ist ungueltig';
				return false;
			}
		}

		if($this->svnr!='')
		{
			//Pruefen ob bereits ein Eintrag mit dieser SVNR vorhanden ist
			$qry = "SELECT person_id FROM public.tbl_person WHERE svnr='$this->svnr'";
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					if($row->person_id!=$this->person_id)
					{
						$this->errormsg = 'Es existiert bereits eine Person mit dieser SVNR! Daten wurden NICHT gepeichert.';
						return false;
					}
				}
			}
		}

		if(utf8_strlen($this->ersatzkennzeichen)>10)
		{
			$this->errormsg = 'Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->familienstand)>1)
		{
			$this->errormsg = 'Familienstand ist ungueltig';
			return false;
		}
		if($this->anzahlkinder!='' && !is_numeric($this->anzahlkinder))
		{
			$this->errormsg = 'Anzahl der Kinder ist ungueltig';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv ist ungueltig';
			return false;
		}
		if(utf8_strlen($this->insertvon)>16)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->updatevon)>16)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->ext_id!='' && !is_numeric($this->ext_id))
		{
			$this->errormsg = 'Ext_ID ist keine gueltige Zahl';
			return false;
		}
		if(utf8_strlen($this->geschlecht)>1)
		{
			$this->errormsg = 'Geschlecht darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->geburtsnation)>3)
		{
			$this->errormsg = 'Geburtsnation darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if(utf8_strlen($this->staatsbuergerschaft)>3)
		{
			$this->errormsg = 'Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if($this->geschlecht!='m' && $this->geschlecht!='w' && $this->geschlecht!='u')
		{
			$this->errormsg = 'Geschlecht mu� w, m oder u sein!';
			return false;
		}
		
		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if($this->svnr!='' && $this->gebdatum!='')
		{
			if(ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})",$this->gebdatum, $regs))
			{
				//$day = sprintf('%02s',$regs[1]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = substr($regs[3],2,2);
			}
			elseif(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->gebdatum, $regs))
			{
				//$day = sprintf('%02s',$regs[3]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = substr($regs[1],2,2);
			}
			else 
			{
				$this->errormsg = 'Format des Geburtsdatums ist ungueltig';
				return false;
			}
			
			/* das muss nicht immer so sein
			$day_svnr = substr($this->svnr, 4, 2);
			$month_svnr = substr($this->svnr, 6, 2);
			$year_svnr = substr($this->svnr, 8, 2);
		
			if($day_svnr!=$day || $month_svnr!=$month || $year_svnr!=$year)
			{
				$this->errormsg = 'SVNR und Geburtsdatum passen nicht zusammen';
				return false;
			}
			*/
		}

		return true;
	}

	// ************************************************************
	// * Speichert die Personendaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $person_id upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!person::validate())
			return false;

		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
			                    gebdatum, gebort, gebzeit, foto, anmerkung, homepage, svnr, ersatzkennzeichen,
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
			                    geschlecht, geburtsnation, staatsbuergerschaft, ext_id, kurzbeschreibung)
			        VALUES('.$this->addslashes($this->sprache).','.
					$this->addslashes($this->anrede).','.
					$this->addslashes($this->titelpost).','.
				        $this->addslashes($this->titelpre).','.
				        $this->addslashes($this->nachname).','.
				        $this->addslashes($this->vorname).','.
				        $this->addslashes($this->vornamen).','.
				        $this->addslashes($this->gebdatum).','.
				        $this->addslashes($this->gebort).','.
				        $this->addslashes($this->gebzeit).','.
				        $this->addslashes($this->foto).','.
				        $this->addslashes($this->anmerkungen).','.
				        $this->addslashes($this->homepage).','.
				        $this->addslashes($this->svnr).','.
				        $this->addslashes($this->ersatzkennzeichen).','.
				        $this->addslashes($this->familienstand).','.
				        $this->addslashes($this->anzahlkinder).','.
				        ($this->aktiv?'true':'false').','.
				        'now(),'.
				        $this->addslashes($this->insertvon).','.
				        'now(),'.
				        $this->addslashes($this->updatevon).','.
				        $this->addslashes($this->geschlecht).','.
				        $this->addslashes($this->geburtsnation).','.
				        $this->addslashes($this->staatsbuergerschaft).','.
				        $this->addslashes($this->ext_id).','.
				        $this->addslashes($this->kurzbeschreibung).');';
		}
		else
		{
			//person_id auf gueltigkeit pruefen
			if(!is_numeric($this->person_id))
			{
				$this->errormsg = "person_id muss eine gueltige Zahl sein\n";
				return false;
			}

			$qry = 'UPDATE public.tbl_person SET'.
			       ' sprache='.$this->addslashes($this->sprache).','.
			       ' anrede='.$this->addslashes($this->anrede).','.
			       ' titelpost='.$this->addslashes($this->titelpost).','.
			       ' titelpre='.$this->addslashes($this->titelpre).','.
			       ' nachname='.$this->addslashes($this->nachname).','.
			       ' vorname='.$this->addslashes($this->vorname).','.
			       ' vornamen='.$this->addslashes($this->vornamen).','.
			       ' gebdatum='.$this->addslashes($this->gebdatum).','.
			       ' gebort='.$this->addslashes($this->gebort).','.
			       ' gebzeit='.$this->addslashes($this->gebzeit).','.
			       ' foto='.$this->addslashes($this->foto).','.
			       ' anmerkung='.$this->addslashes($this->anmerkungen).','.
			       ' homepage='.$this->addslashes($this->homepage).','.
			       ' svnr='.$this->addslashes($this->svnr).','.
			       ' ersatzkennzeichen='.$this->addslashes($this->ersatzkennzeichen).','.
			       ' familienstand='.$this->addslashes($this->familienstand).','.
			       ' anzahlkinder='.$this->addslashes($this->anzahlkinder).','.
			       ' aktiv='.($this->aktiv?'true':'false').','.
			       //' insertamum='.$this->addslashes($this->insertamum).','.
			       //' insertvon='.$this->addslashes($this->insertvon).','.
			       ' updateamum=now(),'.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
			       ' geschlecht='.$this->addslashes($this->geschlecht).','.
			       ' geburtsnation='.$this->addslashes($this->geburtsnation).','.
			       ' staatsbuergerschaft='.$this->addslashes($this->staatsbuergerschaft).','.
			       ' ext_id='.$this->addslashes($this->ext_id).','.
			       ' kurzbeschreibung='.$this->addslashes($this->kurzbeschreibung).
			       ' WHERE person_id='.$this->person_id.';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
				if($this->db_query($qry))
				{
					if($row=$this->db_fetch_object())
						$this->person_id=$row->id;
					else
					{
						$this->errormsg = "Sequence konnte nicht ausgelesen werden\n";
						return false;
					}
				}
				else 
				{
					$this->errormsg = "Fehler beim Auslesen der Sequence";
					return false;
				}
			}
			//Log schreiben
			return true;

		}
		else
		{
			$this->errormsg = "Fehler beim Speichern des Person-Datensatzes:".$this->db_last_error();
			return false;
		}
	}
	
	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param $filter String mit Vorname oder Nachname
	 * @param $order Sortierkriterium
	 * @return array mit LPersonen oder false=fehler
	 */
	public function getTab($filter, $order='person_id')
	{
		$sql_query = "SELECT * FROM public.tbl_person WHERE true ";
		
		if($filter!='')
		{
			$sql_query.=" AND 	nachname ~* '".addslashes($filter)."' OR 
								vorname ~* '".addslashes($filter)."' OR
								(nachname || ' ' || vorname) ~* '".addslashes($filter)."' OR
								(vorname || ' ' || nachname) ~* '".addslashes($filter)."'";
		}

		$sql_query .= " ORDER BY $order";
		if($filter=='')
		   $sql_query .= " LIMIT 30";
		
		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$l = new person();
				$l->person_id = $row->person_id;
				$l->staatsbuergerschaft = $row->staatsbuergerschaft;
				$l->geburtsnation = $row->geburtsnation;
				$l->sprache = $row->sprache;
				$l->anrede = $row->anrede;
				$l->titelpost = $row->titelpost;
				$l->titelpre = $row->titelpre;
				$l->nachname = $row->nachname;
				$l->vorname = $row->vorname;
				$l->vornamen = $row->vornamen;
				$l->gebdatum = $row->gebdatum;
				$l->gebort = $row->gebort;
				$l->gebzeit = $row->gebzeit;
				$l->foto = $row->foto;
				$l->anmerkungen = $row->anmerkung;
				$l->homepage = $row->homepage;
				$l->svnr = $row->svnr;
				$l->ersatzkennzeichen = $row->ersatzkennzeichen;
				$l->familienstand = $row->familienstand;
				$l->geschlecht = $row->geschlecht;
				$l->anzahlkinder = $row->anzahlkinder;
				$l->aktiv = $row->aktiv;
				$l->updateamum = $row->updateamum;
				$l->updatevon = $row->updatevon;
				$l->insertamum = $row->insertamum;
				$l->insertvon = $row->insertvon;
				$l->ext_id = $row->ext_id;
				$l->kurzbeschreibung = $row->kurzbeschreibung;
				$this->personen[]=$l;
			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		return true;
	}

}
?>