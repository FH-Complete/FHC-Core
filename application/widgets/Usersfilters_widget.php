<?php

class Usersfilters_widget extends Widget
{
	// Properties containing parameters given by the view
	private $studiengang;
	private $studiensemester;
	private $gruppe;
	private $reihungstest;
	private $stufe;

	public function __construct($name, $args)
	{
		// Calling daddy
		parent::__construct($name, $args);

		// Loads helper message to manage returning messages
		$this->load->helper('message');

		// Initialising properties
		$this->_setProperties($args);
	}

    public function display($widgetData)
	{
		$errors = array(); // Array containing possible errors

		// Studiengaenge
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->StudiengangModel->resetQuery();
		$this->StudiengangModel->addOrder('kurzbzlang');
		$studiengaenge = $this->StudiengangModel->loadWhere(array('aktiv' => true));
		if (hasData($studiengaenge))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->studiengang_kz = '-1';
			$emptyElement->kurzbzlang = 'Select a studiengang...';
			$emptyElement->bezeichnung = '';
			array_unshift($studiengaenge->retval, $emptyElement);
		}
		else
		{
			$errors[] = $studiengaenge; // Adding the error to the array of errors
		}

		// Studiensemester
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->StudiengangModel->addSelect('studiensemester_kurzbz, studiensemester_kurzbz AS beschreibung');
		$this->StudiengangModel->addOrder('studiensemester_kurzbz', 'DESC');
		$studiensemester = $this->StudiensemesterModel->load();
		if (hasData($studiensemester))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->studiensemester_kurzbz = '-1';
			$emptyElement->beschreibung = 'Select a studiensemester...';
			array_unshift($studiensemester->retval, $emptyElement);
		}
		else
		{
			$errors[] = $studiensemester; // Adding the error to the array of errors
		}

		// Gruppen
		$this->load->model('organisation/Gruppe_model', 'GruppeModel');
		$this->GruppeModel->addOrder('beschreibung');
		$gruppen = $this->GruppeModel->loadWhere(array('aktiv' => true, 'aufnahmegruppe' => true));
		if (hasData($gruppen))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->gruppe_kurzbz = '-1';
			$emptyElement->beschreibung = 'Select a group...';
			array_unshift($gruppen->retval, $emptyElement);
		}
		else
		{
			$errors[] = $gruppen; // Adding the error to the array of errors
		}

		// Stufe
		$this->load->model('crm/Reihungstest_model', 'ReihungstestModel');
		$this->ReihungstestModel->addSelect('DISTINCT ON(stufe) stufe, stufe AS beschreibung');
		$this->ReihungstestModel->addOrder('stufe');
		$stufen = $this->ReihungstestModel->loadWhere('stufe IS NOT NULL');
		if (hasData($stufen))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->stufe = '-1';
			$emptyElement->beschreibung = 'Select a stufe...';
			array_unshift($stufen->retval, $emptyElement);
		}
		else
		{
			$errors[] = $stufen; // Adding the error to the array of errors
		}

		// Reihungstest
		$reihungstest = success(array()); // default value empty array
		// If the parameters studiengang or studiensemester are given and are not empty
		if (($this->studiengang != null && !empty($this->studiengang))
			|| ($this->studiensemester != null && !empty($this->studiensemester)))
		{
			$this->ReihungstestModel->resetQuery(); // cleans any previous setting
			$this->ReihungstestModel->addSelect('reihungstest_id, concat(datum, \' \',  uhrzeit, \' \', anmerkung) AS beschreibung');
			$this->ReihungstestModel->addOrder('datum', 'DESC');

			$parametersArray = array();
			if ($this->studiengang != null)
			{
				$parametersArray['studiengang_kz'] = $this->studiengang;
			}
			if ($this->studiensemester != null)
			{
				$parametersArray['studiensemester_kurzbz'] = $this->studiensemester;
			}

			$reihungstest = $this->ReihungstestModel->loadWhere($parametersArray);
			if (isError($reihungstest))
			{
				$errors[] = $reihungstest; // Adding the error to the array of errors
			}
		}

		if (!isError($reihungstest))
		{
			// Adding an empty element at the beginning
			$emptyElement = new stdClass();
			$emptyElement->reihungstest_id = '-1';
			$emptyElement->beschreibung = 'Select a reihungstest...';
			array_unshift($reihungstest->retval, $emptyElement);
		}

		// Data to be used in the widget view
		$viewData = array(
			'studiengaenge' => $studiengaenge->retval,
			'studiensemester' => $studiensemester->retval,
			'gruppen' => $gruppen->retval,
			'stufen' => $stufen->retval,
			'reihungstest' => $reihungstest->retval,
			'errors' => $errors,
			'selectedStudiengang' => $this->studiengang,
			'selectedStudiensemester' => $this->studiensemester,
			'selectedGruppe' => $this->gruppe,
			'selectedReihungstest' => $this->reihungstest,
			'selectedStufe' => $this->stufe
		);

		// Loads widget view
		$this->view('widgets/usersfilters', $viewData);
    }

    /**
     * Initialising properties
     */
    private function _setProperties($args)
    {
		if (isset($args) && is_array($args))
		{
			if (isset($args['studiengang']) && $args['studiengang'] != '-1')
			{
				$this->studiengang = $args['studiengang'];
			}
			if (isset($args['studiensemester']) && $args['studiensemester'] != '-1')
			{
				$this->studiensemester = $args['studiensemester'];
			}
			if (isset($args['gruppe']) && $args['gruppe'] != '-1')
			{
				$this->gruppe = $args['gruppe'];
			}
			if (isset($args['reihungstest']) && $args['reihungstest'] != '-1')
			{
				$this->reihungstest = $args['reihungstest'];
			}
			if (isset($args['stufe']) && $args['stufe'] != '-1')
			{
				$this->stufe = $args['stufe'];
			}
		}
    }
}