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
                'index' => 'lehre:r',
                'Protokoll' => 'lehre:r'
            )
        );

        // Load models
        $this->load->model('education/Abschlusspruefung_model', 'AbschlusspruefungModel');
        $this->load->model('education/Abschlussbeurteilung_model', 'AbschlussbeurteilungModel');

        // Load language phrases
        $this->loadPhrases(
            array(
                'abschlusspruefung',
				'global',
				'person',
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
	 */
	public function Protokoll()
	{
		$abschlusspruefung_id = $this->input->get('abschlusspruefung_id');

		if (!is_numeric($abschlusspruefung_id))
			show_error('invalid abschlusspruefung');

		$abschlusspruefung = $this->AbschlusspruefungModel->getAbschlusspruefung($abschlusspruefung_id);

		if (isError($abschlusspruefung))
			show_error(getError($abschlusspruefung));
		else
			$abschlusspruefung = getData($abschlusspruefung);

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
			'abschlussbeurteilung' => $abschlussbeurteilung
		);

		$this->load->view('lehre/pruefungsprotokoll.php', $data);
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
}
