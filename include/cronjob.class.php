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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane@technikum-wien.at>.
 */
/**
 * Klasse Cronjob
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/datum.class.php');
require_once(dirname(__FILE__).'/functions.inc.php');

class cronjob extends basis_db
{
	public $new;
	public $result = array();
	public $output = array();

	//Tabellenspalten
	public $cronjob_id;
	public $server_kurzbz;
	public $titel;
	public $beschreibung;
	public $file;
	public $last_execute;
	public $aktiv=true;
	public $running=false;
	public $jahr;
	public $monat;
	public $tag;
	public $wochentag;
	public $stunde;
	public $minute;
	public $standalone=false;
	public $reihenfolge;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $variablen;

	/**
	 * Konstruktor
	 * @param $cronjob_id ID des Cronjobs der geladen werden soll (Default=null)
	 */
	public function __construct($cronjob_id=null)
	{
		parent::__construct();

		if(!is_null($cronjob_id))
			$this->load($cronjob_id);
	}

	/**
	 * Laedt einen Cronjob mit der ID $cronjob_id
	 * @param  $cronjob_id ID des zu ladenden Cronjobs
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($cronjob_id)
	{
		if(!is_numeric($cronjob_id))
		{
			$this->errormsg = 'id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM system.tbl_cronjob WHERE cronjob_id=".$this->db_add_param($cronjob_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->cronjob_id = $row->cronjob_id;
				$this->server_kurzbz = $row->server_kurzbz;
				$this->titel = $row->titel;
				$this->beschreibung = $row->beschreibung;
				$this->file = $row->file;
				$this->last_execute = $row->last_execute;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->running = $this->db_parse_bool($row->running);
				$this->jahr = $row->jahr;
				$this->monat = $row->monat;
				$this->tag = $row->tag;
				$this->wochentag = $row->wochentag;
				$this->stunde = $row->stunde;
				$this->minute = $row->minute;
				$this->standalone = $this->db_parse_bool($row->standalone);
				$this->reihenfolge = $row->reihenfolge;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->variablen = $row->variablen;
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
			$this->errormsg = 'Fehler beim Laden der Daten';
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
		if(mb_strlen($this->titel)>64)
		{
			$this->errormsg = 'Titel darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->jahr)>6)
		{
			$this->errormsg = 'Jahr darf nicht laenger als 6 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->monat)>4)
		{
			$this->errormsg = 'Monat darf nicht laenger als 4 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->tag)>4)
		{
			$this->errormsg = 'Tag darf nicht laenger als 4 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->stunde)>4)
		{
			$this->errormsg = 'Stunde darf nicht laenger als 4 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->minute)>4)
		{
			$this->errormsg = 'Stunde darf nicht laenger als 4 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $cronjob_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			if($this->last_execute=='')
				$this->last_execute = date('Y-m-d H:i:s');

			//Neuen Datensatz einfuegen

			$qry = 'BEGIN;INSERT INTO system.tbl_cronjob (server_kurzbz, titel, beschreibung, file, last_execute, aktiv,
					running, jahr, monat, tag, wochentag, stunde, minute, standalone, reihenfolge, updateamum, updatevon,
					insertamum, insertvon, variablen) VALUES('.
			       $this->db_add_param($this->server_kurzbz).', '.
			       $this->db_add_param($this->titel).', '.
			       $this->db_add_param($this->beschreibung).', '.
			       $this->db_add_param($this->file).', '.
			       $this->db_add_param($this->last_execute).', '.
			       $this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
			       $this->db_add_param($this->running, FHC_BOOLEAN).', '.
			       $this->db_add_param($this->jahr).', '.
			       $this->db_add_param($this->monat).', '.
			       $this->db_add_param($this->tag).', '.
			       $this->db_add_param($this->wochentag).', '.
			       $this->db_add_param($this->stunde).', '.
			       $this->db_add_param($this->minute).', '.
				   $this->db_add_param($this->standalone, FHC_BOOLEAN).', '.
			       $this->db_add_param($this->reihenfolge).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).','.
			       $this->db_add_param($this->variablen).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob id eine gueltige Zahl ist
			if(!is_numeric($this->cronjob_id))
			{
				$this->errormsg = 'cronjob_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE system.tbl_cronjob SET '.
			'server_kurzbz='.$this->db_add_param($this->server_kurzbz).', '.
			'titel='.$this->db_add_param($this->titel).', '.
 			'beschreibung='.$this->db_add_param($this->beschreibung).', '.
 			'file='.$this->db_add_param($this->file).', '.
 			'last_execute='.$this->db_add_param($this->last_execute).', '.
 			'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
 			'running='.$this->db_add_param($this->running, FHC_BOOLEAN).', '.
 			'jahr='.$this->db_add_param($this->jahr).', '.
 			'monat='.$this->db_add_param($this->monat).', '.
 			'tag='.$this->db_add_param($this->tag).', '.
 			'wochentag='.$this->db_add_param($this->wochentag).', '.
 			'stunde='.$this->db_add_param($this->stunde).', '.
 			'minute='.$this->db_add_param($this->minute).', '.
 			'standalone='.$this->db_add_param($this->standalone, FHC_BOOLEAN).', '.
 			'reihenfolge='.$this->db_add_param($this->reihenfolge).', '.
 			'variablen='.$this->db_add_param($this->variablen).','.
 			'updateamum='.$this->db_add_param($this->updateamum).','.
 			'updatevon='.$this->db_add_param($this->updatevon).' '.
 			'WHERE cronjob_id='.$this->db_add_param($this->cronjob_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('system.tbl_cronjob_cronjob_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->cronjob_id = $row->id;
						$this->db_query('COMMIT');
						return true;
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $cronjob_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($cronjob_id)
	{
		if(!is_numeric($cronjob_id))
		{
			$this->errormsg = 'Id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM system.tbl_cronjob WHERE cronjob_id=".$this->db_add_param($cronjob_id, FHC_INTEGER);

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert alle Cronjobs
	 * @param $server
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($server=null, $order=null, $aktiv=null)
	{
		$qry = "SELECT * FROM system.tbl_cronjob WHERE 1=1";
		if($server!=null)
			$qry.=" AND server_kurzbz=".$this->db_add_param($server);
		if ($aktiv)
			$qry.=' AND aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$obj = new cronjob();

			$obj->cronjob_id = $row->cronjob_id;
			$obj->server_kurzbz = $row->server_kurzbz;
			$obj->titel = $row->titel;
			$obj->beschreibung = $row->beschreibung;
			$obj->file = $row->file;
			$obj->last_execute = $row->last_execute;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->running = $this->db_parse_bool($row->running);
			$obj->jahr = $row->jahr;
			$obj->monat = $row->monat;
			$obj->tag = $row->tag;
			$obj->wochentag = $row->wochentag;
			$obj->stunde = $row->stunde;
			$obj->minute = $row->minute;
			$obj->standalone = $this->db_parse_bool($row->standalone);
			$obj->reihenfolge = $row->reihenfolge;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->variablen = $row->variablen;

			$this->result[] = $obj;
		}
		return true;
	}

	/**
	 * Startet einen geladenen Cronjob
	 *
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function execute()
	{
		$return = true;
		if($this->running)
		{
			$this->errormsg = 'Job kann nicht ausgefuehrt werden, da er bereits laeuft';
			return false;
		}
		if($this->standalone && $this->isJobRunning())
		{
			$this->errormsg = 'Job kann nicht ausgefuehrt werden, da noch ein anderer Job laeuft';
			return false;
		}

		if($this->server_kurzbz!=SERVER_NAME)
		{
			$this->errormsg = 'Fehler: Dieses Script kann nur am Server '.$this->server_kurzbz.' gestartet werden. (aktueller Server laut config: '.SERVER_NAME.')';
			return false;
		}

		$this->running = true;
		if(!$this->save())
			return false;

		unset($this->output);

		/**
		 * If CI cronjobs are used, the parameters needs to be handled separately otherwise the
		 * paramters are recognized as part of the original path if they contain slashes
		 *
		 * /var/www/index.ci.php jobs/foo method
		 */
		if(mb_strpos($this->file,' ') !== false)
		{
			$path = dirname(mb_substr($this->file,0, mb_strpos($this->file,' ')));
			$file = basename(mb_substr($this->file,0, mb_strpos($this->file,' ')));
			$parameter = mb_substr($this->file, mb_strpos($this->file,' '));
		}
		else
		{
			$path = dirname($this->file);
			$file = basename($this->file);
			$parameter = ' id='.$this->cronjob_id;
		}

		$file .= $parameter;
		if(chdir($path))
		{
			exec("php $file", $this->output);
			//echo "Execute: php $file";
			$this->last_execute = date('Y-m-d H:i:s');
		}
		else
		{
			$this->errormsg = 'Fehler: Falscher Verzeichnisname';
			$return = false;
		}

		$this->running = false;

		if(!$this->save())
			return false;

		return $return;
	}

	/**
	 * Startet einen geladenen Cronjob mit Initialisierungsparameter
	 * Der Job setzt dann die Standardwerte fuer die Variablen
	 *
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function init()
	{
		$return = true;

		unset($this->output);
		$path = dirname($this->file);
		$file = basename($this->file);
		$file .= ' -i';
		$file .= ' id='.$this->cronjob_id;
		if(chdir($path))
		{
			exec("php $file", $this->output);
			//echo "Execute: php $file";
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler: Falscher Verzeichnisname';
			return false;
		}
	}

	/**
	 * Prueft ob zur Zeit ein Cronjob laeuft
	 *
	 * @return boolean
	 */
	public function isJobRunning()
	{
		$qry = 'SELECT count(*) as anzahl FROM system.tbl_cronjob WHERE running=true';

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				if($row->anzahl>0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der Daten';
			return false;
		}
	}

	/**
	 * Parst die Schrittweite aus einem Feld
	 * Wenn eine fixe Zeitangabe enthalten ist, wird false zurueckgeliefert
	 *
	 * @param $value
	 * @return Schrittweite oder false wenn fixe Zeitangabe
	 */
	public function parseSchrittweite($value)
	{
		$matches='';
		//Pruefen ob der Wert im Format "*/<Zahl>" ist
		if(preg_match('/^\*\/(?P<value>\d+)/',$value, $matches))
			return $matches['value'];
		else
			return false;
	}

	/**
	 * Prueft, ob der Wert ein Fixdatum ist
	 *
	 * @param $value
	 * @return boolean
	 */
	public function isFixDatum($value)
	{
		if($value=='')
			return false;
		if($this->parseSchrittweite($value))
			return false;

		return true;
	}

	/**
	 * Liefert die naechste Ausfuehrungszeit des aktuell geladenen Cronjobs
	 *
	 * @return timestamp des naechsten Starts
	 */
	public function getNextExecutionTime()
	{
		$datum = new datum();
		$last_execute = $datum->mktime_fromtimestamp($this->last_execute);
		$executiontime = $last_execute;
		$jahr = date('Y', $last_execute);
		$monat = date('m', $last_execute);
		$tag = date('d', $last_execute);
		$stunde = date('H', $last_execute);
		$stunde_last = date('H', $last_execute);
		$minute = date('i', $last_execute);

		// wenn ein wochentag gewaehlt wird, dann wird jahr, monat und tag
		// nicht beruecksichtigt
		if($this->wochentag!='')
		{
			$stamp = jump_weekday($last_execute, $this->wochentag);
			$jahr = date('Y',$stamp);
			$monat = date('m',$stamp);
			$tag = date('d',$stamp);
		}
		else
		{
			//jahr
			if(!$jahr_schritt = $this->parseSchrittweite($this->jahr))
				$jahr = ($this->jahr!=''?$this->jahr:$jahr);
			else
				$jahr+= $jahr_schritt;

			//monat
			if(!$monat_schritt = $this->parseSchrittweite($this->monat))
			{
				if($this->monat<$monat && $this->monat!='' && !$this->isFixDatum($this->jahr))
				{
					if(!$this->isFixDatum($this->jahr))
					{
						$jahr++;
					}
				}

				$monat = ($this->monat!=''?$this->monat:$monat);

			}
			else
				$monat+= $monat_schritt;

			//tag
			if(!$tag_schritt = $this->parseSchrittweite($this->tag))
			{
				if($this->tag<$tag && $this->tag!='')
				{
					if(!$this->isFixDatum($this->monat))
					{
						$monat++;
					}
					elseif(!$this->isFixDatum($this->jahr))
					{
						$jahr++;
					}
				}
				$tag = ($this->tag!=''?$this->tag:$tag);
			}
			else
				$tag+= $tag_schritt;
		}

		//Stunde
		if(!$stunde_schritt = $this->parseSchrittweite($this->stunde))
		{
			if($this->stunde<$stunde && $this->stunde!='')
			{
				if(!$this->isFixDatum($this->tag))
				{
					$tag++;
				}
				elseif(!$this->isFixDatum($this->monat))
				{
					$monat++;
				}
				elseif(!$this->isFixDatum($this->jahr))
				{
					$jahr++;
				}
			}
			$stunde = ($this->stunde!=''?$this->stunde:$stunde);
		}
		else
			$stunde+= $stunde_schritt;

		//Minute
		if(!$minute_schritt = $this->parseSchrittweite($this->minute))
		{
			if($this->stunde<=$stunde_last)
			{
				if($this->minute<=$minute && $this->minute!='')
				{
					if(!$this->isFixDatum($this->stunde))
					{
						$stunde++;
					}
					elseif(!$this->isFixDatum($this->tag))
					{
						$tag++;
					}
					elseif(!$this->isFixDatum($this->monat))
					{
						$monat++;
					}
					elseif(!$this->isFixDatum($this->jahr))
					{
						$jahr++;
					}
				}
			}
			$minute = ($this->minute!=''?$this->minute:$minute);
		}
		else
			$minute+= $minute_schritt;

		$next = mktime($stunde, $minute, 0, $monat, $tag, $jahr);

		//Cronjobs die nicht mehr ausgefuehrt werden (Datum vor der letzten Ausfuehrung)
		if($next<$last_execute)
			$next=false;
		return $next;
	}

	/**
	 * Parst die Cronjob ID aus den Kommandozeilenparametern
	 *
	 * @return cronjob_id wenn ok, sonst false
	 */
	public function getCronjobID()
	{
		foreach ($_SERVER['argv'] as $row)
		{
			if(strstr($row,'id='))
			{
				return substr($row,strlen('id='));
			}
		}

		return false;
	}

	/**
	 * Prueft ob der Script-Aufruf ein Inistialisierungsaufruf ist
	 *
	 * @return boolean
	 */
	public function isInitialCall()
	{
		foreach ($_SERVER['argv'] as $row)
		{
			if($row=='-i')
			{
				return true;
			}
		}

		return false;
	}
}
?>
