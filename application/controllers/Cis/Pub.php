<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 *
 */
class Pub extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
		    array(
			'bild' => ['basis/cis:r']
		    )
		);
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
		$person_id_user = getAuthPersonId();
		$serverzugriff = false;
		// Default Bild (Dummy Profilbild)
		$outputFileName = FHCPATH . 'skin/images/profilbild_dummy.jpg';
		$outputFileContent = '';
		$mimetype = 'application/jpeg';

		$this->load->model('person/Person_model', 'PersonModel');

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
					$person_id_user = getData($result)[0]->person_id;
			} elseif (isset($_SESSION['prestudent/user'])) { // Von Prestudententool
				$result = $this->PersonModel->loadWhere([
					'zugangscode' => $_SESSION['prestudent/user']
				]);
				if (hasData($result))
					$person_id_user = getData($result)[0]->person_id;
			} elseif (isset($_SESSION['bewerbung/personId'])) { // Von Bewerbungstool
				$person_id_user = $_SESSION['bewerbung/personId'];
			}
		} else {
			$serverzugriff = true;
		}

		// If the picture is from the person table
		if ($source == 'person' && is_numeric($id)) {
			$foto_gesperrt = false;
			// Person laden und Fotosperre überprüfen
			$result = $this->PersonModel->load($id);
			if (hasData($result)) {
				$person = getData($result)[0];
				if ($person->foto_sperre) {
					// Wenn der User selbst darauf zugreift darf er das Bild sehen
					$foto_gesperrt = ($person_id_user != $id);
				} elseif (!$person_id_user && !$serverzugriff) {
					$foto_gesperrt = true;
				}

				if (!isEmptyString($person->foto) && !$foto_gesperrt) {
					$outputFileContent = base64_decode($person->foto);
				}
			}
		}

		// If the picture is from the akte/dms
		if($source == 'akte' && is_numeric($id))
		{
			$mimetype = '';
			$this->load->library('AkteLib');

			$akteResult = $this->aktelib->getByAkteId($id, 'Lichtbil');

			if (hasData($akteResult)) {
				$foto_gesperrt = false;
				$akte = getData($akteResult)[0];

				$personResult = $this->PersonModel->load($akte->person_id);
				if (hasData($personResult)) {
					$person = getData($personResult)[0];
					if ($person->foto_sperre) {
						$foto_gesperrt = ($person_id_user != $id);
					} elseif (!$person_id_user && !$serverzugriff) {
						$foto_gesperrt = true;
					}
				}

				// Wenn das Foto nicht im Inhalt steht wird aus aus dem DMS geladen
				if (!isEmptyString($akte->inhalt)) {
					$outputFileContent = base64_decode($akte->inhalt);
					$mimetype = $akte->mimetype;
				}
			}
		}

		// If file content is provided
		if (!isEmptyString($outputFileContent))
		{
			// If a mimetype is still not found
			if (isEmptyString($mimetype))
			{
				$fo = finfo_open();
				$mimetype = finfo_buffer($fo, $outputFileContent, FILEINFO_MIME_TYPE);
			}
			$this->outputImageByContent($mimetype, $outputFileContent);
		}
		else // otherwise use the file
		{
			$this->outputImageByFile($mimetype, $outputFileName);
		}
	}
}

