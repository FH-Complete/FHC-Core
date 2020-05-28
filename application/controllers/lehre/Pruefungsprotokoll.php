<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class Pruefungsprotokoll extends Auth_Controller
{
    private $_uid;  // uid of the logged user

    /**
     * Constructor
     */
    public function __construct()
    {
        // Set required permissions
        parent::__construct(
            array(
                'index' => 'lehre/pruefungsbeurteilung:r',
                'Protokoll' => 'lehre/pruefungsbeurteilung:r',
                'saveProtokoll' => 'lehre/pruefungsbeurteilung:rw',
            )
        );

        // Load models
        $this->load->model('education/Abschlusspruefung_model', 'AbschlusspruefungModel');
        $this->load->model('education/Abschlussbeurteilung_model', 'AbschlussbeurteilungModel');

        $this->load->library('PermissionLib');
        $this->load->library('AuthLib');

        // Load language phrases
        $this->loadPhrases(
            array(
            	'ui',
                'global',
                'person',
                'abschlusspruefung',
				'password',
				'lehre'
            )
        );

        $this->_setAuthUID(); // sets property uid

        $this->setControllerId(); // sets the controller id
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
	public function index()
	{
		$this->load->library('WidgetLib');
		$this->load->view('lehre/pruefungsprotokollUebersicht.php');
	}

	/**
	 * Show Pruefungsprotokoll.
	 */
	public function Protokoll()
	{
		$abschlusspruefung_id = $this->input->get('abschlusspruefung_id');

		if (!is_numeric($abschlusspruefung_id))
			show_error('invalid abschlusspruefung');

		$abschlusspruefung_saved = false;
		$abschlusspruefung = $this->_getAbschlusspruefungBerechtigt($abschlusspruefung_id);

		if (isError($abschlusspruefung))
			show_error(getError($abschlusspruefung));
		else
		{
			$abschlusspruefung = getData($abschlusspruefung);
			$abschlusspruefung_saved = isset($abschlusspruefung->protokoll) && isset($abschlusspruefung->abschlussbeurteilung_kurzbz);
		}

		$this->AbschlussbeurteilungModel->addOrder("sort", "ASC");
		$this->AbschlussbeurteilungModel->addOrder("(CASE WHEN abschlussbeurteilung_kurzbz = 'ausgezeichnet' THEN 1
														WHEN abschlussbeurteilung_kurzbz = 'gut' THEN 2
														WHEN abschlussbeurteilung_kurzbz = 'bestanden' THEN 3
														WHEN abschlussbeurteilung_kurzbz = 'angerechnet' THEN 4
														ELSE 5
														END
													)");
		$abschlussbeurteilung = $this->AbschlussbeurteilungModel->load();

		if (isError($abschlussbeurteilung))
			show_error(getError($abschlussbeurteilung));
		else
			$abschlussbeurteilung = getData($abschlussbeurteilung);

		$data = array(
			'abschlusspruefung' => $abschlusspruefung,
			'abschlussbeurteilung' => $abschlussbeurteilung,
			'abschlusspruefung_saved' => $abschlusspruefung_saved
		);

		$this->load->view('lehre/pruefungsprotokoll.php', $data);
	}

	/**
	 * Save Pruefungsprotokoll (including possible Freigabe)
	 */
	public function saveProtokoll()
	{
		$abschlusspruefung_id = $this->input->post('abschlusspruefung_id');
		$data = $this->input->post('protocoldata');

		if (isset($abschlusspruefung_id) && is_numeric($abschlusspruefung_id) && isset($data))
		{
			// check permission
			$berechtigt = $this->_getAbschlusspruefungBerechtigt($abschlusspruefung_id);
			if (isError($berechtigt))
				$this->outputJsonError(getError($berechtigt));
			else
			{
				$freigabe = isset($data['freigabedatum']) && $data['freigabedatum'];

				if ($freigabe)
				{
					// Verify password
					$password = $data['password'];
					unset($data['password']);
					if (!isEmptyString($password))
					{
						$result = $this->authlib->checkUserAuthByUsernamePassword($this->_uid, $password);
						if (isError($result))
						{
							return $this->outputJsonError('Falsches Passwort');    // exit if password is incorrect
						}
					}
					else
					{
						return $this->outputJsonError('Passwort fehlt');
					}
				}

				$data = $this->_prepareAbschlusspruefungDataForSave($data);
				$result = $this->AbschlusspruefungModel->update($abschlusspruefung_id, $data);

				if (hasData($result))
				{
					$abschlusspruefung_id = getData($result);
					$updateresult = array('abschlusspruefung_id' => $abschlusspruefung_id);
					if ($freigabe)
						$updateresult['freigabedatum'] = date_format(date_create($data['freigabedatum']), 'd.m.Y');

					$this->outputJsonSuccess($updateresult);
				}
				else
					$this->outputJsonError('Fehler beim Speichern des Prüfungsprotokolls');
			}
		}
		else
			$this->outputJsonError('Ungültige Parameter');
	}

    // -----------------------------------------------------------------------------------------------------------------
    // Private methods

    /**
     * Retrieve the UID of the logged user and checks if it is valid
     */
    private function _setAuthUID()
    {
        $this->_uid = getAuthUID();

        if (!$this->_uid) show_error('User authentification failed');
    }

	/**
	 * Retrieves an Abschlussprüfung, with permission check
	 * permission: admin, assistance of study programe or Vorsitz of the Prüfung
	 * @param $abschlusspruefung_id
	 * @return object success or error
	 */
    private function _getAbschlusspruefungBerechtigt($abschlusspruefung_id)
	{
		$result = error('Error when getting Abschlusspruefung');

		if (isset($this->_uid))
		{
			$abschlusspruefung = $this->AbschlusspruefungModel->getAbschlusspruefung($abschlusspruefung_id);

			if (hasData($abschlusspruefung))
			{
				$abschlusspruefung_data = getData($abschlusspruefung);
				if ($this->permissionlib->isBerechtigt('admin') ||
					(isset($abschlusspruefung_data->studiengang_kz) && $this->permissionlib->isBerechtigt('assistenz', 'suid', $abschlusspruefung_data->studiengang_kz))
					|| $this->_uid === $abschlusspruefung_data->uid_vorsitz)
					$result = $abschlusspruefung;
				else
					$result = error('Permission denied');
			}
		}

		return $result;
	}

	/**
	 * Prepares Abschlussprüfung for save in database, replaces '' with null, sets Freigabedatum
	 * @param $data
	 * @return array
	 */
	private function _prepareAbschlusspruefungDataForSave($data)
	{
		$nullfields = array('uhrzeit', 'endezeit', 'abschlussbeurteilung_kurzbz', 'protokoll');
		foreach ($data as $idx => $item)
		{
			if (in_array($idx, $nullfields) & $item === '')
				$data[$idx] = null;
		}

		if (isset($data['freigabedatum']) && $data['freigabedatum'])
			$data['freigabedatum'] = date('Y-m-d');

		return $data;
	}
}
