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

class person
{
	var $conn;     			// resource DB-Handle
	var $errormsg; 		// string
	var $new;      			// boolean
	var $personen = array(); 	// person Objekt
	var $done=false;		// boolean

	//Tabellenspalten
	var $person_id;        		// integer
	var $sprache;			// varchar(16)
	var $anrede;			// varchar(16)
	var $titelpost;         		// varchar(32)
	var $titelpre;          		// varchar(64)
	var $nachname;          	// varchar(64)
	var $vorname;           	// varchar(32)
	var $vornamen;          	// varchar(128)
	var $gebdatum;          	// date
	var $gebort;            		// varchar(128)
	var $gebzeit;           		// time
	var $foto;              		// text
	var $anmerkungen;       	// varchar(256)
	var $homepage;          	// varchar(256)
	var $svnr;			// char(10)
	var $ersatzkennzeichen; 	// char(10)
	var $familienstand;     	// char(1)
	var $anzahlkinder;      	// smalint
	var $aktiv;             		// boolean
	var $insertamum;        	// timestamp
	var $insertvon;         		// varchar(16)
	var $updateamum;        	// timestamp
	var $updatevon;         	// varchar(16)
	var $geschlecht;		// varchar(1)
	var $staatsbuergerschaft;	// varchar(3)
	var $geburtsnation;		// varchar(3);
	var $ext_id;            		// bigint

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Person
	// * @param $conn        	Datenbank-Connection
	// *        $person_id      Person die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function person($conn, $person_id=null, $unicode=false)
	{
		$this->conn = $conn;
		if(!$unicode==null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden\n";
				return false;
			}
		}

		if($person_id != null)
			$this->load($person_id);
	}

	// *********************************************************
	// * Laedt Person mit der uebergebenen ID
	// * @param $person_id ID der Person die geladen werden soll
	// *********************************************************
	function load($person_id)
	{
		//person_id auf gueltigkeit pruefen
		if(is_numeric($person_id) && $person_id!='')
		{
			$qry = "SELECT person_id, sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
				gebdatum, gebort, gebzeit, foto, anmerkung, homepage, svnr, ersatzkennzeichen,
				familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon, ext_id,
				geschlecht, staatsbuergerschaft, geburtsnation
				FROM public.tbl_person WHERE person_id='$person_id'";

			if(!$result=pg_query($this->conn,$qry))
			{
				$this->errormsg = "Fehler beim lesen der Personendaten\n";
				return false;
			}

			if($row = pg_fetch_object($result))
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
	function validate()
	{
		if(utf8_strlen($this->sprache)>16)
		{
			$this->errormsg = "*****\nSprache darf nicht laenger als 16 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->anrede)>16)
		{
			$this->errormsg = "*****\nAnrede darf nicht laenger als 16 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->titelpost)>32)
		{
			$this->errormsg = "*****\nTitelpost darf nicht laenger als 32 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->titelpre)>64)
		{
			$this->errormsg = "*****\nTitelpre darf nicht laenger als 64 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->nachname)>64)
		{
			$this->errormsg = "*****\nNachname darf nicht laenger als 64 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if($this->nachname=='' || is_null($this->nachname))
		{
			$this->errormsg = "*****\nNachname muss eingegeben werden: ".$this->ext_id.", ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}

		if(utf8_strlen($this->vorname)>32)
		{
			$this->errormsg = "*****\nVorname darf nicht laenger als 32 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->vornamen)>128)
		{
			$this->errormsg = "*****\nVornamen darf nicht laenger als 128 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
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
			$this->errormsg = "*****\nGeburtsort darf nicht laenger als 128 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}

		if(utf8_strlen($this->homepage)>256)
		{
			$this->errormsg = "*****\nHomepage darf nicht laenger als 256 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->svnr)>10)
		{
			$this->errormsg = "*****\nSVNR darf nicht laenger als 10 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
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
			if($result = pg_query($this->conn, $qry))
			{
				if($row = pg_fetch_object($result))
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
			$this->errormsg = "*****\nErsatzkennzeichen darf nicht laenger als 10 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->familienstand)>1)
		{
			$this->errormsg = "*****\nFamilienstand ist ungueltig: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if($this->anzahlkinder!='' && !is_numeric($this->anzahlkinder))
		{
			$this->errormsg = "*****\nAnzahl der Kinder ist ungueltig: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = "*****\nAktiv ist ungueltig: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->insertvon)>16)
		{
			$this->errormsg = "*****\nInsertvon darf nicht laenger als 16 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->updatevon)>16)
		{
			$this->errormsg = "*****\nUpdatevon darf nicht laenger als 16 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if($this->ext_id!='' && !is_numeric($this->ext_id))
		{
			$this->errormsg = "*****\nExt_ID ist keine gueltige Zahl: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->geschlecht)>1)
		{
			$this->errormsg = "*****\ngeschlecht darf nicht laenger als 1 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->geburtsnation)>3)
		{
			$this->errormsg = "*****\nGeburtsnation darf nicht laenger als 3 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if(utf8_strlen($this->staatsbuergerschaft)>3)
		{
			$this->errormsg = "*****\nStaatsbuergerschaft darf nicht laenger als 3 Zeichen sein: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		if($this->geschlecht!='m' && $this->geschlecht!='w')
		{
			$this->errormsg = "*****\nGeschlecht mu� entweder w oder m sein!: ".$this->nachname.", ".$this->vorname."\n*****\n";
			return false;
		}
		
		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if($this->svnr!='' && $this->gebdatum!='')
		{
			if(ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})",$this->gebdatum, $regs))
			{
				$day = sprintf('%02s',$regs[1]);
				$month = sprintf('%02s',$regs[2]);
				$year = substr($regs[3],2,2);
			}
			elseif(ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->gebdatum, $regs))
			{
				$day = sprintf('%02s',$regs[3]);
				$month = sprintf('%02s',$regs[2]);
				$year = substr($regs[1],2,2);
			}
			else 
			{
				$this->errormsg = 'Format des Geburtsdatums ist ungueltig';
				return false;
			}
			
			$day_svnr = substr($this->svnr, 4, 2);
			$month_svnr = substr($this->svnr, 6, 2);
			$year_svnr = substr($this->svnr, 8, 2);
		
			if($day_svnr!=$day || $month_svnr!=$month || $year_svnr!=$year)
			{
				$this->errormsg = 'SVNR und Geburtsdatum passen nicht zusammen';
				return false;
			}
		}

		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert die Personendaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $person_id upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!person::validate())
			return false;

		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
			                    gebdatum, gebort, gebzeit, foto, anmerkung, homepage, svnr, ersatzkennzeichen,
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
			                    geschlecht, geburtsnation, staatsbuergerschaft, ext_id)
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
				        $this->addslashes($this->ext_id).');';
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
			       ' ext_id='.$this->addslashes($this->ext_id).
			       ' WHERE person_id='.$this->person_id.';';
		}

		if(pg_query($this->conn,$qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
				if($row=pg_fetch_object(pg_query($this->conn,$qry)))
					$this->person_id=$row->id;
				else
				{
					$this->errormsg = "Sequence konnte nicht ausgelesen werden\n";
					return false;
				}
			}
			//Log schreiben
			return true;

		}
		else
		{
			$this->errormsg = "Fehler beim Speichern des Person-Datensatzes:".pg_errormessage($this->conn);
			return false;
		}
	}
	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param 	$nn Nachname
	 *		$vn Vorname
	 *		$order Sortierkriterium
	 * @return array mit LPersonen oder false=fehler
	 */
	function getTab($filter, $order='person_id')
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
		
		if($result=pg_query($this->conn,$sql_query))
		{
			while($row=pg_fetch_object($result))
			{
				$l = new person($this->conn);
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
				$this->personen[]=$l;
			}
		}
		else
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		return true;
	}

}
?>