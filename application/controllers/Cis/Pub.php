<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 *
 */
class Pub extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param string	$source [person|akte]
	 * @param integer	$id
	 * @return void
	 */
	public function bild($source, $id)
	{
		$this->load->model('person/Person_model', 'PersonModel');

		$person_id_user = '';
		$serverzugriff = false;

		// Wenn das Bild direkt aufgerufen wird, ist eine Authentifizierung erforderlich
		// Wenn es vom Server selbst aufgerufen wird, ist keine Auth. notwendig
		// (z.B. fuer die Erstellung von PDFs)
		if ($_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR']) {
			// Wenn Session gesetzt ist, keine Abfrage, da diese Personen noch keine UID haben
			
			if (isset($_SESSION['incoming/user'])) { // Von Incomingtool
				$result = $this->PersonModel->loadWhere([
					'zugangscode' => $_SESSION['incoming/user']
				]);
				if (hasData($result))
					$person_id_user = current(getData($result))->person_id;
			} elseif (isset($_SESSION['prestudent/user'])) { // Von Prestudententool
				$result = $this->PersonModel->loadWhere([
					'zugangscode' => $_SESSION['prestudent/user']
				]);
				if (hasData($result))
					$person_id_user = current(getData($result))->person_id;
			} elseif (isset($_SESSION['bewerbung/personId'])) { // Von Bewerbungstool
				$person_id_user = $_SESSION['bewerbung/personId'];
			} else {
				$person_id_user = getAuthPersonId();
			}
		} else {
			$serverzugriff = true;
		}

		// Default Bild (Dummy Profilbild)
		$cTmpHEX = base64_encode(file_get_contents(FHCPATH . 'skin/images/profilbild_dummy.jpg'));

		if ($source == 'person' && $id) {
			$foto_gesperrt = false;
			// Person laden und Fotosperre überprüfen
			$result = $this->PersonModel->load($id);
			if (hasData($result)) {
				$person = current(getData($result));
				if ($person->foto_sperre) {
					// Wenn der User selbst darauf zugreift darf er das Bild sehen
					$foto_gesperrt = ($person_id_user != $id);
				} elseif (!$person_id_user && !$serverzugriff) {
					$foto_gesperrt = true;
				}

				if ($person->foto && !$foto_gesperrt) {
					$cTmpHEX = base64_decode($person->foto);
				}
			}
		}
		if($source == 'akte' && $id != '')
		{
			$this->load->model('crm/Akte_model', 'AkteModel');

			$this->AkteModel->addJoin('public.tbl_person', 'person_id');
			$result = $this->AkteModel->loadWhere([
				'person_id' => $id,
				'dokument_kurzbz' => 'Lichtbil'
			]);

			if (hasData($result)) {
				$foto_gesperrt = false;

				$akte = current(getData($result));
				if ($akte->foto_sperre) {
					// Wenn der User selbst darauf zugreift darf er das Bild sehen
					$foto_gesperrt = ($person_id_user != $id);
				} elseif (!$person_id_user && !$serverzugriff) {
					$foto_gesperrt = true;
				}

				// Wenn das Foto nicht im Inhalt steht wird aus aus dem DMS geladen
				if (!$akte->inhalt && $akte->dms_id) {
					$this->load->model('content/Dms_model', 'DmsModel');
					$this->load->model('content/DmsVersion_model', 'DmsVersionModel');

					$this->DmsModel->addJoin('campus.tbl_dms_version', 'dms_id');
					$this->DmsModel->addOrder('version', 'DESC');
					$this->DmsModel->addLimit(1);
					$result = $this->DmsModel->load($akte->dms_id);

					if (!hasData($result))
						die('Kein Dokument vorhanden');

					$dms = current(getData($result));

					$filename = DMS_PATH . $dms->filename;

					$this->DmsVersionModel->update([
						'dms_id' => $dms->dms_id,
						'version' => $dms->version
					], [
						'letzterzugriff' => date('c')
					]);

					if (file_exists($filename)) {
						$handle = fopen($filename, "r");
						if ($handle) {
							while (!feof($handle)) {
								$akte->inhalt .= fread($handle, 8192);
							}
							fclose($handle);
						} else {
							echo 'Fehler: Datei konnte nicht geoeffnet werden';
						}
					} else {
						echo 'Die Datei existiert nicht';
					}
				}

				if ($akte->inhalt && !$foto_gesperrt) {
					$cTmpHEX = $akte->inhalt;
				}
			}
		}

		// die bilder werden, sofern es funktioniert, in jpg umgewandelt da es sonst zu fehlern beim erstellen
		// von pdfs kommen kann.

		$im = @imagecreatefromstring(base64_decode($cTmpHEX));
		if ($im) {
			@ob_clean();
			header("Content-type: image/jpeg");
			exit(imagejpeg($im));
		} else {
			// bei manchen Bildern funktioniert die konvertierung nicht
			// diese werden dann einfach so angezeigt.
			@ob_clean();
			header("Content-type: image/gif");
			exit($cTmpHEX);
		}
	}
}
