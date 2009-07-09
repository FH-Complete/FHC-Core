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
/**
 * Klasse benutzerfunktion (FAS-Online)
 * @create 04-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');	
class benutzerfunktion extends basis_db
{

	var $new;     		// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var benutzerfunktion Objekt

	//Tabellenspalten
	var $benutzerfunktion_id;	// @var serial
	var $fachbereich_kurzbz;	// @var integer
	var $uid;				// @var varchar(16)
	var $studiengang_kz;	// @var integer
	var $funktion_kurzbz;	// @var varchar(16)
	var $updateamum;		// @var timestamp
	var $updatevon=0;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon=0;		// @var string
	var $ext_id;			// @var bigint
	var $semester;			// @var smallint


	// **********************************************************
	// * Konstruktor
	// * @param $conn Connection zur DB
	// *        $benutzerfunktion_id ID der zu ladenden Funktion
	// **********************************************************

	function __construct($benutzerfunktion_id=null)
	{
		parent::__construct();
		if($benutzerfunktion_id != null)
			$this->load($benutzerfunktion_id);
	}

	// *********************************************
	// * Laedt alle verfuegbaren Benutzerfunktionen
	// * @return true wenn ok, false im Fehlerfall
	// *********************************************
	function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_benutzerfunktion ORDER BY benutzerfunktion_id;';

		if(!$res = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object($res))
		{
			$pfunktion_obj = new benutzerfunktion();

			$pfunktion_obj->benutzerfunktion_id = $row->benutzerfunktion_id;
			$pfunktion_obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$pfunktion_obj->uid = $row->uid;
			$pfunktion_obj->studiengang_kz = $row->studiengang_kz;
			$pfunktion_obj->funktion_kurzbz = $row->funtion_kurzbz;
			$pfunktion_obj->insertamum = $row->insertamum;
			$pfunktion_obj->insertvon = $row->insertvon;
			$pfunktion_obj->updateamum = $row->updateamum;
			$pfunktion_obj->updatevon = $row->updatevon;
			$pfunktion_obj->semester = $row->semester;

			$this->result[] = $pfunktion_obj;
		}
		return true;
	}

	// *********************************
	// * Prueft ob der Benutzer $uid die
	// * Funktion $benutzerfunktion hat
	// *********************************
	function benutzerfunktion_exists($uid, $benutzerfunktion)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_benutzerfunktion WHERE uid='".addslashes($uid)."' AND funktion_kurzbz='".addslashes($benutzerfunktion)."'";

		if($row = $this->db_fetch_object($this->db_query($qry)))
		{
			if($row->anzahl>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Bentuzerfunktionen';
			return false;
		}
	}

	// *********************************
	// * Laedt eine BenutzerFunktion
	// * @param uid, funktion_kurzbz, studiengang_kz
	// * @return false wenn nicht vorhanden oder fehler
	// *         sonst true
	// *********************************
	function getBenutzerFunktion($uid, $funktion_kurzbz, $studiengang_kz)
	{
		$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE uid='".addslashes($uid)."' AND funktion_kurzbz='".addslashes($funktion_kurzbz)."' AND studiengang_kz='".addslashes($studiengang_kz)."'";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->benutzerfunktion_id = $row->benutzerfunktion_id;
				$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$this->uid = $row->uid;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->funktion_kurzbz = $row->funktion_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->semester = $row->semester;
				return true;
			}
			else
			{
				$this->errormsg = "Benutzerfunktion wurde nicht gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Bentuzerfunktionen';
			return false;
		}
	}
	
	// *********************************
	// * Laedt mehrere BenutzerFunktionen
	// * @param funktion_kurzbz, studiengang_kz, semester
	// * @return false wenn nicht vorhanden oder fehler
	// *         sonst true
	// *********************************
	function getBenutzerFunktionen($funktion_kurzbz, $studiengang_kz='', $semester='')
	{
		$qry = "SELECT * FROM public.tbl_benutzerfunktion 
				WHERE funktion_kurzbz='".addslashes($funktion_kurzbz)."'";

		if($studiengang_kz!='')
			$qry.=" AND studiengang_kz='".addslashes($studiengang_kz)."'";
		if($semester!='')
			$qry.=" AND semester='".addslashes($semester)."'";

		$qry.=" ORDER BY funktion_kurzbz, studiengang_kz, semester";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzerfunktion(null, null);
				
				$obj->benutzerfunktion_id = $row->benutzerfunktion_id;
				$obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$obj->uid = $row->uid;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->semester = $row->semester;
				
				$this->result[] = $obj;
				
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Bentuzerfunktionen';
			return false;
		}
	}

	// *********************************************************
	// * Laedt eine Benutzerfunktion
	// * @param $bnutzerfunktion_id ID der zu ladenden Funktion
	// * @return true wenn ok, false im Fehlerfall
	// *********************************************************
	function load($benutzerfunktion_id)
	{
		if($benutzerfunktion_id == '')
		{
			$this->errormsg = 'benutzerfunktion_id mu� eine g�ltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE benutzerfunktion_id = '$benutzerfunktion_id';";

		if(!$res = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=$this->db_fetch_object($res))
		{
			$this->benutzerfunktion_id = $row->benutzerfunktion_id;
			$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$this->uid = $row->uid;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->funktion_kurzbz = $row->funktion_kurzbz;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->semester = $row->semester;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	// ****************************************************************************
	// * Loescht einen Datensatz
	// * @param $fbenutzerfunktion_id id des Datensatzes der geloescht werden soll
	// * @return true wenn ok, false im Fehlerfall
	// ****************************************************************************
	function delete($benutzerfunktion_id)
	{
		$qry = "DELETE FROM public.tbl_benutzerfunktion WHERE benutzerfunktion_id='$benutzerfunktion_id'";
		if(!$this->db_query($qry))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		else
			return true;
	}
	
	// *******************************************
	// * Prueft die Gueltigkeit der Variablen
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	// *******************************************
	// * Speichert den aktuellen Datensatz
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function save($new=null)
	{
		//Gueltigkeit der Variablen pruefen
		//if(!$this->checkvars())
		//	return false;
		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			//Pruefen ob uid vorhanden
			$qry = "SELECT uid FROM public.tbl_benutzer WHERE uid = '$this->uid';";
			if(!$resx = $this->db_query($qry))
			{
				$this->errormsg = 'Fehler beim Laden des Datensatzes';
				return false;
			}
			else
			{
				if ($this->db_num_rows($resx)==0)
				{
					$this->errormsg = "uid <b>$this->uid</b> in Tabelle tbl_benutzer nicht gefunden!";
					return false;
				}
			}
			$qry = 'BEGIN;INSERT INTO public.tbl_benutzerfunktion (fachbereich_kurzbz, uid, studiengang_kz, funktion_kurzbz, insertamum, insertvon,
				updateamum, updatevon, semester) VALUES ('.
				$this->addslashes($this->fachbereich_kurzbz).', '.
				$this->addslashes($this->uid).', '.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->funktion_kurzbz).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).', '.
				$this->addslashes($this->semester).'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob benutzerfunktion_id eine gueltige Zahl ist
			if(!is_numeric($this->benutzerfunktion_id) || $this->benutzerfunktion_id == '')
			{
				$this->errormsg = 'benutzerfunktion_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE public.tbl_benutzerfunktion SET '.
				'benutzerfunktion_id='.$this->addslashes($this->benutzerfunktion_id).', '.
				'fachbereich_kurzbz='.$this->addslashes($this->fachbereich_kurzbz).', '.
				'uid='.$this->addslashes($this->uid).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
				'funktion_kurzbz='.$this->addslashes($this->funktion_kurzbz).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).',  '.
				'semester='.$this->addslashes($this->semester).'  '.
				'WHERE benutzerfunktion_id = '.$this->addslashes($this->benutzerfunktion_id).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence Auslesen
				$qry = "SELECT currval('public.tbl_benutzerfunktion_benutzerfunktion_id_seq') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->benutzerfunktion_id = $row->id;
						$this->db_query('COMMIT;');
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes - '.$this->db_last_error();
			return false;
		}
	}
}
?>