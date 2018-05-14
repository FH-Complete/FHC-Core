<?php

if (! defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Database Class
 *
 */

class DBTools extends FHC_Controller
{
	private $cli = false;
	/**
	 * Path to seed classes
	 *
	 * @var string
	 */
	protected $seed_path;

	/**
	 * Seed basename regex
	 *
	 * @var string
	 */
	protected $seed_regex = '/^\d{3}_(\w+)$/';

	/**
	 * Initialize DB-Tools Class
	 *
	 * @return	void
	 */
	public function __construct()
	{
		// An empty array as parameter will ensure that this controller is ONLY callable from command line
		parent::__construct(array());

		$this->seed_path = APPPATH.'seeds/';

		if ($this->input->is_cli_request())
		{
			$cli = true;

			$this->load->database('system'); //Use the system-Connection for DB-Manipulation
			$this->config->load('migration');
			$this->load->library('migration');

			// If not set, set it
			$this->seed_path !== '' OR $this->seed_path = APPPATH.'seeds/';
			// Add trailing slash if not set
			$this->seed_path = rtrim($this->seed_path, '/').'/';

			// Load seed language
			$this->lang->load('seed');

			// initiate faker
			$this->faker = \Faker\Factory::create();

		}
		else
		{
			$this->output->set_status_header(403, 'Migrations must be run from the CLI');
			echo "Migrations must be run from the CLI";
			exit;
		}
	}

	/**
	 * Main function index as help
	 *
	 * @return	void
	 */
    public function index()
	{
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.ci.php DBTools migrate [\"version_number\"]    Run migrations. (latest/current) ";
		$result .= "The version number is optional.\n";
        $result .= "php index.ci.php DBTools seed [\"file_name\"]              Run the specified seed (Name of Seed. expl: 'Organisation').\n";

        echo $result.PHP_EOL;
    }

	/**
	 * Migrate to latest or current version
	 *
	 * @param  string $version [optional] One of either "latest" or "current"
	 * @return	void
	 */
	public function migrate($version = 'latest')
	{
		echo 'DB-Migration';
		if ($version != 'latest' && $version != 'current')
		{
			$this->__failed('Migration version must be either latest or current');
		}
		elseif ($this->cli && !$this->migration->$version())
		{
			show_error($this->migration->error_string());
		}
		elseif (!$this->migration->$version())
		{
			$this->__failed();
		}

		$this->__succeeded();
	}


	/**
	 * Migrate to a specific version
	 *
	 * @return	void
	 */
	public function version()
	{
		if ($version == 'latest' || $version == 'current')
		{
			$this->index($version);
			exit;
		}

		if (!$this->migrate->version($version))
		{
			$this->__failed();
		}

		$this->__succeeded();
	}

	/**
	 * Roll-back to the last version before current
	 *
	 * @param  int $version	The migration to rollback to, defaults to previous
	 * @return	void
	 */
	public function rollback($version = null)
	{
		if (is_null($version))
		{
			$version = $this->__getVersion() ?: 1;
			$version--;
		}

		// Check it's definitely false, we could be rolling back to v0
		if (false === $this->migration->version($version))
		{
			$this->__failed();
		}

		$this->__succeeded('rolled back');
	}

	/**
	 * ROLLBACK ALL THE THINGS!
	 *
	 * @return	void
	 */
	public function uninstall()
	{
		$this->rollback(0);
	}

	/**
	 * Seeds DB with Testdata
	 *
	 * @param	string	$name Name of the seed file.
	 * @return	bool
	 */
	public function seed($name = null)
	{
		$seeds = $this->findSeeds();

		if (empty($seeds))
		{
			$this->_error_string = $this->lang->line('seed_none_found');
			return false;
		}

		$method = 'seed';
		$pending = array();


		foreach ($seeds as $number => $file)
		{
			include_once($file);
			$class = 'Seed_'.ucfirst(strtolower($this->_getSeedName(basename($file, '.php'))));

			// Validate the seed file structure
			if (! class_exists($class, false))
			{
				$this->_error_string = sprintf($this->lang->line('seed_class_doesnt_exist'), $class);
				return false;
			}
			// method_exists() returns true for non-public methods,
			// while is_callable() can't be used without instantiating.
			// Only get_class_methods() satisfies both conditions.
			elseif (! in_array($method, array_map('strtolower', get_class_methods($class))))
			{
				$this->_error_string = sprintf($this->lang->line('seed_missing_'.$method.'_method'), $class);
				return false;
			}

			$pending[$number] = array($class, $method);
		}

		// Now just run the necessary seeds
		foreach ($pending as $number => $seed)
		{
			if (is_null($name))
			{
				log_message('debug', 'Seeding '.$method);

				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
			elseif ($seed[0] == 'Seed_'.$name)
			{
				log_message('debug', 'Seeding '.$method);

				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
		}
	}

	/**
	 * Retrieves list of available seed files
	 *
	 * @return	array	list of seed file paths sorted by version
	 */
	public function findSeeds()
	{
		$seeds = array();

		// Load all *_*.php files in the seeds path
		foreach (glob($this->seed_path.'*_*.php') as $file)
		{
			$name = basename($file, '.php');

			// Filter out non-seed files
			if (preg_match($this->seed_regex, $name))
			{
				$number = $this->_getSeedNumber($name);

				// There cannot be duplicate seed numbers
				if (isset($seeds[$number]))
				{
					$this->_error_string = sprintf($this->lang->line('seed_multiple_version'), $number);
					show_error($this->_error_string);
				}

				$seeds[$number] = $file;
			}
		}

		ksort($seeds);
		return $seeds;
	}

/**
	 * Truncate DB from Testdata
	 *
	 * @param	string	$name Name of the seed file.
	 * @return	bool
	 */
	public function truncate($name)
	{
		$seeds = $this->findSeeds();

		if (empty($name))
		{
			$this->_error_string = $this->lang->line('seed_none_found');
			return false;
		}

		$method = 'truncate';
		$pending = array();


		foreach ($seeds as $number => $file)
		{
			include_once($file);
			$class = 'Seed_'.ucfirst(strtolower($this->_getSeedName(basename($file, '.php'))));

			// Validate the seed file structure
			if (! class_exists($class, false))
			{
				$this->_error_string = sprintf($this->lang->line('seed_class_doesnt_exist'), $class);
				return false;
			}
			// method_exists() returns true for non-public methods,
			// while is_callable() can't be used without instantiating.
			// Only get_class_methods() satisfies both conditions.
			elseif (! in_array($method, array_map('strtolower', get_class_methods($class))))
			{
				$this->_error_string = sprintf($this->lang->line('seed_missing_'.$method.'_method'), $class);
				return false;
			}

			$pending[$number] = array($class, $method);
		}

		// Now just run the necessary seeds
		foreach ($pending as $number => $seed)
		{
			if (is_null($name))
			{
				log_message('debug', 'Seeding '.$method);

				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
			elseif ($seed[0] == 'Seed_'.$name)
			{
				log_message('debug', 'Seeding '.$method);

				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
		}
	}
	/**
	 * Extracts the seed number from a filename
	 *
	 * @param	string	$seed Filename of the seed.
	 * @return	string	Numeric portion of a seed filename
	 */
	protected function _getSeedNumber($seed)
	{
		return sscanf($seed, '%[0-9]+', $number)
			? $number : '0';
	}
	/**
	 * Extracts the seed class name from a filename
	 *
	 * @param	string	$seed Filename of the seed.
	 * @return	string	text portion of a migration filename
	 */
	protected function _getSeedName($seed)
	{
		$parts = explode('_', $seed);
		array_shift($parts);
		return implode('_', $parts);
	}

	/**
	 * Yay, it worked! Tell the user.
	 *
	 * @param  string $task What did we just do? We...
	 * @return void
	 */
	private function __succeeded($task = 'migrated')
	{
		$version = $this->__getVersion();
		exit('Successfully '.$task.' to version '.$version);
	}

	/**
	 * Output an error message when it all goes tits up
	 *
	 * @param  string $message Error to output (default to CI's migration error)
	 * @return void
	 */
	private function __failed($message = null)
	{
		$message = $message ?: $this->migration->error_string();
		show_error($message);
	}

	/**
	 * Carbon copy of parent::__getVersion, but that's protected.
	 *
	 * @return int Currently installed migration number
	 */
	private function __getVersion()
	{
		$row = $this->db->get($this->config->item('migration_table'))->row();
		return $row ? $row->version : 0;
	}

	/**
	 * Check DB for different things like permissions or roles
	 *
	 * @param  string $action What to check.
	 * @return	void
	 */
	public function check($action = 'schema')
    {
		echo 'DB-Check';
	    switch ($action)
		{
			// **** Permission ****
			// ToDo: Check Persmissions in the bottom of this file
			case 'permissions':
				echo ' Permissions!';
				//$this->config->load('roles');
				foreach ($this->config->item('fhc_acl') as $b)
				{
					$qry = "SELECT * FROM system.tbl_berechtigung
							WHERE berechtigung_kurzbz='".$b."';";

					if($result = $this->db->query($qry))
					{
						if($result->num_rows($result)==0)
						{
							// Nicht vorhanden -> anlegen
							$qry_insert="INSERT INTO system.tbl_berechtigung (berechtigung_kurzbz) VALUES('".$b."');";

							if($this->db->query($qry_insert))
							{
								echo '<br>Recht '.$b.' <b>hinzugefügt</b>';
								$neue=true;
							}
							else
								echo '<br><span class="error">Fehler: Recht '.$b.' hinzufügen nicht möglich</span>';
						}
						else
							echo "- $b -";
					}
				}
				break;
			// **** Roles ****
			case 'roles':
				echo ' Roles!';
				$this->config->load('roles');
				foreach ($this->config->item('roles') as $role)
				{
					echo "\n\n".'Check role '.$role['rolle_kurzbz'];
					$qry = "SELECT * FROM system.tbl_rolle
							WHERE rolle_kurzbz='".$role['rolle_kurzbz']."';";

					if($result = $this->db->query($qry))
					{
						if($result->num_rows($result)==0)
						{
							// Nicht vorhanden -> anlegen
							$qry_insert="INSERT INTO system.tbl_rolle(rolle_kurzbz, beschreibung) VALUES ('".$role['rolle_kurzbz']."','".$role['rolle_kurzbz']."');";

							if($this->db->query($qry_insert))
							{
								echo "\nRolle ".$role['rolle_kurzbz'].' hinzugefügt';
								$neue=true;
							}
							else
							{
								echo "\nFehler: ".$role['rolle_kurzbz'].' Rolle hinzufügen nicht möglich';
								continue;
							}
						}
					}

					foreach ($role['berechtigung'] as $b)
					{
						$qry = "SELECT * FROM system.tbl_rolleberechtigung
								WHERE rolle_kurzbz='".$role['rolle_kurzbz']."'
								AND berechtigung_kurzbz='".$b."';";

						if($result = $this->db->query($qry))
						{
							if($result->num_rows($result)==0)
							{
								// Nicht vorhanden -> anlegen
								$qry_insert="INSERT INTO system.tbl_rolleberechtigung (rolle_kurzbz, berechtigung_kurzbz, art) VALUES ('".$role['rolle_kurzbz']."','".$b."', 'suid');";

								if($this->db->query($qry_insert))
								{
									echo "\n".$role['rolle_kurzbz'].' -> '.$b.' hinzugefügt';
									$neue=true;
								}
								else
									echo "\nFehler: ".$role['rolle_kurzbz'].' -> '.$b.' hinzufügen nicht möglich';
							}
							else
								echo "\n- $b -";
						}
					}
				}
				break;
			// **** Default ****
			default: echo ' what? roles or permisssions?';
			exit();
		}

		exit('Succesfully checked!');
	}

	/**
	 * Create User in DB
	 *
	 * @param  string $action What to check.
	 * @return	void
	 */
	public function createadminuser($uid, $person_id = 1)
	{
		echo 'Create User!';
		$qry = "SELECT * FROM public.tbl_benutzer
							WHERE uid='".$uid."';";
		if ($result = $this->db->query($qry))
		{
			if ($result->num_rows($result)==0)
			{
				// Nicht vorhanden -> anlegen
				$qry_insert="INSERT INTO public.tbl_benutzer (uid, person_id) VALUES('".$uid."', ".$person_id.");";
				if($this->db->query($qry_insert))
					echo '<br>User '.$uid.' <b>angelegt</b>';
				else
					echo '<br><span class="error">Fehler: User '.$uid.' anlegen nicht möglich!</span>';
				// Join Role Admin
				$qry_insert="INSERT INTO system.tbl_benutzerrolle (rolle_kurzbz, uid) VALUES('admin','".$uid."');";
				if($this->db->query($qry_insert))
					echo '<br>Rolle Admin für User '.$uid.' <b>hinzugefügt</b>';
				else
					echo '<br><span class="error">Rolle Admin hinzufügen für User '.$b.' hinzufügen nicht möglich</span>';
			}
		}

		exit('Succesfully created User!');
	}
}

/* Check also this permissions:
basis/fhausweis -> Verwaltungstools für FH Ausweis – Kartentausch, Bildpruefung, Druck hinzugefügt
buchung/typen -> Verwaltung von Buchungstypen hinzugefügt
buchung/mitarbeiter -> Verwaltung von Buchungen fuer Mitarbeiter hinzugefügt
inout/incoming -> Incomingverwaltung hinzugefügt
inout/outgoing -> Outgoingverwaltung hinzugefügt
inout/uebersicht -> Verbandsanzeige fuer Incoming/Outgoing im FAS hinzugefügt
lehre/lehrfach:begrenzt -> Lehrfachverwaltung - nur aktiv aenderbar, nur aktive LF werden angezeigt hinzugefügt
lehre/pruefungsanmeldungAdmin -> Erlaubt die Verwaltung der Prüfungsanmeldungen. hinzugefügt
lehre/pruefungsbeurteilung -> Erlaubt dem Benutzer Beurteilungen zu Prüfungen einzutragen. hinzugefügt
lehre/pruefungsbeurteilungAdmin -> Erlaubt dem Benutzer für alle Prüfungen Beurteilungen einzutragen. hinzugefügt
lehre/pruefungsterminAdmin -> Recht für jeden Lektor eine Prüfung anzulegen hinzugefügt
lehre/pruefungsfenster -> Erlaubt dem Benutzer Prüfungsfenster anzulegen. hinzugefügt
lv-plan/gruppenentfernen -> Erlaut das Entfernen von Gruppen aus LVPlan vom FAS aus hinzugefügt
lv-plan/lektorentfernen -> Erlaut das Entfernen von Lektoren aus LVPlan vom FAS aus hinzugefügt
mitarbeiter/bankdaten -> Bankdaten für Mitarbeiter und Studierende anzeigen hinzugefügt
mitarbeiter/personalnummer -> Editieren der Personalnummer im FAS hinzugefügt
mitarbeiter/urlaube -> Mit diesem Recht werden im CIS die Urlaube von allen Mitarbeiter sichtbar hinzugefügt
planner -> Planner Verwaltung hinzugefügt
reihungstest -> Recht für Anzeige des Reihungstests im Vilesci hinzugefügt
sdTools -> Recht für Anzeige der SD-Tools im Vilesci hinzugefügt
soap/lv -> Recht für LV Webservice hinzugefügt
soap/lvplan -> Recht für LV-Plan Webservice hinzugefügt
soap/mitarbeiter -> Recht für Mitarbeiter-Webservice hinzugefügt
soap/ort -> Recht für Ort Webservice hinzugefügt
soap/pruefungsfenster -> Recht für Pruefungsfenster Webservice hinzugefügt
soap/student -> Recht für Student Webservice hinzugefügt
soap/studienordnung -> Recht für Studienordnung Webservice hinzugefügt
soap/benutzer -> Berechtigung für Bentutzerabfrage Addon Kontoimport hinzugefügt
soap/buchungen -> Berechtigung für Buchungsabfrage Addon Kontoimport hinzugefügt
student/bankdaten -> Bankdaten des Studenten hinzugefügt
student/anrechnung -> Anrechnungen des Studenten hinzugefügt
student/anwesenheit -> Anwesenheiten im FAS hinzugefügt
system/developer -> Anzeige zusätzlicher Developerinfos hinzugefügt
system/loginasuser -> Berechtigung zum Einloggen als anderer User hinzugefügt
vertrag/mitarbeiter -> Verwalten von Vertraegen hinzugefügt
vertrag/typen -> Verwalten von Vertragstypen hinzugefügt
wawi/berichte -> Alle Berichte anzeigen hinzugefügt
wawi/delete_advanced -> Loeschen von freigegebenen Bestellungen hinzugefügt
Webservice Berechtigungen pruefen

soap/studienordnung/load_lva_oe->lehrveranstaltung hinzugefügt
soap/studienordnung/load->lehrveranstaltung hinzugefügt
soap/studienordnung/deleteStudienplanLehrveranstaltung->studienplan hinzugefügt
soap/studienordnung/containsLehrveranstaltung->studienplan hinzugefügt
soap/studienordnung/loadStudienplanLehrveranstaltung->studienplan hinzugefügt
soap/studienordnung/saveStudienplanLehrveranstaltung->studienplan hinzugefügt
soap/studienordnung/loadStudienordnung->studienordnung hinzugefügt
soap/studienordnung/delete->lvregel hinzugefügt
soap/studienordnung/save->lvregel hinzugefügt
soap/studienordnung/load->lvregel hinzugefügt
soap/studienordnung/loadLVRegelTypen->lvregel hinzugefügt
soap/studienordnung/load_lva->lehrveranstaltung hinzugefügt
soap/studienordnung/getAll->lehrtyp hinzugefügt
soap/studienordnung/getAll->organisationseinheit hinzugefügt
soap/studienordnung/getLVRegelTree->lvregel hinzugefügt
soap/studienordnung/save->studienplan hinzugefügt
soap/studienordnung/save->studienordnung hinzugefügt
soap/studienordnung/loadStudienplanSTO->studienplan hinzugefügt
soap/studienordnung/loadStudienordnungSTG->studienordnung hinzugefügt
soap/studienordnung/loadStudienordnungSTGInaktiv->studienordnung hinzugefügt
soap/studienordnung/loadStudienplan->studienplan hinzugefügt
soap/studienordnung/saveSemesterZuordnung->studienordnung hinzugefügt
soap/studienordnung/deleteSemesterZuordnung->studienordnung hinzugefügt
soap/studienordnung/getLVkompatibel->lehrveranstaltung hinzugefügt
soap/studienordnung/getLvTree->lehrveranstaltung hinzugefügt
soap/pruefungsfenster/getByStudiensemester->pruefungsfenster hinzugefügt
soap/studienordnung/exists->lvregel hinzugefügt
soap/studienordnung/saveSortierung->studienplan hinzugefügt
soap/benutzer/search->benutzer hinzugefügt
soap/buchungen/getBuchungen-> */
