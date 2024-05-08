<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Verwaltet die Vorlagen fuer die Dokumentenerstellung
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/organisationseinheit.class.php');

class vorlage extends basis_db
{
	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $errormsg;
	public $new;

	//Tabellenspalten
	public $vorlage_kurzbz;					// varchar(16)
	public $studiengang_kz;	 				// integer
	public $version;						// smallint
	public $text;							// text
	public $mimetype;						// varchar(64)
	public $bezeichnung;					// varchar(64)
	public $anmerkung;						// text
	public $style;							// text
	public $berechtigung;					// varchar(32)[]
	public $oe_kurzbz;						// varchar(32)
	public $vorlagestudiengang_id;			// bigint
	public $anmerkung_vorlagestudiengang;	// text
	public $aktiv;							// boolean
	public $sprache;                        // varchar(16)
	public $subject;                        // text
	public $orgform_kurzbz;                 // varchar(3)

	public $archivierbar = false;
	public $signierbar = false;
	public $stud_selfservice = false;
	public $dokument_kurzbz;
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
	}

	/**
	 * Laedt eine Vorlage
	 * @param $vorlage_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadVorlage($vorlage_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_vorlage WHERE vorlage_kurzbz=".$this->db_add_param($vorlage_kurzbz);
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->vorlage_kurzbz = $row->vorlage_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->anmerkung = $row->anmerkung;
				$this->mimetype = $row->mimetype;
				$this->signierbar = $this->db_parse_bool($row->signierbar);
				$this->archivierbar = $this->db_parse_bool($row->archivierbar);
				$this->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
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
	 * Speichert eine Vorlage
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveVorlage($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		if($new)
		{
			$qry = "INSERT INTO public.tbl_vorlage(vorlage_kurzbz, bezeichnung, anmerkung, mimetype,
					archivierbar, signierbar, stud_selfservice, dokument_kurzbz, insertamum, insertvon, updateamum, updatevon) VALUES(".
					$this->db_add_param($this->vorlage_kurzbz).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->anmerkung).','.
					$this->db_add_param($this->mimetype).','.
					$this->db_add_param($this->archivierbar, FHC_BOOLEAN).','.
					$this->db_add_param($this->signierbar, FHC_BOOLEAN).','.
					$this->db_add_param($this->stud_selfservice, FHC_BOOLEAN).','.
					$this->db_add_param($this->dokument_kurzbz).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_vorlage
					SET 	bezeichnung='.$this->db_add_param($this->bezeichnung).',
							anmerkung='.$this->db_add_param($this->anmerkung).',
							mimetype='.$this->db_add_param($this->mimetype).',
							archivierbar='.$this->db_add_param($this->archivierbar, FHC_BOOLEAN).',
							signierbar='.$this->db_add_param($this->signierbar, FHC_BOOLEAN).',
							stud_selfservice='.$this->db_add_param($this->stud_selfservice, FHC_BOOLEAN).',
							dokument_kurzbz='.$this->db_add_param($this->dokument_kurzbz).',
							updateamum='.$this->db_add_param($this->updateamum).',
							updatevon='.$this->db_add_param($this->updatevon).'
					WHERE vorlage_kurzbz='.$this->db_add_param($this->vorlage_kurzbz).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern: '.$this->db_last_error();
			return false;
		}
	}

	/**
	 * Liefert alle Vorlagen
	 * @param $order Sortierreihenfolge. Default:vorlage_kurzbz
	 */
	public function getAllVorlagen($order='vorlage_kurzbz')
	{
		$qry ="SELECT * FROM public.tbl_vorlage ORDER BY ".$order.";";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new vorlage();
				$obj->vorlage_kurzbz = $row->vorlage_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->anmerkung = $row->anmerkung;
				$obj->mimetype = $row->mimetype;
				$obj->signierbar = $this->db_parse_bool($row->signierbar);
				$obj->archivierbar = $this->db_parse_bool($row->archivierbar);
				$obj->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[]= $obj;
			}
		}
		else
			return false;
	}

	/**
	 * Liefert alle Vorlagen die archiviert werden koennen
	 */
	public function getVorlagenArchiv()
	{
		$qry ="SELECT * FROM public.tbl_vorlage WHERE archivierbar=true ORDER BY bezeichnung";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new vorlage();
				$obj->vorlage_kurzbz = $row->vorlage_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->anmerkung = $row->anmerkung;
				$obj->mimetype = $row->mimetype;
				$obj->archivierbar = $this->db_parse_bool($row->archivierbar);
				$obj->signierbar = $this->db_parse_bool($row->signierbar);
				$obj->stud_selfservice = $this->db_parse_bool($row->stud_selfservice);
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[]= $obj;
			}
		}
		else
			return false;
	}

	/**
	 * Laedt die Vorlage zu einer OE
	 * @param $vorlage_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadVorlageOE($vorlagestudiengang_id)
	{
		$qry = "SELECT * FROM public.tbl_vorlagestudiengang WHERE vorlagestudiengang_id=".$this->db_add_param($vorlagestudiengang_id);
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->vorlagestudiengang_id = $row->vorlagestudiengang_id;
				$this->vorlage_kurzbz = $row->vorlage_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->version = $row->version;
				$this->text = $row->text;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->style = $row->style;
				$this->berechtigung = $row->berechtigung;
				$this->anmerkung_vorlagestudiengang = $row->anmerkung_vorlagestudiengang;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->sprache = $row->sprache;
				$this->subject = $row->subject;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
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
	 * Laedt alle Versionen einer Vorlage
	 * @param $vorlage_kurzbz
	 * @param $oe_kurzbz Optional. Gibt nur die Vorlagen zu dieser OE aus.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAllVersions($vorlage_kurzbz=null, $oe_kurzbz=null)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_vorlagestudiengang
				WHERE
					1=1";
		if(!is_null($vorlage_kurzbz) && $vorlage_kurzbz!='')
		{
			$qry.=" AND vorlage_kurzbz=".$this->db_add_param($vorlage_kurzbz);
		}
		if(!is_null($oe_kurzbz) && $oe_kurzbz!='')
		{
			$qry.=" AND oe_kurzbz=".$this->db_add_param($oe_kurzbz);
		}

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new vorlage();
				$obj->vorlagestudiengang_id = $row->vorlagestudiengang_id;
				$obj->vorlage_kurzbz = $row->vorlage_kurzbz;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->version = $row->version;
				$obj->text = $row->text;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->style = $row->style;
				$obj->berechtigung = $row->berechtigung;
				$obj->anmerkung_vorlagestudiengang = $row->anmerkung_vorlagestudiengang;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->sprache = $row->sprache;
				$obj->subject = $row->subject;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[]= $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert alle OEs, welche die $vorlage_kurzbz verwenden
	 * @param $vorlage_kurzbz Kurzbezeichnung der Vorlage
	 */
	public function getOEsFromVorlage($vorlage_kurzbz=null)
	{
		$qry ="SELECT DISTINCT
					tbl_organisationseinheit.*
				FROM
					public.tbl_vorlagestudiengang
				JOIN
					public.tbl_organisationseinheit USING (oe_kurzbz)
				WHERE
					vorlage_kurzbz=".$this->db_add_param($vorlage_kurzbz)."
				ORDER BY oe_kurzbz";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new vorlage();
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);

				$this->result[]= $obj;
			}
		}
		else
			return false;
	}

	/**
	 * Speichert die Vorlage zu einer OE
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveVorlageOE($new=null)
	{
		if($new == null)
			$new = $this->new;

		if($new)
		{
			$qry = "INSERT INTO public.tbl_vorlagestudiengang(vorlage_kurzbz,studiengang_kz,version,text,oe_kurzbz,style,berechtigung,anmerkung_vorlagestudiengang,aktiv,sprache,subject,orgform_kurzbz,insertamum,insertvon,updateamum,updatevon) VALUES(".
					$this->db_add_param($this->vorlage_kurzbz).','.
					$this->db_add_param($this->studiengang_kz).','.
					$this->db_add_param($this->version).','.
					$this->db_add_param($this->text).','.
					$this->db_add_param($this->oe_kurzbz).','.
					$this->db_add_param($this->style).','.
					$this->db_add_param($this->berechtigung).','.
					$this->db_add_param($this->anmerkung_vorlagestudiengang).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->sprache).','.
					$this->db_add_param($this->subject).','.
					$this->db_add_param($this->orgform_kurzbz).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_vorlagestudiengang
					SET 	vorlage_kurzbz='.$this->db_add_param($this->vorlage_kurzbz).',
							studiengang_kz='.$this->db_add_param($this->studiengang_kz).',
							version='.$this->db_add_param($this->version).',
							text='.$this->db_add_param($this->text).',
							oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).',
							style='.$this->db_add_param($this->style).',
							berechtigung='.$this->db_add_param($this->berechtigung).',
							aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).',
							anmerkung_vorlagestudiengang='.$this->db_add_param($this->anmerkung_vorlagestudiengang).',
							sprache='.$this->db_add_param($this->sprache).',
							subject='.$this->db_add_param($this->subject).',
							orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).',
							updateamum='.$this->db_add_param($this->updateamum).',
							updatevon='.$this->db_add_param($this->updatevon).'
					WHERE vorlagestudiengang_id='.$this->db_add_param($this->vorlagestudiengang_id).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern: '.$this->db_last_error();
			return false;
		}
	}

	/**
	 * Löscht die Vorlagestudiengagn
	 * @param type $vorlagestudiengang_id ID der Vorlage
	 */
	public function deleteVorlagestudiengang($vorlagestudiengang_id)
	{
		$qry = 'DELETE FROM public.tbl_vorlagestudiengang WHERE vorlagestudiengang_id='.$this->db_add_param($vorlagestudiengang_id).';';

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Vorlage konnte nicht gelöscht werden';
			return false;
		}
	}

	/**
	 * Liefert die hoechste Version der Vorlage
	 *
	 * @param $oe_kurzbz Organisationseinheit der Vorlage
	 * @param $vorlage_kurzbz Name der Vorlage
	 */
	public function getMaxVersion($oe_kurzbz, $vorlage_kurzbz)
	{
		$qry = "SELECT
					max(version) maxversion
				FROM
					public.tbl_vorlagestudiengang
				WHERE
					vorlage_kurzbz=".$this->db_add_param($vorlage_kurzbz)."
				AND
					oe_kurzbz=".$this->db_add_param($oe_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
				return $row->maxversion;
			else
				return 0;
		}
		else
		{
			$this->errormsg='Fehler beim Ermitteln der hoechsten Version';
			return false;
		}
	}


	/**
	 * Liefert die aktuelle Vorlage
	 *
	 *
	 * @param $oe_kurzbz Organisationseinheit der Vorlage
	 * 		Fuer Kompatibilitaetszwecke kann hier statt der oe_kurzbz auch die Studiengangskennzahl uebergeben werden.
	 *		In diesem Fall wird ein load der OE des Studiengangs durchgef�hrt und die entsprechende OE verwendet.
	 * @param string $vorlage_kurzbz Name der Vorlage
	 * @param integer $version optional kann die Versionsnummer der Vorlage uebergeben werden
	 * @param boolean $aktiv default:true. Optional. Wenn false: werden nur inaktive Vorlagen geladen. Wenn null, werden alle Vorlagen geladen.
	 * @return boolean
	 */
	public function getAktuelleVorlage($oe_kurzbz, $vorlage_kurzbz, $version=null, $aktiv=true)
	{
		$studiengang_kz='';
		if(is_numeric($oe_kurzbz))
		{
			$studiengang = new studiengang();
			$studiengang->load($oe_kurzbz);
			$oe_kurzbz=$studiengang->oe_kurzbz;
			//Durch diese Bedingung wird die Abfrage der studiengang_kz im folgenden Abschnitt hinfaellig.
		}

		if($studiengang_kz!='') // Es sollte aktuell keine Vorlage mehr ueber die Studiengang_kz aufgerufen werden, da hier kein Fallback der OE erfolgt. Fuer Testzwecke bleibt das noch bestehen. Kindlm 11.09.2015
		{
			$qry = "SELECT
						tbl_vorlagestudiengang.*, tbl_vorlage.mimetype, tbl_vorlage.bezeichnung
					FROM
						public.tbl_vorlagestudiengang
						JOIN public.tbl_vorlage USING(vorlage_kurzbz)
					WHERE
					(studiengang_kz=0 OR studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER).") AND
					vorlage_kurzbz=".$this->db_add_param($vorlage_kurzbz);
			if(!is_null($version) && $version!='')
			{
				$qry.=" AND version=".$this->db_add_param($version, FHC_INTEGER);
			}
			if(!is_null($aktiv) && $aktiv!='')
			{
				$qry.=" AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);
			}
			if($studiengang_kz<0) //Damit bei negativer studiengang_kz richtiges Ergebnis kommt
			{
				$qry .=" ORDER BY studiengang_kz ASC, version DESC LIMIT 1;";
			}
			else
			{
				$qry .=" ORDER BY studiengang_kz DESC, version DESC LIMIT 1;";
			}
		}
		else
		{
			$qry = "SELECT
						tbl_vorlagestudiengang.*, tbl_vorlage.mimetype, tbl_vorlage.bezeichnung
					FROM
						public.tbl_vorlagestudiengang
						JOIN public.tbl_vorlage USING(vorlage_kurzbz)
					WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz)."
						AND vorlage_kurzbz=".$this->db_add_param($vorlage_kurzbz);
			if(!is_null($version) && $version!='')
			{
				$qry.=" AND version=".$this->db_add_param($version, FHC_INTEGER);
			}
			if(!is_null($aktiv) && $aktiv!='')
			{
				$qry.=" AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);
			}
			$qry.=" ORDER BY version DESC LIMIT 1";
		}

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->vorlage_kurzbz = $row->vorlage_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->version = $row->version;
				$this->text = $row->text;
				$this->mimetype = $row->mimetype;
				$this->bezeichnung = $row->bezeichnung;
				$this->style = $row->style;
				$this->berechtigung = $this->db_parse_array($row->berechtigung);
				$this->anmerkung_vorlagestudiengang = $row->anmerkung_vorlagestudiengang;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->sprache = $row->sprache;
				$this->subject = $row->subject;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->vorlagestudiengang_id = $row->vorlagestudiengang_id;

				return true;
			}
			else
			{
				if($studiengang_kz!='')
				{
					$this->errormsg = 'Keine Vorlage gefunden';
					return false;
				}
				else
				{
					//Wenn keine Vorlage zu dieser Organisationseinheit gefunden wurde,
					//nachsehen ob fuer eine der uebergeordneten OEs eine Vorlage vorhanden ist.
					$oe = new organisationseinheit();
					$oe->load($oe_kurzbz);

					if($oe->oe_parent_kurzbz!='')
					{
						return $this->getAktuelleVorlage($oe->oe_parent_kurzbz, $vorlage_kurzbz, $version, $aktiv);
					}
					else
					{
						$this->errormsg = 'Keine Vorlage gefunden';
						return false;
					}
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Vorlage';
			return false;
		}
	}
}
?>
