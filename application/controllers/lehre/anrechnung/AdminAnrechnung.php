<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class AdminAnrechnung extends Auth_Controller
{
	public function __construct()
	{
		// Set required permissions
		parent::__construct(
			array(
				'index'     => 'lehre/anrechnungszeitfenster:rw',
				'save'      => 'lehre/anrechnungszeitfenster:rw',
				'edit'      => 'lehre/anrechnungszeitfenster:rw',
				'delete'    => 'lehre/anrechnungszeitfenster:rw'
			)
		);
		
		// Load models
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
		$this->load->model('education/Anrechnungszeitraum_model', 'AnrechnungszeitraumModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		
		// Load libraries
		$this->load->library('WidgetLib');
		$this->load->library('PermissionLib');
		$this->load->library('AnrechnungLib');

		
		// Load language phrases
		$this->loadPhrases(
			array(
				'global',
				'ui',
                'lehre',
                'anrechnung',
                'table'
			)
		);

		$this->_setAuthUID();
		
		$this->setControllerId();
	}
	
	public function index()
	{
        // Set nearest Studiensemester as default
        $result = $this->StudiensemesterModel->getNearest();
        $studiensemester_kurzbz = hasData($result) ? getData($result)[0]->studiensemester_kurzbz : '';

        // Get existing Anrechnungszeitraeume
        $this->AnrechnungszeitraumModel->addOrder('anrechnungszeitraum_id', 'DESC');
        $result = $this->AnrechnungszeitraumModel->load();
        $anrechnungszeitraum_arr = hasData($result) ? getData($result) : array();

		$viewData = array(
            'studiensemester_kurzbz' => $studiensemester_kurzbz,
            'anrechnungszeitraum_arr' => $anrechnungszeitraum_arr
        );
		
		$this->load->view('lehre/anrechnung/adminAnrechnung.php', $viewData);
	}

    /**
     * Save new Anrechnungszeitraum.
     */
    public function save()
    {
        $this->_validate($this->input->post());

        $studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
        $anrechnungstart = $this->input->post('anrechnungstart');
        $anrechnungende = $this->input->post('anrechnungende');

        $result = $this->AnrechnungszeitraumModel->insertAzr($studiensemester_kurzbz, $anrechnungstart, $anrechnungende);

        if (isError($result))
        {
            $this->terminateWithJsonError(getError($result));
        }

        if (hasData($result))
        {
            $this->outputJsonSuccess(array('anrechnungszeitraum_id' => getData($result)));
        }
    }

    /**
     * Edit Anrechnungszeitraum.
     */
    public function edit()
    {
        $this->_validate($this->input->post());

        $anrechnungszeitraum_id = $this->input->post('anrechnungszeitraum_id');
        $studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
        $anrechnungstart = $this->input->post('anrechnungstart');
        $anrechnungende = $this->input->post('anrechnungende');
        
        $result = $this->AnrechnungszeitraumModel->updateAzr(
            $anrechnungszeitraum_id,
            $studiensemester_kurzbz,
            $anrechnungstart,
            $anrechnungende
        );

        if (isError($result))
        {
            $this->terminateWithJsonError(getError($result));
        }

        if (hasData($result))
        {
            $this->outputJsonSuccess(array('anrechnungszeitraum_id' => getData($result)));
        }
    }

    /**
     * Delete Anrechnungszeitraum.
     */
    public function delete()
    {
        $anrechnungszeitraum_id = $this->input->post('anrechnungszeitraum_id');

        $result = $this->AnrechnungszeitraumModel->deleteAzr($anrechnungszeitraum_id);

        if (isError($result))
        {
            $this->terminateWithJsonError(getError($result));
        }

        if (hasData($result))
        {
            $this->outputJsonSuccess(array('anrechnungszeitraum_id' => getData($result)));
        }
    }

    /**
     * Validates post parameters.
     *
     * @param $post
     */
    private function _validate($post)
    {
        $studiensemester_kurzbz = $post['studiensemester_kurzbz'];
        $anrechnungstart = $post['anrechnungstart'];
        $anrechnungende = $post['anrechnungende'];

        if (isEmptyString($studiensemester_kurzbz)
            || isEmptyString($anrechnungstart)
            || isEmptyString($anrechnungende))
        {
            $this->terminateWithJsonError($this->p->t('ui', 'errorFelderFehlen'));
        }

        if ($anrechnungstart > $anrechnungende)
        {
            $this->terminateWithJsonError($this->p->t('ui', 'errorStartdatumNachEndedatum'));
        }

        $result = $this->StudiensemesterModel->load($studiensemester_kurzbz);
        $studiensemester = getData($result)[0];

        if ($anrechnungstart < $studiensemester->start || $anrechnungstart > $studiensemester->ende)
        {
            $this->terminateWithJsonError($this->p->t('ui', 'errorStartdatumNichtInStudiensemester'));
        }

        if ($anrechnungende < $studiensemester->start || $anrechnungende > $studiensemester->ende)
        {
            $this->terminateWithJsonError($this->p->t('ui', 'errorEndedatumNichtInStudiensemester'));
        }


    }

    /**
     * Retrieve the UID of the logged user and checks if it is valid
     */
    private function _setAuthUID()
    {
        $this->_uid = getAuthUID();

        if (!$this->_uid) show_error('User authentification failed');
    }
}